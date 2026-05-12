<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != '1') {
    header("Location: employee_dashboard.php");
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
  grid-template-columns: 1fr 1fr; /* Fixed 2-column pair for desktop */
  gap: 24px;
  margin-bottom: 24px;
}

/* Responsive Stack Driver for Tablets/Mobile */
@media (max-width: 950px) {
  .dashboard-grid {
    grid-template-columns: 1fr; /* Force vertical stacking sooner */
  }
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
  background: transparent;
  border: none;
  box-shadow: none;
}
.widget-card.roster-card .widget-header,
.widget-card.roster-card .widget-body {
  padding-left: 0;
  padding-right: 0;
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
@media (max-width: 400px) {
  .quick-launch-grid { gap: 12px 8px; }
  .ql-icon-wrapper { width: 46px !important; height: 46px !important; }
  .ql-item { font-size: 11px !important; }
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
  border-spacing: 0 12px;
  font-size: 14px;
  text-align: left;
  margin-top: 0px;
}

.minimal-table thead th {
  background: transparent;
  padding: 12px 24px;
  font-weight: 700;
  color: #64748b;
  font-size: 12.5px;
  letter-spacing: 0.5px;
  white-space: nowrap;
}

.th-content {
  display: flex;
  align-items: center;
  gap: 6px;
}

.header-sort-icon {
  width: 12px;
  height: 12px;
  opacity: 0.5;
}

.minimal-table td {
  padding: 16px 24px;
  color: #475569;
  background: #ffffff;
  border-top: 1px solid rgba(0, 0, 0, 0.04);
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
  vertical-align: middle;
  transition: all 0.2s ease;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.015);
}

.minimal-table td:first-child {
  border-left: 1px solid rgba(0, 0, 0, 0.04);
  border-top-left-radius: 25px;
  border-bottom-left-radius: 25px;
  padding-left: 28px;
}

.minimal-table td:last-child {
  border-right: 1px solid rgba(0, 0, 0, 0.04);
  border-top-right-radius: 25px;
  border-bottom-right-radius: 25px;
  padding-right: 28px;
}

.minimal-table tr:hover td {
  background-color: #fafbfc;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.025);
}

