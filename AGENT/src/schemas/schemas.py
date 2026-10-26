"""
schemas.py
----------
All Pydantic data models used across the admissions pipeline.

  AdmissionRecord   — raw LLM-extracted student data (no system metadata)
  StudentBatch      — batch wrapper used for multi-student LLM extraction
  ExtractionRequest — FastAPI request body model for POST /api/extract
  ValidationResult  — LLM eligibility decision produced by the Validator Agent
"""

import re
from pydantic import BaseModel, Field, field_validator
from typing import List, Literal


# ---------------------------------------------------------------------------
# LLM extraction models
# ---------------------------------------------------------------------------

class AdmissionRecord(BaseModel):
    """
    Represents the raw student data extracted by the LLM from free-form text.

    System-generated fields (student_id and application_status) are
    deliberately absent — they are injected by admission_service after
    extraction so the LLM is never asked to invent administrative values.
    """
    name: str = Field(
        description="Full name of the student.",
    )
    email: str = Field(
        default="Not Provided",
        description=(
            "The applicant's email address (e.g. user@domain.com). "
            "Search the entire text carefully, including lines labelled "
            "'email', 'e-mail', or 'mail'. "
            "Use 'Not Provided' if no email address is present."
        ),
    )
    phone: str = Field(
        default="Not Provided",
        description=(
            "The applicant's phone or mobile number. "
            "Use 'Not Provided' if no phone number is present."
        ),
    )
    desired_program: str = Field(
        description="The academic programme the student wants to enrol in.",
    )
    marks_percentage: float = Field(
        default=0.0,
        description=(
            "The applicant's previous academic marks expressed as a percentage "
            "(a number between 0 and 100). Recognise formats such as '78%', "
            "'85 percent', 'scored 72', 'marks: 88', or '91.5'. "
            "Do NOT mistake phone numbers, CNICs, dates, or roll numbers for marks. "
            "Use 0.0 if no marks figure is mentioned."
        ),
    )
    cnic: str = Field(
        default="Not Provided",
        description=(
            "The applicant's CNIC number in XXXXX-XXXXXXX-X format. "
            "Use 'Not Provided' if no CNIC is present."
        ),
    )
    # NOTE: seet_roll_number is intentionally absent from this schema.
    # llama3.2 loops indefinitely when asked to fill it, blocking all other
    # fields.  It is extracted reliably via regex in extractor.py and injected
    # into the record dict after the LLM call.


class StudentBatch(BaseModel):
    """
    A batch container holding multiple AdmissionRecord objects extracted
    from a single unstructured notes file in one LLM pass.
    """
    students: List[AdmissionRecord] = Field(
        description="List of all student admission records found in the source text",
    )


# ---------------------------------------------------------------------------
# Validator Agent output model
# ---------------------------------------------------------------------------

class ValidationResult(BaseModel):
    """
    The structured eligibility decision returned by the Validator Agent LLM.

    Fields:
        status    — "Eligible" if the student meets admission criteria,
                    "Rejected" if they fall below the threshold or have
                    Pending marks.
        reasoning — A concise one-to-two sentence explanation citing the
                    programme requirement and the student's actual marks.
    """
    status: Literal["Eligible", "Rejected"] = Field(
        description="Eligibility decision: 'Eligible' or 'Rejected'",
    )
    reasoning: str = Field(
        description=(
            "Brief explanation citing the programme's required marks threshold "
            "and the student's actual marks_percentage."
        ),
    )


# ---------------------------------------------------------------------------
# API request model
# ---------------------------------------------------------------------------

class ExtractionRequest(BaseModel):
    """HTTP request body for POST /api/extract."""
    text: str
