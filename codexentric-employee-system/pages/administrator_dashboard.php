<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$current_page = "administrator_dashboard";
$extra_css    = "admin_dashboard";
$title        = "Admin Dashboard";
require_once '../pages/database.php';
require_once '../pages/Employee.php';
$db = new Database();
$connection = $db->getConnection();
$employeeObj = new Employee($connection);

$dashboardData = $employeeObj->getDashboardData();

$stats = [
    ["label" => "Total Employees",  "value" => (string)$dashboardData['total_headcount']],
    ["label" => "Monthly Payroll",  "value" => "Rs " . number_format($dashboardData['monthly_payroll'])],
    ["label" => "Recent Hires",     "value" => count($dashboardData['team_members'])]
];

$team_members = $dashboardData['team_members'];

include_once "../includes/header.php";
include_once "../includes/sidebar.php";
?>

<style>
/* ── OrangeHRM Style Token System ── */
:root {
  --bg: #f4f6f9; /* Crisp, premium light grey-blue background from reference */
  --card-bg: #ffffff;
  --border: #eef2f6; /* Very soft, clean border */
  --text-main: #334155;
  --text-muted: #64748b;
  --brand-orange: #ff7b1d;
  --brand-orange-hover: #e66a15;
  --brand-green: #186D55;
  --icon-bg: #f4f6f9;
  --font-body: 'Nunito Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
}

body {
  background-color: var(--bg);
  font-family: var(--font-body);
  color: var(--text-main);
  margin: 0;
}

.cx-dash {
  padding: 0 30px 30px 30px;
  min-height: 100vh;
  box-sizing: border-box;
  max-width: 1200px;
  margin: 0 auto;
}

@media (max-width: 600px) {
  .cx-dash {
    padding: 0 15px 15px 15px;
  }
}

/* ── Top Header & Actions ── */
.dash-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.dash-header h1 {
  font-size: 22px;
  font-weight: 700;
  margin: 0;
  color: var(--text-main);
}

.header-actions {
  display: flex;
  gap: 12px;
}

.btn-minimal {
  display: inline-block;
  padding: 8px 16px;
  border: 1px solid var(--border);
  background: var(--card-bg);
  color: var(--text-main);
  border-radius: 6px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-minimal:hover {
  background: var(--icon-bg);
}

.btn-primary {
  display: inline-block;
  padding: 8px 16px;
  border: 1px solid var(--brand-orange);
  background: var(--brand-orange);
  color: #ffffff;
  border-radius: 6px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary:hover {
  background: var(--brand-orange-hover);
  border-color: var(--brand-orange-hover);
}

/* ── Dashboard Grid ── */
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

/* ── Widget Cards ── */
.widget-card {
  background: var(--card-bg);
  border-radius: 20px; /* Highly rounded corners to match reference */
  border: 1px solid var(--border);
  box-shadow: 0 4px 24px rgba(0,0,0,0.03); /* Soft, elegant shadow to match reference */
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.widget-card.roster-card {
  background: #ffffff; /* Uniform clean background */
  border: 1px solid var(--border);
  border-radius: 20px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.03);
}

.widget-header {
  padding: 20px 24px;
  background: #ffffff; /* Transparent/white header from reference */
  border-bottom: 1px solid #f8fafc;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  font-weight: 700;
  color: #1e293b;
}

.widget-header svg {
  width: 18px;
  height: 18px;
  stroke: var(--text-muted);
  stroke-width: 2;
  fill: none;
}

.widget-body {
  padding: 24px;
  flex: 1;
}

/* ── Stats List (Inside Overview Card) ── */
.stat-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.stat-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 16px;
  border-bottom: 1px dashed var(--border);
}

.stat-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.stat-label {
  font-size: 14px;
  color: var(--text-muted);
  font-weight: 600;
}

.stat-value {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-main);
}

/* ── Quick Launch Icons ── */
.quick-launch-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px 10px;
}

.ql-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  color: var(--text-muted);
  font-size: 12px;
  font-weight: 600;
  transition: all 0.2s ease;
}

.ql-icon-wrapper {
  width: 54px;
  height: 54px;
  border-radius: 50%;
  background: #f0f4f8; /* Muted gray-blue circle */
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.ql-icon-wrapper svg {
  width: 24px;
  height: 24px;
  stroke: #475569;
  stroke-width: 1.5;
  fill: none;
}

.ql-item:hover .ql-icon-wrapper {
  background: var(--brand-green);
}
.ql-item:hover .ql-icon-wrapper svg {
  stroke: #ffffff;
}
.ql-item:hover {
  color: var(--brand-green);
}

/* ── Directory Table ── */
.table-container {
  width: 100%;
  overflow-x: auto;
}

.minimal-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 10px;
  font-size: 14px;
  text-align: left;
  margin-top: -10px; /* Offset the first row spacing */
}

.minimal-table thead th {
  background: transparent;
  padding: 12px 24px;
  font-weight: 700;
  color: #475569;
  font-size: 13px;
  letter-spacing: 0.2px;
  white-space: nowrap;
}

