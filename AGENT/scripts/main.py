"""
main.py
-------
FastAPI server that wraps local_excel_agent.py as a web API.

Routes
------
GET  /                  → Serves the static index.html frontend
POST /api/extract       → Accepts JSON { "text": "..." }, extracts ONE record
POST /api/batch-extract → Accepts a file upload (.txt / .docx / .pdf),
                          extracts ALL student records in one pass, and saves
                          them all to admissions.xlsx

Run with:
    uvicorn main:app --reload
"""

from fastapi import FastAPI, HTTPException, UploadFile, File
from fastapi.responses import FileResponse
from fastapi.staticfiles import StaticFiles
from pydantic import BaseModel
import json
import os
import tempfile
import shutil

# ---------------------------------------------------------------------------
# Import core logic
# ---------------------------------------------------------------------------
from local_excel_agent import extract_data, extract_batch_data, read_unstructured_file, save_to_excel

# ---------------------------------------------------------------------------
# App setup
# ---------------------------------------------------------------------------
app = FastAPI(
    title="Admission Data Extraction API",
    description="AI-powered admission record extractor backed by Ollama llama3.2",
    version="1.0.0",
)

# ---------------------------------------------------------------------------
# Request schema
# ---------------------------------------------------------------------------

class ExtractionRequest(BaseModel):
    text: str


# ---------------------------------------------------------------------------
# Routes
# ---------------------------------------------------------------------------

@app.get("/", response_class=FileResponse, include_in_schema=False)
async def serve_frontend():
    """Serve the single-page frontend."""
    index_path = os.path.join(os.path.dirname(__file__), "index.html")
    if not os.path.exists(index_path):
        raise HTTPException(status_code=404, detail="index.html not found")
    return FileResponse(index_path, media_type="text/html")


@app.post("/api/extract")
async def extract_admission(payload: ExtractionRequest):
    """
    Extract structured admission data from plain-text input.

    Request body:
        { "text": "<free-form admission description>" }

    Returns:
        200  – { name, email, phone, desired_program, marks_percentage }
        400  – Empty or whitespace-only input
        500  – Extraction or Excel-write failure
    """
    user_text = payload.text.strip()

    # --- Input validation ---
    if not user_text:
        raise HTTPException(
            status_code=400,
            detail="Input text cannot be empty. Please describe the admission record.",
        )

    # --- Extract structured data ---
    try:
        record = extract_data(user_text)
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
            detail=f"Extraction failed: {str(exc)}",
        )

    # --- Persist to Excel ---
    try:
        save_to_excel([record])
    except Exception as exc:
        raise HTTPException(
            status_code=500,
            detail=f"Record extracted but could not be saved to Excel: {str(exc)}",
        )

    return record


# ---------------------------------------------------------------------------
# Batch File Upload Route
# ---------------------------------------------------------------------------

@app.post("/api/batch-extract")
async def batch_extract_admission(file: UploadFile = File(...)):
    """
    Upload a notes file (.txt, .docx, or .pdf) containing messy descriptions
    of multiple students. The AI reads the entire file in one pass and returns
    all extracted student records, saving them all to admissions.xlsx.

    Returns:
        200 – { count: int, records: list[dict] }
        400 – Unsupported file type or empty file
        500 – Extraction or Excel-write failure
    """
    # --- Validate file extension ---
    allowed = {".txt", ".docx", ".pdf"}
    ext = os.path.splitext(file.filename or "")[1].lower()
    if ext not in allowed:
        raise HTTPException(
            status_code=400,
            detail=f"Unsupported file type '{ext}'. Please upload a .txt, .docx, or .pdf file.",
        )

    # --- Save the uploaded file to a temp path so read_unstructured_file can use it ---
    tmp_path = None
    try:
        with tempfile.NamedTemporaryFile(delete=False, suffix=ext) as tmp:
            shutil.copyfileobj(file.file, tmp)
            tmp_path = tmp.name
    except Exception as exc:
        raise HTTPException(status_code=500, detail=f"Failed to save uploaded file: {exc}")

    # --- Read, extract, and save ---
    try:
        raw_text = read_unstructured_file(tmp_path)

        if not raw_text.strip():
            raise HTTPException(status_code=400, detail="The uploaded file appears to be empty.")

        records = extract_batch_data(raw_text)

    except HTTPException:
        raise
    except json.JSONDecodeError:
        raise HTTPException(
            status_code=500,
            detail="The AI returned malformed JSON. Try simplifying the file or submitting again.",
        )
    except Exception as exc:
        raise HTTPException(status_code=500, detail=f"Extraction failed: {exc}")
    finally:
        # Always clean up the temp file
        if tmp_path and os.path.exists(tmp_path):
            os.remove(tmp_path)

    try:
        save_to_excel(records)
    except Exception as exc:
        raise HTTPException(
            status_code=500,
            detail=f"Records extracted but could not be saved to Excel: {exc}",
        )

    return {"count": len(records), "records": records}
