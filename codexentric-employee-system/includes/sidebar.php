<style>
  /* ── SIDEBAR ── */
  .sidebar { width: 220px; min-width: 220px; background: var(--sidebar-bg); border-right: 1px solid var(--border); padding: 20px 0; display: flex; flex-direction: column; gap: 2px; }
  .sidebar-section-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); padding: 14px 20px 6px; }
  .sidebar-link { display: flex; align-items: center; gap: 10px; padding: 9px 20px; font-size: 13.5px; font-weight: 500; color: var(--text-secondary); text-decoration: none; border-left: 3px solid transparent; transition: all 0.15s; }
  .sidebar-link:hover, .sidebar-link.active { background: var(--blue-light); color: var(--blue); border-left-color: var(--blue); }
  .sidebar-link.active { font-weight: 600; }
  .sidebar-link svg { width: 16px; height: 16px; flex-shrink: 0; opacity: 0.65; }
  .sidebar-link.active svg, .sidebar-link:hover svg { opacity: 1; }
  @media (max-width: 900px) { .sidebar { display: none; } }
</style>

<?php
// Only show sidebar if NOT on the login page AND a user is logged in
if (!isset($current_page) || $current_page === 'login' || !isset($_SESSION['role_name'])) {
    return; // Stop rendering the sidebar entirely
}

$role = $_SESSION['role_name'];
?>

<aside class="sidebar">
    <div class="sidebar-section-label">Main Menu</div>
    
    <?php if ($role === 'admin'): ?>
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
        <a href="payroll.php" class="sidebar-link <?php echo ($current_page == 'payroll') ? 'active' : ''; ?>">
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
<a href="<?php echo ($_SESSION['role_name'] === 'admin') ? 'settings.php' : 'employee_settings.php'; ?>" 
   class="sidebar-link <?php echo ($current_page == 'employee_settings' || $current_page == 'settings') ? 'active' : ''; ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M12 2v2M12 20v2M20 12h2M2 12h2M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41"/></svg>
    Settings
</a>

    <a href="logout.php" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Logout
    </a>
</aside>