<?php
    session_start();

    if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

    $user_name = "Hammad";
    $user_role = "Employee";
    $current_page = "attendance";
    $user_dept = "Software Engineering";

    // Load styles
    $extra_css = "attendance";
    $title = "Attendance Tracker - CodeXentric";
    include_once "../includes/header.php";
?>
<style>
:root {
  --bg: #f1f5f9;
  --card-bg: #ffffff;
  --border: #e2e8f0;
  --text-main: #334155;
  --text-muted: #64748b;
  --brand-orange: #ff7b1d;
  --brand-orange-hover: #e66a15;
  --brand-green: #186D55;
  --font-body: 'Nunito Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
}

.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px;
  font-family: var(--font-body);
}

/* ── Minimal Header ── */
.dash-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 30px;
  padding-bottom: 15px;
  border-bottom: 1px solid var(--border);
}

.dash-header h1 {
  font-size: 24px;
  font-weight: 800;
  color: var(--text-main);
  margin: 0;
  letter-spacing: -0.5px;
}

.dash-subtitle {
  font-size: 14px;
  color: var(--text-muted);
  margin: 5px 0 0 0;
}

.btn-minimal {
  height: 40px;
  padding: 0 16px;
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  color: var(--text-main);
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}

.btn-minimal:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

/* ── Widget Card ── */
.widget-card {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}

.card-title {
  font-size: 14px;
  font-weight: 800;
  color: var(--text-main);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}

/* ── Time Display ── */
.time-display {
  text-align: center;
  margin-bottom: 30px;
  padding: 20px;
  background: #fcfcfd;
  border-radius: 12px;
  border: 1px solid var(--border);
}

.current-time {
  font-size: 36px;
  font-weight: 800;
  color: var(--text-main);
  letter-spacing: -1px;
}

.date-display {
  font-size: 14px;
  font-weight: 700;
  color: var(--brand-orange);
  margin-top: 5px;
}

/* ── Status Grid ── */
.status-info {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 15px;
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--border);
}

.status-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.status-label {
  font-size: 11px;
  font-weight: 800;
  color: var(--text-muted);
  text-transform: uppercase;
}

.status-value {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-main);
}

/* ── Form Controls ── */
.input-group {
  margin-bottom: 20px;
}

.input-group label {
  display: block;
  font-size: 13px;
  font-weight: 700;
  color: var(--text-main);
  margin-bottom: 8px;
}

.dash-input {
  width: 100%;
  height: 44px;
  padding: 0 16px;
  border: 1px solid var(--border);
  border-radius: 8px;
  font-size: 14px;
  font-family: var(--font-body);
  color: var(--text-main);
  background: #fff;
  transition: all 0.2s;
}

.dash-input:focus {
  border-color: var(--brand-orange);
  box-shadow: 0 0 0 3px rgba(255, 123, 29, 0.1);
  outline: none;
}

.action-buttons {
  display: flex;
  gap: 15px;
}

.btn-brand {
  height: 44px;
  padding: 0 24px;
  background: var(--brand-orange);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 800;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}

.btn-brand:hover {
  background: var(--brand-orange-hover);
  transform: translateY(-1px);
}

.btn-brand.danger {
  background: #ef4444;
}

.btn-brand.danger:hover {
  background: #dc2626;
}

.btn-brand:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

/* ── Table Design ── */
.table-wrapper {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}

.attendance-table {
  width: 100%;
  border-collapse: collapse;
}

.attendance-table th {
  background: #fcfcfd;
  padding: 12px 16px;
  text-align: left;
  font-size: 11px;
  font-weight: 800;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 1px;
  border-bottom: 1px solid var(--border);
}

.attendance-table td {
  padding: 16px;
  border-bottom: 1px solid var(--border);
  font-size: 13px;
  color: var(--text-main);
}

.attendance-table tr:hover {
  background: #f8fafc;
}

.duration-tag {
  font-weight: 800;
  color: var(--brand-green);
}

.btn-action {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: 1px solid var(--border);
  background: #fff;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-action:hover {
  background: #f1f5f9;
  color: var(--brand-orange);
  border-color: var(--brand-orange);
}
</style>

