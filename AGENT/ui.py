"""
ui.py  —  Admissions Pipeline internal dashboard.
Run with:  streamlit run ui.py
"""

import io
import os
import sys
import contextlib
import threading

import pandas as pd
import streamlit as st

from src.config import EXCEL_COLUMNS, EXCEL_FILENAME
from src.repositories.excel_repository import load_excel_file


# ── Page config ──────────────────────────────────────────────────────────────

st.set_page_config(page_title="Admissions OS", layout="wide",
                   initial_sidebar_state="expanded")


# ── CSS ───────────────────────────────────────────────────────────────────────

st.markdown("""
<style>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@300;400;500&display=swap');

*, html, body, [class*="css"] {
    font-family: 'IBM Plex Mono', 'Courier New', monospace !important;
}

/* App shell */
.stApp { background: #0a0a0a; color: #c0c0c0; }

/* Kill default Streamlit padding */
.block-container { padding: 2rem 2.5rem 2rem 2.5rem !important; }

/* Sidebar */
section[data-testid="stSidebar"] {
    background: #0a0a0a;
    border-right: 1px solid #1e1e1e;
}
section[data-testid="stSidebar"] > div { padding: 1.4rem 1rem; }

/* All text in sidebar */
section[data-testid="stSidebar"] * { color: #888 !important; }

/* Sidebar phase buttons */
section[data-testid="stSidebar"] .stButton > button {
    width: 100%;
    background: transparent;
    color: #b0c8b0 !important;
    border: 1px solid #222;
    border-radius: 2px;
    font-family: 'IBM Plex Mono', monospace !important;
    font-size: 0.78rem;
    letter-spacing: 0.02em;
    padding: 0.5rem 0.75rem;
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
</style>
""", unsafe_allow_html=True)


# ── StreamlitRedirect ─────────────────────────────────────────────────────────

class StreamlitRedirect(contextlib.AbstractContextManager):
    """Captures print() from services and streams it to a Streamlit placeholder."""

    def __init__(self, placeholder):
        self._placeholder = placeholder
        self._buffer      = io.StringIO()
        self._lock        = threading.Lock()
        self._orig_stdout = None
        self._orig_stderr = None

    class _Writer:
        def __init__(self, buffer, lock, callback):
            self._buf = buffer
            self._lock = lock
            self._cb = callback
        def write(self, text):
            with self._lock:
                self._buf.write(text)
            self._cb(self._buf.getvalue())
            return len(text)
        def flush(self): pass

    def _render(self, text):
        self._placeholder.markdown(
            f'<div class="term">{text.replace(chr(96), chr(39))}</div>',
            unsafe_allow_html=True,
        )

    def __enter__(self):
        self._buffer      = io.StringIO()
        self._orig_stdout = sys.stdout
        self._orig_stderr = sys.stderr
        w = self._Writer(self._buffer, self._lock, self._render)
        sys.stdout = sys.stderr = w
        return self

    def __exit__(self, *_):
        sys.stdout = self._orig_stdout
        sys.stderr = self._orig_stderr
        self._render(self._buffer.getvalue())
        return False


# ── Helpers ───────────────────────────────────────────────────────────────────

def _load_df():
    if not os.path.exists(EXCEL_FILENAME):
        return None
    try:
        return load_excel_file(EXCEL_FILENAME)
    except Exception as exc:
        st.error(str(exc))
        return None


def _services():
    from src.services.admission_service     import process_batch
    from src.services.validator_service     import run_validation
    from src.services.score_scraper_service import run_score_scraper
    from src.services.merit_service         import run_merit_calculation
    return process_batch, run_validation, run_score_scraper, run_merit_calculation


def _run(fn, terminal):
    msg = err = None
    with StreamlitRedirect(terminal):
        try:
            fn()
            msg = "done."
        except FileNotFoundError as e:
            err = f"file not found — {e}"
        except ValueError as e:
            err = f"value error — {e}"
        except Exception as e:
            err = f"error — {e}"
    return msg, err


# ── Sidebar ───────────────────────────────────────────────────────────────────

with st.sidebar:
    st.markdown('<span class="sidebar-label">Agents</span>', unsafe_allow_html=True)

    run_p1 = st.button("01  /  Batch Extraction",  key="p1")
    run_p2 = st.button("02  /  Validator",          key="p2")
    run_p3 = st.button("03  /  Scraper",             key="p3")
    run_p4 = st.button("04  /  Merit Calculator",   key="p4")

    st.markdown("<br>", unsafe_allow_html=True)
    st.markdown('<span class="sidebar-label">admissions os</span>', unsafe_allow_html=True)


# ── Header ────────────────────────────────────────────────────────────────────

col_title, col_status = st.columns([5, 2])
with col_title:
    st.markdown("### ADMISSIONS OS")
with col_status:
    st.markdown(
        f"<div style='text-align:right;color:#333;font-size:0.72rem;"
        f"padding-top:0.6rem;'>{EXCEL_FILENAME}</div>",
        unsafe_allow_html=True,
    )

st.markdown("<hr>", unsafe_allow_html=True)


# ── Terminal ──────────────────────────────────────────────────────────────────

terminal = st.empty()
terminal.markdown(
    '<div class="term" style="color:#2a2a2a;">&#x25B6; idle</div>',
    unsafe_allow_html=True,
)


# ── Phase execution ───────────────────────────────────────────────────────────

process_batch, run_validation, run_score_scraper, run_merit_calculation = _services()

if run_p1:
    notes = "data/notes.txt"
    if not os.path.exists(notes):
        st.error(f"notes file not found: {notes}")
    else:
        raw = open(notes, encoding="utf-8").read()
        msg, err = _run(lambda: process_batch(raw), terminal)
        if msg: st.success(f"phase 1 — {msg}")
        if err: st.error(f"phase 1 — {err}")

if run_p2:
    msg, err = _run(run_validation, terminal)
    if msg: st.success(f"phase 2 — {msg}")
    if err: st.error(f"phase 2 — {err}")

if run_p3:
    msg, err = _run(run_score_scraper, terminal)
    if msg: st.success(f"phase 3 — {msg}")
    if err: st.error(f"phase 3 — {err}")

if run_p4:
    msg, err = _run(run_merit_calculation, terminal)
    if msg: st.success(f"phase 4 — {msg}")
    if err: st.error(f"phase 4 — {err}")


# ── Database viewer ───────────────────────────────────────────────────────────

st.markdown("<hr>", unsafe_allow_html=True)

db_col, refresh_col = st.columns([8, 1])
with db_col:
    st.markdown("### DATABASE")
with refresh_col:
    st.markdown("<div style='padding-top:0.35rem;'>", unsafe_allow_html=True)
    refresh = st.button("↺", key="ref")
    st.markdown("</div>", unsafe_allow_html=True)

force = (refresh or run_p1 or run_p2 or run_p3 or run_p4
         or "df" not in st.session_state)

if force:
    df = _load_df()
    if df is not None:
        st.session_state["df"] = df.reindex(columns=EXCEL_COLUMNS)
    else:
        st.session_state.pop("df", None)

if "df" in st.session_state:
    df = st.session_state["df"]
    st.markdown(
        f"<div style='color:#333;font-size:0.70rem;"
        f"margin-bottom:0.4rem;'>{len(df)} records</div>",
        unsafe_allow_html=True,
    )
    st.dataframe(df, use_container_width=True, hide_index=True)
else:
    st.markdown(
        "<div style='color:#333;font-size:0.78rem;padding:0.6rem 0;'>"
        "no data — run phase 01 to populate the database."
        "</div>",
        unsafe_allow_html=True,
    )
