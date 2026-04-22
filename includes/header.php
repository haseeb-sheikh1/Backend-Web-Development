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
    <link rel="stylesheet" href="../includes/styles/global.css">

    <?php if (isset($extra_css) && !empty($extra_css)): ?>
        <link rel="stylesheet" href="../includes/styles/<?php echo htmlspecialchars($extra_css); ?>.css">
    <?php endif; ?>

    <style>
      /* Layout & Topbar Variables */
      :root {
        --blue:          #1E6FD9;
        --blue-dark:     #1559B5;
        --blue-light:    #EBF2FC;
        --topbar-bg:     #1559B5;
        --sidebar-bg:    #FFFFFF;
        --body-bg:       #F0F4FA;
        --text-primary:  #111827;
        --text-secondary:#4B5563;
        --text-muted:    #9CA3AF;
        --border:        #E2E8F0;
        --font:          'Source Sans 3', sans-serif;
      }

      body {
        font-family: var(--font);
        background: var(--body-bg);
        color: var(--text-primary);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        margin: 0;
      }

      /* ── TOP BAR ── */
      .topbar {
        background: var(--topbar-bg);
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 24px;
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: 0 2px 8px rgba(21,89,181,0.28);
      }
      .topbar-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
      
      /* Updated Logo Styles */
      .topbar-logo { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; }
      .topbar-logo img { width: 100%; height: 100%; object-fit: contain; } /* Ensures image doesn't stretch */
      
      .topbar-title { font-size: 17px; font-weight: 700; color: #fff; letter-spacing: 0.3px; }
      .topbar-nav { display: flex; align-items: center; gap: 4px; }
      .topbar-nav a { color: rgba(255,255,255,0.85); text-decoration: none; font-size: 13.5px; font-weight: 500; padding: 6px 14px; border-radius: 5px; transition: background 0.15s; }
      .topbar-nav a:hover, .topbar-nav a.active { background: rgba(255,255,255,0.15); color: #fff; }
      
      .topbar-right { display: flex; align-items: center; gap: 14px; position: relative; }
      .topbar-user  { display: flex; align-items: center; gap: 8px; cursor: pointer; }
      .topbar-avatar { width: 32px; height: 32px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; color: #fff; border: 1.5px solid rgba(255,255,255,0.45); }
      .topbar-username { font-size: 13.5px; font-weight: 600; color: #fff; }

      /* User Dropdown Bridge Styles */
      .dropdown-menu { display: none; position: absolute; top: 45px; right: 0; background: #fff; border: 1px solid var(--border); border-radius: 8px; box-shadow: 0 4px 14px rgba(0,0,0,0.1); width: 200px; overflow: hidden; z-index: 200; }
      .dropdown-menu.show { display: block; }
      .dropdown-header { padding: 12px 16px; background: #f8f9fa; border-bottom: 1px solid var(--border); }
      .dropdown-header p { margin: 0; }
      .user-name { font-size: 14px; font-weight: 600; color: var(--text-primary); }
      .user-email { font-size: 12px; color: var(--text-muted); }
      .dropdown-item { display: block; padding: 10px 16px; color: var(--text-secondary); text-decoration: none; font-size: 13.5px; transition: background 0.15s; }
      .dropdown-item:hover { background: var(--blue-light); color: var(--blue); }
      .dropdown-divider { height: 1px; background: var(--border); }
      .dropdown-item-danger { color: #E53E3E; }
      .dropdown-item-danger:hover { background: #FFF5F5; color: #E53E3E; }

      /* ── LAYOUT ── */
      .app-body { display: flex; flex: 1; }
      .main-content { flex: 1; padding: 28px 32px; overflow-y: auto; }
      @media (max-width: 900px) { .main-content { padding: 20px 16px; } }
    </style>
</head>
<body>

    <header class="topbar" role="banner">
        <a href="../pages/administrator_dashboard.php" class="topbar-brand">
            <div class="topbar-logo">
                <img src="../assets/logo.png" alt="CodeXentric Logo">
            </div>
            <span class="topbar-title">CodeXentric</span>
        </a>

        <?php if (!isset($is_login_page) || !$is_login_page): ?>
            <nav class="topbar-nav" role="navigation">
                <a href="administrator_dashboard.php">Dashboard</a>
                <a href="manage_employee.php" class="active">Employees</a>
                <a href="payroll.php">Payroll</a>
            </nav>

            <div class="topbar-right">
             <div class="topbar-user" id="userMenuToggle" aria-expanded="false">
    <div class="topbar-avatar">
        <?php echo isset($_SESSION['user_initials']) ? htmlspecialchars(substr($_SESSION['user_initials'], 0, 2)) : 'U'; ?>
    </div>
    <span class="topbar-username">
        <?php 
            // If the user's name is in the session, print it. 
            // Otherwise, print their role (Admin or Employee) capitalized.
            if (isset($_SESSION['user_name']) && !empty($_SESSION['user_name'])) {
                echo htmlspecialchars($_SESSION['user_name']);
            } else {
                echo isset($_SESSION['user_role']) ? ucfirst(htmlspecialchars($_SESSION['user_role'])) : 'Employee';
            }
        ?>
    </span>
</div>
                
                <div class="dropdown-menu" id="userDropdown" aria-hidden="true">
                    <div class="dropdown-header">
                        <p class="user-name"><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User'; ?></p>
                        <p class="user-email"><?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?></p>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="../pages/settings.php" class="dropdown-item">Settings</a>
                    <a href="../pages/update_profile.php" class="dropdown-item">Profile</a>
                    <div class="dropdown-divider"></div>
                    <a href="login.php" class="dropdown-item dropdown-item-danger">Logout</a>
                </div>
            </div>
        <?php endif; ?>
    </header>

    <?php if (!isset($is_login_page) || !$is_login_page): ?>
        <div class="app-body">
         <?php include_once "sidebar.php"; ?>   
            <main class="main-content" role="main">
    <?php endif; ?>

    <script>
        // Restored Dropdown JS logic
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuToggle = document.getElementById('userMenuToggle');
            const userDropdown = document.getElementById('userDropdown');
            
            if (userMenuToggle && userDropdown) {
                userMenuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isExpanded = userMenuToggle.getAttribute('aria-expanded') === 'true';
                    userMenuToggle.setAttribute('aria-expanded', !isExpanded);
                    userDropdown.setAttribute('aria-hidden', isExpanded);
                    userDropdown.classList.toggle('show');
                });

                document.addEventListener('click', function(e) {
                    if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
                        userMenuToggle.setAttribute('aria-expanded', 'false');
                        userDropdown.setAttribute('aria-hidden', 'true');
                        userDropdown.classList.remove('show');
                    }
                });
            }
        });
    </script>