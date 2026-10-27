"""
admission_service.py
--------------------
The orchestrator / service layer for the admissions pipeline.

This is the single bridge between the I/O layers and the API/CLI layers.
It owns the full end-to-end workflow for both single-record and batch-record
processing:

  1. Call extractor.py  — get raw LLM-validated record dicts
  2. Normalize fields   — replace None / 0.0 / empty values with fallbacks
  3. Inject metadata    — generate student_id and initial application status
  4. Call excel_repository.py — persist the finalized records

No other module should contain normalization or metadata-injection logic.
"""

import uuid

from src.config import (
    DEFAULT_APPLICATION_STATUS,
    DEFAULT_CNIC,
    DEFAULT_MERIT_SCORE,
    DEFAULT_ROLL_NUMBER,
    DEFAULT_TEST_SCORE,
    EXCEL_FILENAME,
)
from src.llm.extractor import extract_data, extract_batch_data
from src.repositories.excel_repository import save_to_excel


# ---------------------------------------------------------------------------
# Field-fallback table (the only place this lives in the entire codebase)
# ---------------------------------------------------------------------------

_FIELD_FALLBACKS: dict[str, str] = {
    "email":            "Not Provided",
    "phone":            "Not Provided",
    "seet_roll_number":  DEFAULT_ROLL_NUMBER,
    "cnic":             DEFAULT_CNIC,
    "marks_percentage": "Pending",
}
_DEFAULT_FALLBACK: str = "-"


# ---------------------------------------------------------------------------
# Private helpers (not exported)
# ---------------------------------------------------------------------------

def _normalize(record: dict) -> dict:
    """
    Replace missing or sentinel values with human-readable fallbacks.

    Rules:
      - None, empty string, "nan", "None"  → field-specific fallback or "-"
      - marks_percentage == 0.0            → "Pending"  (0.0 is never a real mark)
      - "Not Provided" (LLM sentinel)      → field-specific fallback (idempotent)

    Args:
        record (dict): Raw dict from AdmissionRecord.model_dump().

    Returns:
        dict: A new dict with all missing/sentinel values replaced.
    """
    cleaned: dict = {}
    for key, value in record.items():
        is_missing = (
            value is None
            or str(value).strip() in ("", "nan", "None", "Not Provided")
            or (key == "marks_percentage" and value == 0.0)
        )
        cleaned[key] = _FIELD_FALLBACKS.get(key, _DEFAULT_FALLBACK) if is_missing else value
    cleaned.setdefault("seet_roll_number", DEFAULT_ROLL_NUMBER)
    cleaned.setdefault("cnic", DEFAULT_CNIC)
    cleaned["entry_test_score"] = DEFAULT_TEST_SCORE
    cleaned["final_merit_score"] = DEFAULT_MERIT_SCORE
    cleaned["eligibility_reason"] = ""
    return cleaned


def _inject_metadata(record: dict) -> dict:
    """
    Add system-generated administrative fields to a normalized record dict.

    Fields injected:
        student_id          — "STU-" + 8-character uppercase hex (uuid4)
        application_status  — config.DEFAULT_APPLICATION_STATUS

    Args:
        record (dict): Normalized AdmissionRecord dict (no metadata keys yet).

    Returns:
        dict: The same dict extended with student_id and application_status.
    """
    record["student_id"]        = "STU-" + uuid.uuid4().hex[:8].upper()
    record["application_status"] = DEFAULT_APPLICATION_STATUS
    return record


def _prepare(record: dict) -> dict:
    """Normalize then inject metadata — the two steps always run together."""
    return _inject_metadata(_normalize(record))


# ---------------------------------------------------------------------------
# Public service API
# ---------------------------------------------------------------------------

def process_single(user_text: str, filename: str = EXCEL_FILENAME) -> dict:
    """
    End-to-end pipeline for a single free-form admission description.

    Steps:
      1. Extract one AdmissionRecord via Ollama (extractor.extract_data)
      2. Normalize + inject metadata
      3. Save to Excel
      4. Return the finalized record dict

    Args:
        user_text (str): Natural-language description of one student.
        filename  (str): Target Excel file path. Defaults to config.EXCEL_FILENAME.

    Returns:
        dict: Fully prepared admission record (normalized + metadata-enriched).

    Raises:
        json.JSONDecodeError: Propagated from extractor if JSON is malformed.
        Exception:            Propagated from extractor or excel_repository.
    """
    raw_record  = extract_data(user_text)
    final_record = _prepare(raw_record)
    save_to_excel([final_record], filename=filename)
    return final_record


def process_batch(raw_text: str, filename: str = EXCEL_FILENAME, temperature: float = 0.0) -> list[dict]:
    """
    End-to-end pipeline for a batch of students described in unstructured text.

    Steps:
      1. Extract all AdmissionRecords via Ollama (extractor.extract_batch_data)
      2. Normalize + inject metadata for every record
      3. Bulk-save all records to Excel
      4. Return the list of finalized record dicts

    Args:
        raw_text (str): Unstructured text containing one or more student records.
        filename (str): Target Excel file path. Defaults to config.EXCEL_FILENAME.
        temperature (float): Temperature for the LLM (0.0 = deterministic, 1.0 = creative).
                            Defaults to 0.0.

    Returns:
        list[dict]: Fully prepared admission records (normalized + metadata-enriched).

    Raises:
        json.JSONDecodeError: Propagated from extractor if JSON is malformed.
        ValueError:           If no records were extracted after 2 attempts.
        Exception:            Propagated from extractor or excel_repository.
    """
    raw_records   = extract_batch_data(raw_text, temperature=temperature)
    final_records = [_prepare(r) for r in raw_records]
    save_to_excel(final_records, filename=filename)
    return final_records
