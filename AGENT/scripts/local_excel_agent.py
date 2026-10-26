"""
local_excel_agent.py
--------------------
A local AI-powered admission data extraction agent that:
  - Reads unstructured notes files (.txt, .docx, .pdf) containing multiple students
  - Uses Ollama (llama3.2) to extract ALL student records in one pass via Pydantic schema
  - Saves the full batch of structured records to an Excel file (admissions.xlsx) using pandas

Dependencies: ollama, pydantic, pandas, openpyxl
Optional:     python-docx (for .docx files), pypdf (for .pdf files)
"""

# ---------------------------------------------------------------------------
# SSL Fix — must be done BEFORE importing ollama/httpx
# ---------------------------------------------------------------------------
# The SSL_CERT_FILE environment variable may be set by tools like XAMPP and
# point to a file that no longer exists. httpx (used by ollama) reads this
# variable at import time and raises FileNotFoundError if the path is invalid.
# Clearing it here forces httpx to use Python's built-in default CA bundle.
import os
os.environ.pop("SSL_CERT_FILE", None)
os.environ.pop("REQUESTS_CA_BUNDLE", None)

import json
import uuid
from datetime import date

import ollama
import pandas as pd
from pydantic import BaseModel, Field
from typing import List, Optional


MAX_INPUT_CHARACTERS = 15000

# Exact column order enforced in the Excel output
EXCEL_COLUMNS = [
    "application_id",
    "submission_date",
    "name",
    "email",
    "phone",
    "desired_program",
    "marks_percentage",
    "application_status",
    "entry_test_status",
]


# ---------------------------------------------------------------------------
# Pydantic Models
# ---------------------------------------------------------------------------

class AdmissionRecord(BaseModel):
    """
    Represents the raw student data extracted by the LLM from free-form text.
    System-generated fields (application_id, submission_date, application_status,
    entry_test_status) are NOT part of this schema — they are injected by Python
    after extraction so the LLM is never asked to invent them.
    """
    name: str = Field(description="Full name of the student")
    email: Optional[str] = Field(default=None, description="Email address of the student, if mentioned")
    phone: Optional[str] = Field(default=None, description="Phone number of the student, if mentioned")
    desired_program: str = Field(description="The academic program the student wants to enroll in")
    marks_percentage: Optional[float] = Field(
        default=None,
        description="The student's marks or grade as a percentage (0–100), if mentioned",
    )


class StudentBatch(BaseModel):
    """
    A batch container holding multiple AdmissionRecord objects extracted
    from a single unstructured notes file.
    """
    students: List[AdmissionRecord] = Field(
        description="List of all student admission records found in the source text"
    )


# ---------------------------------------------------------------------------
# System Metadata Injection
# ---------------------------------------------------------------------------

def inject_metadata(record: dict) -> dict:
    """
    Injects system-generated metadata into a raw LLM-extracted record dict.
    This keeps the LLM focused on student data only and ensures all
    administrative fields are consistent and machine-generated.

    Fields injected:
        application_id      — "APP-" + 8-character uppercase hex string (uuid4)
        submission_date     — Today's date in YYYY-MM-DD format
        application_status  — Default: "Under Review"
        entry_test_status   — Default: "Pending"

    Args:
        record (dict): Raw dict from AdmissionRecord.model_dump().

    Returns:
        dict: The same dict with metadata fields added.
    """
    record["application_id"]     = "APP-" + uuid.uuid4().hex[:8].upper()
    record["submission_date"]    = date.today().isoformat()          # e.g. 2026-10-22
    record["application_status"] = "Under Review"
    record["entry_test_status"]  = "Pending"
    return record


# ---------------------------------------------------------------------------
# File Reading
# ---------------------------------------------------------------------------

def _validate_text_size(text: str) -> str:
    if len(text) > MAX_INPUT_CHARACTERS:
        raise ValueError(
            f"Extracted text is too large ({len(text)} characters; "
            f"maximum is {MAX_INPUT_CHARACTERS}). Split the file into smaller files and try again."
        )
    return text