<div class="dashboard-container">
    <!-- Minimal Header -->
    <header class="dash-header">
        <div>
            <h1>Attendance Tracker</h1>
            <p class="dash-subtitle">Check in and out, or view your attendance history.</p>
        </div>
        <div class="dash-header-actions">
            <a href="employee_dashboard.php" class="btn-minimal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </header>

        <!-- Check In/Out Section -->
        <section class="check-in-section" aria-labelledby="check-in-heading" style="margin-bottom: 30px;">
            <h2 id="check-in-heading" class="sr-only">Check In/Out</h2>
            <div class="check-card" style="background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                <div class="time-display" style="margin-bottom: 20px;">
                    <div class="time-header" style="display: flex; align-items: center; gap: 8px; color: #1e3a8a;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <h3 class="time-title" style="margin: 0; font-size: 1.1rem;">Current Time</h3>
                    </div>
                    <div class="current-time" id="currentTime" style="font-size: 2rem; font-weight: 700; color: #0f172a; margin-top: 10px;">--:--:-- --</div>
                    <p class="date-display" id="currentDate" style="color: #64748b; margin: 0;">--</p>
                </div>

                <div class="status-info" style="display: flex; gap: 20px; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
                    <div class="status-item">
                        <span class="status-label" style="display: block; font-size: 0.85rem; color: #64748b;">Today's Status</span>
                        <span class="status-value" id="todayStatus" style="font-weight: 600; color: #0f172a;">Not Checked In</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label" style="display: block; font-size: 0.85rem; color: #64748b;">Check In Time</span>
                        <span class="status-value" id="checkInTime" style="font-weight: 600; color: #0f172a;">--:--:--</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label" style="display: block; font-size: 0.85rem; color: #64748b;">Check Out Time</span>
                        <span class="status-value" id="checkOutTime" style="font-weight: 600; color: #0f172a;">--:--:--</span>
                    </div>
                </div>

                <!-- NEW: Note Input Field -->
                <div class="note-input-container" style="margin-bottom: 20px;">
                    <label for="punchNote" style="display: block; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">Add Note (Optional)</label>
                    <input type="text" id="punchNote" placeholder="e.g., Working from home, leaving for doctor appt..." style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem; outline: none; transition: border-color 0.2s;">
                </div>

                <div class="action-buttons" style="display: flex; gap: 15px;">
                    <button class="action-button primary" id="checkInBtn" onclick="handleCheckIn()" aria-label="Check in for today" style="background: #1e3a8a; color: #fff; padding: 10px 20px; border: none; border-radius: 6px; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M9 11l3 3L22 4"/>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                        Check In
                    </button>
                    <button class="action-button danger" id="checkOutBtn" onclick="handleCheckOut()" disabled aria-label="Check out for today" style="background: #ef4444; color: #fff; padding: 10px 20px; border: none; border-radius: 6px; display: flex; align-items: center; gap: 8px; cursor: not-allowed; opacity: 0.5;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Check Out
                    </button>
                </div>

                <div class="alert-message" id="alertMessage" style="display: none; margin-top: 15px; padding: 12px; border-radius: 6px;" role="alert" aria-live="polite"></div>
            </div>
        </section>

        <!-- NEW: My Attendance Records Filter (Based on Screenshot) -->
        <section class="records-filter-section" style="background: #fff; border-radius: 8px; padding: 20px 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
                <h2 style="font-size: 1.1rem; color: #1e3a8a; font-weight: 600; margin: 0;">My Attendance Records</h2>
                <button style="background: none; border: none; cursor: pointer; color: #64748b;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 15l-6-6-6 6"/></svg>
                </button>
            </div>
            
            <div class="filter-controls" style="display: flex; align-items: flex-end; justify-content: space-between;">
                <div class="date-input-group" style="width: 250px;">
                    <label for="recordDate" style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 8px;">Date*</label>
                    <div style="position: relative;">
                        <input type="date" id="recordDate" value="2026-04-05" style="width: 100%; padding: 10px 35px 10px 15px; border: 1px solid #e2e8f0; border-radius: 8px; color: #334155; outline: none;">
                    </div>
                </div>
                
                <div class="filter-actions" style="display: flex; align-items: center; gap: 20px;">
                    <span style="font-size: 0.85rem; color: #1e3a8a;">* Required</span>
                    <button style="background: #84cc16; color: white; padding: 8px 32px; border: none; border-radius: 20px; font-weight: 600; cursor: pointer; transition: background 0.2s;">View</button>
                </div>
            </div>
        </section>

        <!-- Attendance History Table (Updated to match Screenshot columns) -->
        <section class="attendance-history" aria-labelledby="history-heading" style="background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); margin-bottom: 30px;">
            <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
                <span style="color: #64748b; font-size: 0.95rem;">Total Duration (Hours): <span style="font-weight: 600; color: #334155;">0.00</span></span>
            </div>
            
            <div style="margin-bottom: 15px;">
                <span style="color: #64748b; font-size: 0.95rem;">(3) Records Found</span>
            </div>

            <div class="table-container" style="overflow-x: auto;">
                <table class="attendance-table" role="table" style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                        <tr>
                            <th scope="col" style="padding: 12px 16px; width: 40px;"><input type="checkbox" style="border-radius: 4px; border: 1px solid #cbd5e1;"></th>
                            <th scope="col" style="padding: 12px 16px; color: #475569; font-size: 0.85rem; font-weight: 600;">Punch In</th>
                            <th scope="col" style="padding: 12px 16px; color: #475569; font-size: 0.85rem; font-weight: 600;">Punch In Note</th>
                            <th scope="col" style="padding: 12px 16px; color: #475569; font-size: 0.85rem; font-weight: 600;">Punch Out</th>
                            <th scope="col" style="padding: 12px 16px; color: #475569; font-size: 0.85rem; font-weight: 600;">Punch Out Note</th>
                            <th scope="col" style="padding: 12px 16px; color: #475569; font-size: 0.85rem; font-weight: 600;">Duration (Hours)</th>
                            <th scope="col" style="padding: 12px 16px; color: #475569; font-size: 0.85rem; font-weight: 600;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTableBody">
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 16px;"><input type="checkbox"></td>
                            <td style="padding: 16px; font-size: 0.9rem; color: #334155;">2026-04-05 03:32 PM<br><span style="color: #94a3b8; font-size: 0.8rem;">GMT +05:30</span></td>
                            <td style="padding: 16px; font-size: 0.9rem; color: #334155;">-</td>
                            <td style="padding: 16px; font-size: 0.9rem; color: #334155;">2026-04-05 03:32 PM<br><span style="color: #94a3b8; font-size: 0.8rem;">GMT +05:30</span></td>
                            <td style="padding: 16px; font-size: 0.9rem; color: #334155;">i am going</td>
                            <td style="padding: 16px; font-size: 0.9rem; color: #334155;">0.00</td>
                            <td style="padding: 16px;">
                                <div style="display: flex; gap: 8px;">
                                    <button style="background: #f1f5f9; border: none; padding: 6px; border-radius: 4px; cursor: pointer; color: #64748b;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
                                    <button style="background: #f1f5f9; border: none; padding: 6px; border-radius: 4px; cursor: pointer; color: #64748b;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                                </div>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 16px;"><input type="checkbox"></td>
                            <td style="padding: 16px; font-size: 0.9rem; color: #334155;">2026-04-05 03:30 PM<br><span style="color: #94a3b8; font-size: 0.8rem;">GMT +05:30</span></td>
                            <td style="padding: 16px; font-size: 0.9rem; color: #334155;">Arrived slightly late due to traffic</td>
                            <td style="padding: 16px; font-size: 0.9rem; color: #334155;">2026-04-05 03:30 PM<br><span style="color: #94a3b8; font-size: 0.8rem;">GMT +05:30</span></td>
                            <td style="padding: 16px; font-size: 0.9rem; color: #334155;">-</td>
                            <td style="padding: 16px; font-size: 0.9rem; color: #334155;">0.00</td>
                            <td style="padding: 16px;">
                                <div style="display: flex; gap: 8px;">
                                    <button style="background: #f1f5f9; border: none; padding: 6px; border-radius: 4px; cursor: pointer; color: #64748b;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
                                    <button style="background: #f1f5f9; border: none; padding: 6px; border-radius: 4px; cursor: pointer; color: #64748b;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

