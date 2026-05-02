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
   <?php 
// Place this at the top of your header.php file
$baseURL = 'http://' . $_SERVER['HTTP_HOST'] . '/codexentric-employee-system'; 
?>
    <style>
      <style>
  :root {
    --blue:          #1a6eff;
    --blue-dark:     #1252cc;
    --blue-light:    #1a6eff18;
    --topbar-bg:     #0f1c2e;
    --sidebar-bg:    #0f1c2e;
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
    background: #0a1525;
    height: 68px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 28px;
    position: sticky;
    top: 0;
    z-index: 100;
    border-bottom: 1px solid #1a2d45;
    box-shadow: 0 2px 24px rgba(0,0,0,0.4);
  }

  .topbar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
  }

  .topbar-logo {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #1a6eff, #0a4fcf);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 0 1px #1a6eff40;
  }

  .topbar-logo img {
    width: 22px;
    height: 22px;
    object-fit: contain;
    filter: brightness(0) invert(1);
  }

  .topbar-title {
    font-size: 17px;
    font-weight: 700;
    color: #e8f1fb;
    letter-spacing: 0.2px;
  }

  /* Vertical divider after brand */
  .topbar-brand::after {
    content: '';
    display: block;
    width: 1px;
    height: 24px;
    background: #1a2d45;
    margin-left: 16px;
  }

  .topbar-nav { display: flex; align-items: center; gap: 4px; }
  .topbar-nav a {
    color: rgba(255,255,255,0.55);
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 500;
    padding: 6px 14px;
    border-radius: 6px;
    transition: background 0.15s, color 0.15s;
  }
  .topbar-nav a:hover, .topbar-nav a.active {
    background: rgba(255,255,255,0.07);
    color: #fff;
  }

  .topbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
  }

  /* Vertical divider before user area */
  .topbar-right::before {
    content: '';
    display: block;
    width: 1px;
    height: 24px;
    background: #1a2d45;
    margin-right: 4px;
  }

  .topbar-user {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 6px 12px;
    border-radius: 10px;
    border: 1px solid transparent;
    transition: background 0.15s, border-color 0.15s;
  }
  .topbar-user:hover {
    background: rgba(255,255,255,0.06);
    border-color: #1a2d45;
  }

  .topbar-avatar {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #1a6eff30, #1a6eff55);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    color: #7dbfff;
    border: 1.5px solid #1a6eff45;
    overflow: hidden;
    flex-shrink: 0;
  }
  .topbar-avatar img {
    width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block;
  }

  .topbar-user-info { display: flex; flex-direction: column; line-height: 1.3; }
  .topbar-username  { font-size: 13.5px; font-weight: 600; color: #dce9f5; }
  .topbar-role-badge {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.9px;
    color: #4d9fff;
  }

  /* Chevron indicator */
  .topbar-user::after {
    content: '';
    display: block;
    width: 0;
    height: 0;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    border-top: 5px solid #4a6080;
    margin-left: 2px;
    flex-shrink: 0;
  }

  /* Dropdown */
  .dropdown-menu {
    display: none;
    position: absolute;
    top: 52px;
    right: 0;
    background: #0f1c2e;
    border: 1px solid #1a2d45;
    border-radius: 12px;
    box-shadow: 0 12px 32px rgba(0,0,0,0.5);
    width: 215px;
    overflow: hidden;
    z-index: 200;
  }
  .dropdown-menu.show { display: block; }

  .dropdown-header {
    padding: 14px 16px 12px;
    background: #0a1525;
    border-bottom: 1px solid #1a2d45;
  }
  .dropdown-header p { margin: 0; }
  .user-name  { font-size: 14px; font-weight: 600; color: #dce9f5; }
  .user-role  { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: #4d9fff; margin-top: 3px; }
  .user-id    { font-size: 11.5px; color: #4a6080; margin-top: 3px; }

  .dropdown-item {
    display: flex;
    align-items: center;
    padding: 10px 16px;
    color: #8bacc8;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: background 0.15s, color 0.15s;
  }
  .dropdown-item:hover { background: rgba(255,255,255,0.05); color: #e0eaf4; }

  .dropdown-divider { height: 1px; background: #1a2d45; }

  .dropdown-item-danger { color: #f87171; }
  .dropdown-item-danger:hover { background: rgba(248,113,113,0.08); color: #fca5a5; }

  /* ── LAYOUT ── */
  .app-body { display: flex; flex: 1; }
  .main-content { flex: 1; padding: 28px 32px; overflow-y: auto; }
  @media (max-width: 900px) { .main-content { padding: 20px 16px; } }
</style>
    </style>
</head>
<body>

    <header class="topbar" role="banner">
        <a href="../pages/administrator_dashboard.php" class="topbar-brand">
            <div class="topbar-logo">
                <img src="<?php echo $baseURL; ?>/assets/logo.png" alt="CodeXentric Logo">
            </div>
            <span class="topbar-title">CodeXentric</span>
        </a>

        <?php
     if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true):

            // Role: check role_id first, fall back to role
            $role_raw   = isset($_SESSION['role_id']) ? $_SESSION['role_id']
                        : (isset($_SESSION['role'])      ? $_SESSION['role'] : '');
            $is_admin   = (strtolower($role_raw) === '1');
            $role_label = $is_admin ? 'Admin' : 'Employee';

            // Display name: prefer user_name, fall back to role label
            $display_name = (!empty($_SESSION['first_name']))
                          ? htmlspecialchars($_SESSION['first_name'])
                          : $role_label;

            // Avatar initials
            $initials = 'U';
            if (!empty($_SESSION['first_name']) && !empty($_SESSION['last_name'])) {
                $f_initial = substr($_SESSION['first_name'], 0, 1);
                $l_initial = substr($_SESSION['last_name'], 0, 1);
                $initials = strtoupper($f_initial . $l_initial); 
            } elseif (!empty($_SESSION['first_name'])) {
                $parts    = explode(' ', trim($_SESSION['first_name']));
                $initials = strtoupper(substr($parts[0], 0, 1));
                if (count($parts) > 1) $initials .= strtoupper(substr($parts[1], 0, 1));
            }
        ?>

        <div class="topbar-right">
            <div class="topbar-user" id="userMenuToggle" aria-expanded="false">
                <div class="topbar-avatar">
    <?php if (!empty($_SESSION['profile_image'])): ?>
        <img 
            src="../assets/uploads/<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" 
            alt="<?php echo $display_name; ?>"
            onerror="this.style.display='none'; this.parentElement.textContent='<?php echo $initials; ?>';"
        >
    <?php else: ?>
        <?php echo $initials; ?>
    <?php endif; ?>
</div>
                <div class="topbar-user-info">
                    <span class="topbar-username"><?php echo $display_name; ?></span>
                    <span class="topbar-role-badge"><?php echo $role_label; ?></span>
                </div>
            </div>

            <div class="dropdown-menu" id="userDropdown" aria-hidden="true">
                <div class="dropdown-header">
                    <p class="user-name"><?php echo $display_name; ?></p>
                    <p class="user-role"><?php echo $role_label; ?></p>
                    <?php if (!empty($_SESSION['email'])): ?>
                        <p class="user-id"><?php  echo "CEMS-". htmlspecialchars($_SESSION['user_id']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="dropdown-divider"></div>
                <a href="../pages/settings.php" class="dropdown-item">Settings</a>
        
                <a href="../pages/update_profile.php" class="dropdown-item">Profile</a>
                <div class="dropdown-divider"></div>
                <a href="../pages/logout.php" class="dropdown-item dropdown-item-danger">Logout</a>
            </div>
        </div>

        <?php endif; ?>
    </header>

    <?php if (isset($_SESSION['role_id'])): ?>
        <div class="app-body">
            <?php include_once "sidebar.php"; ?>
            <main class="main-content" role="main">
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuToggle = document.getElementById('userMenuToggle');
            const userDropdown   = document.getElementById('userDropdown');
            
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