def read_unstructured_file(file_path: str) -> str:
    """
    Reads raw text from a file. Supports:
      - .txt  — read natively via Python's open()
      - .docx — parsed with python-docx (install: pip install python-docx)
      - .pdf  — parsed with pypdf (install: pip install pypdf)

    Args:
        file_path (str): Absolute or relative path to the source file.

    Returns:
        str: The full raw text content of the file.

    Raises:
        FileNotFoundError: If the file does not exist.
        ValueError:        If the file extension is not supported.
        ImportError:       If a required optional library is missing.
    """
    if not os.path.exists(file_path):
        raise FileNotFoundError(f"File not found: '{file_path}'")

    ext = os.path.splitext(file_path)[1].lower()

    # --- Plain text ---
    if ext == ".txt":
        with open(file_path, "r", encoding="utf-8", errors="replace") as fh:
            text = _validate_text_size(fh.read())
        print(f"  [+] Read .txt file: '{file_path}' ({len(text)} chars)")
        return text

    # --- Word document ---
    elif ext == ".docx":
        try:
            from docx import Document  # python-docx
        except ImportError:
            raise ImportError(
                "python-docx is required to read .docx files. "
                "Install it with: pip install python-docx"
            )
        doc = Document(file_path)
        paragraphs = [p.text for p in doc.paragraphs if p.text.strip()]
        text = _validate_text_size("\n".join(paragraphs))
        print(f"  [+] Read .docx file: '{file_path}' ({len(paragraphs)} paragraphs, {len(text)} chars)")
        return text

    # --- PDF document ---
    elif ext == ".pdf":
        try:
            from pypdf import PdfReader  # pypdf
        except ImportError:
            raise ImportError(
                "pypdf is required to read .pdf files. "
                "Install it with: pip install pypdf"
            )
        reader = PdfReader(file_path)
        pages_text = []
        for page in reader.pages:
            page_text = page.extract_text()
            if page_text:
                pages_text.append(page_text)
        text = _validate_text_size("\n".join(pages_text))
        print(f"  [+] Read .pdf file: '{file_path}' ({len(reader.pages)} pages, {len(text)} chars)")
        return text

    else:
        raise ValueError(
            f"Unsupported file extension: '{ext}'. "
            "Supported formats: .txt, .docx, .pdf"
        )


# ---------------------------------------------------------------------------
# Batch Extraction via Ollama
# ---------------------------------------------------------------------------

def extract_batch_data(raw_text: str) -> list[dict]:
    """
    Sends raw_text (which may describe multiple students) to Ollama (llama3.2)
    and extracts ALL student admission records, retrying once if no records are returned.

    The response is forced to match the StudentBatch JSON schema, validated
    with Pydantic, and returned as a clean list of dicts (one per student).

    Args:
        raw_text (str): Raw unstructured text that may contain many student records.

    Returns:
        list[dict]: A list of dicts, each matching the AdmissionRecord schema.

    Raises:
        json.JSONDecodeError: If the model returns invalid JSON.
        ValueError:           If the model returns no student records after both attempts.
        Exception:            For Ollama connectivity or validation errors.
    """
    _validate_text_size(raw_text)

    system_prompt = (
        "You are an expert data extraction assistant. "
        "The user will provide messy, unstructured notes that contain information "
        "about MULTIPLE students. Your job is to carefully read ALL of the text and "
        "extract EVERY student's admission record into a structured list. Carefully "
        "search the entire text for each student's email address, including addresses "
        "near the end of a note or labeled as email, e-mail, or mail. "
        "Recognize marks in formats such as '78%', '85 percent', 'scored 72', "
        "'marks: 88', and decimal percentages such as '91.5%'. Treat a score stated "
        "without a percent sign as a percentage only when the surrounding context "
        "identifies it as marks, a score, or a grade. Do not mistake phone numbers, "
        "dates, or other numbers for marks. "
        "Do not skip any student, even if the information is incomplete or scattered. "
        "For any field that is not mentioned, use null, including marks_percentage. "
        "Always return valid JSON that matches the provided schema exactly."
    )

    for attempt in range(2):
        print("  [~] Sending batch to llama3.2 via Ollama...")

        response = ollama.chat(
            model="llama3.2",
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user",   "content": raw_text},
            ],
            # Force the response to match StudentBatch (which contains a list of AdmissionRecords)
            format=StudentBatch.model_json_schema(),
            options={"temperature": 0},
        )

        raw_content = response["message"]["content"]
        batch_dict = json.loads(raw_content)
        validated_batch = StudentBatch(**batch_dict)
        records = [student.model_dump() for student in validated_batch.students]

        if records:
            # Inject system metadata into every record after LLM extraction
            records = [inject_metadata(r) for r in records]
            print(f"  [OK] Extracted {len(records)} student record(s) from the text.\n")
            return records
        if attempt == 0:
            print("  [!] No student records returned; retrying once...")

    raise ValueError("No student records were extracted after 2 attempts.")


