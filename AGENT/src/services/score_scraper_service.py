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
from playwright.sync_api import TimeoutError as PlaywrightTimeoutError, sync_playwright, Page

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

# The walk-in result table is always present on the page and also contains
# an "Obtained Marks" column. Every result lookup must exclude it, otherwise
# a student with no matching record can silently receive a walk-in score.
_SEL_RESULT_TABLE    = "table:not(.walkintbl)"

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


def _submit_form(
    page: Page,
    roll_number: str,
    cnic: str,
    reload_page: bool = False,
) -> None:
    """
    Navigate to the SEET results page, fill in the search form, and submit.

    Args:
        page        : Active Playwright page object.
        roll_number : Student's roll number string.
        cnic        : Student's CNIC string.
    """
    if reload_page:
        page.reload(timeout=60_000, wait_until="domcontentloaded")
    else:
        page.goto(RESULTS_URL, timeout=60_000, wait_until="domcontentloaded")

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

    # The result is rendered server-side. Wait for the actual applicant
    # result row instead of sleeping for a fixed 15 seconds: this is faster
    # and does not break when the portal responds slowly.
    page.wait_for_load_state("domcontentloaded")
    page.wait_for_selector(f"{_SEL_RESULT_TABLE} tbody tr", timeout=15_000)


def _extract_score(page: Page, roll_number: str, cnic: str = "") -> float:
    """
    Read the obtained marks from the applicant result table.

    Checks for the "No Record" message first (Edge Case 1). Then locates the
    applicant result table (never the always-present walk-in table), finds
    the "Obtained Marks" column by header name, and reads the row whose roll
    number and CNIC match the requested student.

    Args:
        page        : Active Playwright page object (post-submit state).
        roll_number : Student's SEET roll number, used to select the row.
        cnic        : Student's CNIC, used to verify the row (optional).

    Returns:
        float: Parsed obtained marks, or 0.0 if the portal reports no result.

    Raises:
        ValueError: If the result table or obtained marks value cannot be parsed.
    """
    # Edge Case 1 — Portal explicitly reports no match
    not_found = page.locator(_SEL_NOT_FOUND_MSG)
    if not_found.count() and not_found.first.is_visible():
        print(
            f"    [!] No record found on portal for roll number '{roll_number}'. "
            "Score left as 0.0."
        )
        return DEFAULT_TEST_SCORE

    # Scope every lookup to the applicant result table. The walk-in table
    # at the bottom of the page also has an "Obtained Marks" header.
    result_table = page.locator(_SEL_RESULT_TABLE).filter(
        has=page.locator("th", has_text="Obtained Marks")
    ).first
    if result_table.count() == 0:
        raise ValueError("The applicant result table was not rendered.")

    headers = [
        " ".join(header.split()).casefold()
        for header in result_table.locator("thead th").all_inner_texts()
    ]

    def column_index(*needles: str) -> int | None:
        for position, header in enumerate(headers):
            if any(needle in header for needle in needles):
                return position
        return None

    obtained_index = column_index("obtained marks")
    roll_index     = column_index("rollno", "roll no", "roll#")
    cnic_index     = column_index("cnic")
    status_index   = column_index("status")

    if obtained_index is None:
        raise ValueError(
            f"'Obtained Marks' column not found in result table headers: {headers}"
        )

    requested_roll = str(roll_number).strip()
    requested_cnic = re.sub(r"\D", "", str(cnic))

    rows = result_table.locator("tbody tr")
    for position in range(rows.count()):
        cells = [
            " ".join(cell.split())
            for cell in rows.nth(position).locator("td").all_inner_texts()
        ]
        if len(cells) <= obtained_index:
            continue  # "No Record found" row or a malformed row

        # Verify the roll number (trailing numeric part, e.g. ...-78).
        if roll_index is not None and roll_index < len(cells):
            row_roll_match = re.search(r"(\d+)\s*$", cells[roll_index])
            if row_roll_match and requested_roll.isdigit():
                if int(row_roll_match.group(1)) != int(requested_roll):
                    continue
            elif requested_roll and requested_roll.casefold() not in cells[roll_index].casefold():
                continue

        # Verify the CNIC when both sides provide one.
        if cnic_index is not None and cnic_index < len(cells):
            row_cnic = re.sub(r"\D", "", cells[cnic_index])
            if requested_cnic and row_cnic and row_cnic != requested_cnic:
                continue

        score_match = re.search(r"\d+(?:\.\d+)?", cells[obtained_index])
        if score_match is None:
            continue

        score = float(score_match.group())
        status_text = (
            cells[status_index]
            if status_index is not None and status_index < len(cells)
            else ""
        )
        print(
            f"    [OK] Parsed obtained marks: {score}"
            + (f" (status: {status_text})" if status_text else "")
        )
        return score

    raise ValueError(
        f"No result row matched roll number '{roll_number}'"
        + (f" and CNIC '{cnic}'" if cnic else "")
        + "."
    )


