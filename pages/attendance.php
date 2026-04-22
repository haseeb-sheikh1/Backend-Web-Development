<?php
    session_start();

    // Check if the session variable we set in login.php exists
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        // If not logged in, kick them back to the login UI
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

<main class="main-content" role="main">
    <div class="dashboard-container">
        <!-- Page Header -->
        <header class="page-header" role="banner">
            <div class="header-content">
                <h1 class="page-title">Attendance Tracker</h1>
                <p class="page-subtitle">Check in and out, or view your attendance history.</p>
            </div>
            <div class="header-actions">
                <a href="employee_dashboard.php" class="action-button secondary" aria-label="Return to employee dashboard">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </header>

        <!-- Check In/Out Section -->
        <section class="check-in-section" aria-labelledby="check-in-heading">
            <h2 id="check-in-heading" class="sr-only">Check In/Out</h2>
            <div class="check-card">
                <div class="time-display">
                    <div class="time-header">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <h3 class="time-title">Current Time</h3>
                    </div>
                    <div class="current-time" id="currentTime">--:--:-- --</div>
                    <p class="date-display" id="currentDate">--</p>
                </div>

                <div class="status-info">
                    <div class="status-item">
                        <span class="status-label">Today's Status</span>
                        <span class="status-value" id="todayStatus">Not Checked In</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Check In Time</span>
                        <span class="status-value" id="checkInTime">--:--:--</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Check Out Time</span>
                        <span class="status-value" id="checkOutTime">--:--:--</span>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <button class="action-button primary" id="checkInBtn" onclick="handleCheckIn()" aria-label="Check in for today">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    Check In
                </button>
                <button class="action-button danger" id="checkOutBtn" onclick="handleCheckOut()" disabled aria-label="Check out for today">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Check Out
                </button>
            </div>

            <div class="alert-message" id="alertMessage" style="display: none;" role="alert" aria-live="polite"></div>
        </section>

        <!-- Attendance History -->
        <section class="attendance-history" aria-labelledby="history-heading">
            <div class="section-header">
                <h2 id="history-heading" class="section-title">Attendance History</h2>
            </div>
            <div class="table-container">
                <table class="attendance-table" role="table" aria-label="Attendance history">
                    <thead>
                        <tr>
                            <th scope="col">Date</th>
                            <th scope="col">Check In</th>
                            <th scope="col">Check Out</th>
                            <th scope="col">Duration</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceTableBody">
                        <tr>
                            <td><strong>Apr 18, 2026</strong></td>
                            <td>09:05:30</td>
                            <td>17:45:15</td>
                            <td>8h 39m</td>
                            <td><span class="status-badge present">Present</span></td>
                        </tr>
                        <tr>
                            <td><strong>Apr 17, 2026</strong></td>
                            <td>08:55:22</td>
                            <td>17:30:45</td>
                            <td>8h 35m</td>
                            <td><span class="status-badge present">Present</span></td>
                        </tr>
                        <tr>
                            <td><strong>Apr 16, 2026</strong></td>
                            <td>09:12:10</td>
                            <td>17:50:30</td>
                            <td>8h 38m</td>
                            <td><span class="status-badge present">Present</span></td>
                        </tr>
                        <tr>
                            <td><strong>Apr 15, 2026</strong></td>
                            <td>--:--:--</td>
                            <td>--:--:--</td>
                            <td>--:--</td>
                            <td><span class="status-badge absent">Absent</span></td>
                        </tr>
                        <tr>
                            <td><strong>Apr 14, 2026</strong></td>
                            <td>09:00:00</td>
                            <td>17:25:00</td>
                            <td>8h 25m</td>
                            <td><span class="status-badge present">Present</span></td>
                        </tr>
                        <tr>
                            <td><strong>Apr 11, 2026</strong></td>
                            <td>08:58:45</td>
                            <td>17:40:20</td>
                            <td>8h 41m</td>
                            <td><span class="status-badge present">Present</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Monthly Summary -->
        <section class="monthly-summary" aria-labelledby="summary-heading">
            <h2 id="summary-heading" class="sr-only">Monthly Summary</h2>
            <div class="summary-header">
                <h3 class="summary-title">Monthly Summary</h3>
            </div>
            <div class="stats-grid">
                <article class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 12l2 2 4-4"/>
                                <path d="M21 12c.552 0 1-.448 1-1V5c0-.552-.448-1-1-1H3c-.552 0-1 .448-1 1v6c0 .552.448 1 1 1"/>
                                <path d="M3 12v7c0 .552.448 1 1 1h16c.552 0 1-.448 1-1v-7"/>
                            </svg>
                        </div>
                        <h4 class="stat-title">Present Days</h4>
                    </div>
                    <div class="stat-value">16</div>
                    <p class="stat-description">This month</p>
                </article>
                <article class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon absent">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="15" y1="9" x2="9" y2="15"/>
                                <line x1="9" y1="9" x2="15" y2="15"/>
                            </svg>
                        </div>
                        <h4 class="stat-title">Absent Days</h4>
                    </div>
                    <div class="stat-value">2</div>
                    <p class="stat-description">This month</p>
                </article>
                <article class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </div>
                        <h4 class="stat-title">Total Hours</h4>
                    </div>
                    <div class="stat-value">128h</div>
                    <p class="stat-description">This month</p>
                </article>
                <article class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                        </div>
                        <h4 class="stat-title">Avg Daily</h4>
                    </div>
                    <div class="stat-value">8h</div>
                    <p class="stat-description">Hours worked</p>
                </article>
            </div>
        </section>
    </div>
</main>

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

    // Handle Check In
    function handleCheckIn() {
        const checkInBtn = document.getElementById('checkInBtn');
        const checkOutBtn = document.getElementById('checkOutBtn');
        const checkInTime = document.getElementById('checkInTime');
        const todayStatus = document.getElementById('todayStatus');
        const alertMsg = document.getElementById('alertMessage');

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
        alertMsg.className = 'alert-message alert-success';
        alertMsg.textContent = '✓ Successfully checked in at ' + timeString;
        setTimeout(() => alertMsg.style.display = 'none', 3000);

        // Disable button styling
        checkInBtn.style.opacity = '0.5';
        checkInBtn.style.cursor = 'not-allowed';
    }

    // Handle Check Out
    function handleCheckOut() {
        const checkInBtn = document.getElementById('checkInBtn');
        const checkOutBtn = document.getElementById('checkOutBtn');
        const checkOutTime = document.getElementById('checkOutTime');
        const todayStatus = document.getElementById('todayStatus');
        const alertMsg = document.getElementById('alertMessage');

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
        alertMsg.className = 'alert-message alert-success';
        alertMsg.textContent = '✓ Successfully checked out at ' + timeString;
        setTimeout(() => alertMsg.style.display = 'none', 3000);

        // Disable button styling
        checkOutBtn.style.opacity = '0.5';
        checkOutBtn.style.cursor = 'not-allowed';
    }
</script>