# ---------------------------------------------------------------------------
# Single-Record Extraction (kept for backward compatibility with main.py API)
# ---------------------------------------------------------------------------

def extract_data(user_text: str) -> dict:
    """
    Sends user_text to Ollama llama3.2 and extracts a SINGLE structured
    admission record by enforcing the AdmissionRecord JSON schema.

    This function is retained for backward compatibility with the FastAPI
    server in main.py, which handles one record at a time.

    Args:
        user_text (str): Raw natural language input describing one student.

    Returns:
        dict: A dictionary matching the AdmissionRecord schema.
    """
    system_prompt = (
        "You are an expert data extraction assistant. "
        "Extract the admission record details from the user's message. "
        "If a field is not mentioned, use null for optional fields. "
        "Always return valid JSON that matches the provided schema exactly."
    )

    response = ollama.chat(
        model="llama3.2",
        messages=[
            {"role": "system", "content": system_prompt},
            {"role": "user",   "content": user_text},
        ],
        format=AdmissionRecord.model_json_schema(),
    )

    raw_content = response["message"]["content"]
    record_dict = json.loads(raw_content)
    validated_record = AdmissionRecord(**record_dict)
    # Inject system-generated metadata before returning
    return inject_metadata(validated_record.model_dump())


# ---------------------------------------------------------------------------
# Save to Excel (batch-capable)
# ---------------------------------------------------------------------------

def save_to_excel(records: list[dict], filename: str = "admissions.xlsx") -> None:
    """
    Saves a batch of extracted admission records to an Excel file.
    - Accepts a list of dicts (one dict per student, already metadata-enriched).
    - Applies field-specific fallback values for missing data:
        email / phone        → "Not Provided"
        marks_percentage     → "Pending"
        all other fields     → "-"
    - Enforces the canonical EXCEL_COLUMNS column order.
    - Appends all rows at once to the existing file using pd.concat.
    - Creates the file with headers if it does not yet exist.

    Args:
        records  (list[dict]): List of structured, metadata-enriched admission dicts.
        filename (str):        Path/name of the Excel file. Defaults to 'admissions.xlsx'.
    """
    if not records:
        print("  [!] No records to save — skipping Excel write.\n")
        return

    # --- Field-specific missing-value replacements ---
    FIELD_FALLBACKS = {
        "email":            "Not Provided",
        "phone":            "Not Provided",
        "marks_percentage": "Pending",
    }
    DEFAULT_FALLBACK = "-"

    cleaned_records: list[dict] = []
    for record_dict in records:
        cleaned: dict = {}
        for key, value in record_dict.items():
            is_missing = (
                value is None
                or str(value).strip() in ("", "nan", "None")
                or (key == "marks_percentage" and value == 0.0)
            )
            if is_missing:
                cleaned[key] = FIELD_FALLBACKS.get(key, DEFAULT_FALLBACK)
            else:
                cleaned[key] = value
        cleaned_records.append(cleaned)

    # Build DataFrame and enforce canonical column order
    new_rows_df = pd.DataFrame(cleaned_records).reindex(columns=EXCEL_COLUMNS)

    if os.path.exists(filename):
        existing_df = pd.read_excel(filename, engine="openpyxl")
        updated_df  = pd.concat([existing_df, new_rows_df], ignore_index=True)
        print(f"  [+] Appending {len(cleaned_records)} record(s) to existing file: '{filename}'")
    else:
        updated_df = new_rows_df
        print(f"  [+] Creating new file '{filename}' with {len(cleaned_records)} record(s).")

    # Final reindex guarantees column order even when appending to an old file
    updated_df = updated_df.reindex(columns=EXCEL_COLUMNS)

    updated_df.to_excel(filename, index=False, engine="openpyxl")
    print(f"  [OK] Saved! Total records in '{filename}': {len(updated_df)}\n")


