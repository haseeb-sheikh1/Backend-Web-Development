"""
extractor.py
------------
AI processing layer for the admissions pipeline.

Responsibilities (and ONLY these):
  - Send raw text to Ollama (llama3.2) via the ollama.chat API
  - Enforce the correct JSON schema (AdmissionRecord, StudentBatch, ValidationResult)
  - Parse + Pydantic-validate the model response
  - Return raw validated record dicts — no metadata, no business rules

This module deliberately has no knowledge of:
  - student_id and initial application status  → admission_service
  - marks / field normalization                → admission_service
  - Excel or file I/O                          → excel_repository / document_reader

Safeguards implemented against indefinite hanging:
  - stream=True    — prints tokens as they arrive so hangs are immediately visible
  - num_predict    — hard cap on generated tokens to prevent infinite rambling
  - temperature    — set to 0.0 for maximum determinism
  - timeout        — passed to the ollama.Client so every call has a hard deadline
  - format         — JSON schema always enforced so the model outputs data, not prose
"""

# ---------------------------------------------------------------------------
# SSL Fix — must precede ollama / httpx imports
# ---------------------------------------------------------------------------
# Some environments (e.g. XAMPP) set SSL_CERT_FILE to a path that no longer
# exists. httpx (used by ollama) reads this variable at import time and raises
# FileNotFoundError if the path is invalid. Clearing it forces httpx to fall
# back to Python's bundled CA certificates.
import os
os.environ.pop("SSL_CERT_FILE", None)
os.environ.pop("REQUESTS_CA_BUNDLE", None)

import json
import re
import sys

import ollama
import httpx

from src.config import MODEL_NAME, MAX_INPUT_CHARACTERS
from src.schemas.schemas import AdmissionRecord, StudentBatch, ValidationResult


# ---------------------------------------------------------------------------
# Ollama client — created once with a hard request timeout
# ---------------------------------------------------------------------------

# If Ollama does not return the first byte within OLLAMA_TIMEOUT seconds the
# call raises httpx.ReadTimeout, which we catch and re-raise as a RuntimeError
# with a human-readable message.  120 s is generous for a local 3-B model;
# lower it if your hardware is faster.
OLLAMA_TIMEOUT: int = 120  # seconds

# Generation options applied to every ollama.chat call:
#   temperature=0.0  → fully deterministic, no creative padding
#   num_predict=300  → token budget.  llama3.2 can ramble inside string
#                      fields even with format=schema; 300 gives the JSON
#                      enough headroom to close properly.  If you see
#                      JSONDecodeError on very large batches, raise further.
_GENERATION_OPTIONS: dict = {
    "temperature": 0.0,
    "num_predict": 300,
}

_client = ollama.Client(
    host="http://localhost:11434",
    timeout=httpx.Timeout(OLLAMA_TIMEOUT),
)


# ---------------------------------------------------------------------------
# Internal helpers
# ---------------------------------------------------------------------------

_ROLL_NUMBER_PATTERN = re.compile(
    r"\b(?:SEET\s+)?(?:entry[\s-]*test\s+)?roll"
    r"(?:\s*(?:number|no\.?|#))?\s*(?:is\b|[:=#-])?\s*"
    r"([A-Za-z0-9][A-Za-z0-9_-]*)",
    re.IGNORECASE,
)

_EMAIL_PATTERN = re.compile(
    r"[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}",
)

_PHONE_PATTERN = re.compile(
    r"(?:\+92[\s\-]?|0)(?:3\d{2})[\s\-]?\d{7}",
)

_LABELED_MARKS_PATTERN = re.compile(
    r"""
    \b(?:academic\s+)?(?:marks?(?:[_\s]+percentage)?|percentage|scored?|score|got|obtained)
    \s*(?:(?:is|of)\s*)?[:=]?\s*
    (\d{1,3}(?:\.\d+)?)
    \s*(?:%|percent(?:age)?|out\s+of\s+100)?
    (?=\s|$|[.,;!?)])
    """,
    re.IGNORECASE | re.VERBOSE,
)

