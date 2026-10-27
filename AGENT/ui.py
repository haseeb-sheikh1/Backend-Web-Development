"""
ui.py  —  Admissions Pipeline internal dashboard.
Run with:  streamlit run ui.py
"""

import io
import os
import contextlib
import tempfile
import uuid

import pandas as pd
import streamlit as st

from src.config import (
    DEFAULT_APPLICATION_STATUS,
    EXCEL_COLUMNS,
    EXCEL_FILENAME,
)
from src.repositories.excel_repository import load_excel_file
from src.services import notification_service


# ── Page config ──────────────────────────────────────────────────────────────

st.set_page_config(page_title="UAJK Automated admissions", layout="wide",
                   initial_sidebar_state="expanded")


# ── CSS ───────────────────────────────────────────────────────────────────────

st.markdown("""
<style>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@300;400;500&display=swap');

*, html, body {
    font-family: 'IBM Plex Mono', 'Courier New', monospace !important;
}

/* Keep Material icons as icons. The font rule above would otherwise render
   their ligature names (e.g. "keyboard_arrow_right") over neighbouring text. */
[data-testid="stIconMaterial"] {
    font-family: 'Material Symbols Rounded' !important;
    font-weight: normal !important;
    font-style: normal !important;
    font-feature-settings: 'liga' !important;
    -webkit-font-feature-settings: 'liga' !important;
    letter-spacing: normal !important;
    text-transform: none !important;
    white-space: nowrap !important;
    line-height: 1 !important;
    overflow: hidden !important;
}

/* App shell */
.stApp { background: #0a0a0a; color: #c0c0c0; }
.main .block-container {
    max-width: 1200px !important;
    padding-top: 1rem !important;
    padding-bottom: 1rem !important;
}

/* Kill default Streamlit padding */
.block-container { padding: 1rem 1.5rem 1rem 1.5rem !important; max-width: 1200px !important; margin: 0 auto !important; }

/* Sidebar */
section[data-testid="stSidebar"] {
    background: #0a0a0a;
    border-right: 1px solid #1e1e1e;
}
section[data-testid="stSidebar"] > div { padding: 1rem 0.75rem; }

/* All text in sidebar */
section[data-testid="stSidebar"] { color: #888 !important; }

/* Sidebar phase buttons */
section[data-testid="stSidebar"] .stButton > button {
    width: 100%;
    background: transparent;
    color: #b0c8b0 !important;
    border: 1px solid #222;
    border-radius: 2px;
    font-family: 'IBM Plex Mono', monospace !important;
    font-size: 0.72rem;
    letter-spacing: 0.02em;
    padding: 0.4rem 0.6rem;
    text-align: left;
    margin-bottom: 2px;
    transition: border-color 0.1s, color 0.1s;
}
section[data-testid="stSidebar"] .stButton > button:hover {
    border-color: #444;
    color: #d0e8d0 !important;
    background: #111;
}

/* Main buttons (Refresh) */
.stButton > button {
    background: transparent;
    color: #666 !important;
    border: 1px solid #222;
    border-radius: 2px;
    font-family: 'IBM Plex Mono', monospace !important;
    font-size: 0.75rem;
    padding: 0.3rem 0.7rem;
}
.stButton > button:hover { border-color: #444; color: #999 !important; }

/* Headings */
h1, h2, h3, h4 {
    color: #d0d0d0 !important;
    font-weight: 500 !important;
    letter-spacing: 0.08em;
}
h3 {
    font-size: 0.9rem !important;
}

/* Divider */
hr { border: none; border-top: 1px solid #1a1a1a !important; margin: 1rem 0; }

/* Terminal block */
.term {
    background: #080808;
    border: 1px solid #1e1e1e;
    border-radius: 2px;
    padding: 0.9rem 1.1rem;
    font-size: 0.76rem;
    line-height: 1.65;
    color: #7ab87a;
    white-space: pre-wrap;
    word-break: break-word;
    max-height: 380px;
    overflow-y: auto;
    min-height: 80px;
}

/* Alerts */
div[data-testid="stAlert"] {
    border-radius: 2px;
    font-size: 0.78rem;
    background: transparent !important;
    border-left-width: 2px !important;
}

/* Dataframe */
.stDataFrame { border: 1px solid #1a1a1a !important; }
[data-testid="stDataFrame"] * { font-size: 0.76rem !important; }

/* File Uploader */
[data-testid="stFileUploader"] {
    background: #111 !important;
    border: 1px solid #333 !important;
    border-radius: 4px !important;
    padding: 0.5rem !important;
}
[data-testid="stFileUploader"] > label p {
    color: #888 !important;
    font-size: 0.75rem !important;
}
[data-testid="stFileUploader"] [data-testid="stFileUploaderDropzone"] {
    background: #1a1a1a !important;
    border: 1px dashed #444 !important;
    border-radius: 4px !important;
    padding: 0.75rem !important;
}
[data-testid="stFileUploaderDropzoneInstructions"],
[data-testid="stFileUploaderDropzoneInstructions"] span {
    color: #666 !important;
    font-size: 0.65rem !important;
}
[data-testid="stFileUploader"] button {
    background: #222 !important;
    color: #b0c8b0 !important;
    border: 1px solid #444 !important;
    border-radius: 4px !important;
    font-family: 'IBM Plex Mono', monospace !important;
    font-size: 0.7rem !important;
    padding: 0.25rem 0.6rem !important;
    margin: 0 !important;
}
[data-testid="stFileUploader"] button:hover {
    background: #333 !important;
    border-color: #555 !important;
    color: #d0e8d0 !important;
}

/* Remove Streamlit branding */
#MainMenu, footer, header { visibility: hidden; }

/* Sidebar label override */
.sidebar-label {
    font-size: 0.68rem;
    color: #333 !important;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin-bottom: 0.6rem;
    display: block;
}

/* Main content cards */
.content-card {
    background: #111;
    border: 1px solid #1e1e1e;
    border-radius: 4px;
    padding: 0.75rem 1rem;
    margin-bottom: 0.75rem;
}
.content-card h3 {
    color: #d0d0d0 !important;
    font-size: 0.8rem;
    font-weight: 500;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
    border-bottom: 1px solid #1e1e1e;
    padding-bottom: 0.3rem;
}

/* Sidebar expanders (Streamlit 1.66 uses <details>/<summary>) */
section[data-testid="stSidebar"] div[data-testid="stExpander"] {
    border: none !important;
    background: transparent !important;
    margin-bottom: 0.35rem !important;
}
section[data-testid="stSidebar"] div[data-testid="stExpander"] > details > summary {
    background: transparent !important;
    border: 1px solid #1e1e1e !important;
    border-radius: 2px !important;
    padding: 0.35rem 0.6rem !important;
    min-height: 2rem !important;
    color: #777 !important;
    transition: border-color 0.1s, color 0.1s;
}
section[data-testid="stSidebar"] div[data-testid="stExpander"] > details > summary:hover {
    border-color: #444 !important;
    color: #b0c8b0 !important;
}
section[data-testid="stSidebar"] div[data-testid="stExpander"] > details > summary p {
    font-family: 'IBM Plex Mono', monospace !important;
    font-size: 0.72rem !important;
    letter-spacing: 0.03em !important;
    color: #777 !important;
    margin: 0 !important;
}
section[data-testid="stSidebar"] div[data-testid="stExpander"] > details > summary:hover p {
    color: #b0c8b0 !important;
}
section[data-testid="stSidebar"] div[data-testid="stExpanderDetails"] {
    background: #0d0d0d !important;
    border: 1px solid #1e1e1e !important;
    border-top: none !important;
    padding: 0.75rem !important;
}

/* Sidebar widgets — Streamlit's light-theme label/input colours are
   unreadable on the dark panel, so bring them into the app palette. */
section[data-testid="stSidebar"] div[data-testid="stExpanderDetails"] label p,
section[data-testid="stSidebar"] div[data-testid="stExpanderDetails"] [data-testid="stWidgetLabel"] p {
    color: #999 !important;
    text-transform: none !important;
}
section[data-testid="stSidebar"] div[data-testid="stExpanderDetails"] [data-testid="stSliderThumbValue"] {
    color: #7ab87a !important;
}
section[data-testid="stSidebar"] div[data-testid="stExpanderDetails"] input,
section[data-testid="stSidebar"] div[data-testid="stExpanderDetails"] textarea {
    color: #c0c0c0 !important;
    background: #0a0a0a !important;
    border-color: #222 !important;
    font-size: 0.8rem !important;
}
/* Wrappers keep Streamlit's light-theme white background around the inputs. */
section[data-testid="stSidebar"] div[data-testid="stExpanderDetails"] [data-testid="stNumberInputContainer"],
section[data-testid="stSidebar"] div[data-testid="stExpanderDetails"] [data-testid="stTextInputRootElement"],
section[data-testid="stSidebar"] div[data-testid="stExpanderDetails"] [data-testid="stTextAreaRootElement"] {
    background: #0a0a0a !important;
    border: 1px solid #222 !important;
    border-radius: 4px !important;
    overflow: hidden !important;
}
section[data-testid="stSidebar"] div[data-testid="stExpanderDetails"] [data-testid="stNumberInputContainer"] input,
section[data-testid="stSidebar"] div[data-testid="stExpanderDetails"] [data-testid="stTextAreaRootElement"] textarea {
    border: none !important;
}
section[data-testid="stSidebar"] div[data-testid="stExpanderDetails"] [data-testid="stNumberInputStepDown"],
section[data-testid="stSidebar"] div[data-testid="stExpanderDetails"] [data-testid="stNumberInputStepUp"] {
    background: #111 !important;
    color: #999 !important;
    border: none !important;
    border-left: 1px solid #222 !important;
}
section[data-testid="stSidebar"] div[data-testid="stExpanderDetails"] [data-testid="stNumberInputStepDown"]:hover,
section[data-testid="stSidebar"] div[data-testid="stExpanderDetails"] [data-testid="stNumberInputStepUp"]:hover {
    background: #1f1f1f !important;
    color: #d0e8d0 !important;
}

/* Live analytics (main content) */
.block-container div[data-testid="stExpander"] {
    border: 1px solid #1e1e1e !important;
    border-radius: 3px !important;
    background: #0d0d0d !important;
    margin-bottom: 1rem !important;
}
.block-container div[data-testid="stExpander"] > details > summary {
    background: #111 !important;
    color: #999 !important;
    border-bottom: 1px solid #1e1e1e !important;
    padding: 0.45rem 0.75rem !important;
    min-height: 2rem !important;
}
.block-container div[data-testid="stExpander"] > details > summary p {
    color: #b0c8b0 !important;
    font-size: 0.8rem !important;
    letter-spacing: 0.02em !important;
    margin: 0 !important;
}
.block-container div[data-testid="stExpander"] > details > summary:hover p {
    color: #d0e8d0 !important;
}
.block-container div[data-testid="stExpander"] > details > summary [data-testid="stIconMaterial"] {
    color: #777 !important;
}
.block-container div[data-testid="stExpanderDetails"] {
    background: #0d0d0d !important;
    padding: 0.9rem 0.9rem 0.5rem !important;
}

[data-testid="stMetric"] {
    background: #111 !important;
    border: 1px solid #1e1e1e !important;
    border-radius: 4px !important;
    padding: 0.6rem 0.6rem !important;
}
[data-testid="stMetricLabel"],
[data-testid="stMetricLabel"] p {
    color: #777 !important;
    font-size: 0.55rem !important;
    letter-spacing: 0.03em !important;
    text-transform: uppercase !important;
}
[data-testid="stMetricLabel"] .stMarkdownEllipsis,
[data-testid="stMetricLabel"] .stMarkdownEllipsis p {
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: clip !important;
    word-break: normal !important;
    overflow-wrap: normal !important;
    line-height: 1.25 !important;
    margin: 0 !important;
}
[data-testid="stMetricValue"] {
    color: #d0e8d0 !important;
    font-size: 1.55rem !important;
}

/* st.bar_chart inherits Streamlit's light Vega theme — darken it in place. */
[data-testid="stVegaLiteChart"] svg {
    background-color: transparent !important;
}
[data-testid="stVegaLiteChart"] svg text {
    fill: #999 !important;
}
[data-testid="stVegaLiteChart"] svg .role-axis-grid line {
    stroke: #1e1e1e !important;
}
[data-testid="stVegaLiteChart"] svg .role-axis line,
[data-testid="stVegaLiteChart"] svg .role-axis path {
    stroke: #333 !important;
}

/* Login page styling */
.login-container {
    max-width: 350px;
    margin: 0 auto;
    padding: 1.5rem;
    background: #111;
    border: 1px solid #333;
    border-radius: 6px;
    text-align: center;
}
.login-title {
    color: #d0d0d0 !important;
    font-size: 1.1rem;
    font-weight: 500;
    letter-spacing: 0.05em;
    margin-bottom: 0.3rem;
}
.login-subtitle {
    color: #666;
    font-size: 0.75rem;
    margin-bottom: 1rem;
}

/* Center login form */
[data-testid="stVerticalBlock"] > [style*="flex-direction: column"] > [data-testid="stVerticalBlock"] {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 1rem 0;
}

/* Login input styling */
div[data-testid="stTextInput"] > div > div > input {
    background: #0a0a0a !important;
    border: 1px solid #333 !important;
    border-radius: 4px !important;
    color: #c0c0c0 !important;
    padding: 0.75rem 1rem !important;
    font-family: 'IBM Plex Mono', monospace !important;
}
div[data-testid="stTextInput"] > div > div > input:focus {
    border-color: #555 !important;
    outline: none !important;
}
div[data-testid="stTextInput"] > label {
    color: #888 !important;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

/* Login button styling */
button[kind="primary"] {
    width: 100% !important;
    margin-top: 1.5rem !important;
    background: #1a1a1a !important;
    color: #b0c8b0 !important;
    border: 1px solid #333 !important;
    border-radius: 4px !important;
    font-family: 'IBM Plex Mono', monospace !important;
    font-size: 0.85rem !important;
    letter-spacing: 0.05em !important;
    padding: 0.75rem 1.5rem !important;
    transition: all 0.2s ease !important;
}
button[kind="primary"]:hover {
    background: #222 !important;
    border-color: #444 !important;
    color: #d0e8d0 !important;
}

/* Download button styling */
.stDownloadButton > button {
    background: #1a1a1a !important;
    color: #b0c8b0 !important;
    border: 1px solid #333 !important;
    border-radius: 4px !important;
    font-family: 'IBM Plex Mono', monospace !important;
    font-size: 0.85rem !important;
    letter-spacing: 0.05em !important;
    padding: 0.75rem 1.5rem !important;
    transition: all 0.2s ease !important;
}
.stDownloadButton > button:hover {
    background: #222 !important;
    border-color: #444 !important;
    color: #d0e8d0 !important;
}

/* Metrics styling */
[data-testid="stMetric"] {
    background: #111 !important;
    border: 1px solid #1e1e1e !important;
    border-radius: 4px !important;
    padding: 0.75rem !important;
}
[data-testid="stMetric"] > div {
    color: #888 !important;
    font-family: 'IBM Plex Mono', monospace !important;
    font-size: 0.7rem !important;
}
[data-testid="stMetric"] > div > div {
    color: #d0d0d0 !important;
    font-family: 'IBM Plex Mono', monospace !important;
    font-size: 1.1rem !important;
    font-weight: 500 !important;
}

/* Chart styling */
[data-testid="stElementContainer"] > div > div {
    background: #111 !important;
    border: 1px solid #1e1e1e !important;
    border-radius: 4px !important;
    padding: 0.75rem !important;
}
</style>
""", unsafe_allow_html=True)


