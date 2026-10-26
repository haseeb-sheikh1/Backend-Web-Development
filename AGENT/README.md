# Admissions Pipeline

A professional AI-powered admissions data extraction and processing system backed by Ollama (llama3.2).

## Project Structure

```
AGENT/
├── src/                          # Source code
│   ├── config.py                # Central configuration
│   ├── api.py                   # FastAPI web controller
│   ├── cli.py                   # Command-line interface
│   ├── services/                # Business logic layer
│   │   ├── admission_service.py
│   │   ├── score_scraper_service.py
│   │   ├── validator_service.py
│   │   └── merit_service.py
│   ├── repositories/            # Data access layer
│   │   └── excel_repository.py
│   ├── llm/                     # AI/LLM processing
│   │   └── extractor.py
│   ├── schemas/                 # Pydantic data models
│   │   └── schemas.py
│   └── interfaces/              # External interfaces
│       ├── document_reader.py
│       └── policy_reader.py
├── static/                      # Static files
│   └── index.html              # Web frontend
├── data/                        # Data files
│   ├── admissions.xlsx
│   └── admissions_policy.txt
├── scripts/                     # Utility scripts
│   ├── generate_test_scores.py
│   ├── generate_test_files.py
│   ├── local_excel_agent.py
│   └── main.py
└── README.md
```

## Installation

1. Install Python dependencies:
```bash
pip install -r requirements.txt
```

2. Ensure Ollama is running with the llama3.2 model:
```bash
ollama pull llama3.2
```

## Usage

### CLI Mode

**Interactive menu:**
```bash
python run_cli.py
```

**Batch file processing:**
```bash
python run_cli.py data/notes.txt
```

**Run validation:**
```bash
python run_cli.py --validate
```

**Calculate merit:**
```bash
python run_cli.py --calculate-merit
```
This updates `final_merit_score` and `application_status` in `data/admissions.xlsx`.

**Scrape entry-test scores for eligible students:**
```bash
pip install playwright
playwright install chromium
python src/cli.py --scrape-scores
```

### Web API Mode

**Start the FastAPI server:**
```bash
python run_api.py
```

Then visit http://localhost:8000

### Generate Test Data

**Generate mock entry-test scores in the master workbook:**
```bash
python scripts/generate_test_scores.py
```

## Pipeline Phases

1. **Phase 1**: Data extraction from unstructured text (CLI or API)
2. **Phase 2**: Eligibility validation using LLM, updating the master workbook
3. **Phase 3**: Entry-test score scraping, updating the master workbook
4. **Phase 4**: Merit calculation, updating the master workbook

All phases use `data/admissions.xlsx`; the workbook contains lifecycle columns
for validation notes, entry-test scores, and final merit from its creation.

## Configuration

Edit `src/config.py` to modify:
- LLM model name
- Input character limits
- Default statuses
- Merit calculation weights
- Excel output paths
