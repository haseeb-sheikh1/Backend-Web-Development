"""
score_scraper_service.py
------------------------
Phase 3 of the admissions pipeline — Entry-Test Score Scraper.

Fetches SEET entry-test scores for every student who is:
  - application_status == "Eligible"
  - entry_test_score   == 0.0  (or NaN / missing)

Results are written back to the master admissions.xlsx in a single
atomic batch update via excel_repository.update_students_in_master().

Public API
----------
  run_score_scraper()
  Load master file → filter eligible students → launch visible
      Chromium → submit the SEET portal form for each student →
      extract score → persist all updates in one write.
"""

import pandas as pd
import re
from playwright.sync_api import sync_playwright, Page

from src.config import (
    DEFAULT_CNIC,
    DEFAULT_ROLL_NUMBER,
    DEFAULT_TEST_SCORE,
    EXCEL_FILENAME,
)
from src.repositories.excel_repository import load_excel_file, update_students_in_master


# ---------------------------------------------------------------------------
# Constants
# ---------------------------------------------------------------------------

RESULTS_URL = "https://seet.must.edu.pk/seet_result.php"

# Selector map — update here if the portal's markup changes
_SEL_ADMISSION_PHASE = 'select[name="jadmid"]'
_SEL_UNIVERSITY      = 'select[name="tcenter_id"]'
_SEL_ROLL_NUMBER     = "#rollno"
_SEL_CNIC            = "#cnic_2"
_SEL_SUBMIT          = 'button[formaction="seet_result.php"]'
_SEL_NOT_FOUND_MSG   = 'text="No Record found against the request!"'

# Expected label values
_PHASE_LABEL      = "SEET26-P1-"
_UNIVERSITY_LABEL = "UAJK"

# ---------------------------------------------------------------------------
# Private helpers
# ---------------------------------------------------------------------------

def _clean_credential(value: object, default: str) -> str:
    """
    Convert a raw Excel cell value into a usable credential string.

    Rules:
      - None / NaN            → default
      - Empty or whitespace   → default
      - Matches default text  → default  (case-insensitive)
      - Numeric float artefact (e.g. "35201.0") → strip trailing ".0"

    Args:
        value   : Raw cell value from the DataFrame.
        default : The sentinel string to fall back to (DEFAULT_ROLL_NUMBER
                  or DEFAULT_CNIC from config).

    Returns:
        str: Cleaned credential, or ``default`` if unusable.
    """
    if pd.isna(value):
        return default

    credential = str(value).strip()

    if not credential or credential.casefold() == default.casefold():
        return default

    # Strip float artefact that Pandas adds to integer-like values
    if credential.endswith(".0") and credential[:-2].isdigit():
        credential = credential[:-2]

    return credential


def _submit_form(page: Page, roll_number: str, cnic: str) -> None:
    """
    Navigate to the SEET results page, fill in the search form, and submit.

    Args:
        page        : Active Playwright page object.
        roll_number : Student's roll number string.
        cnic        : Student's CNIC string.
    """
    page.goto(RESULTS_URL, wait_until="domcontentloaded")

    # Select admission phase — gracefully ignore if label is absent or renamed
    try:
        page.locator(_SEL_ADMISSION_PHASE).select_option(label=_PHASE_LABEL)
    except Exception as exc:
        print(
            f"    [~] Could not select admission phase '{_PHASE_LABEL}' "
            f"(portal may have changed): {exc}"
        )

    # University, roll number and CNIC are mandatory
    page.locator(_SEL_UNIVERSITY).select_option(label=_UNIVERSITY_LABEL)
    page.locator(_SEL_ROLL_NUMBER).fill(str(roll_number))
    page.locator(_SEL_CNIC).fill(str(cnic))

    page.locator(_SEL_SUBMIT).click()
    page.wait_for_timeout(15000)


def _extract_score(page: Page, roll_number: str) -> float:
    """
    Read the score from rendered result-row text after a successful submit.

    Checks for the "no record" message first (Edge Case 1).
    Then locates the row below the "Obtained Marks" header and parses the
    score from the trailing total-marks, obtained-marks, and status values.

    Args:
        page        : Active Playwright page object (post-submit state).
        roll_number : Used only for log messages.

    Returns:
        float: Parsed obtained marks, or 0.0 if the portal reports no result.

    Raises:
        ValueError: If the result table or obtained marks value cannot be parsed.
    """
    # Edge Case 1 — Portal explicitly reports no match
    if page.locator(_SEL_NOT_FOUND_MSG).is_visible():
        print(
            f"    [!] No record found on portal for roll number '{roll_number}'. "
            "Score left as 0.0."
        )
        return DEFAULT_TEST_SCORE

    # Read rows as rendered text so extraction does not depend on td/th markup
    # or a fixed child index.
    rows = page.locator("table tr").all_inner_texts()
    if not rows:
        raise ValueError("The result table contains no readable rows.")

    header_index = next(
        (
            index
            for index, row_text in enumerate(rows)
            if "obtained marks" in " ".join(row_text.casefold().split())
        ),
        -1,
    )
    if header_index < 0:
        raise ValueError("Could not find the 'Obtained Marks' table header.")

    for row_text in rows[header_index + 1:]:
        print("[DEBUG] Found Row Text:", row_text)
        normalized_row = " ".join(row_text.split())
        match = re.search(
            r"(?<!\S)(\d+(?:\.\d+)?)\s+(\d+(?:\.\d+)?)\s+\S+\s*$",
            normalized_row,
        )
        if match:
            score = float(match.group(2))
            print(f"    [OK] Parsed obtained marks: {score}")
            return score

    raise ValueError(
        f"Could not parse obtained marks from result rows: {rows[header_index + 1:]}"
    )