# ---------------------------------------------------------------------------
# Public orchestrator
# ---------------------------------------------------------------------------

def run_score_scraper(master_file: str = EXCEL_FILENAME, headless: bool = True) -> dict:
    """
    Full Phase 3 pipeline: scrape SEET entry-test scores for eligible students.

    Steps
    -----
    1. Load the master admissions.xlsx into a DataFrame.
    2. Validate that all required columns are present.
    3. Filter for students who are Eligible but have no test score yet.
    4. Skip students who are missing a roll_number or CNIC (log + score = 0.0).
    5. Launch Chromium via Playwright (headless or visible based on parameter).
    6. For each scrapeable student:
       a. Submit the SEET portal search form.
       b. Extract the Obtained Marks from the result table.
       c. Append {"student_id": ..., "entry_test_score": ...} to updates[].
    7. Close the browser.
    8. Persist all updates to the master workbook in one atomic write.

    Args:
        master_file (str): Path to the master Excel file.
                           Defaults to config.EXCEL_FILENAME.
        headless (bool): Whether to run Chromium in headless mode.
                         Defaults to True.

    Returns:
        dict: Summary with ``pending``, ``scraped``, ``failed``, ``skipped``,
              ``under_review`` and ``failures`` (per-student error reasons).

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
    under_review = int((df["application_status"] == "Under Review").sum())
    print(f"  [2/4] {total_pending} eligible student(s) with no entry-test score.\n")

    if total_pending == 0:
        if under_review:
            print(
                f"  [!] {under_review} student(s) are still 'Under Review'. "
                "Phase 3 only scrapes 'Eligible' students.\n"
                "      Run Phase 2 (Validator) first, then retry the scraper.\n"
            )
        else:
            print("  [!] Nothing to scrape. Exiting.\n")
        return {
            "pending": 0,
            "scraped": 0,
            "failed": 0,
            "skipped": 0,
            "under_review": under_review,
            "failures": [],
        }

    # ------------------------------------------------------------------
    # Step 3 — Separate students by credential availability
    # ------------------------------------------------------------------
    updates: list[dict] = []
    scrapeable: list[tuple[str, str, str]] = []  # (student_id, roll_number, cnic)
    scraped_count = 0
    failed_count = 0
    skipped_count = 0
    failures: list[str] = []

    for _, row in needs_scrape.iterrows():
        student_id  = str(row["student_id"]).strip()
        roll_number = _clean_credential(row["seet_roll_number"], DEFAULT_ROLL_NUMBER)
        cnic        = _clean_credential(row["cnic"],        DEFAULT_CNIC)

        if roll_number == DEFAULT_ROLL_NUMBER or cnic == DEFAULT_CNIC:
            # Cannot scrape without valid credentials — log and keep score at 0.0
            skipped_count += 1
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
        print(f"  [3/4] Launching Chromium for {len(scrapeable)} student(s)...\n")

        with sync_playwright() as pw:
            browser = pw.chromium.launch(headless=headless, slow_mo=500)
            try:
                page = browser.new_page()

                for student_id, roll_number, cnic in scrapeable:
                    print(f"  [-] Scraping: {student_id}  |  Roll: {roll_number}  |  CNIC: {cnic}")
                    try:
                        for attempt in range(2):
                            try:
                                _submit_form(
                                    page,
                                    roll_number,
                                    cnic,
                                    reload_page=attempt > 0,
                                )
                                score = _extract_score(page, roll_number, cnic)
                                break
                            except PlaywrightTimeoutError:
                                if attempt == 1:
                                    raise
                                print(
                                    "    [!] Timed out while loading or extracting "
                                    "the result; retrying in 3 seconds."
                                )
                                page.wait_for_timeout(3_000)
                        print(f"    [OK] Score obtained: {score}")
                        scraped_count += 1
                        updates.append({
                            "student_id":       student_id,
                            "entry_test_score":  score,
                        })
                    except Exception as exc:
                        # Isolate per-student failures — never crash the batch
                        failed_count += 1
                        failures.append(f"{student_id}: {exc}")
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

    return {
        "pending": total_pending,
        "scraped": scraped_count,
        "failed": failed_count,
        "skipped": skipped_count,
        "under_review": under_review,
        "failures": failures,
    }
