"""
local_excel_agent.py
--------------------
A local AI-powered admission data extraction agent that:
  - Accepts free-form user text describing an admission record
  - Uses Ollama (llama3.2) to extract structured data via a Pydantic schema
  - Saves the structured data to an Excel file (admissions.xlsx) using pandas

Dependencies: ollama, pydantic, pandas, openpyxl
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

import ollama
import pandas as pd
from pydantic import BaseModel, Field
from typing import Optional


# ---------------------------------------------------------------------------
# Pydantic Model
# ---------------------------------------------------------------------------

class AdmissionRecord(BaseModel):
    """
    Represents a single student admission record.
    All fields are extracted from free-form natural language input.
    """
    name: str = Field(description="Full name of the student")
    email: Optional[str] = Field(default=None, description="Email address of the student, if mentioned")
    phone: Optional[str] = Field(default=None, description="Phone number of the student, if mentioned")
    desired_program: str = Field(description="The academic program the student wants to enroll in")
    marks_percentage: float = Field(description="The student's marks or grade as a percentage (0–100)")


# ---------------------------------------------------------------------------
# Data Extraction via Ollama
# ---------------------------------------------------------------------------

def extract_data(user_text: str) -> dict:
    """
    Sends user_text to the Ollama llama3.2 model and extracts structured
    admission data by enforcing the AdmissionRecord JSON schema.

    Args:
        user_text (str): Raw natural language input from the user.

    Returns:
        dict: A dictionary matching the AdmissionRecord schema.
    """
    # System prompt instructs the model to act as a data extractor
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
        # Enforce structured output using the Pydantic model's JSON schema
        format=AdmissionRecord.model_json_schema(),
    )

    # Parse the JSON string returned by the model into a Python dict
    raw_content = response["message"]["content"]
    record_dict = json.loads(raw_content)

    # Validate the extracted data against our Pydantic model
    # This ensures type correctness and raises clear errors if data is invalid
    validated_record = AdmissionRecord(**record_dict)

    return validated_record.model_dump()


# ---------------------------------------------------------------------------
# Save to Excel
# ---------------------------------------------------------------------------

def save_to_excel(record_dict: dict, filename: str = "admissions.xlsx") -> None:
    """
    Saves the extracted admission record to an Excel file.
    - Appends to the file if it already exists.
    - Creates a new file with headers if it does not exist.

    Args:
        record_dict (dict): The structured admission record to save.
        filename (str):     Path/name of the Excel file. Defaults to 'admissions.xlsx'.
    """
    # Convert the single record dict into a one-row DataFrame
    new_row_df = pd.DataFrame([record_dict])

    if os.path.exists(filename):
        # --- File exists: load it and append the new row ---
        existing_df = pd.read_excel(filename, engine="openpyxl")
        updated_df = pd.concat([existing_df, new_row_df], ignore_index=True)
        print(f"  [+] Appending to existing file: '{filename}'")
    else:
        # --- File does not exist: start fresh ---
        updated_df = new_row_df
        print(f"  [+] Creating new file: '{filename}'")

    # Write back to Excel (openpyxl engine required for .xlsx format)
    updated_df.to_excel(filename, index=False, engine="openpyxl")
    print(f"  [OK] Record saved! Total records in file: {len(updated_df)}\n")


# ---------------------------------------------------------------------------
# Main Loop
# ---------------------------------------------------------------------------

def main():
    """
    Interactive terminal loop that continuously:
      1. Prompts the user to enter admission details in natural language
      2. Extracts structured data using Ollama + llama3.2
      3. Prints the clean extracted dictionary
      4. Saves the record to admissions.xlsx
    Type 'quit' or 'exit' to stop the loop.
    """
    print("=" * 60)
    print("  [*] Local Admission Data Extraction Agent")
    print("  Powered by Ollama (llama3.2) + Pydantic + Pandas")
    print("=" * 60)
    print("  Describe a student's admission details in plain English.")
    print("  Type 'quit' or 'exit' to stop.\n")

    while True:
        try:
            # --- Get user input ---
            user_text = input(">> Enter admission details: ").strip()

            # --- Exit conditions ---
            if user_text.lower() in ("quit", "exit", "q"):
                print("\nGoodbye! All records saved to 'admissions.xlsx'.")
                break

            if not user_text:
                print("  [!] Input cannot be empty. Please try again.\n")
                continue

            # --- Extract structured data via Ollama ---
            print("\n  [~] Extracting data with llama3.2...")
            record = extract_data(user_text)

            # --- Print the clean extracted dictionary ---
            print("\n  Extracted Record:")
            print("  " + "-" * 40)
            for key, value in record.items():
                print(f"  {key:<20}: {value}")
            print("  " + "-" * 40)

            # --- Save to Excel ---
            save_to_excel(record)

        except json.JSONDecodeError:
            print("\n  [ERROR] The model returned invalid JSON. Please try rephrasing your input.\n")
        except Exception as e:
            print(f"\n  [ERROR] An unexpected error occurred: {e}\n")


# ---------------------------------------------------------------------------
# Entry Point
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    main()
