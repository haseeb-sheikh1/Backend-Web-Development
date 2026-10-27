"""
notification_service.py
-----------------------
Phase 5 — sends personalised admission-decision emails and records delivery.

The admin chooses which application statuses to contact and may rewrite both
templates. Students who already received an email are skipped by default, so a
repeated run cannot spam applicants. Every successful send is written back to
the ``email_sent`` column of ``data/admissions.xlsx``.

Delivery modes
--------------
* ``smtp``       — used when ``SENDER_EMAIL`` / ``SENDER_PASSWORD`` (or the
                   ``SMTP_*`` aliases) are present. Gmail addresses default to
                   ``smtp.gmail.com:587`` with STARTTLS.
* ``simulation`` — used when ``EMAIL_SIMULATION=1``. Nothing leaves the
                   machine, but the same validation, duplicate checks, logging
                   and state tracking run, so the workflow can be tested safely.

Public API
----------
  run_notification_batch(
      terminal=None,
      target_statuses=None,
      admitted_template=DEFAULT_ADMITTED_TEMPLATE,
      rejected_template=DEFAULT_REJECTED_TEMPLATE,
      prevent_duplicates=True,
  ) -> dict
"""

from __future__ import annotations

import os
import smtplib
import sys
import tempfile
import time
import traceback
from email.message import EmailMessage
from pathlib import Path
from string import Formatter

import pandas as pd

from src.config import EXCEL_COLUMNS, EXCEL_FILENAME
from src.repositories.excel_repository import load_excel_file


# Statuses the communication dashboard can target.
TARGET_STATUS_CHOICES: list[str] = ["Admitted", "Waitlisted", "Rejected"]

# Template defaults shown in the sidebar. Placeholders are filled with
# str.format(), so the admin can reorder or reword them freely.
DEFAULT_ADMITTED_TEMPLATE = (
    "Dear {name},\n"
    "Congratulations! You are selected for {program} with a score of {score}.\n"
    "Please verify your documents."
)

DEFAULT_REJECTED_TEMPLATE = (
    "Dear {name},\n"
    "Your application for {program} is currently {status}.\n"
    "Thank you."
)

# Only these names may appear inside braces in a template.
ALLOWED_PLACEHOLDERS = frozenset({"name", "program", "score", "status"})

# Sent with every message. The decision status is appended per recipient.
EMAIL_SUBJECT = "Admission Application Update"

# Column that records a completed send. Kept in sync with src.config.EXCEL_COLUMNS
# so manual saves and repository updates never drop it.
EMAIL_SENT_COLUMN = "email_sent"


# ---------------------------------------------------------------------------
# Small helpers
# ---------------------------------------------------------------------------


def _text(value: object) -> str:
    """Return a trimmed string, treating NaN/None as an empty value."""
    if value is None:
        return ""
    try:
        if pd.isna(value):
            return ""
    except (TypeError, ValueError):
        pass
    return str(value).strip()


# ---------------------------------------------------------------------------
# .env loading and credentials
# ---------------------------------------------------------------------------

_PROJECT_ROOT = Path(__file__).resolve().parents[2]
DOTENV_PATH = _PROJECT_ROOT / ".env"


def _load_dotenv(path: Path = DOTENV_PATH) -> bool:
    """
    Load simple KEY=VALUE pairs from a .env file into os.environ.

    Existing environment variables always win, and quoted values are
    unquoted. This avoids adding a python-dotenv dependency.
    """
    if not path.is_file():
        return False

    for raw_line in path.read_text(encoding="utf-8-sig").splitlines():
        line = raw_line.strip()
        if not line or line.startswith("#"):
            continue
        if line.startswith("export "):
            line = line[7:].lstrip()
        if "=" not in line:
            continue

        key, value = line.split("=", 1)
        key = key.strip()
        if not key:
            continue

        value = value.strip()
        if len(value) >= 2 and value[0] == value[-1] and value[0] in ("'", '"'):
            value = value[1:-1]

        # Real environment variables take priority over the .env file.
        if key not in os.environ:
            os.environ[key] = value

    return True