_PERCENT_MARKS_PATTERN = re.compile(
    r"""
    (?<![\w.-])
    (\d{1,3}(?:\.\d+)?)
    \s*(?:%|percent(?:age)?|out\s+of\s+100)
    (?=\s|$|[.,;!?)])
    """,
    re.IGNORECASE | re.VERBOSE,
)


def _find_roll_number(text: str) -> str | None:
    """Extract a SEET roll number from explicitly labeled text."""
    match = _ROLL_NUMBER_PATTERN.search(text)
    if match is None:
        return None
    value = match.group(1)
    if value.casefold() in {"not", "provided", "pending", "none", "null", "n/a"}:
        return None
    return value


def _find_email(text: str) -> str | None:
    """Extract the first email address found in text."""
    match = _EMAIL_PATTERN.search(text)
    return match.group(0) if match else None


def _find_phone(text: str) -> str | None:
    """Extract the first Pakistani phone number found in text."""
    match = _PHONE_PATTERN.search(text)
    if match is None:
        return None
    # Normalise to 0XXX-XXXXXXX format
    digits = re.sub(r"[^\d]", "", match.group(0))
    if digits.startswith("92"):
        digits = "0" + digits[2:]
    return f"{digits[:4]}-{digits[4:]}" if len(digits) == 11 else digits


def _find_marks(text: str) -> float | None:
    """Extract academic marks percentage from text; returns None if not found."""
    for pattern in (_LABELED_MARKS_PATTERN, _PERCENT_MARKS_PATTERN):
        for match in pattern.finditer(text):
            try:
                value = float(match.group(1))
                if 0.0 < value <= 100.0:
                    return value
            except (ValueError, TypeError):
                continue
    return None


def _apply_regex_fallbacks(record: dict, text: str) -> dict:
    """
    Inject seet_roll_number, email, phone, and marks_percentage into *record*
    using regex matches on the original *text* whenever the LLM left them
    as their default sentinel values.

    llama3.2 consistently skips optional fields in structured-output mode.
    Regex is fully deterministic and more reliable for these well-structured
    patterns than another LLM call.
    """
    if record.get("seet_roll_number", "Not Provided") == "Not Provided":
        found = _find_roll_number(text)
        record["seet_roll_number"] = found if found is not None else "Not Provided"

    if record.get("email", "Not Provided") == "Not Provided":
        found = _find_email(text)
        if found:
            record["email"] = found

    if record.get("phone", "Not Provided") == "Not Provided":
        found = _find_phone(text)
        if found:
            record["phone"] = found

    found = _find_marks(text)
    if found is not None:
        record["marks_percentage"] = found

    return record


def _safe_parse_json(raw: str) -> dict:
    """
    Parse *raw* as JSON, recovering gracefully from truncated output.

    llama3.2 sometimes exhausts the num_predict budget while writing a
    string field value, leaving an unterminated string and therefore
    invalid JSON.  This function:

      1. Tries a normal json.loads first (fast path).
      2. On JSONDecodeError, truncates the string at the point of failure,
         appends closing characters ('"', '}', ']') to balance the
         open structures, and retries once.
      3. Raises the original JSONDecodeError if recovery also fails.

    The truncated field value will then be sanitised by the Pydantic
    field_validator defined on AdmissionRecord.roll_number.

    Args:
        raw (str): Raw string returned by the model (may be truncated).

    Returns:
        dict: Parsed JSON object.

    Raises:
        json.JSONDecodeError: If both parse attempts fail.
    """
    parse_err = None
    try:
        return json.loads(raw)
    except json.JSONDecodeError as exc:
        parse_err = exc
        print(f"  [!] JSON truncated — attempting structural recovery...")

    # ----------------------------------------------------------------
    # Recovery: walk the FULL raw content with a STACK to learn the
    # exact structural state at the END of the buffer.
    #
    # Why a stack and not flat counters?
    #   For nesting like  {  [  {   the correct close is  } ] }
    #   Flat counters would produce ] } }  (wrong order) which fails
    #   with "Expecting ',' delimiter".  A stack records each opener's
    #   matching closer and is popped in LIFO order → correct sequence.
    # ----------------------------------------------------------------
    stack     : list[str] = []   # pending closing chars in LIFO order
    in_string : bool      = False
    prev_ch   : str       = ""

    for ch in raw:
        if in_string:
            if ch == '"' and prev_ch != "\\":
                in_string = False           # string closed normally
        else:
            if ch == '"':
                in_string = True            # string opened
            elif ch == "{":
                stack.append("}")
            elif ch == "}":
                if stack and stack[-1] == "}":
                    stack.pop()
            elif ch == "[":
                stack.append("]")
            elif ch == "]":
                if stack and stack[-1] == "]":
                    stack.pop()
        prev_ch = ch

    # Strip trailing commas / whitespace before appending closers
    truncated = raw.rstrip(", \t\r\n")

    # Build the minimal closing sequence in proper LIFO order:
    #   1. close the open string (if currently inside one)
    #   2. pop remaining stack in reverse = innermost closer first
    closing  = '"' if in_string else ""
    closing += "".join(reversed(stack))

    recovered = truncated + closing
    try:
        result = json.loads(recovered)
        print("  [OK] JSON recovery succeeded.")
        return result
    except json.JSONDecodeError:
        raise parse_err  # surface the original error

