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


def run_merit_calculation() -> None:
    """Calculate merit for scored eligible students and update the master file."""
    print("\n" + "=" * 60)
    print("  [*] Merit Calculation — Phase 4")
    print("=" * 60)
    print(
        f"\n  [~] Merit config — academic: {ACADEMIC_WEIGHT}, "
        f"entry test: {ENTRY_TEST_WEIGHT}, "
        f"min merit: {MINIMUM_FINAL_MERIT}"
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
    scored_students = students_df[
        (students_df["application_status"] == "Eligible")
        & (score_values > 0.0)
    ].copy()
    if scored_students.empty:
        print("  [!] No eligible students with an entry-test score to calculate.\n")
        return

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
        marks_values * ACADEMIC_WEIGHT
        + scored_students["entry_test_score"] * ENTRY_TEST_WEIGHT
    )
    scored_students["application_status"] = scored_students["final_merit_score"].apply(
        lambda score: "Admitted" if score >= MINIMUM_FINAL_MERIT else "Waitlisted"
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

    admitted_count = (scored_students["application_status"] == "Admitted").sum()
    waitlisted_count = (scored_students["application_status"] == "Waitlisted").sum()
    print(
        f"  [+] Calculated merit for {len(updates)} student(s). "
        f"Admitted: {admitted_count}, Waitlisted: {waitlisted_count}"
    )
    print("  [OK] Merit calculation complete!\n")
