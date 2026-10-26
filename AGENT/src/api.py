"""
api.py
------
FastAPI web controller for the admissions pipeline.

This module is a pure HTTP adapter — it has no knowledge of LLMs, Excel,
normalization, or metadata. Its only responsibilities are:

  - Validate HTTP request inputs
  - Delegate all real work to admission_service
  - Return HTTP responses (or raise HTTPException on failure)

Routes
------
GET  /                  → Serves the static index.html frontend
POST /api/extract       → Single-record extraction from plain text
POST /api/batch-extract → Batch extraction from an uploaded file

Run with:
    uvicorn api:app --reload
"""

import json
import os
import shutil
import tempfile

from fastapi import FastAPI, File, HTTPException, UploadFile
from fastapi.responses import FileResponse

from src.schemas.schemas import ExtractionRequest
from src.services.admission_service import process_single, process_batch

# ---------------------------------------------------------------------------
# App setup
# ---------------------------------------------------------------------------

app = FastAPI(
    title="Admission Data Extraction API",
    description="AI-powered admission record extractor backed by Ollama llama3.2",
    version="2.0.0",
)


# ---------------------------------------------------------------------------
# Static frontend
# ---------------------------------------------------------------------------

@app.get("/", response_class=FileResponse, include_in_schema=False)
async def serve_frontend():
    """Serve the single-page frontend (index.html)."""
    index_path = os.path.join(os.path.dirname(__file__), "..", "static", "index.html")
    if not os.path.exists(index_path):
        raise HTTPException(status_code=404, detail="index.html not found")
    return FileResponse(index_path, media_type="text/html")


# ---------------------------------------------------------------------------
# Single-record extraction
# ---------------------------------------------------------------------------

@app.post("/api/extract")
async def extract_admission(payload: ExtractionRequest):
    """
    Extract a single structured admission record from plain-text input.

    Request body:
        { "text": "<free-form admission description>" }

    Returns:
        200 – Fully prepared admission record dict
        400 – Empty or whitespace-only input
        500 – Extraction or save failure
    """
    user_text = payload.text.strip()

    if not user_text:
        raise HTTPException(
            status_code=400,
            detail="Input text cannot be empty. Please describe the admission record.",
        )

    try:
        record = process_single(user_text)
    except json.JSONDecodeError:
        raise HTTPException(
            status_code=500,
            detail=(
                "The AI model returned malformed JSON. "
                "Try rephrasing your input and submit again."
            ),
        )
    except Exception as exc:
        raise HTTPException(
            status_code=500,
            detail=f"Extraction failed: {exc}",
        )

    return record


# ---------------------------------------------------------------------------
# Batch file-upload extraction
# ---------------------------------------------------------------------------

@app.post("/api/batch-extract")
async def batch_extract_admission(file: UploadFile = File(...)):
    """
    Upload a notes file (.txt, .docx, or .pdf) containing descriptions of
    multiple students. The AI reads the entire file in one pass and returns
    all extracted student records, saving them all to admissions.xlsx.

    Returns:
        200 – { "count": int, "records": list[dict] }
        400 – Unsupported file type or empty file content
        500 – Extraction or save failure
    """
    # --- Validate file extension ---
    allowed = {".txt", ".docx", ".pdf"}
    ext = os.path.splitext(file.filename or "")[1].lower()
    if ext not in allowed:
        raise HTTPException(
            status_code=400,
            detail=(
                f"Unsupported file type '{ext}'. "
                "Please upload a .txt, .docx, or .pdf file."
            ),
        )

    # --- Buffer the uploaded bytes to a temp file ---
    tmp_path: str | None = None
    try:
        with tempfile.NamedTemporaryFile(delete=False, suffix=ext) as tmp:
            shutil.copyfileobj(file.file, tmp)
            tmp_path = tmp.name
    except Exception as exc:
        raise HTTPException(
            status_code=500,
            detail=f"Failed to buffer the uploaded file: {exc}",
        )

    # --- Read text then delegate fully to the service layer ---
    try:
        from src.interfaces.document_reader import read_unstructured_file

        raw_text = read_unstructured_file(tmp_path)

        if not raw_text.strip():
            raise HTTPException(
                status_code=400,
                detail="The uploaded file appears to be empty.",
            )

        records = process_batch(raw_text)

    except HTTPException:
        raise  # re-raise 400s unchanged
    except json.JSONDecodeError:
        raise HTTPException(
            status_code=500,
            detail=(
                "The AI returned malformed JSON. "
                "Try simplifying the file or submitting again."
            ),
        )
    except Exception as exc:
        raise HTTPException(
            status_code=500,
            detail=f"Extraction failed: {exc}",
        )
    finally:
        # Always clean up the temp file regardless of outcome
        if tmp_path and os.path.exists(tmp_path):
            os.remove(tmp_path)

    return {"count": len(records), "records": records}