def _validate_text_size(text: str) -> str:
    """
    Guard against oversized inputs before hitting the LLM.

    Args:
        text (str): The raw input text.

    Returns:
        str: The same text, unchanged, if within the limit.

    Raises:
        ValueError: If len(text) > MAX_INPUT_CHARACTERS.
    """
    if len(text) > MAX_INPUT_CHARACTERS:
        raise ValueError(
            f"Input text is too large ({len(text)} characters; "
            f"maximum is {MAX_INPUT_CHARACTERS}). "
            "Split the content into smaller chunks and try again."
        )
    return text


def _stream_response(stream) -> str:
    """
    Consume a streaming ollama.chat response with two early-stop guards.

    Guard 1 — brace-depth (normal path)
        Track open/close braces and brackets.  Break the moment the
        outermost JSON object closes (depth → 0).  This fires correctly
        for well-behaved responses.

    Guard 2 — per-field string length (infinite-loop path)
        llama3.2 can enter an infinite loop inside a *string value* even
        with format=schema and temperature=0 (e.g. the roll_number field
        produces "78% or 78 (SEET entry test roll number) or 78 (SEET …"
        forever).  Because the closing quote of that string never arrives,
        Guard 1 never fires.

        Guard 2 tracks how many characters have been accumulated inside the
        current string.  If it exceeds MAX_STRING_CHARS, we force-stop the
        stream immediately.  _safe_parse_json will then recover the partial
        JSON by closing any open structures.

    Args:
        stream: The iterator returned by ollama.Client.chat(stream=True).

    Returns:
        str: The assembled content up to the early-stop point.
    """
    MAX_STRING_CHARS = 400   # max chars allowed inside any single string field

    full_content: list[str] = []
    print("  [streaming] ", end="", flush=True)

    depth       = 0      # net open { and [
    in_string   = False  # currently inside a JSON string value?
    started     = False  # have we seen the opening { ?
    prev_char   = ""     # for escape detection
    str_len     = 0      # chars accumulated in the current string value
    force_stop  = False

    for chunk in stream:
        token: str = chunk["message"]["content"]
        print(token, end="", flush=True)
        full_content.append(token)

        for ch in token:
            if in_string:
                if ch == '"' and prev_char != "\\":
                    in_string = False   # closing quote — string finished
                    str_len   = 0
                else:
                    str_len += 1
                    if str_len > MAX_STRING_CHARS:
                        # This string is a runaway loop — cut the stream now.
                        print(
                            f"\n  [early-stop: string field exceeded "
                            f"{MAX_STRING_CHARS} chars — cutting stream]",
                            flush=True,
                        )
                        force_stop = True
                        break
            else:
                if ch == '"':
                    in_string = True
                    str_len   = 0
                elif ch in ("{", "["):
                    depth += 1
                    started = True
                elif ch in ("}", "]"):
                    depth -= 1
            prev_char = ch

        if force_stop:
            break

        # Normal exit: outermost JSON object is balanced
        if started and depth == 0:
            print("  [early-stop: JSON complete]", flush=True)
            break

    print()  # newline after stream
    return "".join(full_content)


