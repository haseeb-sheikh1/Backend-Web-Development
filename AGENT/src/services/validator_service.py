"""
validator_service.py
--------------------
Orchestrator for the Validator Agent (Phase 2 of the admissions pipeline).

This module acts as the bridge between:
  - document_reader   — reads admissions_policy.txt
  - excel_repository  — fetches Under Review records and updates the master
  - extractor         — calls the LLM for each student's eligibility decision

It owns the complete validation workflow so neither the CLI nor the API
needs to know about the individual steps.

Public API
----------
  run_validation(policy_file, master_file)
      Full end-to-end run: read policy → fetch students → evaluate each →
      update their status and validation notes in the master workbook.
"""

import json

from src.config import EXCEL_FILENAME
from src.interfaces.document_reader import read_unstructured_file
from src.repositories.excel_repository import (
    get_under_review_records,
    update_students_in_master,
)
from src.llm.extractor import evaluate_student_eligibility
from src.interfaces.policy_reader import get_section_text

# Default file paths (overridable via function args)
DEFAULT_POLICY_FILE  = "data/admissions_policy.txt"


# ---------------------------------------------------------------------------
# Public orchestrator
# ---------------------------------------------------------------------------

def run_validation(
    policy_file:  str = DEFAULT_POLICY_FILE,
    master_file:  str = EXCEL_FILENAME,
) -> list[dict]:
    """
    Run the full Validator Agent pipeline.

    Steps
    -----
    1. Read the admissions policy document (via document_reader).
    2. Fetch all "Under Review" student records from the master Excel file
       (via excel_repository.get_under_review_records).
    3. For each student, call the LLM to evaluate eligibility
       (via extractor.evaluate_student_eligibility).
    4. Collect the new statuses and notes and update the master workbook
       in one write (via excel_repository.update_students_in_master).
    5. Return the list of per-student results for caller display.

    Args:
        policy_file (str): Path to the plain-text admissions policy document.
                           Defaults to 'admissions_policy.txt'.
        master_file (str): Path to the master Excel admissions workbook.
                           Defaults to config.EXCEL_FILENAME.
    Returns:
        list[dict]: One result dict per evaluated student, each containing:
                      - All original student fields
                      - ``application_status`` : "Eligible" | "Rejected"
                      - ``eligibility_reason`` : LLM explanation string

    Raises:
        FileNotFoundError: If policy_file or master_file does not exist.
        ValueError:        If policy_file is empty.
        Exception:         Propagated from extractor or excel_repository.
    """
    # ------------------------------------------------------------------
    # Step 1 — Load admissions policy
    # ------------------------------------------------------------------
    print("\n" + "=" * 60)
    print("  [*] Validator Agent — Starting Eligibility Run")
    print("=" * 60)

    print(f"\n  [1/4] Reading policy document: '{policy_file}'")

    # Use only the [ELIGIBILITY] section so [MERIT] rules don't confuse the LLM
    policy_text = get_section_text("ELIGIBILITY", policy_file)

    # Fall back to reading the whole file if the section header isn't present
    # (backwards-compatible with older policy files that have no section tags)
    if not policy_text.strip():
        policy_text = read_unstructured_file(policy_file)

    if not policy_text.strip():
        raise ValueError(
            f"Policy file '{policy_file}' is empty. "
            "Populate it with admission rules before running validation."
        )
    print(f"  [OK] Policy loaded ({len(policy_text)} characters).\n")

    # ------------------------------------------------------------------
    # Step 2 — Fetch students pending evaluation
    # ------------------------------------------------------------------
    print(f"  [2/4] Fetching 'Under Review' students from '{master_file}'")
    students = get_under_review_records(filename=master_file)

    if not students:
        print("  [!] No 'Under Review' students found. Nothing to validate.\n")
        return []

    print(f"  [OK] {len(students)} student(s) will be evaluated.\n")

    # ------------------------------------------------------------------
    # Step 3 — Evaluate each student via LLM
    # ------------------------------------------------------------------
    print(f"  [3/4] Evaluating each student against the policy...\n")

    results: list[dict] = []
    student_updates: list[dict] = []

    for student in students:
        try:
            decision = evaluate_student_eligibility(student, policy_text)

            new_status = decision["status"]   # "Eligible" | "Rejected"
            reasoning  = decision["reasoning"]

            print(f"       → Decision: {new_status}")
            print(f"         Reason  : {reasoning}\n")

            student_updates.append({
                "student_id":         student["student_id"],
                "application_status":  new_status,
                "eligibility_reason":  reasoning,
            })

            # Build the enriched result dict for the caller / CLI display
            results.append({
                **student,
                "application_status":  new_status,
                "eligibility_reason":  reasoning,
            })

        except json.JSONDecodeError:
            name = student.get("name", "?")
            print(f"  [ERROR] LLM returned invalid JSON for '{name}'. Skipping.\n")
        except Exception as exc:
            name = student.get("name", "?")
            print(f"  [ERROR] Could not evaluate '{name}': {exc}\n")

    # ------------------------------------------------------------------
    # Step 4 — Update master workbook with statuses and validation notes
    # ------------------------------------------------------------------
    print(f"  [4/4] Writing status and notes to '{master_file}'")
    update_students_in_master(
        updates=student_updates,
        filename=master_file,
    )

    # ------------------------------------------------------------------
    # Summary
    # ------------------------------------------------------------------
    eligible_count = sum(1 for r in results if r["application_status"] == "Eligible")
    rejected_count = len(results) - eligible_count

    print("=" * 60)
    print("  [*] Validation Complete")
    print(f"      Evaluated : {len(results)}")
    print(f"      Eligible  : {eligible_count}")
    print(f"      Rejected  : {rejected_count}")
    print("=" * 60 + "\n")

    return results
