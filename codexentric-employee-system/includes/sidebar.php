<?php
// Only show sidebar if a user is logged in
if (!isset($current_page) || $current_page === 'login' || !isset($_SESSION['role_id'])) {
    return;
}
$role = $_SESSION['role_id'];
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ─── Sidebar shell ───────────────────────────────── */
.sidebar {
    position: fixed;
    top: 0; left: 0;
    width: 249px;
    height: 100vh;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    font-family: 'Nunito Sans', sans-serif;
    z-index: 200;
    overflow: visible;          /* let toggle button hang outside */
    transition: width 0.25s cubic-bezier(.4,0,.2,1);
    border-right: 1px solid #e2e8f0 !important;
    box-shadow: 4px 0 10px rgba(0,0,0,0.03);
}

/* ── Collapsed ── */
.sidebar.collapsed { width: 64px; }

.sidebar.collapsed .sidebar-label,
.sidebar.collapsed .sidebar-search-input,
.sidebar.collapsed .sidebar-brand-name,
.sidebar.collapsed .sidebar-brand-sub {
    display: none;
}
.sidebar.collapsed .sidebar-search {
    justify-content: center;
    padding: 0;
    gap: 0;
    background: transparent;
}
.sidebar.collapsed .sidebar-link {
    justify-content: center;
    padding: 0;
    gap: 0;
}

/* ─── Brand ───────────────────────────────────────── */
.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 0 18px;
    height: 60px;
    min-height: 60px;
    flex-shrink: 0;
    text-decoration: none;
    overflow: hidden;
    border-bottom: none !important; /* Forces the horizontal black line to hide */
}

.sidebar-brand-icon {
    width: 36px;
    height: 36px;
    flex-shrink: 0;
}

.sidebar-brand-text {
    display: flex;
    flex-direction: column;
    white-space: nowrap;
    overflow: hidden;
}
.sidebar-brand-name {
    font-size: 15.5px;
    font-weight: 800;
    color: #1a6b58;
    letter-spacing: 0.1px;
    transition: opacity 0.2s;
}
.sidebar-brand-sub {
    font-size: 9px;
    font-weight: 700;
    color: #f5a623;
    letter-spacing: 2.2px;
    text-transform: uppercase;
    transition: opacity 0.2s;
}