def _first_env(*names: str) -> str:
    """Return the first non-empty environment variable from ``names``."""
    for name in names:
        value = os.environ.get(name, "")
        if value and value.strip():
            return value.strip()
    return ""


def _credentials() -> tuple[str, str]:
    """Resolve sender credentials, accepting both naming conventions."""
    return (
        _first_env("SENDER_EMAIL", "SMTP_USERNAME"),
        _first_env("SENDER_PASSWORD", "SMTP_PASSWORD"),
    )


def _mask_email(value: object) -> str:
    """Mask an address for logs without revealing the full mailbox."""
    text = _text(value)
    if not text:
        return "<missing>"
    if "@" in text:
        local, domain = text.split("@", 1)
        visible = local[:2] if len(local) > 2 else ""
        return f"{visible}***@{domain}"
    return text[:2] + "***"


ENV_FILE_LOADED = _load_dotenv()
SENDER_EMAIL, SENDER_PASSWORD = _credentials()


def _score_text(value: object) -> str:
    """Render marks as '85' rather than '85.0'; return '' when unknown."""
    text = _text(value)
    if not text:
        return ""
    try:
        number = float(text.rstrip("%"))
    except ValueError:
        return text
    return f"{number:g}"


def _log(terminal, message: str) -> None:
    """Write to the supplied terminal stream, or stdout when none is given."""
    if terminal is None:
        print(message)
    else:
        terminal.write(message + "\n")


def _debug(terminal, message: str) -> None:
    """
    Emit a verbose debug line.

    Streamlit's phase runner redirects stdout/stderr, so the line is also
    written to the real process stdout. That makes it visible in the terminal
    running ``streamlit run ui.py`` while still travelling through the normal
    terminal capture path used by tests and the CLI.
    """
    line = f"[DEBUG] {message}"
    _log(terminal, line)

    real_stdout = getattr(sys, "__stdout__", None)
    try:
        if real_stdout is not None and real_stdout is not sys.stdout:
            print(line, file=real_stdout, flush=True)
    except Exception:
        pass


def _report_error(terminal, message: str) -> None:
    """Print an error and surface it in Streamlit when a script context exists."""
    _log(terminal, message)

    try:
        import streamlit as st
        from streamlit.runtime.scriptrunner import get_script_run_ctx

        if get_script_run_ctx() is not None:
            st.error(message)
    except Exception:
        # Bare mode, CLI, or a future Streamlit API change: the printed log
        # above is always available, so never let reporting mask the real error.
        pass


def _empty_summary(message: str, mode: str = "unavailable") -> dict:
    """Return an error-shaped summary without processing any student."""
    return {
        "sent": 0,
        "skipped": 0,
        "duplicates": 0,
        "failed": 0,
        "total": 0,
        "mode": mode,
        "saved": False,
        "save_error": None,
        "error": message,
        "drafts": [],
    }


def _canonical_status(value: object) -> str:
    """Map a workbook status onto its canonical spelling, else return it raw."""
    text = _text(value)
    for status in TARGET_STATUS_CHOICES:
        if text.casefold() == status.casefold():
            return status
    return text


def _is_marked_sent(value: object) -> bool:
    """True when the persisted marker says this student was already emailed."""
    return _text(value).casefold() == "yes"


# ---------------------------------------------------------------------------
# Validation
# ---------------------------------------------------------------------------