.status-indicator {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 600;
  color: #475569;
  text-transform: capitalize;
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

/* ── MOBILE VIEW OVERHAUL ── */
@media (max-width: 768px) {
  .minimal-table thead {
    display: none; /* Hide original headers */
  }
  
  .minimal-table, .minimal-table tbody, .minimal-table tr {
    display: block;
    width: 100%;
  }
  
  .minimal-table tr {
    background: #ffffff;
    border: 1px solid #eef2f6;
    border-radius: 24px;
    margin-bottom: 20px;
    padding: 24px;
    position: relative;
    box-shadow: 0 4px 24px rgba(0,0,0,0.02);
    box-sizing: border-box;
  }
  
  /* Remove standard hover translation on mobile to ensure tap stability */
  .minimal-table tr:hover td { transform: none; background: transparent; }
  .minimal-table tr:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,0,0,0.03); transition: all 0.3s; }

  .minimal-table td {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    width: 100% !important;
    box-sizing: border-box;
    padding: 0 !important;
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    margin-bottom: 20px;
  }
  
  /* Reintroduce Labels via CSS Content */
  .minimal-table td[data-label]:not([data-label=""])::before {
    content: attr(data-label);
    display: block;
    font-size: 10.5px;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
  }

  /* Target Name Row to render large and separated at top */
  .minimal-table td[data-label="Employee Name"] {
    border-bottom: 1px solid #f1f5f9 !important;
    padding-bottom: 20px !important;
    margin-bottom: 20px;
  }
  .minimal-table td[data-label="Employee Name"] span {
    font-size: 18px;
    font-weight: 800 !important;
    color: #1e293b;
  }

  /* Last data entry spacing */
  .minimal-table td:nth-last-child(2) { margin-bottom: 0px; }

  /* Float the action buttons top-right precisely like user mockup */
  .minimal-table td:last-child {
    position: absolute;
    top: 24px;
    right: 24px;
    width: auto !important;
    margin: 0 !important;
  }
  
  .action-trigger-group {
    flex-direction: row-reverse;
    gap: 10px;
  }
  
  .action-icon-btn {
    width: 38px;
    height: 38px;
    background: #f8fafc;
    border: 1px solid #edf2f7;
  }
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
        <div class="quick-launch-grid" style="max-width: 320px; gap: 24px 20px;">
          <a href="employees_list.php" class="ql-item">
            <div class="ql-icon-wrapper">
              <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <span>Directory</span>
          </a>

          <a href="add_employee.php" class="ql-item">
            <div class="ql-icon-wrapper">
              <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>
            </div>
            <span>Add User</span>
          </a>

          <a href="payroll_management.php" class="ql-item">
            <div class="ql-icon-wrapper">
              <svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
            </div>
            <span>Payroll</span>
          </a>

          <a href="salary_reports.php" class="ql-item">
            <div class="ql-icon-wrapper">
              <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <span>Salary Reports</span>
          </a>

          <a href="expenses.php" class="ql-item">
            <div class="ql-icon-wrapper">
              <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <span>Expenses</span>
          </a>

          <a href="settings.php" class="ql-item">
            <div class="ql-icon-wrapper">
              <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M12 2v2M12 20v2M20 12h2M2 12h2M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41"/></svg>
            </div>
            <span>Settings</span>
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
              <th>
                <div class="th-content">
                  Personnel
                  <svg class="header-sort-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m7 15 5 5 5-5M7 9l5-5 5 5"/></svg>
                </div>
              </th>
              <th>
                <div class="th-content">
                  Role
                  <svg class="header-sort-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m7 15 5 5 5-5M7 9l5-5 5 5"/></svg>
                </div>
              </th>
              <th>
                <div class="th-content">
                  Salary
                  <svg class="header-sort-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m7 15 5 5 5-5M7 9l5-5 5 5"/></svg>
                </div>
              </th>
              <th>
                <div class="th-content">
                  Status
                  <svg class="header-sort-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m7 15 5 5 5-5M7 9l5-5 5 5"/></svg>
                </div>
              </th>
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
              <td data-label="Employee Name">
                <div class="emp-name-cell">
                  <span style="font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($employee['name']); ?></span>
                </div>
              </td>
              <td data-label="Position"><span class="role-tag" style="background: transparent; padding: 0; color: #1e293b; font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($employee['role']); ?></span></td>
              <td data-label="Base Salary"><span class="salary-text" style="font-weight: 600; color: #1e293b; font-size: 15px;"><?php echo htmlspecialchars($employee['salary']); ?></span></td>
              <td data-label="Status">
                <?php 
                  $raw_status = strtolower($employee['status'] ?? 'active');
                  $status_class = str_replace('_', '-', $raw_status);
                  $display_label = str_replace('_', ' ', $raw_status);
                  if ($raw_status === 'onboarding') { $status_class = 'on-leave'; $display_label = 'on leave'; }
                  elseif ($raw_status === 'terminated') { $status_class = 'deactivated'; $display_label = 'terminated'; }
                ?>
                <div class="status-indicator <?php echo $status_class; ?>" style="margin-top: 2px;">
                  <span class="status-dot"></span>
                  <span class="status-text" style="color: #1e293b; font-weight: 600;"><?php echo htmlspecialchars($display_label); ?></span>
                </div>
              </td>
              <td style="text-align: right;">
                <div class="action-trigger-group">
                  <a href="manage_employee.php?id=<?php echo $employee['user_id']; ?>" class="action-icon-btn" title="Edit">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                  </a>
                  <a href="#" class="action-icon-btn delete" title="Delete" onclick="alert('Delete operations must be confirmed from main directory management.'); return false;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
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