# ── Helpers ───────────────────────────────────────────────────────────────────

def _load_df():
    if not os.path.exists(EXCEL_FILENAME):
        return None
    try:
        return load_excel_file(EXCEL_FILENAME)
    except Exception as exc:
        st.error(str(exc))
        return None


def _render_analytics(df: pd.DataFrame) -> None:
    """Render live admission KPIs and a status distribution chart."""
    status = (
        df["application_status"]
        .fillna("Unknown")
        .astype(str)
        .str.strip()
        .replace("", "Unknown")
    )

    total_applicants = len(df)
    total_admitted   = int(status.eq("Admitted").sum())
    total_waitlisted = int(status.eq("Waitlisted").sum())
    total_rejected   = int(status.eq("Rejected").sum())

    with st.expander("Live Admission Analytics", expanded=False):
        cols = st.columns(4)
        cols[0].metric("Total Applicants", total_applicants)
        cols[1].metric("Total Admitted",   total_admitted)
        cols[2].metric("Total Waitlisted", total_waitlisted)
        cols[3].metric("Total Rejected",   total_rejected)

        status_counts = (
            df.assign(application_status=status)
            .groupby("application_status")
            .size()
        )

        if status_counts.empty:
            st.caption("No application status data available yet.")
        else:
            chart_df = status_counts.rename("applicants").reset_index()
            st.bar_chart(
                chart_df,
                x="application_status",
                y="applicants",
                x_label="",
                y_label="",
                color="#7ab87a",
                height=200,
            )