def _normalise_target_statuses(target_statuses) -> set[str]:
    """
    Validate the requested audience and return it as a casefolded set.

    ``None`` means "every supported status", which keeps direct/CLI calls
    working without arguments. An explicitly empty list is an admin mistake
    and is rejected instead of silently emailing nobody.
    """
    if target_statuses is None:
        return {status.casefold() for status in TARGET_STATUS_CHOICES}

    if isinstance(target_statuses, str):
        target_statuses = [target_statuses]

    requested = [status for status in target_statuses if _text(status)]
    if not requested:
        raise ValueError("Select at least one status to email.")

    allowed = {status.casefold(): status for status in TARGET_STATUS_CHOICES}
    selected: set[str] = set()
    unsupported: list[str] = []
    for status in requested:
        key = _text(status).casefold()
        if key not in allowed:
            unsupported.append(_text(status))
        else:
            selected.add(key)

    if unsupported:
        raise ValueError(
            "Unsupported status(es): "
            + ", ".join(sorted(set(unsupported)))
            + ". Allowed: "
            + ", ".join(TARGET_STATUS_CHOICES)
            + "."
        )
    return selected


def _validate_template(label: str, template: str) -> str:
    """
    Reject blank templates and unknown placeholders before any email is sent.

    Raises:
        ValueError: If the template is empty, has malformed braces, or uses a
            placeholder outside ``ALLOWED_PLACEHOLDERS``.
    """
    if template is None or not str(template).strip():
        raise ValueError(f"{label} cannot be empty.")

    text = str(template)
    try:
        parsed = list(Formatter().parse(text))
    except ValueError as exc:
        raise ValueError(f"{label} has malformed braces: {exc}") from exc

    fields: set[str] = set()
    for _, field_name, _, _ in parsed:
        if not field_name:
            continue
        root = field_name.split(".")[0].split("[")[0]
        fields.add(root)

    unknown = fields.difference(ALLOWED_PLACEHOLDERS)
    if unknown:
        raise ValueError(
            f"{label} uses unsupported placeholder(s): "
            + ", ".join(sorted(unknown))
            + ". Allowed placeholders: "
            + ", ".join(sorted(ALLOWED_PLACEHOLDERS))
            + "."
        )
    return text


# ---------------------------------------------------------------------------
# SMTP
# ---------------------------------------------------------------------------


def _env_flag(name: str, default: bool) -> bool:
    """Read a boolean environment variable."""
    raw = os.environ.get(name)
    if raw is None or not raw.strip():
        return default
    return raw.strip().casefold() in {"1", "true", "yes", "on"}


def _smtp_settings() -> dict | None:
    """
    Build SMTP settings from the environment and the project .env file.

    ``SENDER_EMAIL`` / ``SENDER_PASSWORD`` are accepted as aliases for
    ``SMTP_USERNAME`` / ``SMTP_PASSWORD``. When no ``SMTP_HOST`` is given and
    the sender is a Gmail address, ``smtp.gmail.com`` is used automatically.

    Returns:
        dict | None: Connection settings, or ``None`` when no host can be
        determined.

    Raises:
        ValueError: If the configured port or credentials are incomplete.
    """
    _load_dotenv()
    username, password = _credentials()

    host = os.environ.get("SMTP_HOST", "").strip()
    if not host and username.casefold().endswith("@gmail.com"):
        host = "smtp.gmail.com"
    if not host:
        return None

    use_ssl = _env_flag("SMTP_USE_SSL", False)
    use_tls = _env_flag("SMTP_USE_TLS", not use_ssl)

    raw_port = os.environ.get("SMTP_PORT", "").strip()
    if raw_port:
        try:
            port = int(raw_port)
        except ValueError as exc:
            raise ValueError(f"SMTP_PORT must be a number, got {raw_port!r}.") from exc
    else:
        port = 465 if use_ssl else 587

    if bool(username) != bool(password):
        raise ValueError(
            "SENDER_EMAIL/SENDER_PASSWORD (or SMTP_USERNAME/SMTP_PASSWORD) "
            "must be configured together."
        )

    sender = (
        os.environ.get("SMTP_FROM", "").strip()
        or username
        or "no-reply@localhost"
    )

    return {
        "host": host,
        "port": port,
        "use_ssl": use_ssl,
        "use_tls": use_tls,
        "username": username,
        "password": password,
        "sender": sender,
    }


