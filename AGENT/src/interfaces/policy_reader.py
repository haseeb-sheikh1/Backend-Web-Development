"""
policy_reader.py
----------------
Parses the admissions_policy.txt file into named sections.

Sections are delimited by [SECTION_NAME] headers. Each section's content
can be retrieved either as raw text (for LLM prompts) or as a parsed
key=value dict (for numeric configuration like merit weights).

Fallback values from config.py are used automatically when a section or
key is missing, so existing functionality never breaks.

Public API
----------
  get_section_text(section, policy_file)  -> str
      Returns the raw text lines under a given [SECTION] header.

  get_merit_config(policy_file)           -> dict
      Returns merit weights and threshold as floats, with safe fallbacks.
"""

import re
from src.config import (
    ACADEMIC_WEIGHT,
    ENTRY_TEST_WEIGHT,
    MINIMUM_FINAL_MERIT,
)

DEFAULT_POLICY_FILE = "admissions_policy.txt"


# ---------------------------------------------------------------------------
# Internal parser
# ---------------------------------------------------------------------------

def _parse_sections(policy_file: str) -> dict[str, str]:
    """
    Read *policy_file* and split it into a dict of {section_name: content}.

    Section names are the text between [ ] on a header line, uppercased.
    Content is everything between one header and the next (or EOF).

    Args:
        policy_file (str): Path to the policy text file.

    Returns:
        dict[str, str]: Mapping of section name → raw section text.
                        Returns an empty dict if the file cannot be read.
    """
    try:
        with open(policy_file, "r", encoding="utf-8") as fh:
            raw = fh.read()
    except OSError:
        return {}

    sections: dict[str, str] = {}
    current_name: str | None = None
    current_lines: list[str] = []

    for line in raw.splitlines():
        match = re.match(r"^\[([A-Za-z0-9_]+)\]\s*$", line.strip())
        if match:
            # Save previous section using a normalized name so header casing
            # does not affect lookups later in the module.
            if current_name is not None:
                sections[current_name] = "\n".join(current_lines).strip()
            current_name = match.group(1).upper()
            current_lines = []
        else:
            if current_name is not None:
                current_lines.append(line)

    # Save the last section
    if current_name is not None:
        sections[current_name] = "\n".join(current_lines).strip()

    return sections


# ---------------------------------------------------------------------------
# Public API
# ---------------------------------------------------------------------------

def get_section_text(
    section: str,
    policy_file: str = DEFAULT_POLICY_FILE,
) -> str:
    """
    Return the raw text content of a named section from the policy file.

    Useful for feeding the [ELIGIBILITY] section directly into an LLM prompt
    without exposing unrelated sections (e.g. [MERIT]).

    Args:
        section     (str): Section name to retrieve (case-insensitive).
        policy_file (str): Path to the policy file.
                           Defaults to 'admissions_policy.txt'.

    Returns:
        str: The raw text of the requested section, or an empty string if
             the section is not found.
    """
    sections = _parse_sections(policy_file)
    return sections.get(section.strip().upper(), "")


def get_merit_config(policy_file: str = DEFAULT_POLICY_FILE) -> dict:
    """
    Parse the [MERIT] section of the policy file and return merit parameters
    as a typed dict with safe fallbacks to config.py defaults.

    Expected format inside [MERIT]:
        academic_weight      = 0.6
        entry_test_weight    = 0.4
        minimum_final_merit  = 70.0

    Args:
        policy_file (str): Path to the policy file.
                           Defaults to 'admissions_policy.txt'.

    Returns:
        dict with keys:
            - ``academic_weight``     (float)
            - ``entry_test_weight``   (float)
            - ``minimum_final_merit`` (float)
        Falls back to config.py values for any key that is missing or
        cannot be parsed as a float.
    """
    # Start with hardcoded defaults — guarantees nothing breaks
    result = {
        "academic_weight":     ACADEMIC_WEIGHT,
        "entry_test_weight":   ENTRY_TEST_WEIGHT,
        "minimum_final_merit": MINIMUM_FINAL_MERIT,
    }

    sections = _parse_sections(policy_file)
    merit_text = sections.get("MERIT", "")

    for line in merit_text.splitlines():
        if "=" not in line:
            continue
        key, _, value = line.partition("=")
        key   = key.strip().lower()
        value = value.strip()

        if key in result:
            try:
                result[key] = float(value)
            except ValueError:
                pass  # Keep the default if value is not a valid float

    return result