# ---------------------------------------------------------------------------
# Public orchestrator
# ---------------------------------------------------------------------------

def run_score_scraper(master_file: str = EXCEL_FILENAME) -> None:
    """
    Full Phase 3 pipeline: scrape SEET entry-test scores for eligible students.

    Steps
    -----
    1. Load the master admissions.xlsx into a DataFrame.
    2. Validate that all required columns are present.
    3. Filter for students who are Eligible but have no test score yet.
    4. Skip students who are missing a roll_number or CNIC (log + score = 0.0).
    5. Launch visible Chromium via Playwright for interactive debugging.
    6. For each scrapeable student:
       a. Submit the SEET portal search form.
       b. Extract the Obtained Marks from the result table.
       c. Append {"student_id": ..., "entry_test_score": ...} to updates[].
    7. Close the browser.
    8. Persist all updates to the master workbook in one atomic write.

    Args:
        master_file (str): Path to the master Excel file.
                           Defaults to config.EXCEL_FILENAME.

    Raises:
        FileNotFoundError : If master_file does not exist.
        ValueError        : If the master file is missing required columns.
    """
    print("\n" + "=" * 60)
    print("  [*] Score Scraper — Phase 3 Starting")
    print("=" * 60)

    # ------------------------------------------------------------------
    # Step 1 — Load master workbook
    # ------------------------------------------------------------------
    print(f"\n  [1/4] Loading master file: '{master_file}'")
    df = load_excel_file(master_file)

    required_columns = {"student_id", "application_status", "entry_test_score",
                        "seet_roll_number", "cnic"}
    missing = required_columns.difference(df.columns)
    if missing:
        raise ValueError(
            "Master workbook is missing required column(s): "
            + ", ".join(sorted(missing))
        )

    # ------------------------------------------------------------------
    # Step 2 — Filter: Eligible students with no score yet
    # ------------------------------------------------------------------
    score_numeric = pd.to_numeric(df["entry_test_score"], errors="coerce")
    needs_scrape = df[
        (df["application_status"] == "Eligible")
        & (score_numeric.fillna(DEFAULT_TEST_SCORE) == DEFAULT_TEST_SCORE)
    ]

    total_pending = len(needs_scrape)
    print(f"  [2/4] {total_pending} eligible student(s) with no entry-test score.\n")

    if total_pending == 0:
        print("  [!] Nothing to scrape. Exiting.\n")
        return

    # ------------------------------------------------------------------
    # Step 3 — Separate students by credential availability
    # ------------------------------------------------------------------
    updates: list[dict] = []
    scrapeable: list[tuple[str, str, str]] = []  # (student_id, roll_number, cnic)

    for _, row in needs_scrape.iterrows():
        student_id  = str(row["student_id"]).strip()
        roll_number = _clean_credential(row["seet_roll_number"], DEFAULT_ROLL_NUMBER)
        cnic        = _clean_credential(row["cnic"],        DEFAULT_CNIC)

        if roll_number == DEFAULT_ROLL_NUMBER or cnic == DEFAULT_CNIC:
            # Cannot scrape without valid credentials — log and keep score at 0.0
            print(
                f"  [SKIP] Student '{student_id}': missing SEET roll number or CNIC. "
                "Score left as 0.0."
            )
            updates.append({
                "student_id":      student_id,
                "entry_test_score": DEFAULT_TEST_SCORE,
            })
        else:
            scrapeable.append((student_id, roll_number, cnic))

    # ------------------------------------------------------------------
    # Step 4 — Launch browser and scrape each student
    # ------------------------------------------------------------------
    if scrapeable:
        print(f"  [3/4] Launching visible Chromium for {len(scrapeable)} student(s)...\n")

        with sync_playwright() as pw:
            browser = pw.chromium.launch(headless=False)
            try:
                page = browser.new_page()

                for student_id, roll_number, cnic in scrapeable:
                    print(f"  [-] Scraping: {student_id}  |  Roll: {roll_number}  |  CNIC: {cnic}")
                    try:
                        _submit_form(page, roll_number, cnic)
                        score = _extract_score(page, roll_number)
                        print(f"    [OK] Score obtained: {score}")
                        updates.append({
                            "student_id":       student_id,
                            "entry_test_score":  score,
                        })
                    except Exception as exc:
                        # Isolate per-student failures — never crash the batch
                        print(
                            f"    [ERROR] Failed to scrape '{student_id}': {exc}. "
                            "Score left as 0.0."
                        )
                        try:
                            print(
                                "[DEBUG] Page text at failure:",
                                page.inner_text("body")[:500],
                            )
                        except Exception as page_exc:
                            print(
                                f"[DEBUG] Could not read page text: {page_exc}"
                            )
                        updates.append({
                            "student_id":       student_id,
                            "entry_test_score":  DEFAULT_TEST_SCORE,
                        })
            finally:
                browser.close()
                print("\n  [OK] Browser closed.")
    else:
        print("  [3/4] No students with valid credentials to scrape.")

    # ------------------------------------------------------------------
    # Step 5 — Persist all updates to the master workbook
    # ------------------------------------------------------------------
    print(f"\n  [4/4] Writing {len(updates)} score update(s) to '{master_file}'...")
    update_students_in_master(updates=updates, filename=master_file)

    print("=" * 60)
    print("  [*] Score Scraper — Phase 3 Complete")
    print("=" * 60 + "\n")