def _call_ollama(messages: list[dict], schema: dict, label: str = "", temperature: float | None = None) -> str:
    """
    Make a single streaming Ollama call with all safeguards applied.

    Args:
        messages (list[dict]): The chat messages list (system + user).
        schema   (dict):       The Pydantic model_json_schema() to enforce.
        label    (str):        Short description printed before the call (optional).
        temperature (float | None): Override the default temperature. If None, uses _GENERATION_OPTIONS['temperature'].

    Returns:
        str: The full raw content string returned by the model.

    Raises:
        RuntimeError: On timeout or any Ollama/httpx connectivity error.
    """
    options = _GENERATION_OPTIONS.copy()
    if temperature is not None:
        options["temperature"] = temperature

    if label:
        print(f"  [~] {label} (model={MODEL_NAME}, timeout={OLLAMA_TIMEOUT}s, "
              f"num_predict={options['num_predict']}, temperature={options['temperature']})...")

    try:
        stream = _client.chat(
            model=MODEL_NAME,
            messages=messages,
            format=schema,           # Enforce JSON output — model must stop after JSON
            options=options,
            stream=True,             # Stream tokens; hang is immediately visible
        )
        return _stream_response(stream)

    except httpx.ReadTimeout:
        msg = (
            f"\n  [ERROR] Ollama did not respond within {OLLAMA_TIMEOUT} seconds.\n"
            "  Possible causes:\n"
            "    • The Ollama server is not running  (run: ollama serve)\n"
            "    • The model is still loading        (wait, then retry)\n"
            "    • The model is stuck / OOM          (restart Ollama)\n"
            "  Aborting to avoid an indefinite hang."
        )
        print(msg, file=sys.stderr)
        raise RuntimeError(msg) from None

    except httpx.ConnectError:
        msg = (
            "\n  [ERROR] Cannot connect to Ollama at http://localhost:11434.\n"
            "  Make sure Ollama is running:  ollama serve\n"
            "  Aborting."
        )
        print(msg, file=sys.stderr)
        raise RuntimeError(msg) from None


# ---------------------------------------------------------------------------
# Public API
# ---------------------------------------------------------------------------

def extract_data(user_text: str) -> dict:
    """
    Send *user_text* to Ollama and extract a single structured admission record.

    The response is forced to match the AdmissionRecord JSON schema and
    validated with Pydantic before being returned as a plain dict.

    Note: system-generated metadata (student_id and application_status)
    is NOT added here — that is the responsibility of admission_service.

    Args:
        user_text (str): Natural-language description of one student.

    Returns:
        dict: A dict matching AdmissionRecord fields (no metadata keys).

    Raises:
        json.JSONDecodeError: If the model returns invalid JSON.
        pydantic.ValidationError: If the JSON does not match AdmissionRecord.
        RuntimeError: On Ollama timeout or connectivity failure.
    """
    _validate_text_size(user_text)

    system_prompt = (
        "You are an expert data extraction assistant. "
        "Extract the admission record details from the user's message. "
        "If cnic is not mentioned, use an empty string. "
        "If another optional field is not mentioned, use null. "
        "Always return valid JSON that matches the provided schema exactly."
    )

    raw_content = _call_ollama(
        messages=[
            {"role": "system", "content": system_prompt},
            {"role": "user",   "content": user_text},
        ],
        schema=AdmissionRecord.model_json_schema(),
        label="Extracting single admission record",
    )

    record_dict = _safe_parse_json(raw_content)
    validated = AdmissionRecord(**record_dict)
    return _apply_regex_fallbacks(validated.model_dump(), user_text)


