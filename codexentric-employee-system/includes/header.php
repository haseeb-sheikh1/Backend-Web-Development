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

    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/global.css">

    <?php if (isset($extra_css) && !empty($extra_css)): ?>
        <link rel="stylesheet" href="../styles/<?php echo htmlspecialchars($extra_css); ?>.css">
    <?php endif; ?>
    <link rel="stylesheet" href="../styles/notifications.css">

    <!-- Production Grade Calendar Architecture Assets -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <style>
        /* Flatpickr Overrides to enforce User Green Theme */
        .flatpickr-calendar {
            border-radius: 14px !important;
            box-shadow: 0 15px 40px rgba(0,0,0,0.12) !important;
            border: 1px solid rgba(0,0,0,0.05) !important;
            font-family: 'Nunito Sans', sans-serif !important;
            padding: 8px;
        }
        .flatpickr-day.selected, .flatpickr-day.selected:hover {
            background: #186D55 !important;
            border-color: #186D55 !important;
            color: #fff !important;
        }
        .flatpickr-months .flatpickr-month { background: #fff !important; color: #1e293b !important; }
        .flatpickr-current-month .flatpickr-monthDropdown-months { font-weight: 700 !important; }
        .flatpickr-weekday { color: #64748b !important; font-weight: 700 !important; font-size: 12px !important; }
        .flatpickr-day.today { border-color: #186D55 !important; color: #186D55 !important; }
        
        /* Month Plugin Override */
        .flatpickr-monthSelect-month.selected {
            background: #186D55 !important;
            border-color: #186D55 !important;
            color: #fff !important;
        }
    </style>

    <style>
    /* ═══════════════════════════════════════════════
       RESET & ROOT
    ═══════════════════════════════════════════════ */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --brand-green:  #186D55;
        --brand-green-dark: #11523F;
        --sidebar-w:    249px;
        --topbar-h:     66px; /* Updated to 66px (50px content + 8px top/bottom padding) */
        --sidebar-bg:   #0f1c2e;
        --sidebar-border: #17293f;
        --body-bg:      #f4f6f9;
        --text-primary: #111827;
        --font:         'Nunito Sans', sans-serif;
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
        z-index: 500;
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
        z-index: 400;
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
    height: 52px;
    background: linear-gradient(to bottom, #fcfdfe, #f8fafc); /* Subtle premium sheen from image */
    border-bottom: 1px solid #eef2f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    flex-shrink: 0;
}

.sub-header-title {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--brand-green); 
    background: rgba(24, 109, 85, 0.06); 
    padding: 6px 18px 6px 14px;
    border-radius: 30px; 
    letter-spacing: 0.2px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: inset 0 0 0 1px rgba(24, 109, 85, 0.08);
}
.sub-header-title::before {
    content: '';
    width: 6px;
    height: 6px;
    background: var(--brand-green);
    border-radius: 50%;
    opacity: 0.75;
    flex-shrink: 0;
}

.help-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #eff2f6;
    border: none;
    color: #64748b;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.2s ease;
}
.help-btn:hover {
    background: #e2e8f0;
    color: #475569;
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

    /* Dropdown CSS Redesign */
    .dropdown-menu {
        display: block;
        opacity: 0;
        transform: translateY(10px) scale(0.95);
        pointer-events: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.02);
        width: 215px;
        overflow: hidden;
        z-index: 300;
        transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .dropdown-menu.show {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: auto;
    }

    .dropdown-header {
        padding: 18px 18px 14px;
        background: linear-gradient(135deg, rgba(24, 109, 85, 0.04) 0%, rgba(24, 109, 85, 0.005) 100%);
        border-bottom: 1px solid #eef2f6;
    }
    .dropdown-header .user-name {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        line-height: 1.2;
    }
    .dropdown-header .user-role {
        display: inline-block;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--brand-green);
        background: rgba(24, 109, 85, 0.08);
        padding: 2.5px 8px;
        border-radius: 6px;
        margin-top: 6px;
        line-height: 1;
    }
    .dropdown-header .user-id {
        font-size: 11px;
        font-weight: 600;
        color: #94a3b8;
        margin-top: 6px;
        margin-bottom: 0;
        line-height: 1;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 18px;
        color: #475569;
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 600;
        transition: all 0.15s ease;
    }
    
    .dropdown-icon {
        width: 15px;
        height: 15px;
        stroke: currentColor;
        stroke-width: 2.2;
        flex-shrink: 0;
        transition: transform 0.15s ease;
    }

    .dropdown-item:hover {
        background: #f8fafc;
        color: var(--brand-green);
    }
    .dropdown-item:hover .dropdown-icon {
        transform: translateX(1.5px);
    }

    .dropdown-divider {
        height: 1px;
        background: #eef2f6;
    }
    
    .dropdown-item-danger {
        color: #ef4444;
    }
    .dropdown-item-danger:hover {
        background: #fef2f2;
        color: #dc2626;
    }

    /* ══════════════════════════════════
       MOBILE RESPONSIVE ARCHITECTURE
    ══════════════════════════════════ */
    @media (max-width: 900px) {
        .sidebar { 
            transform: translateX(-100%); 
            transition: transform .25s; 
        }
        .sidebar.open { 
            transform: translateX(0); 
        }
        .app-right { 
            margin-left: 0 !important; 
        }
        .sidebar.collapsed + .app-right { 
            margin-left: 0 !important; 
        }
        .main-content { 
            padding: 20px 16px; 
        }
        
        /* ── Premium Mobile Header Suite ── */
        .topbar {
            padding: 8px 16px !important;
            background: rgba(255, 255, 255, 0.88) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            position: relative !important;
            border-bottom: 1px solid rgba(226, 232, 240, 0.6) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02) !important;
        }
        
        /* Pure centered branding vector */
        .topbar-breadcrumb {
            position: absolute !important;
            left: 50% !important;
            top: 50% !important;
            transform: translate(-50%, -50%) !important;
            margin: 0 !important;
        }
        .topbar-breadcrumb svg {
            width: 18px !important;
            height: 18px !important;
            stroke: var(--brand-green) !important;
            opacity: 1 !important;
            transition: all 0.3s ease;
        }
        .topbar-breadcrumb span {
            display: none !important;
        }
        
        /* Isolated, shadow-lifted Avatar Halo */
        .topbar-username, .topbar-chevron {
            display: none !important;
        }
        .topbar-user, .topbar-user:hover {
            padding: 0 !important;
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            width: auto !important; height: auto !important;
        }
        .topbar-avatar {
            border: 2px solid #ffffff !important;
            box-shadow: 0 0 0 1.5px rgba(24, 109, 85, 0.15), 0 4px 10px rgba(0,0,0,0.05) !important;
            width: 36px !important;
            height: 36px !important;
            transition: transform 0.2s ease;
        }

        /* Unified control anchors */
        .mobile-toggle-btn {
            display: inline-flex !important;
            background: #f1f5f9 !important;
            border-radius: 10px !important;
            width: 36px !important;
            height: 36px !important;
            justify-content: center !important;
            margin-right: 0 !important;
        }
        
        /* ── Sub-Header Refinement ── */
        .sub-header-bar {
            padding: 10px 16px !important;
            height: auto !important;
            min-height: 48px !important;
        }
        .sub-header-title {
            font-size: 12px !important;
            padding: 5px 12px !important;
            white-space: normal !important;
        }
        
        /* ── Dropdown Production Hardening ── */
        .dropdown-menu {
            width: 225px !important;
            right: -4px !important;
            top: calc(100% + 10px) !important;
            border-radius: 18px !important;
            box-shadow: 0 15px 35px rgba(0,0,0,0.12), 0 2px 6px rgba(0,0,0,0.03) !important;
            border: 1px solid rgba(0,0,0,0.04) !important;
        }
        .dropdown-header {
            padding: 20px 20px 16px !important;
        }
        .dropdown-item {
            padding: 14px 20px !important;
            font-size: 14px !important;
            min-height: 46px !important; /* Enforce 44px+ Apple standard */
        }
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
    </style>
</head>
<body>

<div id="toast-container"></div>

<script>
    /**
     * Ultra-Premium Global Notification System (SaaS Grade)
     */
    window.showToast = function(message, type = 'success', title = '', duration = 5000) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        if (!title) {
            title = type.charAt(0).toUpperCase() + type.slice(1);
        }

        const icons = {
            success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
            error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
            warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
        };

        toast.innerHTML = `
            <div class="toast-icon">${icons[type] || icons.info}</div>
            <div class="toast-body">
                <span class="toast-title">${title}</span>
                <p class="toast-message">${message}</p>
            </div>
            <button class="toast-close" aria-label="Close notification">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <div class="toast-progress">
                <div class="toast-progress-bar" style="animation-duration: ${duration}ms"></div>
            </div>
        `;

        container.appendChild(toast);

        // Force reflow for spring animation
        toast.offsetHeight;
        toast.classList.add('show');

        let timeoutId;
        let startTime = Date.now();
        let remainingTime = duration;

        const dismiss = () => {
            toast.classList.remove('show');
            toast.classList.add('hide');
            setTimeout(() => toast.remove(), 300);
        };

        const startTimer = () => {
            timeoutId = setTimeout(dismiss, remainingTime);
            toast.querySelector('.toast-progress-bar').style.animationPlayState = 'running';
        };

        const pauseTimer = () => {
            clearTimeout(timeoutId);
            remainingTime -= (Date.now() - startTime);
            toast.querySelector('.toast-progress-bar').style.animationPlayState = 'paused';
        };

        startTimer();

        toast.addEventListener('mouseenter', () => {
            pauseTimer();
        });

        toast.addEventListener('mouseleave', () => {
            startTime = Date.now();
            startTimer();
        });

        toast.querySelector('.toast-close').addEventListener('click', dismiss);
    };
</script>

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
                <div class="topbar-breadcrumb" style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; font-weight: 500; color: #94a3b8; letter-spacing: 0.4px;">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.8;"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span style="color: #475569; font-weight: 700; text-transform: none;">CodeXentric</span>
                    <span style="color: #cbd5e1; font-weight: 300; font-size: 14px;">/</span>
                    <span style="opacity: 0.8;">Portal</span>
                </div>
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
                    <a href="../pages/settings.php" class="dropdown-item">
                        <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        Settings
                    </a>
                    <?php if ($is_admin): ?>
                    <a href="../pages/help.php" class="dropdown-item">
                        <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        Support
                    </a>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <a href="../pages/logout.php" class="dropdown-item dropdown-item-danger">
                        <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Logout
                    </a>
                </div>
            </div>
        </header>
        <!-- SUB-HEADER BAR - Global dynamic integration -->
        <div class="sub-header-bar">
            <h1 class="sub-header-title">
                <?php echo isset($title) ? htmlspecialchars($title) : 'System Overview'; ?>
            </h1>
            <?php if ($is_admin): ?>
            <a href="../pages/help.php" class="help-btn" title="System Help Center">?</a>
            <?php endif; ?>
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