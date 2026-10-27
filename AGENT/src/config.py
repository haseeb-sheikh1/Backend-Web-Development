"""
config.py
---------
Central configuration for the admissions pipeline.

All hardcoded constants live here. Every other module imports from this
file instead of repeating literals inline.
"""

# ---------------------------------------------------------------------------
# Ollama / LLM
# ---------------------------------------------------------------------------

# Name of the Ollama model used for all extraction calls
MODEL_NAME: str = "llama3.2"

# ---------------------------------------------------------------------------
# Input validation
# ---------------------------------------------------------------------------

# Maximum number of characters allowed in a single input sent to the LLM
MAX_INPUT_CHARACTERS: int = 15_000

# ---------------------------------------------------------------------------
# Default statuses injected by the service layer after LLM extraction
# ---------------------------------------------------------------------------

DEFAULT_APPLICATION_STATUS: str = "Under Review"
DEFAULT_ENTRY_TEST_STATUS: str = "Pending"
DEFAULT_ROLL_NUMBER: str = "Not Provided"
DEFAULT_CNIC: str = "Not Provided"
DEFAULT_TEST_SCORE: float = 0.0
DEFAULT_MERIT_SCORE: float = 0.0

# ---------------------------------------------------------------------------
# Merit calculation weightage
# ---------------------------------------------------------------------------

ACADEMIC_WEIGHT: float = 0.6
ENTRY_TEST_WEIGHT: float = 0.4
MINIMUM_FINAL_MERIT: float = 70.0

# ---------------------------------------------------------------------------
# Excel output
# ---------------------------------------------------------------------------

# Default output filename used by excel_repository when none is specified
EXCEL_FILENAME: str = "data/admissions.xlsx"

# Canonical column order enforced in every Excel write.
# Changing this list is the single place needed to reorder/add columns.
EXCEL_COLUMNS: list[str] = [
    "student_id",
    "name",
    "email",
    "phone",
    "desired_program",
    "marks_percentage",
    "cnic",
    "seet_roll_number",
    "application_status",
    "eligibility_reason",
    "entry_test_score",
    "final_merit_score",
    "email_sent",
]