def _services():
    from src.services.admission_service     import process_batch
    from src.services.validator_service     import run_validation
    from src.services.score_scraper_service import run_score_scraper
    from src.services.merit_service         import run_merit_calculation
    return process_batch, run_validation, run_score_scraper, run_merit_calculation


def _run(fn):
    """
    Run a service call with its console output suppressed.

    The dashboard intentionally does not stream service logs or model tokens.
    Callers show one concise success/failure message instead.

    Returns:
        tuple: (result, error_reason) — exactly one of the two is not None.
    """
    try:
        with contextlib.redirect_stdout(io.StringIO()), \
             contextlib.redirect_stderr(io.StringIO()):
            return fn(), None
    except Exception as exc:
        reason = str(exc).strip() or type(exc).__name__
        return None, reason


# Valid statuses understood by the validator and merit services.
_EDITOR_STATUSES = ["Under Review", "Eligible", "Rejected", "Admitted", "Waitlisted"]
_EDITOR_SCORE_COLUMNS = ["entry_test_score", "final_merit_score"]


def _coerce_score(value: object) -> float:
    """Turn an edited score cell into a number, defaulting to 0.0."""
    if pd.isna(value):
        return 0.0
    if isinstance(value, str):
        text = value.strip()
        if not text:
            return 0.0
        try:
            return float(text)
        except ValueError:
            return 0.0
    try:
        return float(value)
    except (TypeError, ValueError):
        return 0.0