def _send_via_smtp(
    settings: dict,
    recipient: str,
    status: str,
    body: str,
) -> None:
    """Deliver one message using a fresh SMTP connection."""
    message = EmailMessage()
    message["From"] = settings["sender"]
    message["To"] = recipient
    message["Subject"] = f"{EMAIL_SUBJECT} - {status}"
    message.set_content(body)

    smtp_class = smtplib.SMTP_SSL if settings["use_ssl"] else smtplib.SMTP
    with smtp_class(settings["host"], settings["port"], timeout=30) as server:
        if not settings["use_ssl"]:
            server.ehlo()
            if settings["use_tls"]:
                server.starttls()
                server.ehlo()
        if settings["username"]:
            server.login(settings["username"], settings["password"])
        server.send_message(message)


# ---------------------------------------------------------------------------
# Persistence
# ---------------------------------------------------------------------------


def _save_workbook(students: pd.DataFrame) -> None:
    """
    Atomically replace the master workbook with the updated dataframe.

    A temporary file is written first and then swapped into place, so a failed
    write can never leave a half-written admissions.xlsx behind.

    Raises:
        PermissionError: If Excel (or another program) holds the file open.
    """
    output = students.reindex(columns=EXCEL_COLUMNS)
    absolute_path = os.path.abspath(EXCEL_FILENAME)
    directory = os.path.dirname(absolute_path) or "."
    os.makedirs(directory, exist_ok=True)

    handle, temp_path = tempfile.mkstemp(suffix=".xlsx", dir=directory)
    os.close(handle)
    try:
        output.to_excel(temp_path, index=False, engine="openpyxl")
        os.replace(temp_path, absolute_path)
    except PermissionError as exc:
        raise PermissionError(
            f"could not write '{EXCEL_FILENAME}' - "
            "close the file in Excel and try again"
        ) from exc
    finally:
        if os.path.exists(temp_path):
            os.remove(temp_path)


# ---------------------------------------------------------------------------
# Public entry point
# ---------------------------------------------------------------------------


