<?php
    session_start();

    if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}


    $user_name = "Hammad";
    $user_role = "Senior Developer";
    $current_page = "employee_dashboard";
    $user_dept = "Software Engineering";
    $pto_balance = 14;
    $next_payday = "Apr 25";
    $active_tasks_count = 6;

    // Load styles
    $extra_css = "employee_dashboard";
    $title = "Employee Dashboard - CodeXentric";
    include_once "../includes/header.php";
    include_once "../includes/sidebar.php";

?>

<main class="main-content" role="main">
    <div class="dashboard-container">
        <!-- Page Header -->
        <header class="page-header" role="banner">
            <div class="header-content">
                <h1 class="page-title">Welcome back, <?php echo htmlspecialchars($_SESSION['email']); ?></h1>
                <p class="page-subtitle"><?php echo "$user_dept | $user_role"; ?></p>
            </div>
            <div class="header-actions">
                <a href="request_time_off.php" class="action-button primary" aria-label="Request time off">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Request Time Off
                </a>
                <a href="employee_settings.php" class="action-button secondary" aria-label="View settings">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M12 2v2M12 20v2M20 12h2M2 12h2M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41"/>
                    </svg>
                    Settings
                </a>
            </div>
        </header>

        <!-- Quick Actions -->
        <section class="quick-actions-section" aria-labelledby="quick-actions-heading">
            <h2 id="quick-actions-heading" class="sr-only">Quick Actions</h2>
            <div class="quick-actions-grid">
                <a href="attendance.php" class="action-card" aria-label="Mark your attendance">
                    <div class="action-card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <div class="action-card-content">
                        <h3 class="action-card-title">Mark Attendance</h3>
                        <p class="action-card-description">Check in or out quickly from your dashboard.</p>
                    </div>
                </a>
                <a href="attendance.php" class="action-card" aria-label="View your attendance history">
                    <div class="action-card-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14,2 14,8 20,8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </div>
                    <div class="action-card-content">
                        <h3 class="action-card-title">View Attendance</h3>
                        <p class="action-card-description">Review your daily attendance history.</p>
                    </div>
                </a>
            </div>
        </section>

        <!-- Statistics Overview -->
        <section class="stats-overview" aria-labelledby="stats-heading">
            <h2 id="stats-heading" class="sr-only">Statistics Overview</h2>
            <div class="stats-grid">
                <article class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </div>
                        <h3 class="stat-title">Available PTO</h3>
                    </div>
                    <div class="stat-value"><?php echo $pto_balance; ?> Days</div>
                    <p class="stat-description">Paid time off remaining</p>
                </article>
                <article class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                <line x1="8" y1="21" x2="16" y2="21"/>
                                <line x1="12" y1="17" x2="12" y2="21"/>
                            </svg>
                        </div>
                        <h3 class="stat-title">Next Payday</h3>
                    </div>
                    <div class="stat-value"><?php echo $next_payday; ?></div>
                    <p class="stat-description">Upcoming salary payment</p>
                </article>
                <article class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 11H5a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h2.5"/>
                                <path d="M9 11V9a3 3 0 0 1 6 0v2"/>
                                <path d="M13 11h4a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-2.5"/>
                                <path d="M22 18h-7l-3 3-3-3H2"/>
                            </svg>
                        </div>
                        <h3 class="stat-title">Active Tasks</h3>
                    </div>
                    <div class="stat-value"><?php echo sprintf("%02d", $active_tasks_count); ?></div>
                    <p class="stat-description">Tasks requiring attention</p>
                </article>
            </div>
        </section>
    


        <h3 class="section-title">Recent Leave Requests</h3>
        <section class="table-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date Requested</th>
                        <th>Leave Type</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Mar 15, 2026</strong></td>
                        <td>Sick Leave</td>
                        <td>1 Day</td>
                        <td><span class="badge active">Approved</span></td>
                    </tr>
                </tbody>
            </table>
        </section>
    
</div>
</main>
</div>
<?php include_once "../includes/footer.php"; ?>