<?php include_once "../includes/footer.php"; ?>

<script>
    // Update current time
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit',
            hour12: true 
        });
        const dateString = now.toLocaleDateString('en-US', { 
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        document.getElementById('currentTime').textContent = timeString;
        document.getElementById('currentDate').textContent = dateString;
    }

    // Initialize time on page load
    updateTime();
    setInterval(updateTime, 1000);

    // Global variables to store notes temporarily (for demonstration)
    let currentCheckInNote = "";
    let currentCheckOutNote = "";

    // Handle Check In
    function handleCheckIn() {
        const checkInBtn = document.getElementById('checkInBtn');
        const checkOutBtn = document.getElementById('checkOutBtn');
        const checkInTime = document.getElementById('checkInTime');
        const todayStatus = document.getElementById('todayStatus');
        const alertMsg = document.getElementById('alertMessage');
        const noteInput = document.getElementById('punchNote');

        // Get note value and clear input
        currentCheckInNote = noteInput.value.trim() || "-";
        noteInput.value = "";
        noteInput.placeholder = "Add a check-out note...";

        // Get current time
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit',
            hour12: true 
        });

        // Update UI
        checkInTime.textContent = timeString;
        todayStatus.textContent = 'Checked In';
        checkInBtn.disabled = true;
        checkOutBtn.disabled = false;

        // Show success message
        alertMsg.style.display = 'block';
        alertMsg.style.backgroundColor = '#dcfce7';
        alertMsg.style.color = '#166534';
        alertMsg.style.border = '1px solid #bbf7d0';
        alertMsg.textContent = '✓ Successfully checked in at ' + timeString + (currentCheckInNote !== "-" ? ' (Note attached)' : '');
        setTimeout(() => alertMsg.style.display = 'none', 3000);

        // Styling updates
        checkInBtn.style.opacity = '0.5';
        checkInBtn.style.cursor = 'not-allowed';
        checkOutBtn.style.opacity = '1';
        checkOutBtn.style.cursor = 'pointer';
    }

    // Handle Check Out
    function handleCheckOut() {
        const checkInBtn = document.getElementById('checkInBtn');
        const checkOutBtn = document.getElementById('checkOutBtn');
        const checkOutTime = document.getElementById('checkOutTime');
        const todayStatus = document.getElementById('todayStatus');
        const alertMsg = document.getElementById('alertMessage');
        const noteInput = document.getElementById('punchNote');

        // Get note value and clear input
        currentCheckOutNote = noteInput.value.trim() || "-";
        noteInput.value = "";
        noteInput.placeholder = "Attendance complete for today.";
        noteInput.disabled = true; // Disable input after checkout

        // Get current time
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit',
            hour12: true 
        });

        // Update UI
        checkOutTime.textContent = timeString;
        todayStatus.textContent = 'Checked Out';
        checkOutBtn.disabled = true;

        // Show success message
        alertMsg.style.display = 'block';
        alertMsg.style.backgroundColor = '#dcfce7';
        alertMsg.style.color = '#166534';
        alertMsg.style.border = '1px solid #bbf7d0';
        alertMsg.textContent = '✓ Successfully checked out at ' + timeString + (currentCheckOutNote !== "-" ? ' (Note attached)' : '');
        setTimeout(() => alertMsg.style.display = 'none', 3000);

        // Styling updates
        checkOutBtn.style.opacity = '0.5';
        checkOutBtn.style.cursor = 'not-allowed';
        
        // Here you would typically trigger an AJAX request to save both the check-in and check-out notes to your database
        console.log("Check In Note:", currentCheckInNote);
        console.log("Check Out Note:", currentCheckOutNote);
    }
</script>