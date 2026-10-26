"""
document_reader.py
------------------
Input I/O layer for the admissions pipeline.

Reads raw text from .txt, .docx, and .pdf files and enforces the
character-limit guardrail from config.

This module has no knowledge of LLMs, Excel, or business rules.
"""

import os
from src.config import MAX_INPUT_CHARACTERS


# ---------------------------------------------------------------------------
# Size guardrail
# ---------------------------------------------------------------------------

def _validate_text_size(text: str) -> str:
    """
    Raise ValueError if the text string exceeds MAX_INPUT_CHARACTERS.

    Args:
        text (str): The text to validate.

    Returns:
        str: The same text, unchanged, if it is within the limit.

    Raises:
        ValueError: If len(text) > MAX_INPUT_CHARACTERS.
    """
    if len(text) > MAX_INPUT_CHARACTERS:
        raise ValueError(
            f"Extracted text is too large ({len(text)} characters; "
            f"maximum is {MAX_INPUT_CHARACTERS}). "
            "Split the file into smaller files and try again."
        )
    return text


# ---------------------------------------------------------------------------
# Public reader
# ---------------------------------------------------------------------------

def read_unstructured_file(file_path: str) -> str:
    """
    Reads raw text from a file. Supported formats:
      - .txt  — read natively via Python's built-in open()
      - .docx — parsed with python-docx  (pip install python-docx)
      - .pdf  — parsed with pypdf         (pip install pypdf)

    Args:
        file_path (str): Absolute or relative path to the source file.

    Returns:
        str: The full raw text content of the file.

    Raises:
        FileNotFoundError: If the file does not exist.
        ValueError:        If the file extension is not supported or the
                           extracted text exceeds MAX_INPUT_CHARACTERS.
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
        print(
            f"  [+] Read .docx file: '{file_path}' "
            f"({len(paragraphs)} paragraphs, {len(text)} chars)"
        )
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
        print(
            f"  [+] Read .pdf file: '{file_path}' "
            f"({len(reader.pages)} pages, {len(text)} chars)"
        )
        return text

    else:
        raise ValueError(
            f"Unsupported file extension: '{ext}'. "
            "Supported formats: .txt, .docx, .pdf"
        )
