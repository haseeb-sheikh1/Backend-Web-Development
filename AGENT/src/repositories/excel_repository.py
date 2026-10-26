"""
excel_repository.py
-------------------
Output I/O layer for the admissions pipeline.

Receives a fully prepared list[dict] — values already normalized and
metadata-enriched by admission_service — and persists it to an Excel file.

This module contains persistence logic only, not admission decisions:
  - No fallback value decisions  (that is admission_service._normalize)
  - No metadata injection        (that is admission_service._inject_metadata)
  - No field normalization       (records arrive ready to write)

Its only responsibilities are:
  - Build a Pandas DataFrame from the provided records
  - Load the existing workbook if one is present
  - Update matching applicants or append genuinely new rows
  - Remove duplicate rows for an applicant when they are updated
  - Enforce the canonical EXCEL_COLUMNS order
  - Save the result to disk
  - Read and filter rows for the Validator Agent
  - Update individual student fields in the master workbook
"""

import io
import os
import shutil
import tempfile
import pandas as pd
from src.config import EXCEL_COLUMNS, EXCEL_FILENAME


def save_to_excel(records: list[dict], filename: str = EXCEL_FILENAME) -> None:
    """
    Persists a batch of fully prepared admission records to an Excel file.

    Callers (admission_service) are responsible for ensuring every record is
    already normalized and metadata-enriched before calling this function.

    Args:
        records  (list[dict]): List of fully prepared admission record dicts.
                               Must not contain None values or missing keys —
                               that normalization belongs in admission_service.
        filename (str):        Path/name of the target Excel file.
                               Defaults to config.EXCEL_FILENAME.
    """
    if not records:
        print("  [!] No records to save — skipping Excel write.\n")
        return

    def normalized(value: object) -> str:
        if pd.isna(value):
            return ""
        return str(value).strip().casefold()

    def identity_keys(record: dict) -> set[tuple[str, ...]]:
        keys: set[tuple[str, ...]] = set()
        name = normalized(record.get("name"))
        email = normalized(record.get("email"))
        cnic = "".join(character for character in normalized(record.get("cnic")) if character.isdigit())
        phone = "".join(character for character in normalized(record.get("phone")) if character.isdigit())
        program = normalized(record.get("desired_program"))
        roll_number = normalized(record.get("seet_roll_number"))

        if len(cnic) == 13:
            keys.add(("cnic", cnic))
        if "@" in email:
            keys.add(("email", email))
        if name and program and roll_number not in ("", "not provided"):
            keys.add(("roll", name, program, roll_number))
        if name and phone and program:
            keys.add(("applicant", name, phone, program))
        return keys

    def same_applicant(existing: dict, incoming: dict) -> bool:
        existing_cnic = "".join(
            character
            for character in normalized(existing.get("cnic"))
            if character.isdigit()
        )
        incoming_cnic = "".join(
            character
            for character in normalized(incoming.get("cnic"))
            if character.isdigit()
        )
        if len(existing_cnic) == 13 and len(incoming_cnic) == 13:
            return existing_cnic == incoming_cnic

        existing_email = normalized(existing.get("email"))
        incoming_email = normalized(incoming.get("email"))
        if "@" in existing_email and "@" in incoming_email:
            return existing_email == incoming_email

        return bool(identity_keys(existing).intersection(identity_keys(incoming)))

    def matching_rows(dataframe: pd.DataFrame, record: dict) -> list[int]:
        matches = []
        for index, row in dataframe.iterrows():
            if same_applicant(row.to_dict(), record):
                matches.append(index)
        return matches

    updated_df = (
        pd.read_excel(filename, engine="openpyxl").reindex(columns=EXCEL_COLUMNS)
        if os.path.exists(filename)
        else pd.DataFrame(columns=EXCEL_COLUMNS)
    ).astype(object)
    added_count = 0
    updated_count = 0
    lifecycle_columns = {
        "student_id",
        "application_status",
        "eligibility_reason",
        "entry_test_score",
        "final_merit_score",
    }

    for record in records:
        incoming = pd.DataFrame([record]).reindex(columns=EXCEL_COLUMNS).iloc[0]
        matches = matching_rows(updated_df, record) if identity_keys(record) else []

        if not matches:
            updated_df = pd.concat(
                [updated_df, incoming.to_frame().T],
                ignore_index=True,
            )
            added_count += 1
            continue

        survivor_index = matches[0]
        merged = updated_df.loc[survivor_index].copy()
        for duplicate_index in matches[1:]:
            duplicate = updated_df.loc[duplicate_index]
            for column in EXCEL_COLUMNS:
                if normalized(merged[column]) in ("", "nan", "none", "not provided", "-"):
                    merged[column] = duplicate[column]

        for column in EXCEL_COLUMNS:
            if column in lifecycle_columns:
                continue
            if normalized(incoming[column]) not in ("", "nan", "none", "not provided", "-"):
                merged[column] = incoming[column]

        updated_df.loc[survivor_index] = merged
        duplicate_indices = matches[1:]
        if duplicate_indices:
            updated_df = updated_df.drop(index=duplicate_indices)
        updated_count += 1

    updated_df = updated_df.reindex(columns=EXCEL_COLUMNS).reset_index(drop=True)
    if added_count:
        print(f"  [+] Added {added_count} new record(s) to '{filename}'.")
    if updated_count:
        print(
            f"  [~] Updated {updated_count} existing applicant record(s) "
            f"and removed matching duplicates in '{filename}'."
        )

    updated_df.to_excel(filename, index=False, engine="openpyxl")
    print(f"  [OK] Saved! Total records in '{filename}': {len(updated_df)}\n")


