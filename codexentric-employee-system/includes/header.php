<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : "CodeXentric HRM"; ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/global.css">

    <?php if (isset($extra_css) && !empty($extra_css)): ?>
        <link rel="stylesheet" href="../styles/<?php echo htmlspecialchars($extra_css); ?>.css">
    <?php endif; ?>

    <style>
    /* ═══════════════════════════════════════════════
       RESET & ROOT
    ═══════════════════════════════════════════════ */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --brand-green:  #186D55;
        --brand-green-dark: #11523F;
        --sidebar-w:    240px;
        --topbar-h:     66px; /* Updated to 66px (50px content + 8px top/bottom padding) */
        --sidebar-bg:   #0f1c2e;
        --sidebar-border: #17293f;
        --body-bg:      #f0f4fa;
        --text-primary: #111827;
        --font:         'Source Sans 3', sans-serif;
    }

    html, body {
        height: 100%;
        font-family: var(--font);
        background: var(--body-bg);
        color: var(--text-primary);
    }

    /* ═══════════════════════════════════════════════
       APP SHELL  — sidebar left, right column stacks topbar + content
    ═══════════════════════════════════════════════ */
    .app-shell {
        display: flex;
        height: 100vh;
        overflow: hidden;
    }

    /* ── Sidebar ── */
    .sidebar {
        width: var(--sidebar-w);
        flex-shrink: 0;
        background: var(--sidebar-bg);
        display: flex;
        flex-direction: column;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 200;
        border-right: 1px solid var(--sidebar-border);
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* Sidebar scrollbar */
    .sidebar::-webkit-scrollbar { width: 4px; }
    .sidebar::-webkit-scrollbar-track { background: transparent; }
    .sidebar::-webkit-scrollbar-thumb { background: #1e3450; border-radius: 4px; }

    /* ── Right column ── */
    .app-right {
        margin-left: var(--sidebar-w);
        flex: 1;
        display: flex;
        flex-direction: column;
        height: 100vh;
        overflow: hidden;
        min-width: 0;
        transition: margin-left 0.25s cubic-bezier(.4,0,.2,1);
    }

    /* ── Adjust for Collapsed Sidebar sibling ── */
    .sidebar.collapsed + .app-right {
        margin-left: 64px;
    }

    /* ── Topbar ── */
    .topbar {
        height: var(--topbar-h);
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 20px;
        flex-shrink: 0;
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .topbar-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Page breadcrumb / title area */
    .topbar-page-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
    }

    /* ── Main content area ── */
    .main-content {
        flex: 1;
        overflow-y: auto;
        padding: 20px 32px;
    }
    .main-content::-webkit-scrollbar { width: 6px; }
    .main-content::-webkit-scrollbar-track { background: transparent; }
    .main-content::-webkit-scrollbar-thumb { background: #d1dae8; border-radius: 4px; }

    /* ══════════════════════════════════
       SIDEBAR BRAND (inside sidebar)
    ══════════════════════════════════ */
    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 0 20px;
        height: var(--topbar-h); /* This correctly inherits the 66px so borders line up */
        flex-shrink: 0;
        border-bottom: 1px solid var(--sidebar-border);
        text-decoration: none;
    }
    .sidebar-brand-logo {
        width: 34px; height: 34px;
        background: linear-gradient(135deg, var(--brand-green), var(--brand-green-dark));
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 0 0 1px rgba(24, 109, 85, 0.3);
    }
    .sidebar-brand-logo img {
        width: 20px; height: 20px;
        object-fit: contain;
        filter: brightness(0) invert(1);
    }
    .sidebar-brand-name {
        font-size: 16px;
        font-weight: 700;
        color: #e8f1fb;
        letter-spacing: .2px;
    }
    .sidebar-brand-sub {
        font-size: 10px;
        font-weight: 600;
        color: #4a6080;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: block;
        margin-top: 1px;
    }

    /* ══════════════════════════════════
       SIDEBAR NAV
    ══════════════════════════════════ */
    .sidebar-nav {
        flex: 1;
        padding: 12px 0;
    }

    .sidebar-section-label {
        font-size: 9.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #36506e;
        padding: 16px 22px 6px;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 9px 14px 9px 18px;
        margin: 1px 10px;
        font-size: 13.5px;
        font-weight: 500;
        color: #7a9ab8;
        text-decoration: none;
        border-radius: 7px;
        transition: background .15s, color .15s;
        position: relative;
        white-space: nowrap;
    }
    .sidebar-link:hover {
        background: rgba(255,255,255,.06);
        color: #dce9f5;
    }
    .sidebar-link.active {
        background: rgba(24, 109, 85, 0.15);
        color: #41A88A;
        font-weight: 600;
    }
    .sidebar-link.active::before {
        content: '';
        position: absolute;
        left: -2px; top: 18%; height: 64%;
        width: 3px;
        background: var(--brand-green);
        border-radius: 0 3px 3px 0;
    }
    /* ══════════════════════════════════
   SUB-HEADER BAR
══════════════════════════════════ */
/* ══════════════════════════════════
   SUB-HEADER BAR
══════════════════════════════════ */
.sub-header-bar {
    height: 54px;
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 0 20px;
    flex-shrink: 0;
}

.help-btn {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    color: #64748b;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    line-height: 1;
    font-family: var(--font);
}
.help-btn:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #374151;
}
.help-btn:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #374151;
}
    .sidebar-link svg {
        width: 16px; height: 16px;
        flex-shrink: 0;
        opacity: .5;
        transition: opacity .15s;
    }
    .sidebar-link:hover svg,
    .sidebar-link.active svg { opacity: 1; }

    .sidebar-divider {
        height: 1px;
        background: var(--sidebar-border);
        margin: 8px 18px;
    }

    /* ══════════════════════════════════
       TOPBAR RIGHT — user menu
    ══════════════════════════════════ */
    .topbar-right {
        display: flex;
        align-items: center;
        gap: 8px;
        position: relative;
    }

    .topbar-user {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        padding: 4px 16px 4px 4px;
        border-radius: 50px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        transition: all .2s ease;
        user-select: none;
    }
    .topbar-user:hover {
        background: #f1f5f9;
        border-color: #e2e8f0;
    }

    .topbar-avatar {
        width: 38px; height: 38px;
        background: #ffffff;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700; color: var(--brand-green);
        border: 1px solid #e2e8f0;
        background-clip: padding-box;
        flex-shrink: 0;
    }
    .topbar-avatar img {
        width: 100%; height: 100%; object-fit: cover; border-radius: 50%;
    }

    .topbar-username { 
        font-size: 14px; 
        font-weight: 600; 
        color: var(--text-primary); 
        margin-left: 2px;
    }

    .topbar-chevron {
        width: 16px; height: 16px; color: #64748b;
        transition: transform .2s;
        flex-shrink: 0;
        margin-left: 2px;
    }
    .topbar-user[aria-expanded="true"] .topbar-chevron { transform: rotate(180deg); }

    /* Dropdown */
    .dropdown-menu {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        background: #fff;
        border: 1px solid #e5eaf2;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        width: 210px;
        overflow: hidden;
        z-index: 300;
    }
    .dropdown-menu.show { display: block; }

    .dropdown-header {
        padding: 14px 16px 12px;
        background: #f8fafc;
        border-bottom: 1px solid #eef1f6;
    }
    .dropdown-header .user-name  { font-size: 14px; font-weight: 600; color: #111827; }
    .dropdown-header .user-role  { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; color: var(--brand-green); margin-top: 3px; }
    .dropdown-header .user-id    { font-size: 11.5px; color: #9ca3af; margin-top: 3px; }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 10px 16px;
        color: #374151;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: background .12s;
    }
    .dropdown-item:hover { background: #f5f7fb; }
    .dropdown-divider { height: 1px; background: #eef1f6; }
    .dropdown-item-danger { color: #dc2626; }
    .dropdown-item-danger:hover { background: #fef2f2; color: #b91c1c; }

    /* ══════════════════════════════════
       MOBILE  
    ══════════════════════════════════ */
    @media (max-width: 900px) {
        .sidebar { transform: translateX(-100%); transition: transform .25s; }
        .sidebar.open { transform: translateX(0); }
        .app-right { margin-left: 0 !important; }
        .sidebar.collapsed + .app-right { margin-left: 0 !important; }
        .main-content { padding: 20px 16px; }
    }

    /* ══════════════════════════════════
       PRINT
    ══════════════════════════════════ */
    @media print {
        .sidebar, .topbar { display: none !important; }
        .app-right { margin-left: 0; }
        .main-content { padding: 0; overflow: visible; }
        .app-shell { height: auto; overflow: visible; }
    }
    .mobile-toggle-btn {
        display: none;
        background: none;
        border: none;
        cursor: pointer;
        color: #475569;
        padding: 4px;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: background 0.2s;
        margin-right: 8px;
    }
    .mobile-toggle-btn:hover {
        background: #e2e8f0;
    }
    @media (max-width: 900px) {
        .mobile-toggle-btn {
            display: inline-flex;
        }
    }
    </style>
</head>
<body>

<div class="app-shell">

<?php if (isset($_SESSION['role_id'])): ?>

    <!-- ══════════ SIDEBAR ══════════ -->
    <?php include_once "sidebar.php"; ?>

    <!-- ══════════ RIGHT COLUMN ══════════ -->
    <div class="app-right">

        <!-- TOPBAR -->
        <?php
            $role_raw     = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : (isset($_SESSION['role']) ? $_SESSION['role'] : '');
            $is_admin     = (strtolower($role_raw) === '1');
            $role_label   = $is_admin ? 'Admin' : 'Employee';
            $display_name = (!empty($_SESSION['first_name'])) ? htmlspecialchars($_SESSION['first_name']) : $role_label;

            $initials = 'U';
            if (!empty($_SESSION['first_name']) && !empty($_SESSION['last_name'])) {
                $initials = strtoupper(substr($_SESSION['first_name'],0,1) . substr($_SESSION['last_name'],0,1));
            } elseif (!empty($_SESSION['first_name'])) {
                $parts = explode(' ', trim($_SESSION['first_name']));
                $initials = strtoupper(substr($parts[0],0,1));
                if (count($parts) > 1) $initials .= strtoupper(substr($parts[1],0,1));
            }
        ?>

        <header class="topbar">
            <div class="topbar-left">
                <!-- Mobile hamburger -->
                <button id="mobileSidebarToggle" class="mobile-toggle-btn" aria-label="Toggle sidebar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <span class="topbar-page-title">
                    <?php echo isset($title) ? htmlspecialchars($title) : 'Dashboard'; ?>
                </span>
            </div>

            <div class="topbar-right">
                
                <!-- Pill-Shaped User Profile -->
                <div class="topbar-user" id="userMenuToggle" aria-expanded="false">
                    <div class="topbar-avatar">
                        <?php if (!empty($_SESSION['profile_image'])): ?>
                            <img src="../assets/uploads/<?php echo htmlspecialchars($_SESSION['profile_image']); ?>"
                                 alt="<?php echo $display_name; ?>"
                                 onerror="this.style.display='none'; this.parentElement.textContent='<?php echo $initials; ?>';">
                        <?php else: ?>
                            <?php echo $initials; ?>
                        <?php endif; ?>
                    </div>
                    <span class="topbar-username"><?php echo $display_name; ?></span>
                    <svg class="topbar-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </div>

                <div class="dropdown-menu" id="userDropdown">
                    <div class="dropdown-header">
                        <p class="user-name"><?php echo $display_name; ?></p>
                        <p class="user-role"><?php echo $role_label; ?></p>
                        <?php if (!empty($_SESSION['user_id'])): ?>
                            <p class="user-id">CEMS-<?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
                        <?php endif; ?>
                    </div>
                    <a href="../pages/settings.php" class="dropdown-item">Settings</a>
                    <a href="../pages/update_profile.php" class="dropdown-item">Profile</a>
                    <div class="dropdown-divider"></div>
                    <a href="../pages/logout.php" class="dropdown-item dropdown-item-danger">Logout</a>
                </div>
            </div>
        </header>
        <!-- SUB-HEADER BAR -->
<div class="sub-header-bar">
    <button class="help-btn" title="Help" onclick="alert('Help coming soon!')">?</button>
</div>

        <!-- MAIN CONTENT -->
        <main class="main-content" role="main">

<?php else: ?>

    <!-- Not logged in — render page normally without shell -->
    <div style="width:100%; height: 100vh; overflow: hidden;">
        <main class="main-content" role="main" <?php echo (isset($is_login_page) && $is_login_page) ? 'style="padding:0; margin:0; height:100vh;"' : ''; ?>>

<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle   = document.getElementById('userMenuToggle');
        const dropdown = document.getElementById('userDropdown');
        if (toggle && dropdown) {
            toggle.addEventListener('click', function (e) {
                e.stopPropagation();
                const open = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', String(!open));
                dropdown.classList.toggle('show', !open);
            });
            document.addEventListener('click', function () {
                toggle.setAttribute('aria-expanded', 'false');
                dropdown.classList.remove('show');
            });
        }

        // Mobile sidebar toggling
        const mobileToggle = document.getElementById('mobileSidebarToggle');
        const sidebar = document.getElementById('appSidebar');
        if (mobileToggle && sidebar) {
            mobileToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                sidebar.classList.toggle('open');
            });
            document.addEventListener('click', function(e) {
                if (!sidebar.contains(e.target) && e.target !== mobileToggle) {
                    sidebar.classList.remove('open');
                }
            });
        }
    });
</script>