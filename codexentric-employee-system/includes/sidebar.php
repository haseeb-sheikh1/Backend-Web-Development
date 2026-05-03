<style>

/* ── SIDEBAR ── */
.sidebar {
  width: 220px;
  min-width: 220px;
  background: #0f1c2e;
  border-right: none;
  padding: 24px 0 16px;
  display: flex;
  flex-direction: column;
  gap: 1px;
  box-shadow: 4px 0 20px rgba(0,0,0,0.18);
}

.sidebar-section-label {
  font-size: 9.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.4px;
  color: #4a6080;
  padding: 18px 20px 6px;
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 16px 9px 18px;
  margin: 0 10px;
  font-size: 13px;
  font-weight: 500;
  color: #8bacc8;
  text-decoration: none;
  border-left: none;
  border-radius: 8px;
  transition: all 0.15s ease;
  position: relative;
}

.sidebar-link:hover {
  background: rgba(255,255,255,0.06);
  color: #e0eaf4;
}

.sidebar-link.active {
  background: linear-gradient(135deg, #1a6eff18, #1a6eff28);
  color: #4d9fff;
  font-weight: 600;
  box-shadow: inset 0 0 0 1px #1a6eff30;
}

.sidebar-link.active::before {
  content: '';
  position: absolute;
  left: -2px;
  top: 20%;
  height: 60%;
  width: 3px;
  background: #1a6eff;
  border-radius: 0 3px 3px 0;
}

.sidebar-link svg {
  width: 15px;
  height: 15px;
  flex-shrink: 0;
  opacity: 0.5;
  transition: opacity 0.15s;
}

.sidebar-link.active svg,
.sidebar-link:hover svg {
  opacity: 1;
}

@media (max-width: 900px) { .sidebar { display: none; } }
</style>

<?php
// Only show sidebar if NOT on the login page AND a user is logged in
if (!isset($current_page) || $current_page === 'login' || !isset($_SESSION['role_id'])) {
    return; // Stop rendering the sidebar entirely
}

$role = $_SESSION['role_id'];
?>

<aside class="sidebar">
    <div class="sidebar-section-label">Main Menu</div>
    
    <?php if ($role == '1'): ?>
        <a href="administrator_dashboard.php" class="sidebar-link <?php echo ($current_page == 'administrator_dashboard') ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Overview
        </a>
        <a href="add_employee.php" class="sidebar-link <?php echo ($current_page == 'add_employee') ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Add Employees
        </a>
        <a href="employees_list.php" class="sidebar-link <?php echo ($current_page == 'manage_employees') ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            Manage Employees
        </a>
        <a href="attendance_record.php" class="sidebar-link <?php echo ($current_page == 'attendance_record') ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Attendance Record
        </a>
        <a href="payroll_management.php" class="sidebar-link <?php echo ($current_page == 'payroll') ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Payroll
        </a>

    <?php else: ?>
        <a href="employee_dashboard.php" class="sidebar-link <?php echo ($current_page == 'employee_dashboard') ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Overview
        </a>
        <a href="attendance.php" class="sidebar-link <?php echo ($current_page == 'attendance') ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Attendance
        </a>
        <a href="request_time_off.php" class="sidebar-link <?php echo ($current_page == 'request_time_off') ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            Request Time Off
        </a>
    <?php endif; ?>

  <div class="sidebar-section-label">Administration</div>
<a href="settings.php" 
   class="sidebar-link <?php echo ($current_page == 'settings') ? 'active' : ''; ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M12 2v2M12 20v2M20 12h2M2 12h2M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41"/></svg>
    Settings
</a>

    <a href="logout.php" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Logout
    </a>
</aside>