def _coerce_mark(value: object) -> object:
    """Normalise a marks cell while preserving text such as 'Pending'."""
    if pd.isna(value):
        return ""
    if isinstance(value, str):
        text = value.strip()
        if not text:
            return ""
        try:
            number = float(text.rstrip("%"))
        except ValueError:
            return text
        return int(number) if number.is_integer() else number
    return value


def _normalise_edited_df(df: pd.DataFrame) -> pd.DataFrame:
    """
    Turn raw st.data_editor output into a workbook-ready DataFrame.

    - Enforces the canonical column order.
    - Drops rows the admin added but never filled in (no name, no student_id).
    - Generates a student_id for every genuinely new row.
    - Converts score cells to numbers and text cells to trimmed strings.

    Raises:
        ValueError: If edited rows share a student_id.
    """
    edited = df.reindex(columns=EXCEL_COLUMNS).copy()

    # Normalise every cell before deciding which rows are genuinely blank.
    for column in EXCEL_COLUMNS:
        if column in _EDITOR_SCORE_COLUMNS:
            edited[column] = edited[column].map(_coerce_score)
        elif column == "marks_percentage":
            edited[column] = edited[column].map(_coerce_mark)
        else:
            edited[column] = edited[column].map(
                lambda value: "" if pd.isna(value) else str(value).strip()
            )

    # A row added with "num_rows='dynamic'" stays blank until the admin types.
    edited = edited[
        edited["student_id"].ne("") | edited["name"].ne("")
    ].reset_index(drop=True)

    missing_id = edited["student_id"].eq("")
    if missing_id.any():
        edited.loc[missing_id, "student_id"] = [
            f"STU-{uuid.uuid4().hex[:8].upper()}"
            for _ in range(int(missing_id.sum()))
        ]

    # A manually added applicant enters the pipeline at its normal starting state.
    edited.loc[edited["application_status"].eq(""), "application_status"] = (
        DEFAULT_APPLICATION_STATUS
    )

    duplicates = edited.loc[edited["student_id"].duplicated(), "student_id"]
    if not duplicates.empty:
        raise ValueError(
            "duplicate student_id value(s): " + ", ".join(sorted(set(duplicates)))
        )

    return edited