def extract_batch_data(raw_text: str, temperature: float = 0.0) -> list[dict]:
    """
    Send *raw_text* (which may describe many students) to Ollama and extract
    ALL student admission records in a single LLM pass, retrying once if the
    model returns an empty list.

    The response is forced to match the StudentBatch JSON schema and each
    AdmissionRecord inside it is Pydantic-validated before being returned.

    Note: system-generated metadata and field normalization are NOT applied
    here — that is the responsibility of admission_service.

    Args:
        raw_text (str): Unstructured text containing one or more student records.
        temperature (float): Temperature for the LLM (0.0 = deterministic, 1.0 = creative).
                            Defaults to 0.0.

    Returns:
        list[dict]: A list of dicts, each matching AdmissionRecord fields.
                    The list always has at least one entry on success.

    Raises:
        json.JSONDecodeError: If the model returns invalid JSON.
        ValueError:           If no student records are extracted after 2 attempts.
        RuntimeError:         On Ollama timeout or connectivity failure.
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
        "Extract cnic when provided; use an empty string if not mentioned. "
        "Do not skip any student, even if the information is incomplete or scattered. "
        "For other fields that are not mentioned, use null, including marks_percentage. "
        "Always return valid JSON that matches the provided schema exactly."
    )

    for attempt in range(2):
        raw_content = _call_ollama(
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user",   "content": raw_text},
            ],
            schema=StudentBatch.model_json_schema(),
            label=f"Extracting batch records (attempt {attempt + 1}/2)",
            temperature=temperature,
        )

        batch_dict = _safe_parse_json(raw_content)
        validated_batch = StudentBatch(**batch_dict)
        records = [student.model_dump() for student in validated_batch.students]

        if records:
            if len(records) == 1:
                _apply_regex_fallbacks(records[0], raw_text)
            else:
                source_positions = [
                    raw_text.casefold().find(record["name"].strip().casefold())
                    for record in records
                ]
                if all(position >= 0 for position in source_positions) and len(
                    set(source_positions)
                ) == len(records):
                    ordered_positions = sorted(source_positions)
                    for index, start in enumerate(source_positions):
                        end = next(
                            (
                                position
                                for position in ordered_positions
                                if position > start
                            ),
                            len(raw_text),
                        )
                        _apply_regex_fallbacks(records[index], raw_text[start:end])
                else:
                    # Can't slice reliably — run fallbacks on full text for every record
                    for record in records:
                        _apply_regex_fallbacks(record, raw_text)
            print(f"  [OK] Extracted {len(records)} student record(s) from the text.\n")
            return records

        if attempt == 0:
            print("  [!] No student records returned; retrying once...")

    raise ValueError("No student records were extracted after 2 attempts.")


def evaluate_student_eligibility(student_record: dict, policy_text: str) -> dict:
    """
    Ask Ollama to evaluate whether a student meets the admission criteria
    described in *policy_text*.

    The LLM acts as an admissions officer: it reads the policy, reviews the
    student record, and decides "Eligible" or "Rejected" with a brief
    reasoning string. The output is forced to match ValidationResult schema
    and Pydantic-validated before being returned as a plain dict.

    This function has NO knowledge of Excel, metadata, or business rules —
    it only handles the LLM call and schema validation.

    Args:
        student_record (dict): A fully prepared student record dict, including
                               student_id and application_status.
        policy_text    (str):  Raw text of admissions_policy.txt.

    Returns:
        dict: A dict with keys ``status`` ("Eligible" | "Rejected") and
              ``reasoning`` (str).

    Raises:
        json.JSONDecodeError:      If the model returns invalid JSON.
        pydantic.ValidationError:  If the JSON does not match ValidationResult.
        RuntimeError:              On Ollama timeout or connectivity failure.
    """
    system_prompt = (
        "You are a strict admissions officer. "
        "You must evaluate the student exactly according to the policy text. "
        "Think step-by-step: First, state the required threshold. "
        "Second, state the student's actual marks. "
        "Third, compare the two numbers mathematically. "
        "Fourth, declare the final status. "
        "Do not reject a student because their entry test is pending. "
        "Always return valid JSON that matches the provided schema exactly.\n\n"
        f"--- ADMISSIONS POLICY ---\n{policy_text}\n--- END OF POLICY ---"
    )

    user_message = (
        f"Please evaluate the following student record:\n"
        f"{json.dumps(student_record, indent=2)}"
    )

    raw_content = _call_ollama(
        messages=[
            {"role": "system", "content": system_prompt},
            {"role": "user",   "content": user_message},
        ],
        schema=ValidationResult.model_json_schema(),
        label=f"Evaluating '{student_record.get('name', '?')}' ({student_record.get('desired_program', '?')})",
    )

    result_dict = _safe_parse_json(raw_content)
    validated   = ValidationResult(**result_dict)
    return validated.model_dump()
