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

.dash-header-actions {
  display: flex;
  gap: 12px;
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

.btn-minimal.primary {
  background: var(--brand-orange);
  border-color: var(--brand-orange);
  color: #fff;
}

.btn-minimal.primary:hover {
  background: var(--brand-orange-hover);
}

/* ── Quick Actions ── */
.quick-actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.action-card {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 24px;
  display: flex;
  gap: 16px;
  text-decoration: none;
  transition: all 0.2s;
  box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}

.action-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  border-color: var(--brand-orange);
}

.action-card-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  background: #f1f5f9;
  color: var(--brand-green);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.action-card-title {
  font-size: 15px;
  font-weight: 800;
  color: var(--text-main);
  margin-bottom: 4px;
}

.action-card-description {
  font-size: 13px;
  color: var(--text-muted);
  line-height: 1.4;
}

/* ── Stats Section ── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.stat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-title {
  font-size: 11px;
  font-weight: 800;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-icon {
  color: var(--brand-green);
}

.stat-value {
  font-size: 28px;
  font-weight: 800;
  color: var(--text-main);
  letter-spacing: -0.5px;
}

.stat-description {
  font-size: 12px;
  color: var(--text-muted);
}

/* ── Minimal Table ── */
.section-title {
  font-size: 13px;
  font-weight: 800;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 15px;
  padding-left: 4px;
}

.table-section {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  background: #fcfcfd;
  padding: 12px 20px;
  text-align: left;
  font-size: 11px;
  font-weight: 800;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 1px;
  border-bottom: 1px solid var(--border);
}

.data-table td {
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
  font-size: 14px;
  color: var(--text-main);
}

.data-table tr:last-child td {
  border-bottom: none;
}

.data-table tr:hover {
  background: #f8fafc;
}

.badge {
  font-size: 11px;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: 6px;
  text-transform: uppercase;
}

.badge.active {
  background: #f0fdf4;
  color: #16a34a;
}
</style>

<div class="dashboard-container">
    <!-- Minimal Header -->
    <header class="dash-header">
        <div>
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?></h1>
            <p class="dash-subtitle"><?php echo "$user_dept | $user_role"; ?></p>
        </div>
        <div class="dash-header-actions">
            <a href="request_time_off.php" class="btn-minimal primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Request Time Off
            </a>
            <a href="settings.php" class="btn-minimal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M12 2v2M12 20v2M20 12h2M2 12h2M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41"/>
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
<?php include_once "../includes/footer.php"; ?>