.minimal-table td {
  padding: 14px 20px;
  color: #64748b;
  background: #ffffff;
  border-top: 1px solid #e2e8f0;
  border-bottom: 1px solid #e2e8f0;
  vertical-align: middle;
  transition: all 0.2s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.minimal-table td:first-child {
  border-left: 1px solid #e2e8f0;
  border-top-left-radius: 50px;
  border-bottom-left-radius: 50px;
  padding-left: 30px;
}

.minimal-table td:last-child {
  border-right: 1px solid #e2e8f0;
  border-top-right-radius: 50px;
  border-bottom-right-radius: 50px;
  padding-right: 30px;
}

.minimal-table tr:hover td {
  background-color: #f8fafc;
  border-color: #cbd5e1;
}

.status-indicator {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 500;
  color: #64748b;
  text-transform: lowercase;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-indicator.active .status-dot { background: var(--brand-green); }
.status-indicator.on-leave .status-dot { background: #ff7b1d; }
.status-indicator.deactivated .status-dot { background: #ef4444; }

.action-trigger-group {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.action-icon-btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  text-decoration: none;
  transition: all 0.2s;
}

.action-icon-btn:hover {
  background: var(--brand-green);
  color: #fff;
}

.action-icon-btn.delete:hover {
  background: #ef4444;
}

.emp-name-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.emp-avatar-sm {
  width: 28px;
  height: 28px;
  border-radius: 4px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: 700;
  flex-shrink: 0;
}

.role-tag {
  font-size: 12px;
  color: #475569;
}

.salary-text {
  font-weight: 700;
  color: #334155;
  font-size: 13px;
}

.empty-state {
  padding: 40px 0;
  text-align: center;
  color: var(--text-muted);
  font-size: 14px;
}
</style>

<div class="cx-dash">



  <!-- Top Cards Row -->
  <div class="dashboard-grid">
    
    <!-- Overview Card -->
    <div class="widget-card">
      <div class="widget-header">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Overview Stats
      </div>
      <div class="widget-body">
        <div class="stat-list">
          <?php foreach ($stats as $s): ?>
          <div class="stat-item">
            <span class="stat-label"><?php echo htmlspecialchars($s['label']); ?></span>
            <span class="stat-value"><?php echo htmlspecialchars($s['value']); ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Quick Launch Card -->
    <div class="widget-card">
      <div class="widget-header">
        <svg viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
        Quick Launch
      </div>
      <div class="widget-body" style="display: flex; align-items: center; justify-content: center;">
        <div class="quick-launch-grid">
          
          <a href="manage_employee.php" class="ql-item">
            <div class="ql-icon-wrapper">
              <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <span>Directory</span>
          </a>

          <a href="payroll_management.php" class="ql-item">
            <div class="ql-icon-wrapper">
              <svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
            </div>
            <span>Payroll</span>
          </a>

          <a href="attendance_record.php" class="ql-item">
            <div class="ql-icon-wrapper">
              <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <span>Attendance</span>
          </a>

          <a href="settings.php" class="ql-item">
            <div class="ql-icon-wrapper">
              <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M12 2v2M12 20v2M20 12h2M2 12h2M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41"/></svg>
            </div>
            <span>Settings</span>
          </a>

          <a href="add_employee.php" class="ql-item">
            <div class="ql-icon-wrapper">
              <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>
            </div>
            <span>Add User</span>
          </a>

        </div>
      </div>
    </div>

  </div> <!-- End Top Row -->

  <!-- Full Width Table Row -->
  <div class="widget-card roster-card">
    <div class="widget-header" style="background: transparent; border-bottom: none; padding-bottom: 0;">
      <svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
      Team Roster
    </div>
    <div class="widget-body" style="padding-top: 10px;">
      <?php if (!empty($team_members)): ?>
      <div class="table-container">
        <table class="minimal-table">
          <thead>
            <tr>
              <th>Personnel</th>
              <th>Role</th>
              <th>Salary</th>
              <th>Status</th>
              <th style="text-align: right;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($team_members as $employee): 
                $statusClass = strtolower($employee['status']) == 'active' ? 'active' : '';
                $initials = strtoupper(substr($employee['name'], 0, 1));
                if (strpos($employee['name'], ' ') !== false) {
                    $parts = explode(' ', $employee['name']);
                    $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts)-1], 0, 1));
                }
            ?>
            <tr>
              <td>
                <div class="emp-name-cell">
                  <span style="font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($employee['name']); ?></span>
                </div>
              </td>
              <td><span class="role-tag"><?php echo htmlspecialchars($employee['role']); ?></span></td>
              <td><span class="salary-text"><?php echo htmlspecialchars($employee['salary']); ?> <span style="font-size: 10px; color: #94a3b8;">/mo</span></span></td>
              <td>
                <?php 
                  $raw_status = strtolower($employee['status'] ?? 'active');
                  $status_class = str_replace('_', '-', $raw_status);
                  $display_label = str_replace('_', ' ', $raw_status);
                  if ($raw_status === 'onboarding') { $status_class = 'on-leave'; $display_label = 'on leave'; }
                  elseif ($raw_status === 'terminated') { $status_class = 'deactivated'; $display_label = 'terminated'; }
                ?>
                <div class="status-indicator <?php echo $status_class; ?>">
                  <span class="status-dot"></span>
                  <span class="status-text"><?php echo htmlspecialchars($display_label); ?></span>
                </div>
              </td>
              <td style="text-align: right;">
                <div class="action-trigger-group">
                  <a href="manage_employee.php?id=<?php echo $employee['user_id']; ?>" class="action-icon-btn" title="Edit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="empty-state">
        No team members to display at the moment.
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php include_once "../includes/footer.php"; ?>