# ---------------------------------------------------------------------------
# Main Loop (interactive single-record mode — original behaviour)
# ---------------------------------------------------------------------------

def main():
    """
    Interactive terminal loop that continuously:
      1. Prompts the user to enter admission details in natural language
      2. Extracts a single structured record using Ollama + llama3.2
      3. Prints the clean extracted dictionary
      4. Saves the record to admissions.xlsx
    Type 'quit' or 'exit' to stop.
    """
    print("=" * 60)
    print("  [*] Local Admission Data Extraction Agent")
    print("  Powered by Ollama (llama3.2) + Pydantic + Pandas")
    print("=" * 60)
    print("  Describe a student's admission details in plain English.")
    print("  Type 'quit' or 'exit' to stop.\n")

    while True:
        try:
            user_text = input(">> Enter admission details: ").strip()

            if user_text.lower() in ("quit", "exit", "q"):
                print("\nGoodbye! All records saved to 'admissions.xlsx'.")
                break

            if not user_text:
                print("  [!] Input cannot be empty. Please try again.\n")
                continue

            print("\n  [~] Extracting data with llama3.2...")
            record = extract_data(user_text)

            print("\n  Extracted Record:")
            print("  " + "-" * 40)
            for key, value in record.items():
                print(f"  {key:<20}: {value}")
            print("  " + "-" * 40)

            save_to_excel([record])   # wrap in list — save_to_excel now expects a list

        except json.JSONDecodeError:
            print("\n  [ERROR] The model returned invalid JSON. Please try rephrasing your input.\n")
        except Exception as e:
            print(f"\n  [ERROR] An unexpected error occurred: {e}\n")


# ---------------------------------------------------------------------------
# Batch File Processing Entry Point
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    import sys

    # -----------------------------------------------------------------------
    # Batch mode: python local_excel_agent.py <file>
    # -----------------------------------------------------------------------
    if len(sys.argv) > 1:
        notes_file = sys.argv[1]

        print("=" * 60)
        print("  [*] Batch File Extraction Mode")
        print(f"  Source: {notes_file}")
        print("=" * 60)

        try:
            # Step 1 — Read the unstructured notes file
            raw_text = read_unstructured_file(notes_file)

            # Step 2 — Extract all student records in one LLM pass
            records = extract_batch_data(raw_text)

            # Step 3 — Display each extracted record
            print("\n  Extracted Records:")
            print("  " + "-" * 50)
            for i, rec in enumerate(records, start=1):
                print(f"\n  Student #{i}")
                for key, value in rec.items():
                    print(f"    {key:<20}: {value}")
            print("\n  " + "-" * 50)

            # Step 4 — Bulk-save all records to Excel
            save_to_excel(records)

        except FileNotFoundError as e:
            print(f"\n  [ERROR] {e}")
        except ValueError as e:
            print(f"\n  [ERROR] {e}")
        except ImportError as e:
            print(f"\n  [ERROR] Missing library — {e}")
        except json.JSONDecodeError:
            print("\n  [ERROR] The model returned invalid JSON. Try again or simplify the input file.")
        except Exception as e:
            print(f"\n  [ERROR] Unexpected error: {e}")

    # -----------------------------------------------------------------------
    # Interactive mode (original behaviour): python local_excel_agent.py
    # -----------------------------------------------------------------------
    else:
        main()
