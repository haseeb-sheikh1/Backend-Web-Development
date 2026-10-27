"""
merit_service.py
----------------
Calculates final merit and admission status for scored eligible students.
"""

import pandas as pd

from src.config import (
    ACADEMIC_WEIGHT,
    ENTRY_TEST_WEIGHT,
    EXCEL_FILENAME,
    MINIMUM_FINAL_MERIT,
)
from src.repositories.excel_repository import load_excel_file, update_students_in_master


def _validate_merit_settings(
    entry_test_weight: float,
    inter_weight: float,
    passing_threshold: float,
) -> None:
    """Reject weight/threshold combinations that cannot produce a valid merit."""
    if not 0.0 <= entry_test_weight <= 100.0:
        raise ValueError("Entry-test weight must be between 0 and 100.")
    if not 0.0 <= inter_weight <= 100.0:
        raise ValueError("Intermediate-marks weight must be between 0 and 100.")
    if abs(entry_test_weight + inter_weight - 100.0) > 1e-6:
        raise ValueError(
            "Entry-test and intermediate weights must add up to 100 "
            f"(got {entry_test_weight:g} + {inter_weight:g})."
        )
    if not 0.0 <= passing_threshold <= 100.0:
        raise ValueError("Passing threshold must be between 0 and 100.")


def run_merit_calculation(
    entry_test_weight: float | None = None,
    inter_weight: float | None = None,
    passing_threshold: float | None = None,
) -> dict:
    """Calculate merit for scored eligible students and update the master file.

    Args:
        entry_test_weight: Percentage of the final merit taken from the
            entry-test score (0-100). Defaults to ``ENTRY_TEST_WEIGHT`` * 100.
        inter_weight: Percentage taken from intermediate marks (0-100).
            Must add up to 100 with ``entry_test_weight``. Defaults to
            ``ACADEMIC_WEIGHT`` * 100.
        passing_threshold: Minimum final merit required for "Admitted".
            Defaults to ``MINIMUM_FINAL_MERIT``.

    Returns:
        dict: ``calculated``, ``admitted`` and ``waitlisted`` counts.
    """
    if entry_test_weight is None:
        entry_test_weight = ENTRY_TEST_WEIGHT * 100.0
    if inter_weight is None:
        inter_weight = ACADEMIC_WEIGHT * 100.0
    if passing_threshold is None:
        passing_threshold = MINIMUM_FINAL_MERIT

    _validate_merit_settings(entry_test_weight, inter_weight, passing_threshold)

    print("\n" + "=" * 60)
    print("  [*] Merit Calculation — Phase 4")
    print("=" * 60)
    print(
        f"\n  [~] Merit config — entry test: {entry_test_weight:g}%, "
        f"intermediate: {inter_weight:g}%, "
        f"min merit: {passing_threshold:g}%"
    )

    students_df = load_excel_file(EXCEL_FILENAME)
    required_columns = {
        "student_id",
        "application_status",
        "marks_percentage",
        "entry_test_score",
    }
    missing_columns = required_columns.difference(students_df.columns)
    if missing_columns:
        raise ValueError(
            "Master workbook is missing required column(s): "
            + ", ".join(sorted(missing_columns))
        )

    score_values = pd.to_numeric(students_df["entry_test_score"], errors="coerce")
    # Include already-processed students so Phase 4 can be re-run safely
    # (e.g. after tuning the weights) without silently doing nothing.
    scored_students = students_df[
        students_df["application_status"].isin(
            ["Eligible", "Admitted", "Waitlisted"]
        )
        & (score_values > 0.0)
    ].copy()
    if scored_students.empty:
        print("  [!] No eligible students with an entry-test score to calculate.\n")
        return {"calculated": 0, "admitted": 0, "waitlisted": 0}

    marks_values = pd.to_numeric(scored_students["marks_percentage"], errors="coerce")
    if marks_values.isna().any():
        missing_student_ids = scored_students.loc[
            marks_values.isna(), "student_id"
        ].astype(str).tolist()
        raise ValueError(
            "Cannot calculate merit because marks_percentage is missing or invalid "
            "for student(s): " + ", ".join(missing_student_ids)
        )

    scored_students["entry_test_score"] = score_values.loc[scored_students.index]
    scored_students["final_merit_score"] = (
        scored_students["entry_test_score"] * (entry_test_weight / 100.0)
        + marks_values * (inter_weight / 100.0)
    ).round(2)
    scored_students["application_status"] = scored_students["final_merit_score"].apply(
        lambda score: "Admitted" if score >= passing_threshold else "Waitlisted"
    )

    updates = [
        {
            "student_id": student["student_id"],
            "final_merit_score": float(student["final_merit_score"]),
            "application_status": student["application_status"],
        }
        for _, student in scored_students.iterrows()
    ]
    update_students_in_master(updates)

    admitted_count = int((scored_students["application_status"] == "Admitted").sum())
    waitlisted_count = int((scored_students["application_status"] == "Waitlisted").sum())
    print(
        f"  [+] Calculated merit for {len(updates)} student(s). "
        f"Admitted: {admitted_count}, Waitlisted: {waitlisted_count}"
    )
    print("  [OK] Merit calculation complete!\n")

    return {
        "calculated": len(updates),
        "admitted": admitted_count,
        "waitlisted": waitlisted_count,
    }