def _save_edited_df(df: pd.DataFrame) -> pd.DataFrame:
    """
    Persist manual edits to the master workbook in one atomic write.

    The workbook is written to a temporary file and then swapped into place,
    so a failed write can never leave a half-written admissions.xlsx behind.

    Returns:
        pd.DataFrame: The normalised rows that were written.

    Raises:
        PermissionError: If Excel (or another program) holds the file open.
        Exception:      Any other write failure, surfaced to the UI.
    """
    cleaned = _normalise_edited_df(df)

    directory = os.path.dirname(os.path.abspath(EXCEL_FILENAME)) or "."
    os.makedirs(directory, exist_ok=True)

    handle, temp_path = tempfile.mkstemp(suffix=".xlsx", dir=directory)
    os.close(handle)
    try:
        cleaned.to_excel(temp_path, index=False, engine="openpyxl")
        os.replace(temp_path, EXCEL_FILENAME)
    except PermissionError as exc:
        raise PermissionError(
            f"could not write '{EXCEL_FILENAME}' — "
            "close the file in Excel and try again"
        ) from exc
    finally:
        if os.path.exists(temp_path):
            os.remove(temp_path)

    return cleaned


if not st.session_state.get("authenticated"):
    st.markdown("""
    <div class="login-container">
        <h1 class="login-title">UAJK Automated admissions</h1>
    </div>
    """, unsafe_allow_html=True)

    st.markdown("<br><br>", unsafe_allow_html=True)

    col1, col2, col3 = st.columns([1, 2, 1])
    with col2:
        username = st.text_input("Username", key="login_username")
        password = st.text_input("Password", type="password", key="login_password")

        if st.button("Login", key="login_button", use_container_width=True):
            if username == "admin" and password == "admin123":
                st.session_state["authenticated"] = True
                st.rerun()
            else:
                st.error("Invalid username or password")