/* ─── Search ──────────────────────────────────────── */
.sidebar-search {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 8px 12px 8px;
    padding: 0 12px;
    height: 36px;
    background: #f6f7f9;
    border-radius: 8px;
    flex-shrink: 0;
    cursor: text;
    overflow: hidden;
    transition: padding 0.25s, gap 0.25s;
}
.sidebar-search-icon {
    width: 17px;
    height: 17px;
    stroke: #aaacb5;
    stroke-width: 2;
    fill: none;
    flex-shrink: 0;
}
.sidebar-search-input {
    border: none;
    background: none;
    outline: none;
    font-family: 'Nunito Sans', sans-serif;
    font-size: 13.5px;
    color: #444;
    width: 100%;
    white-space: nowrap;
}
.sidebar-search-input::placeholder { color: #aaacb5; }

/* ─── Nav ─────────────────────────────────────────── */
.sidebar-nav {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 4px 0 16px;
    overflow-y: auto;
    overflow-x: hidden;
}
.sidebar-nav::-webkit-scrollbar { width: 3px; }
.sidebar-nav::-webkit-scrollbar-thumb { background: #e8e8e8; border-radius: 3px; }

/* ─── Link ────────────────────────────────────────── */
.sidebar-link {
    display: flex;
    align-items: center;
    gap: 13px;
    margin: 2px 10px;
    padding: 0 14px;
    height: 44px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    color: #5c5f6e;
    transition: background 0.14s, color 0.14s;
    white-space: nowrap;
    overflow: hidden;
    flex-shrink: 0;
}
.sidebar-link svg {
    width: 21px;
    height: 21px;
    flex-shrink: 0;
    stroke: currentColor;
    fill: none;
    stroke-width: 1.75;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.sidebar-link:hover {
    background: #fff3e8;
    color: #f37b1d;
}

/* ── Active: full solid orange pill, white everything ── */
.sidebar-link.active {
    background: #f37b1d;
    color: #ffffff;
    font-weight: 700;
}
.sidebar-link.active svg { stroke: #ffffff; }
.sidebar-link.active:hover { background: #e56c10; color: #fff; }

/* ─── Collapse toggle ─────────────────────────────── */
.sidebar-toggle {
    position: absolute;
    top: 50%;
    right: -14px;
    transform: translateY(-50%);
    width: 28px;
    height: 28px;
    background: #f37b1d;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 300;
    box-shadow: 0 2px 8px rgba(243,123,29,0.4);
    border: none;
    outline: none;
}
.sidebar-toggle svg {
    width: 14px;
    height: 14px;
    stroke: #fff;
    stroke-width: 2.5;
    stroke-linecap: round;
    stroke-linejoin: round;
    fill: none;
    transition: transform 0.25s;
    flex-shrink: 0;
}
.sidebar.collapsed .sidebar-toggle svg {
    transform: rotate(180deg);
}
</style>

<aside class="sidebar" id="appSidebar">

    <!-- Collapse toggle (orange circle with chevron) -->
    <button class="sidebar-toggle"
            onclick="document.getElementById('appSidebar').classList.toggle('collapsed')"
            aria-label="Toggle sidebar">
        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    </button>

    <!-- ── Brand ── -->
    <a href="<?php echo $role == '1' ? 'administrator_dashboard.php' : 'employee_dashboard.php'; ?>"
       class="sidebar-brand">

        <!-- Icon mark: < CX > in brand colours -->
        <svg class="sidebar-brand-icon" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
            <polyline points="13,5 4,20 13,35" fill="none" stroke="#f5a623" stroke-width="3.8" stroke-linecap="round" stroke-linejoin="round"/>
            <text x="14" y="28" font-family="'Nunito Sans',Arial,sans-serif" font-weight="800" font-size="20" fill="#1a6b58">CX</text>
            <polyline points="27,5 36,20 27,35" fill="none" stroke="#f5a623" stroke-width="3.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>

        <div class="sidebar-brand-text">
            <span class="sidebar-brand-name">CodeXentric</span>
            <span class="sidebar-brand-sub">EMS</span>
        </div>
    </a>

    <!-- ── Search ── -->
    <div class="sidebar-search">
        <svg class="sidebar-search-icon" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input class="sidebar-search-input" type="text" placeholder="Search">
    </div>

    <!-- ── Nav ── -->
    <nav class="sidebar-nav">

        <?php if ($role == '1'): ?>

            <a href="administrator_dashboard.php"
               class="sidebar-link <?php echo ($current_page == 'administrator_dashboard') ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                </svg>
                <span class="sidebar-label">Overview</span>
            </a>

            <a href="add_employee.php"
               class="sidebar-link <?php echo ($current_page == 'add_employee') ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <line x1="19" y1="8" x2="19" y2="14"/>
                    <line x1="16" y1="11" x2="22" y2="11"/>
                </svg>
                <span class="sidebar-label">Add Employees</span>
            </a>

            <a href="employees_list.php"
               class="sidebar-link <?php echo ($current_page == 'manage_employees') ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24">
                     <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
    <circle cx="9" cy="7" r="4"/>
    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span class="sidebar-label">Manage Employees</span>
            </a>


            <a href="payroll_management.php"
               class="sidebar-link <?php echo ($current_page == 'payroll') ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24">
                     <rect x="2" y="4" width="20" height="18" rx="2"/>
                    <path d="M2 10h20"/>
                </svg>
                <span class="sidebar-label">Payroll</span>
            </a>
            <a href="salary_reports.php"
               class="sidebar-link <?php echo ($current_page == 'salary_reports') ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24">
                     <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <span class="sidebar-label">Payroll Reports</span>
            </a>

        <?php else: ?>

            <a href="employee_dashboard.php"
               class="sidebar-link <?php echo ($current_page == 'employee_dashboard') ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                </svg>
                <span class="sidebar-label">Overview</span>
            </a>

            <a href="attendance.php"
               class="sidebar-link <?php echo ($current_page == 'attendance') ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <span class="sidebar-label">Attendance</span>
            </a>

            <a href="request_time_off.php"
               class="sidebar-link <?php echo ($current_page == 'request_time_off') ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24">
                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                </svg>
                <span class="sidebar-label">Request Time Off</span>
            </a>

        <?php endif; ?>

        <!-- Always visible -->
        <a href="settings.php"
           class="sidebar-link <?php echo ($current_page == 'settings') ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M12 2v2M12 20v2M20 12h2M2 12h2M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41"/>
            </svg>
            <span class="sidebar-label">Settings</span>
        </a>

        <a href="logout.php" class="sidebar-link">
            <svg viewBox="0 0 24 24">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span class="sidebar-label">Logout</span>
        </a>

    </nav>

</aside>