def run_notification_batch(
    terminal=None,
    target_statuses=None,
    admitted_template: str = DEFAULT_ADMITTED_TEMPLATE,
    rejected_template: str = DEFAULT_REJECTED_TEMPLATE,
    prevent_duplicates: bool = True,
) -> dict:
    """
    Send admission emails for the selected audience and record each delivery.

    Credentials are read from the project ``.env`` file using
    ``SENDER_EMAIL`` / ``SENDER_PASSWORD`` (or the ``SMTP_*`` aliases). Set
    ``EMAIL_SIMULATION=1`` to run the full workflow without opening SMTP.

    Args:
        terminal: Optional writable stream (for example ``sys.stdout`` or a
            Streamlit redirect target). Messages go to stdout when omitted.
        target_statuses: Statuses to email. ``None`` means all supported
            statuses; an empty list is rejected.
        admitted_template: Body used for ``Admitted`` students. Supports the
            placeholders ``{name}``, ``{program}``, ``{score}`` and ``{status}``.
        rejected_template: Body used for ``Waitlisted`` and ``Rejected``
            students. Supports the same placeholders.
        prevent_duplicates: When true, students whose ``email_sent`` value is
            ``Yes`` are skipped.

    Returns:
        dict: ``sent``, ``skipped`` (outside the selected audience),
        ``duplicates``, ``failed``, ``total``, ``mode`` (``smtp`` or
        ``simulation``), ``saved``, ``save_error``, ``error`` and the
        ``drafts`` list. ``error`` is populated when configuration prevents
        the batch from starting.

    Raises:
        ValueError: If the audience or a template is invalid, or SMTP
            environment variables are inconsistent.
        FileNotFoundError: If the master workbook does not exist.
    """
    env_file_found = _load_dotenv()
    sender_email, sender_password = _credentials()
    simulation_requested = _env_flag("EMAIL_SIMULATION", False)

    _debug(terminal, f".env file: {DOTENV_PATH} (found={env_file_found})")
    _debug(
        terminal,
        "SENDER_EMAIL: "
        + ("present" if sender_email else "MISSING")
        + f" ({_mask_email(sender_email)})",
    )
    _debug(
        terminal,
        "SENDER_PASSWORD: "
        + ("present" if sender_password else "MISSING")
        + f" (length={len(sender_password)})",
    )
    _debug(terminal, f"EMAIL_SIMULATION: {simulation_requested}")

    if not simulation_requested and (not sender_email or not sender_password):
        message = (
            "CRITICAL: .env credentials not found. "
            "SENDER_EMAIL or SENDER_PASSWORD is None."
        )
        _report_error(terminal, message)
        return _empty_summary(message, mode="disabled")

    selected_statuses = _normalise_target_statuses(target_statuses)
    admitted_template = _validate_template(
        "Admitted email template", admitted_template
    )
    rejected_template = _validate_template(
        "Waitlisted/Rejected email template", rejected_template
    )

    prevent_duplicates = bool(prevent_duplicates)

    settings = None if simulation_requested else _smtp_settings()
    if not simulation_requested and settings is None:
        message = (
            "SMTP is not configured: set SMTP_HOST (or use a Gmail "
            "SENDER_EMAIL), or set EMAIL_SIMULATION=1 for a dry run."
        )
        _report_error(terminal, message)
        return _empty_summary(message, mode="disabled")

    mode = "simulation" if settings is None else "smtp"
    _debug(terminal, f"Delivery mode: {mode}")
    if settings is not None:
        _debug(
            terminal,
            f"SMTP host: {settings['host']}:{settings['port']} "
            f"(ssl={settings['use_ssl']}, tls={settings['use_tls']})",
        )
        _debug(terminal, f"SMTP user: {_mask_email(settings['username'])}")
        _debug(terminal, f"SMTP sender: {_mask_email(settings['sender'])}")
    else:
        _debug(terminal, "Simulation mode: no SMTP connection will be opened.")

    students = load_excel_file(EXCEL_FILENAME)

    required_columns = {
        "name",
        "email",
        "desired_program",
        "application_status",
    }
    missing_columns = required_columns.difference(students.columns)
    if missing_columns:
        raise ValueError(
            "Master workbook is missing required column(s): "
            + ", ".join(sorted(missing_columns))
        )

    if EMAIL_SENT_COLUMN not in students.columns:
        students[EMAIL_SENT_COLUMN] = ""
    else:
        students[EMAIL_SENT_COLUMN] = (
            students[EMAIL_SENT_COLUMN].fillna("").map(_text)
        )

    _log(terminal, "\n" + "=" * 60)
    _log(terminal, f"  [*] Notification Agent - Phase 5 ({mode} mode)")
    _log(
        terminal,
        "  [*] Audience: "
        + ", ".join(
            status for status in TARGET_STATUS_CHOICES
            if status.casefold() in selected_statuses
        ),
    )
    _log(terminal, "=" * 60)

    sent = 0
    skipped = 0
    duplicates = 0
    failed = 0
    drafts: list[dict] = []

    for index, student in students.iterrows():
        raw_status = _text(student.get("application_status"))
        status = _canonical_status(raw_status)
        name = _text(student.get("name")) or "Applicant"
        email = _text(student.get("email"))
        program = _text(student.get("desired_program")) or "your selected program"

        _debug(
            terminal,
            f"Checking student: {name} | Status: {raw_status} | index={index}",
        )

        # Outside the audience the admin selected (including Pending,
        # Eligible, Under Review and blank statuses).
        if status.casefold() not in selected_statuses:
            _debug(
                terminal,
                f"Skipping {name} - status '{raw_status}' is outside the "
                "selected audience.",
            )
            skipped += 1
            continue

        if prevent_duplicates and _is_marked_sent(
            student.get(EMAIL_SENT_COLUMN)
        ):
            _debug(
                terminal,
                f"Skipping {name} - Email already marked as sent.",
            )
            duplicates += 1
            _log(terminal, f"[--] Skipped {email or name}: already emailed.")
            continue

        if not email or "@" not in email:
            _debug(
                terminal,
                f"Skipping {name} - no valid email address on file "
                f"(email={email!r}).",
            )
            failed += 1
            _log(
                terminal,
                f"[!] Failed {name}: no valid email address on file.",
            )
            continue

        template = (
            admitted_template if status == "Admitted" else rejected_template
        )
        values = {
            "name": name,
            "program": program,
            "score": _score_text(student.get("marks_percentage"))
            or "not available",
            "status": status,
        }

        _debug(terminal, f"Rendering {status} template for {name} -> {email}")
        try:
            body = template.format(**values)
        except (KeyError, IndexError, ValueError, AttributeError) as exc:
            _debug(terminal, f"Template render failed for {name}: {exc}")
            failed += 1
            _log(
                terminal,
                f"[!] Failed {email}: template could not be rendered ({exc}).",
            )
            continue

        if settings is not None:
            _debug(
                terminal,
                f"Sending SMTP message to {email} via "
                f"{settings['host']}:{settings['port']} ...",
            )
            try:
                _send_via_smtp(settings, email, status, body)
            except Exception as exc:
                error_msg = f"SMTP Error for {email}: {str(exc)}"
                _report_error(terminal, error_msg)
                _debug(terminal, "Traceback:\n" + traceback.format_exc())
                failed += 1
                continue
        else:
            _debug(
                terminal,
                f"Simulation mode: skipping SMTP dispatch for {email}.",
            )

        students.at[index, EMAIL_SENT_COLUMN] = "Yes"
        sent += 1
        drafts.append(
            {
                "student_id": _text(student.get("student_id")),
                "name": name,
                "email": email,
                "status": status,
                "subject": f"{EMAIL_SUBJECT} - {status}",
                "body": body,
                "mode": mode,
            }
        )

        _debug(
            terminal,
            f"Marked {name} as {EMAIL_SENT_COLUMN}=Yes (row index {index}).",
        )

        suffix = " (simulated)" if settings is None else ""
        _log(terminal, f"[OK] Sent {status} email to {email}{suffix}")

        # Google SMTP rate limiting: one message per second.
        time.sleep(1)
        _debug(terminal, "Rate limit: slept 1 second before the next message.")

    _debug(
        terminal,
        f"Batch counters: sent={sent}, duplicates={duplicates}, "
        f"skipped={skipped}, failed={failed}, mode={mode}",
    )

    _log(
        terminal,
        f"  [OK] Batch complete: {sent} sent ({mode} mode), "
        f"{duplicates} duplicate(s) skipped, {skipped} outside audience"
        + (f", {failed} failed" if failed else "")
        + ".",
    )

    saved = False
    save_error = None
    if sent:
        try:
            _debug(
                terminal,
                f"Saving workbook: {os.path.abspath(EXCEL_FILENAME)}",
            )
            _save_workbook(students)
        except Exception as exc:  # keep the send summary even if the write fails
            save_error = str(exc)
            _report_error(
                terminal,
                f"Workbook save error: {exc}",
            )
        else:
            saved = True
            _debug(terminal, f"Workbook saved with {sent} email_sent=Yes update(s).")
            _log(
                terminal,
                f"  [OK] Recorded '{EMAIL_SENT_COLUMN}=Yes' for {sent} "
                f"student(s) in '{EXCEL_FILENAME}'.",
            )

    _log(terminal, "")

    return {
        "sent": sent,
        "skipped": skipped,
        "duplicates": duplicates,
        "failed": failed,
        "total": len(students),
        "mode": mode,
        "saved": saved,
        "save_error": save_error,
        "error": None,
        "drafts": drafts,
    }
