"""
cli.py
------
Command-line interface for the admissions pipeline.

This module is a pure terminal adapter — it has no knowledge of LLMs, Excel,
normalization, or metadata. Its only responsibilities are:

  - Read input from the terminal (or sys.argv)
  - Print results / errors to stdout
  - Delegate all real work to the relevant service

Usage
-----
Top-level menu (default):
    python cli.py

Batch file mode (skips the menu):
    python cli.py <path-to-notes-file>

Entry-test score scraping:
    python cli.py --scrape-scores
"""

import json
import sys

from src.services.admission_service import process_single, process_batch
from src.services.validator_service import run_validation
from src.services.merit_service import run_merit_calculation
from src.interfaces.document_reader import read_unstructured_file


# ---------------------------------------------------------------------------
# Interactive single-record loop
# ---------------------------------------------------------------------------

def run_interactive() -> None:
    """
    Continuously prompt the user for natural-language admission descriptions,
    extract a single record per entry, and save each to admissions.xlsx.
    Type 'quit', 'exit', or 'q' to stop.
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
            record = process_single(user_text)

            print("\n  Extracted Record:")
            print("  " + "-" * 40)
            for key, value in record.items():
                print(f"  {key:<20}: {value}")
            print("  " + "-" * 40 + "\n")

        except json.JSONDecodeError:
            print(
                "\n  [ERROR] The model returned invalid JSON. "
                "Please try rephrasing your input.\n"
            )
        except Exception as exc:
            print(f"\n  [ERROR] An unexpected error occurred: {exc}\n")


# ---------------------------------------------------------------------------
# Batch file processing
# ---------------------------------------------------------------------------

def run_batch(notes_file: str) -> None:
    """
    Read a notes file, extract all student records in one LLM pass,
    print each record to stdout, and save them all to admissions.xlsx.

    Args:
        notes_file (str): Path to the .txt / .docx / .pdf notes file.
    """
    print("=" * 60)
    print("  [*] Batch File Extraction Mode")
    print(f"  Source: {notes_file}")
    print("=" * 60)

    try:
        # Step 1 — Read the unstructured notes file
        raw_text = read_unstructured_file(notes_file)

        # Step 2 — Delegate to the service (extract + normalize + metadata + save)
        records = process_batch(raw_text)

        # Step 3 — Display each finalized record
        print("\n  Extracted Records:")
        print("  " + "-" * 50)
        for i, rec in enumerate(records, start=1):
            print(f"\n  Student #{i}")
            for key, value in rec.items():
                print(f"    {key:<20}: {value}")
        print("\n  " + "-" * 50)

    except FileNotFoundError as exc:
        print(f"\n  [ERROR] {exc}")
    except ValueError as exc:
        print(f"\n  [ERROR] {exc}")
    except ImportError as exc:
        print(f"\n  [ERROR] Missing library — {exc}")
    except json.JSONDecodeError:
        print(
            "\n  [ERROR] The model returned invalid JSON. "
            "Try again or simplify the input file."
        )
    except Exception as exc:
        print(f"\n  [ERROR] Unexpected error: {exc}")


# ---------------------------------------------------------------------------
# Validator Agent menu option
# ---------------------------------------------------------------------------

def run_validation_cli(prompt_for_paths: bool = True) -> None:
    """
    Invoke the Validator Agent pipeline from the terminal.

    Prompts for optional custom file paths when selected from the menu.
    The --validate command uses the service defaults without prompting.
    Delegates to validator_service.run_validation() and prints a summary table
    of every evaluated student.

    Args:
        prompt_for_paths: Whether to prompt for custom input paths.
    """
    print("\n" + "=" * 60)
    print("  [*] Validator Agent — Eligibility Checker")
    print("=" * 60)
    if prompt_for_paths:
        print("  Press Enter to accept the default file paths.\n")

    if prompt_for_paths:
        policy_file = input("  Policy file  [data/admissions_policy.txt] : ").strip()
        master_file = input("  Master Excel [data/admissions.xlsx]        : ").strip()
        policy_file = policy_file or "data/admissions_policy.txt"
        master_file = master_file or "data/admissions.xlsx"

    try:
        if prompt_for_paths:
            results = run_validation(
                policy_file=policy_file,
                master_file=master_file,
            )
        else:
            results = run_validation()

        if results:
            print("\n  Validation Results:")
            print("  " + "-" * 70)
            for i, res in enumerate(results, start=1):
                status  = res.get("application_status", "?")
                reason  = res.get("eligibility_reason", "?")
                name    = res.get("name",            "?")
                program = res.get("desired_program", "?")
                marks   = res.get("marks_percentage", "?")

                symbol = "✅" if status == "Eligible" else "❌"
                print(f"\n  {i}. {symbol}  {name}")
                print(f"     Programme : {program}")
                print(f"     Marks     : {marks}")
                print(f"     Decision  : {status}")
                print(f"     Reason    : {reason}")
            print("\n  " + "-" * 70 + "\n")

    except FileNotFoundError as exc:
        print(f"\n  [ERROR] {exc}\n")
    except ValueError as exc:
        print(f"\n  [ERROR] {exc}\n")
    except Exception as exc:
        print(f"\n  [ERROR] Unexpected error during validation: {exc}\n")


# ---------------------------------------------------------------------------
# Top-level interactive menu
# ---------------------------------------------------------------------------

def run_menu() -> None:
    """
    Present a numbered menu so the user can choose which pipeline to run.
    Loops until the user selects Exit.
    """
    print("\n" + "=" * 60)
    print("  Admissions Pipeline — Main Menu")
    print("=" * 60)

    options = {
        "1": ("Enter a student record (interactive)",   run_interactive),
        "2": ("Run Validator Agent (eligibility check)", run_validation_cli),
        "3": ("Exit",                                   None),
    }

    while True:
        print()
        for key, (label, _) in options.items():
            print(f"  [{key}] {label}")
        choice = input("\n  Select an option: ").strip()

        if choice not in options:
            print("  [!] Invalid choice. Please enter 1, 2, or 3.")
            continue

        label, fn = options[choice]
        if fn is None:
            print("\n  Goodbye!\n")
            break
        fn()


# ---------------------------------------------------------------------------
# Entry point
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    if len(sys.argv) > 1:
        if sys.argv[1] == "--validate":
            run_validation_cli(prompt_for_paths=False)
        elif sys.argv[1] == "--calculate-merit":
            run_merit_calculation()
        elif sys.argv[1] == "--scrape-scores":
            from src.services.score_scraper_service import run_score_scraper

            run_score_scraper()
        else:
            # Any non-flag argument is a batch input file path.
            run_batch(sys.argv[1])
    else:
        run_menu()