# ---------------------------------------------------------------------------
# Validator Agent — read helpers
# ---------------------------------------------------------------------------

def get_under_review_records(filename: str = EXCEL_FILENAME) -> list[dict]:
    """
    Load the master Excel workbook and return only rows whose
    ``application_status`` column equals "Under Review".

    Args:
        filename (str): Path to the master Excel file.
                        Defaults to config.EXCEL_FILENAME.

    Returns:
        list[dict]: One dict per matching row (all columns included).
                    Returns an empty list if the file does not exist or
                    contains no "Under Review" rows.
    """
    if not os.path.exists(filename):
        print(f"  [!] Master file '{filename}' not found — no records to validate.\n")
        return []

    df = pd.read_excel(filename, engine="openpyxl")
    under_review = df[df["application_status"] == "Under Review"]

    count = len(under_review)
    print(f"  [+] Found {count} 'Under Review' record(s) in '{filename}'.")
    return under_review.to_dict(orient="records")


def update_students_in_master(
    updates: list[dict],
    filename: str = EXCEL_FILENAME,
) -> None:
    """
    Update selected columns for students in the master workbook.

    Each update must contain a ``student_id`` and at least one column/value
    pair to update. Student IDs must uniquely identify rows in the workbook.

    Args:
        updates: Student IDs and the specific master columns to overwrite.
        filename: Path to the master Excel file.
                  Defaults to config.EXCEL_FILENAME.

    Raises:
        FileNotFoundError: If the master workbook does not exist.
        KeyError: If the workbook is missing ``student_id``.
        ValueError: If an update is malformed, targets an unknown column or
            student, or the workbook contains duplicate student IDs.
    """
    if not updates:
        print("  [!] No student updates provided — skipping write.\n")
        return

    if not os.path.exists(filename):
        raise FileNotFoundError(f"Master workbook '{filename}' not found.")

    df = pd.read_excel(filename, engine="openpyxl")
    if "student_id" not in df.columns:
        raise KeyError(f"Master workbook '{filename}' has no 'student_id' column.")

    if df["student_id"].isna().any():
        raise ValueError(f"Master workbook '{filename}' contains a missing student_id.")

    normalized_ids = df["student_id"].astype(str).str.strip()
    if normalized_ids.duplicated().any():
        raise ValueError(f"Master workbook '{filename}' contains duplicate student_id values.")
    row_by_id = {student_id: index for index, student_id in normalized_ids.items()}

    validated_updates: list[tuple[int, dict]] = []
    for update in updates:
        if not isinstance(update, dict) or "student_id" not in update:
            raise ValueError("Each student update must be a dictionary with a student_id.")

        student_id = str(update["student_id"]).strip()
        if not student_id or student_id.casefold() == "nan":
            raise ValueError("Student updates must contain a non-empty student_id.")
        if student_id not in row_by_id:
            raise ValueError(f"Student ID {student_id!r} is not present in '{filename}'.")

        fields = {key: value for key, value in update.items() if key != "student_id"}
        if not fields:
            raise ValueError(f"Update for student {student_id!r} has no fields to update.")

        unknown_columns = set(fields).difference(EXCEL_COLUMNS)
        if unknown_columns:
            raise ValueError(
                "Updates contain unsupported master column(s): "
                + ", ".join(sorted(unknown_columns))
            )
        validated_updates.append((row_by_id[student_id], fields))

    # Restore the canonical schema while preserving all current row values.
    df = df.reindex(columns=EXCEL_COLUMNS).astype(object)
    for row_index, fields in validated_updates:
        for column, value in fields.items():
            df.at[row_index, column] = value

    absolute_path = os.path.abspath(filename)
    directory = os.path.dirname(absolute_path)
    temp_fd, temp_path = tempfile.mkstemp(suffix=".xlsx", dir=directory)
    os.close(temp_fd)
    try:
        df.to_excel(temp_path, index=False, engine="openpyxl")
        os.replace(temp_path, absolute_path)
    finally:
        if os.path.exists(temp_path):
            os.remove(temp_path)

    print(f"  [OK] Updated {len(validated_updates)} student record(s) in '{filename}'.\n")


def load_excel_file(filename: str) -> pd.DataFrame:
    """
    Generic helper to load a specified Excel file into a Pandas DataFrame.

    Reads the file via an in-memory byte buffer so it succeeds even when
    the file is currently open (and write-locked) in Excel.

    Args:
        filename (str): Path to the Excel file to load.

    Returns:
        pd.DataFrame: The loaded DataFrame.

    Raises:
        FileNotFoundError: If the file does not exist.
    """
    if not os.path.exists(filename):
        raise FileNotFoundError(f"Excel file '{filename}' not found.")

    # Copy file bytes into memory first to bypass Excel's write-lock
    buffer = io.BytesIO()
    with open(filename, "rb") as fh:
        shutil.copyfileobj(fh, buffer)
    buffer.seek(0)

    df = pd.read_excel(buffer, engine="openpyxl")
    print(f"  [+] Loaded {len(df)} row(s) from '{filename}'.")
    return df