if st.session_state.get("authenticated"):
    # ── Main container for focused layout ─────────────────────────────────────

    # ── Sidebar ───────────────────────────────────────────────────────────────

    with st.sidebar:
        st.markdown('<span class="sidebar-label">Agents</span>', unsafe_allow_html=True)

        run_p1 = st.button("01  /  Batch Extraction",  key="p1")

        with st.expander("Phase 2 Settings", expanded=False):
            use_custom_policy = st.checkbox(
                "Override Default Policy File with Custom Rules",
                value=False,
                key="p2_use_custom_policy",
            )
            custom_policy = None

            if use_custom_policy:
                min_marks = st.number_input(
                    "Minimum Intermediate Marks (%)",
                    value=50,
                    key="p2_min_marks",
                )
                program = st.text_input(
                    "Required Program Name",
                    value="BS Software Engineering",
                    key="p2_program",
                )
                extra_rules = st.text_area(
                    "Additional Policy Rules",
                    value="The applicant must have valid contact information.",
                    key="p2_extra_rules",
                )

                # Format into a single policy string
                custom_policy = (
                    f"Require {min_marks}% marks for {program}. "
                    f"Additional rules: {extra_rules}"
                ).strip()

        run_p2 = st.button("02  /  Validator",          key="p2")
        run_p3 = st.button("03  /  Scraper",             key="p3")

        with st.expander("Advanced Settings", expanded=False):
            show_browser = st.checkbox("Run Scraper Visibly (Headless Off)", value=False)
            llm_temp = st.slider("LLM Temperature (Creativity)", min_value=0.0, max_value=1.0, value=0.0, step=0.1)

        with st.expander("Phase 4 Settings", expanded=False):
            entry_test_weight = st.slider(
                "Entry Test Weight (%)",
                min_value=10,
                max_value=90,
                value=50,
                step=5,
                key="p4_entry_weight",
            )
            inter_weight = 100 - entry_test_weight
            st.caption(f"Intermediate marks weight: {inter_weight}%")

            passing_threshold = st.slider(
                "Minimum Merit to Admit (%)",
                min_value=40,
                max_value=90,
                value=50,
                step=1,
                key="p4_threshold",
            )

        run_p4 = st.button("04  /  Merit Calculator",   key="p4")

        with st.expander("05 / Email Communications", expanded=False):
            target_statuses = st.multiselect(
                "Select Statuses to Email",
                list(notification_service.TARGET_STATUS_CHOICES),
                default=["Admitted"],
                key="p5_statuses",
            )

            admitted_template = st.text_area(
                "Admitted Email Template",
                notification_service.DEFAULT_ADMITTED_TEMPLATE,
                key="p5_admitted_template",
                height=130,
            )
            rejected_template = st.text_area(
                "Waitlisted/Rejected Template",
                notification_service.DEFAULT_REJECTED_TEMPLATE,
                key="p5_rejected_template",
                height=110,
            )

            st.caption(
                "Placeholders: {name}, {program}, {score}, {status}"
            )
            st.caption(
                "Uses SENDER_EMAIL / SENDER_PASSWORD from .env. "
                "Set EMAIL_SIMULATION=1 for a dry run."
            )

            prevent_duplicates = st.checkbox(
                "Skip students who already received an email",
                value=True,
                key="p5_prevent_duplicates",
            )

            run_p5 = st.button("Send Emails", key="p5")

        st.markdown("<br>", unsafe_allow_html=True)
        st.markdown('<span class="sidebar-label">admissions os</span>', unsafe_allow_html=True)


    # ── Header ────────────────────────────────────────────────────────────────

    st.markdown(
        "<div style='text-align:center;margin-bottom:1rem;'>"
        "<h1 style='font-size:1.2rem; line-height:1.3; "
        "letter-spacing:0.05em; color:#d0d0d0; margin:0;'>"
        "UAJK Automated admissions</h1>"
        "<p style='color:#666;font-size:0.75rem;margin-top:0.3rem;'>"
        f"{EXCEL_FILENAME}</p>"
        "</div>",
        unsafe_allow_html=True,
    )

    st.markdown("<hr style='margin:0.5rem 0;'>", unsafe_allow_html=True)


    # ── File Uploader (Data Ingestion) ─────────────────────────────────────────

    st.markdown(
        '<div class="content-card">'
        '<h3>Data Ingestion</h3>'
        '</div>',
        unsafe_allow_html=True,
    )
    uploaded_file = st.file_uploader("Upload Student Notes", type=["txt"], label_visibility="collapsed")
    if uploaded_file is not None:
        os.makedirs("data", exist_ok=True)
        with open("data/notes.txt", "wb") as f:
            f.write(uploaded_file.getbuffer())
        st.success("Notes successfully uploaded and saved to backend!")


    # ── Run status ────────────────────────────────────────────────────────────

    status = st.empty()

    st.markdown("<hr style='margin:0.5rem 0;'>", unsafe_allow_html=True)


    # ── Phase execution ───────────────────────────────────────────────────────

    process_batch, run_validation, run_score_scraper, run_merit_calculation = _services()

    if run_p1:
        notes = "data/notes.txt"
        if not os.path.exists(notes):
            status.error(f"phase 01 — failed: notes file not found: {notes}")
        else:
            raw = open(notes, encoding="utf-8").read()
            with st.spinner("phase 01 — extracting records..."):
                records, err = _run(lambda: process_batch(raw, temperature=llm_temp))
            if err:
                status.error(f"phase 01 — failed: {err}")
            else:
                status.success(f"phase 01 — success: {len(records)} record(s) processed")

    if run_p2:
        with st.spinner("phase 02 — validating eligibility..."):
            results, err = _run(
                lambda: run_validation(custom_policy=custom_policy)
            )
        if err:
            status.error(f"phase 02 — failed: {err}")
        elif not results:
            status.info("phase 02 — success: no 'Under Review' records to validate")
        else:
            eligible = sum(
                1 for result in results
                if result.get("application_status") == "Eligible"
            )
            status.success(
                f"phase 02 — success: {len(results)} evaluated "
                f"({eligible} eligible, {len(results) - eligible} rejected) "
                f"— {'custom policy' if custom_policy else 'policy file'}"
            )

    if run_p3:
        with st.spinner("phase 03 — scraping entry-test scores..."):
            summary, err = _run(lambda: run_score_scraper(headless=not show_browser))
        if err:
            status.error(f"phase 03 — failed: {err}")
        elif not summary:
            status.info("phase 03 — completed: no scores to update")
        else:
            scraped = summary.get("scraped", 0)
            failed = summary.get("failed", 0)
            skipped = summary.get("skipped", 0)
            under_review = summary.get("under_review", 0)

            if scraped == 0 and under_review:
                status.warning(
                    f"phase 03 — nothing scraped: {under_review} record(s) are "
                    "still 'Under Review'. Run phase 02 first."
                )
            elif scraped == 0 and (failed or skipped):
                reasons = "; ".join(summary.get("failures", [])[:3])
                status.error(
                    f"phase 03 — failed: no scores scraped "
                    f"({failed} failed, {skipped} skipped)"
                    + (f" — {reasons}" if reasons else "")
                )
            elif scraped == 0:
                status.info("phase 03 — completed: no scores to update")
            elif failed or skipped:
                reasons = "; ".join(summary.get("failures", [])[:3])
                status.warning(
                    f"phase 03 — partial: {scraped} scraped, "
                    f"{failed} failed, {skipped} skipped"
                    + (f" — {reasons}" if reasons else "")
                )
            else:
                status.success(f"phase 03 — success: {scraped} score(s) scraped")

    if run_p4:
        with st.spinner("phase 04 — calculating merit..."):
            summary, err = _run(
                lambda: run_merit_calculation(
                    entry_test_weight=entry_test_weight,
                    inter_weight=inter_weight,
                    passing_threshold=passing_threshold,
                )
            )
        if err:
            status.error(f"phase 04 — failed: {err}")
        elif not summary or summary.get("calculated", 0) == 0:
            status.info("phase 04 — completed: no scored eligible students")
        else:
            status.success(
                f"phase 04 — success: {summary['calculated']} calculated "
                f"({summary['admitted']} admitted, {summary['waitlisted']} waitlisted) "
                f"@ {entry_test_weight}% test / {inter_weight}% marks, "
                f"threshold {passing_threshold}%"
            )

    if run_p5:
        with st.spinner("phase 05 — sending notification emails..."):
            # _run() is this project's stdout/stderr redirect wrapper.
            summary, err = _run(
                lambda: notification_service.run_notification_batch(
                    None,
                    target_statuses,
                    admitted_template,
                    rejected_template,
                    prevent_duplicates,
                )
            )
        if err:
            status.error(f"phase 05 — failed: {err}")
        elif summary and summary.get("error"):
            status.error(f"phase 05 — failed: {summary['error']}")
        elif not summary:
            status.info("phase 05 — completed: no students matched the audience")
        else:
            sent = summary.get("sent", 0)
            skipped = summary.get("skipped", 0)
            duplicates = summary.get("duplicates", 0)
            failed = summary.get("failed", 0)
            mode = summary.get("mode", "simulation")
            verb = "simulated" if mode == "simulation" else "sent"
            details = (
                f"{sent} email(s) {verb}, {duplicates} duplicate(s) skipped, "
                f"{skipped} outside audience"
            )

            if summary.get("save_error"):
                status.warning(
                    f"phase 05 — warning: {details}, {failed} failed — "
                    f"delivery log not saved: {summary['save_error']}"
                )
            elif sent == 0 and failed:
                status.error(f"phase 05 — failed: {details}, {failed} failed")
            elif sent == 0 and duplicates:
                status.info(
                    f"phase 05 — completed: no new emails "
                    f"({duplicates} already contacted, {skipped} outside audience)"
                )
            elif sent == 0:
                status.info(
                    "phase 05 — completed: no students matched the selected "
                    "audience"
                )
            elif failed:
                status.warning(
                    f"phase 05 — partial: {details}, {failed} failed"
                )
            else:
                status.success(
                    f"phase 05 — success: {details} ({mode} mode)"
                )


    # ── Database viewer ───────────────────────────────────────────────────────

    st.markdown("<hr style='margin:0.5rem 0;'>", unsafe_allow_html=True)

    st.markdown(
        '<div class="content-card">'
        '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">'
        '<h3 style="margin:0;">DATABASE</h3>'
        '</div>'
        '</div>',
        unsafe_allow_html=True,
    )

    db_col, refresh_col = st.columns([8, 1])
    with refresh_col:
        refresh = st.button("↺", key="ref")

    force = (refresh or run_p1 or run_p2 or run_p3 or run_p4 or run_p5
             or "df" not in st.session_state)

    if force:
        df = _load_df()
        if df is not None:
            st.session_state["df"] = df.reindex(columns=EXCEL_COLUMNS)
            # Fresh editor state so stale cell edits cannot leak into a reload.
            st.session_state["editor_rev"] = st.session_state.get("editor_rev", 0) + 1
        else:
            st.session_state.pop("df", None)

    if "df" in st.session_state:
        df = st.session_state["df"]

        flash = st.session_state.pop("db_flash", None)
        if flash:
            st.success(flash)

        _render_analytics(df)

        st.markdown(
            f"<div style='color:#333;font-size:0.65rem;"
            f"margin-bottom:0.3rem;'>{len(df)} records</div>",
            unsafe_allow_html=True,
        )

        edited_df = st.data_editor(
            df,
            use_container_width=True,
            num_rows="dynamic",
            hide_index=True,
            height=400,
            key=f"db_editor_{st.session_state.get('editor_rev', 0)}",
            column_config={
                "student_id": st.column_config.TextColumn(
                    "student_id",
                    help="Generated automatically for new rows.",
                    disabled=True,
                ),
                "application_status": st.column_config.SelectboxColumn(
                    "application_status",
                    options=_EDITOR_STATUSES,
                    required=True,
                ),
                "entry_test_score": st.column_config.NumberColumn(
                    "entry_test_score",
                    min_value=0.0,
                    max_value=100.0,
                    step=1.0,
                    format="%.1f",
                ),
                "final_merit_score": st.column_config.NumberColumn(
                    "final_merit_score",
                    min_value=0.0,
                    max_value=100.0,
                    step=0.1,
                    format="%.1f",
                ),
                "email_sent": st.column_config.TextColumn(
                    "email_sent",
                    help="Set to Yes automatically by Phase 5 after a send.",
                    disabled=True,
                ),
            },
        )

        if st.button("Save Manual Edits", key="save_edits"):
            try:
                saved_df = _save_edited_df(edited_df)
            except Exception as exc:
                st.error(f"Error saving data: {exc}")
            else:
                st.session_state["df"] = saved_df
                st.session_state["editor_rev"] = (
                    st.session_state.get("editor_rev", 0) + 1
                )
                st.session_state["db_flash"] = (
                    "Database manually updated successfully!"
                )
                st.rerun()
    else:
        st.markdown(
            "<div style='color:#333;font-size:0.78rem;padding:0.6rem 0;'>"
            "no data — run phase 01 to populate the database."
            "</div>",
            unsafe_allow_html=True,
        )

    st.markdown("<hr style='margin:0.5rem 0;'>", unsafe_allow_html=True)

    # ── File Downloader (Export Database) ───────────────────────────────────────

    st.markdown(
        '<div class="content-card">'
        '<h3>Export Database</h3>'
        '</div>',
        unsafe_allow_html=True,
    )
    try:
        with open(EXCEL_FILENAME, "rb") as file:
            st.download_button(
                label="Download Final Admissions Data (.xlsx)",
                data=file,
                file_name="final_admissions.xlsx",
                mime="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
            )
    except FileNotFoundError:
        st.warning("No database file found yet. Run Phase 1 to generate it.")
