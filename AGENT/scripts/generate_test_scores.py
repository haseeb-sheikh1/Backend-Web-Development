"""
generate_test_scores.py
-----------------------
Utility script to generate mock entry test scores in the master workbook.

Usage:
    python scripts/generate_test_scores.py
"""

import os
import random
import sys

import pandas as pd

# Add project root to Python path for direct script execution.
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from src.config import DEFAULT_TEST_SCORE, EXCEL_FILENAME
from src.repositories.excel_repository import load_excel_file, update_students_in_master


def generate_test_scores() -> None:
    """Generate mock scores for eligible students with no current score."""
    print("=" * 60)
    print("  [*] Entry Test Score Generator")
    print("=" * 60)

    if not os.path.exists(EXCEL_FILENAME):
        print(f"\n  [ERROR] Master file '{EXCEL_FILENAME}' not found.")
        print("  Please run the admissions pipeline first.\n")
        return

    print(f"\n  [~] Reading eligible students from '{EXCEL_FILENAME}'...")
    students_df = load_excel_file(EXCEL_FILENAME)
    required_columns = {"student_id", "application_status", "entry_test_score"}
    missing_columns = required_columns.difference(students_df.columns)
    if missing_columns:
        print(
            "\n  [ERROR] Required column(s) missing from the master file: "
            + ", ".join(sorted(missing_columns))
            + "\n"
        )
        return

    scores = pd.to_numeric(
        students_df["entry_test_score"],
        errors="coerce",
    ).fillna(DEFAULT_TEST_SCORE)
    eligible = students_df[
        (students_df["application_status"] == "Eligible")
        & (scores == DEFAULT_TEST_SCORE)
    ]
    print(f"  [+] Found {len(eligible)} eligible student(s) without a score.")

    print("  [~] Generating random test scores (50.0 - 95.0)...")
    updates = [
        {
            "student_id": student_id,
            "entry_test_score": round(random.uniform(50.0, 95.0), 2),
        }
        for student_id in eligible["student_id"].tolist()
    ]

    print(f"  [~] Updating scores in '{EXCEL_FILENAME}'...")
    update_students_in_master(updates)
    print(f"  [OK] Generated {len(updates)} test score(s) in the master file.\n")
    print("=" * 60)
    print("  [OK] Test score generation complete!\n")


if __name__ == "__main__":
    generate_test_scores()
