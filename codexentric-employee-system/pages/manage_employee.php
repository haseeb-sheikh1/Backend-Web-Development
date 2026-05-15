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
    require_once '../pages/Database.php';
    require_once '../pages/Employee.php';
    $db = new Database();
    $connection = $db->getConnection();

    // ── Handle AJAX permission toggle ──
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_permission'])) {
        ob_clean(); // Ensure no output buffering issues
        header('Content-Type: application/json');
        $perm_id = (int)$_POST['permission_id'];
        $action  = $_POST['action']; // 'grant' or 'revoke'
        $target_user = (int)$_GET['id'];

        if ($action === 'grant') {
            $ins = $connection->prepare("INSERT IGNORE INTO user_permissions (user_id, permission_id) VALUES (?, ?)");
            $ins->bind_param("ii", $target_user, $perm_id);
            $ins->execute();
            $ins->close();
        } else {
            $del = $connection->prepare("DELETE FROM user_permissions WHERE user_id = ? AND permission_id = ?");
            $del->bind_param("ii", $target_user, $perm_id);
            $del->execute();
            $del->close();
        }
        echo json_encode(['success' => true, 'action' => $action, 'permission_id' => $perm_id]);
        exit;
    }

    $current_page = "manage_employees";
    $extra_css    = "manage_employee";
    $title        = "Employee Profile Editor";
    include_once "../includes/header.php";
    
    $employeeObj = new Employee($connection);

    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $user_id = $_GET['id'];
 
        $employee = $employeeObj->getEmployeeDetailsById($user_id);
        if (!$employee) {
            echo "<p style='color:red; text-align:center; margin-top:40px;'>Employee not found.</p>";
            include_once "../includes/footer.php";
            exit;
        }
    } else {
        echo "<p style='color:red; text-align:center; margin-top:40px;'>No employee ID provided.</p>";
        include_once "../includes/footer.php";
        exit;
    }

    // ── Fetch all permissions and this user's assigned permissions ──
    $all_permissions = [];
    $perm_res = $connection->query("SELECT id, name, description FROM permissions ORDER BY id");
    if ($perm_res) {
        while ($p = $perm_res->fetch_assoc()) {
            $all_permissions[] = $p;
        }
    }

    $user_perms = [];
    $up_stmt = $connection->prepare("SELECT permission_id FROM user_permissions WHERE user_id = ?");
    $up_stmt->bind_param("i", $user_id);
    $up_stmt->execute();
    $up_res = $up_stmt->get_result();
    while ($up = $up_res->fetch_assoc()) {
        $user_perms[] = (int)$up['permission_id'];
    }
    $up_stmt->close();

    // AJAX handler moved above header inclusion.
    if (isset($_POST['deactivate'])) {
        $employeeId = $_GET['id'];
        if ($employeeObj->deleteEmployee($employeeId)) {
            $_SESSION['success_msg'] = "Employee profile has been successfully deleted.";
            header("Location: employees_list.php");
            exit();
        } else {
            $error = "Failed to delete employee profile.";
        }
    }

    include_once "../includes/header.php";
?>

<style>
/* Relying on Source Sans 3 from the global header */

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



/* ── Breadcrumb Pills ── */
.emp-breadcrumb {
  display: flex; 
  align-items: center; 
  gap: 8px; 
  margin-bottom: 24px;
}
.emp-breadcrumb a {
  background: #f1f5f9;
  color: #64748b;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s ease;
}
.emp-breadcrumb a:hover {
  background: #e2e8f0;
  color: #334155;
}
.emp-breadcrumb span {
  background: #e8f3f0;
  color: #186D55;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
}

.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px;
  font-family: var(--font-body);
}

.nav-scroll-arrow {
    display: none;
}

/* ── Settings Theme Layout ── */
.profile-container {
    display: flex;
    align-items: flex-start;
    gap: 30px;
    padding: 0;
    max-width: 1200px;
    margin: 0 auto;
}

.profile-sidebar {
    width: 280px;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 24px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    align-self: flex-start;
    position: sticky;
    top: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}

.profile-avatar-wrapper {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: #186D55;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: 700;
    border: 1px solid #e2e8f0;
    margin-bottom: 12px;
}

.profile-sidebar h2 {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    text-align: center;
    margin: 0 0 5px 0;
}

.profile-sidebar p {
    font-size: 11px;
    color: #64748b;
    margin-bottom: 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
}

.profile-nav {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.profile-nav-link {
    display: block;
    padding: 12px 16px;
    border-radius: 8px;
    color: #475569;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.profile-nav-link:hover {
    background: #f8fafc;
    color: var(--brand-green);
}

.profile-nav-link.active {
    background: #f1f5f9;
    color: #1e293b;
}

.profile-main {
    flex: 1;
}

.section-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    margin-bottom: 16px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    scroll-margin-top: 20px;
}

.section-header {
    padding: 14px 20px;
    border-bottom: 1px solid #f1f5f9;
}

.section-header h3 {
    font-size: 14px;
    font-weight: 700;
    color: #334155;
    margin: 0;
}

.section-body {
    padding: 16px 20px;
}

/* ── Modern Grid & Forms ── */
.modern-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.form-field {
    margin-bottom: 12px;
}

.form-field.full {
    grid-column: span 2;
}

.form-field label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 4px;
}

.modern-input {
    width: 100%;
    height: 38px;
    padding: 0 20px;
    border: 1px solid #e2e8f0;
    border-radius: 20px; /* Sleek rounded pill profile */
    font-size: 13.5px;
    color: #1e293b;
    background: #f8fafc;
    transition: all 0.2s;
    font-family: inherit;
    cursor: not-allowed;
    border-style: dashed;
}

.btn-hero {
  height: 40px;
  padding: 0 24px; /* Wider padding for capsules */
  border-radius: 25px; /* Rounded pills matching reference */
  font-size: 13px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-hero-primary {
  background: var(--brand-green); /* Switching to uniform brand green */
  color: #fff;
  border: none;
  box-shadow: 0 4px 12px rgba(24, 109, 85, 0.15);
}

.btn-hero-primary:hover {
  background: #125542;
  transform: translateY(-1px);
}

.btn-hero-warn {
  background: #fff;
  color: #ef4444;
  border: 1px solid #fee2e2;
  border-radius: 25px; /* Also rounding the warning pill */
}

.btn-hero-warn:hover {
  background: #fef2f2;
  border-color: #fca5a5;
}

.mono {
  font-family: 'Courier New', monospace;
  font-size: 13px; background: var(--surface);
  padding: 3px 8px; border-radius: 6px;
  border: 1px solid var(--border); color: var(--text-b);
  letter-spacing: 0.3px;
}

/* ── Timeline ── */
.emp-timeline { padding: 16px 22px; display: flex; flex-direction: column; gap: 0; }
.tl-item {
  display: flex; gap: 14px; align-items: flex-start;
  padding-bottom: 20px; position: relative;
}
.tl-item::before {
  content: ''; position: absolute;
  left: 11px; top: 26px; bottom: 0; width: 2px;
  background: var(--border);
}
.tl-item:last-child { padding-bottom: 0; }
.tl-item:last-child::before { display: none; }
.tl-dot {
  width: 24px; height: 24px; border-radius: 50%; flex-shrink: 0;
  background: #f0fdf4; border: 2px solid var(--brand-green);
  display: flex; align-items: center; justify-content: center;
  color: var(--brand-green); z-index: 1;
}
.tl-content { flex: 1; }
.tl-title { font-size: 14px; font-weight: 600; color: var(--text-h); margin-bottom: 2px; }
.tl-date  { font-size: 12.5px; color: var(--text-s); margin-bottom: 4px; }
.tl-desc  { font-size: 13px; color: var(--text-m); }

/* ── Quick Actions ── */
.emp-actions-list { padding: 10px 14px; display: flex; flex-direction: column; gap: 8px; }
.emp-qa {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 16px;
  border-radius: 10px;
  background: #fff;
  border: 1px solid var(--border);
  text-decoration: none;
  transition: all 0.2s ease;
  cursor: pointer;
}
.emp-qa:hover { 
  border-color: var(--brand-orange); 
  background: #fffbf8; 
  box-shadow: 0 4px 12px rgba(245, 130, 31, 0.08); 
}
.emp-qa-icon {
  width: 36px; height: 36px; border-radius: 9px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
}
.emp-qa-icon.payslip { background: #fdf2f2; color: #ef4444; }
.emp-qa-icon.attend  { background: #f0fdf4; color: var(--brand-green); }
.emp-qa-icon.review  { background: #fffbeb; color: #d97706; }
.emp-qa-text-wrap { flex: 1; }
.emp-qa-label { font-size: 14px; font-weight: 700; color: var(--text-main); }
.emp-qa-desc  { font-size: 12.5px; color: var(--text-muted); margin-top: 1px; }
.emp-qa-arr { color: #cbd5e1; transition: all 0.2s ease; }
.emp-qa:hover .emp-qa-arr { transform: translateX(3px); color: var(--brand-orange); }

/* Animations */
@keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
.emp-hero  { animation: fadeUp .3s ease both; }
.emp-stats { animation: fadeUp .3s .06s ease both; }
.emp-card  { animation: fadeUp .32s .12s ease both; }
/* ── Stats Row ── */
.emp-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 16px;
}

.emp-stat {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 12px 20px;
  display: flex;
  flex-direction: column;
  gap: 2px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}

.emp-stat-label {
  font-size: 13px;
  font-weight: 700;
  color: var(--text-muted);
}

.emp-stat-value {
  font-size: 20px;
  font-weight: 800;
  color: var(--text-main);
  letter-spacing: -0.3px;
}

.emp-stat-value.green {
  color: var(--brand-green);
}

.emp-stat-sub {
  font-size: 12px;
  color: #94a3b8;
  font-weight: 500;
}
/* ── Responsiveness ── */
@media (max-width: 768px) {
    .dashboard-container {
        padding: 0 12px 24px 12px !important;
    }
    .profile-container {
        flex-direction: column !important;
        gap: 20px !important;
    }
    .profile-sidebar {
        display: grid !important;
        grid-template-columns: auto 1fr !important;
        align-items: center !important;
        gap: 20px !important;
        width: 100% !important;
        position: relative !important;
        top: 0 !important;
        padding: 20px !important;
        box-sizing: border-box !important;
    }
    .profile-avatar-wrapper {
        width: 80px !important;
        height: 80px !important;
        margin-bottom: 0 !important;
    }
    .profile-meta-info {
        display: flex !important;
        flex-direction: column !important;
        text-align: left !important;
    }
    .profile-sidebar h2 {
        text-align: left !important;
        margin: 0 !important;
        font-size: 18px !important;
    }
    .profile-sidebar p {
        text-align: left !important;
        margin: 4px 0 0 0 !important;
    }
    
    /* Convert to Overflow Scrolling Ribbon with Arrow Logic */
    .profile-nav-wrapper {
        grid-column: span 2 !important;
        position: relative !important;
        margin-top: 15px !important;
        border-top: 1px solid #f1f5f9 !important;
        padding: 8px 0 !important;
    }
    .profile-nav {
        display: flex !important;
        flex-direction: row !important;
        gap: 10px !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
        scrollbar-width: none !important;
        width: 100% !important;
    }
    .profile-nav::-webkit-scrollbar {
        display: none;
    }
    
    .nav-scroll-arrow {
        display: flex !important;
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 100%;
        width: 55px;
        border: none;
        background: linear-gradient(to left, rgba(255,255,255,1) 40%, rgba(255,255,255,0));
        align-items: center;
        justify-content: flex-end;
        padding-right: 6px;
        z-index: 10;
        cursor: pointer;
    }
    .nav-scroll-arrow svg {
        background: var(--brand-green);
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        padding: 4px;
        box-shadow: 0 4px 10px rgba(24,109,85,0.25);
    }

    .profile-nav-link {
        white-space: nowrap !important;
        flex-shrink: 0 !important;
        padding: 8px 16px !important;
        border-radius: 20px !important;
        background: #f1f5f9 !important;
        color: #475569 !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        border: none !important;
        transition: all 0.2s;
        text-align: left !important;
    }
    .profile-nav-link.active {
        background: var(--brand-green) !important;
        color: #ffffff !important;
    }
    
    /* Actions block force column span */
    .profile-sidebar-actions {
        grid-column: span 2 !important;
    }
    
    .modern-grid {
        grid-template-columns: 1fr !important;
        gap: 14px !important;
    }
    .form-field.full {
        grid-column: span 1 !important;
    }
    
    /* Split stat metrics into highly readable single items on tight devices */
    .emp-stats {
        grid-template-columns: 1fr !important;
        gap: 12px !important;
    }
    
    .section-header {
        padding: 14px 16px !important;
    }
    .section-body {
        padding: 16px 16px !important;
    }
    
    .emp-breadcrumb {
        flex-wrap: wrap !important;
        gap: 6px !important;
        margin-bottom: 16px !important;
    }
    .emp-breadcrumb a, .emp-breadcrumb span {
        padding: 5px 12px !important;
        font-size: 12px !important;
    }
}
</style>

<div class="dashboard-container">

  <!-- ── Breadcrumb ── -->
  <nav class="emp-breadcrumb" aria-label="Breadcrumb">
    <a href="administrator_dashboard.php">Dashboard</a>
    <a href="employees_list.php">Employees</a>
    <span><?php echo htmlspecialchars($employee['first_name']); ?></span>
  </nav>

  <div class="profile-container" style="margin-top: 20px;">
      
      <!-- Sidebar (Left) -->
      <aside class="profile-sidebar">
          <div class="profile-avatar-wrapper">
              <?php 
                  $initials = strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1));
                  echo $initials;
              ?>
          </div>
          <div class="profile-meta-info">
              <h2><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></h2>
              <p><?php echo htmlspecialchars($employee['position_title']); ?></p>
          </div>
          
          <div class="profile-nav-wrapper">
              <nav class="profile-nav" id="manageNavScroller">
                  <a href="#personal-section" class="profile-nav-link active">Personal Details</a>
                  <a href="#financial-section" class="profile-nav-link">Financial & Banking</a>
                  <a href="#history-section" class="profile-nav-link">Employment History</a>
                  <a href="#permissions-section" class="profile-nav-link">Permissions</a>
                  <a href="#actions-section" class="profile-nav-link">Quick Actions</a>
              </nav>
              <button type="button" class="nav-scroll-arrow" onclick="scrollManageNavRight()" aria-label="Scroll navigation right">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="9 18 15 12 9 6"></polyline></svg>
              </button>
          </div>

          <div class="profile-sidebar-actions" style="width: 100%; border-top: 1px solid var(--border); margin-top: 20px; padding-top: 20px; display: flex; flex-direction: column; gap: 10px;">
              <a href="update_profile.php?id=<?php echo $employee['user_id']; ?>" class="btn-hero btn-hero-primary" style="justify-content: center; width: 100%;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Update Profile
              </a>
              <form action="" method="POST" style="width: 100%; margin: 0;" id="delete-emp-form">
                  <button type="submit" class="btn-hero btn-hero-warn" style="justify-content: center; width: 100%;">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                      Delete Employee
                  </button>
              </form>
          </div>
      </aside>

      <!-- Main Content (Right) -->
      <main class="profile-main">
          
          <!-- ══ Quick Stats ══ -->
          <div class="emp-stats">
              <div class="emp-stat">
                  <span class="emp-stat-label">Base Salary</span>
                  <span class="emp-stat-value"><?php echo htmlspecialchars($employee['base_salary_rs']); ?></span>
                  <span class="emp-stat-sub">Per month</span>
              </div>
              <div class="emp-stat">
                  <span class="emp-stat-label">Employment</span>
                  <span class="emp-stat-value green">
                      <?php
                          $joined_ts = strtotime($employee['date_of_joining']);
                          $diff = (new DateTime('@'.$joined_ts))->diff(new DateTime());
                          echo $diff->y > 0 ? $diff->y . 'y ' . $diff->m . 'm' : $diff->m . ' months';
                      ?>
                  </span>
                  <span class="emp-stat-sub">Since <?php echo htmlspecialchars($employee['date_of_joining']); ?></span>
              </div>
              <div class="emp-stat">
                  <span class="emp-stat-label">Bank</span>
                  <span class="emp-stat-value" style="font-size:18px; line-height: 1.2; padding-top: 2px;"><?php echo htmlspecialchars($employee['bank_name']); ?></span>
                  <span class="emp-stat-sub">Primary account</span>
              </div>
          </div>

          <!-- SECTION 1: PERSONAL DETAILS -->
          <div class="section-card" id="personal-section">
              <div class="section-header">
                  <h3>Personal Details</h3>
              </div>
              <div class="section-body">
                  <div class="modern-grid">
                      <div class="form-field">
                          <label>Email Address</label>
                          <input type="text" class="modern-input" value="<?php echo htmlspecialchars($employee['email']); ?>" disabled>
                      </div>
                      <div class="form-field">
                          <label>Department</label>
                          <input type="text" class="modern-input" value="<?php echo htmlspecialchars($employee['department']); ?>" disabled>
                      </div>
                      <div class="form-field full">
                          <label>Date Joined</label>
                          <input type="text" class="modern-input" value="<?php echo htmlspecialchars($employee['date_of_joining']); ?>" disabled>
                      </div>
                  </div>
              </div>
          </div>

          <!-- SECTION 2: FINANCIAL & BANKING -->
          <div class="section-card" id="financial-section">
              <div class="section-header">
                  <h3>Financial & Banking</h3>
              </div>
              <div class="section-body">
                  <div class="modern-grid">
                      <div class="form-field">
                          <label>Base Salary (Rs)</label>
                          <input type="text" class="modern-input" value="<?php echo htmlspecialchars($employee['base_salary_rs']); ?> / month" disabled>
                      </div>
                      <div class="form-field">
                          <label>Bank Name</label>
                          <input type="text" class="modern-input" value="<?php echo htmlspecialchars($employee['bank_name']); ?>" disabled>
                      </div>
                      <div class="form-field full">
                          <label>Account Number (IBAN)</label>
                          <input type="text" class="modern-input" value="<?php echo htmlspecialchars($employee['bank_account_number']); ?>" disabled>
                      </div>
                  </div>
              </div>
          </div>

          <!-- SECTION 3: EMPLOYMENT HISTORY -->
          <div class="section-card" id="history-section">
              <div class="section-header">
                  <h3>Employment History</h3>
              </div>
              <div class="section-body">
                  <div class="emp-timeline">
                      <div class="tl-item">
                          <div class="tl-dot">
                              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                          </div>
                          <div class="tl-content">
                              <div class="tl-title">Joined Company</div>
                              <div class="tl-date">
                                  <time datetime="<?php echo date('Y-m-d', strtotime($employee['date_of_joining'])); ?>">
                                      <?php echo htmlspecialchars($employee['date_of_joining']); ?>
                                  </time>
                              </div>
                              <div class="tl-desc">Started as <?php echo htmlspecialchars($employee['position_title']); ?></div>
                          </div>
                      </div>
                      <div class="tl-item">
                          <div class="tl-dot" style="background: #f0fdf4; border-color: var(--brand-green); color: var(--brand-green);">
                              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/></svg>
                          </div>
                          <div class="tl-content">
                              <div class="tl-title">Currently Active</div>
                              <div class="tl-date">Present</div>
                              <div class="tl-desc">Status: <strong><?php echo htmlspecialchars($employee['status']); ?></strong></div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>

          <!-- SECTION 4: PERMISSIONS -->
          <div class="section-card" id="permissions-section">
              <div class="section-header">
                  <h3>Access Permissions</h3>
              </div>
              <div class="section-body">
                  <p style="font-size: 13px; color: #64748b; margin-bottom: 20px; line-height: 1.6;">
                      Control what this employee can access. Toggle permissions on or off — changes save instantly.
                  </p>
                  <div class="perm-list">
                      <?php foreach ($all_permissions as $perm): ?>
                          <?php
                              $is_active = in_array((int)$perm['id'], $user_perms);
                              $icon_map = [
                                  'add_expense'        => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
                                  'approve_expense'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
                                  'view_all_expenses'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
                              ];
                              $icon = $icon_map[$perm['name']] ?? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';
                          ?>
                          <div class="perm-row" data-perm-id="<?php echo $perm['id']; ?>">
                              <div class="perm-icon <?php echo $is_active ? 'active' : ''; ?>">
                                  <?php echo $icon; ?>
                              </div>
                              <div class="perm-info">
                                  <div class="perm-name"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $perm['name']))); ?></div>
                                  <div class="perm-desc"><?php echo htmlspecialchars($perm['description']); ?></div>
                              </div>
                              <label class="perm-toggle">
                                  <input type="checkbox" class="perm-checkbox" data-perm-id="<?php echo $perm['id']; ?>" <?php echo $is_active ? 'checked' : ''; ?>>
                                  <span class="perm-slider"></span>
                              </label>
                          </div>
                      <?php endforeach; ?>
                  </div>
                  <div id="perm-save-feedback" style="display: none; margin-top: 16px; padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;"></div>
              </div>
          </div>

          <!-- SECTION 5: QUICK ACTIONS -->
          <div class="section-card" id="actions-section">
              <div class="section-header">
                  <h3>Quick Actions</h3>
              </div>
              <div class="section-body">
                  <div class="emp-actions-list" style="padding: 0;">
                      <a href="payroll_management.php?emp_id=<?php echo htmlspecialchars($employee['user_id']); ?>" class="emp-qa" aria-label="Generate payslip for <?php echo htmlspecialchars($employee['first_name']); ?>">
                          <div class="emp-qa-icon payslip">
                              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                          </div>
                          <div class="emp-qa-text-wrap">
                              <div class="emp-qa-label">Generate Payslip</div>
                              <div class="emp-qa-desc">Go to payroll management to process salary</div>
                          </div>
                          <svg class="emp-qa-arr" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                      </a>
                  </div>
              </div>
          </div>

      </main>
  </div>

  <script>
      // Scroll handling for active sidebar state
      document.addEventListener('DOMContentLoaded', function() {
          const links = document.querySelectorAll('.profile-nav-link');
          const sections = document.querySelectorAll('.section-card');

          window.addEventListener('scroll', function() {
              let current = '';
              sections.forEach(section => {
                  const sectionTop = section.offsetTop;
                  if (pageYOffset >= sectionTop - 120) {
                      current = section.getAttribute('id');
                  }
              });

              links.forEach(link => {
                  link.classList.remove('active');
                  if (link.getAttribute('href').substring(1) === current) {
                      link.classList.add('active');
                  }
              });
          });
          // Modal functionality
          const deleteForm = document.getElementById('delete-emp-form');
          const deleteModal = document.getElementById('delete-modal');
          const cancelBtn = document.getElementById('cancel-delete-btn');
          const confirmBtn = document.getElementById('confirm-delete-btn');

          if (deleteForm && deleteModal) {
              deleteForm.addEventListener('submit', function(e) {
                  e.preventDefault();
                  deleteModal.style.display = 'flex';
                  setTimeout(() => deleteModal.classList.add('active'), 10);
              });

              cancelBtn.addEventListener('click', () => {
                  deleteModal.classList.remove('active');
                  setTimeout(() => deleteModal.style.display = 'none', 300);
              });

              confirmBtn.addEventListener('click', () => {
                  confirmBtn.innerHTML = '<span style="display:flex;align-items:center;gap:8px;justify-content:center;">Processing...</span>';
                  confirmBtn.style.opacity = '0.7';
                  confirmBtn.style.pointerEvents = 'none';
                  
                  const hiddenInp = document.createElement('input');
                  hiddenInp.type = 'hidden';
                  hiddenInp.name = 'deactivate';
                  hiddenInp.value = '1';
                  deleteForm.appendChild(hiddenInp);
                  deleteForm.submit();
              });

              deleteModal.addEventListener('click', (e) => {
                  if (e.target === deleteModal) {
                      deleteModal.classList.remove('active');
                      setTimeout(() => deleteModal.style.display = 'none', 300);
                  }
              });
          }

          // Trigger Toast for success
          if (window.showToast) {
              <?php if (isset($error)): ?>
                  window.showToast(<?php echo json_encode($error); ?>, 'error', 'Error');
              <?php endif; ?>
          }
      });
  </script>

  <!-- Production Modal Template -->
  <div id="delete-modal" class="modal-overlay">
      <div class="modal-content">
          <div class="modal-header">
              <div class="modal-icon-warning">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              </div>
              <h3>Remove Employee?</h3>
          </div>
          <div class="modal-body">
              <p>
                  Are you sure you want to delete <strong id="deleteEmployeeName"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></strong>? 
                  This action will permanently remove their profile and payroll data from the system.
              </p>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn-cancel" id="cancel-delete-btn">No, Keep Profile</button>
              <button type="button" class="btn-confirm-delete" id="confirm-delete-btn">Yes, Delete Permanently</button>
          </div>
      </div>
  </div>

  <style>
  /* ── Premium Modal System ── */
  .modal-overlay {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.4);
    backdrop-filter: blur(8px);
    z-index: 9999;
    display: none; align-items: center; justify-content: center;
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .modal-overlay.active {
    display: flex;
    opacity: 1;
  }
  .modal-content {
    background: #ffffff;
    border-radius: 20px;
    width: 90%; max-width: 440px;
    padding: 32px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    transform: scale(0.9) translateY(20px);
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  }
  .modal-overlay.active .modal-content {
    transform: scale(1) translateY(0);
  }
  .modal-header {
    text-align: center;
    margin-bottom: 24px;
  }
  .modal-icon-warning {
    width: 56px; height: 56px;
    background: #fff1f2;
    color: #e11d48;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px auto;
  }
  .modal-icon-warning svg { width: 28px; height: 28px; }
  .modal-header h3 {
    font-size: 20px; font-weight: 800; color: #1e293b; margin: 0;
  }
  .modal-body p {
    font-size: 14.5px; color: #64748b; line-height: 1.6; text-align: center; margin: 0;
  }
  .modal-footer {
    margin-top: 32px;
    display: flex; gap: 12px;
  }
  .btn-cancel, .btn-confirm-delete {
    flex: 1; height: 46px; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s; border: none;
  }
  .btn-cancel {
    background: #f1f5f9; color: #475569;
  }
  .btn-cancel:hover { background: #e2e8f0; color: #1e293b; }
  .btn-confirm-delete {
    background: #e11d48; color: #ffffff;
    box-shadow: 0 4px 12px rgba(225, 29, 72, 0.2);
  }
  .btn-confirm-delete:hover {
    background: #be123c; transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(225, 29, 72, 0.3);
  }
  </style>

  <!-- Permissions Toggle Styles -->
  <style>
      /* ── Permission Row List ── */
      .perm-list {
          display: flex;
          flex-direction: column;
          gap: 0;
      }
      .perm-row {
          display: flex;
          align-items: center;
          gap: 16px;
          padding: 16px 0;
          border-bottom: 1px solid #f1f5f9;
          transition: background 0.15s;
      }
      .perm-row:last-child {
          border-bottom: none;
      }
      .perm-row:hover {
          background: #fafbfc;
          margin: 0 -20px;
          padding-left: 20px;
          padding-right: 20px;
          border-radius: 8px;
      }

      /* Icon bubble */
      .perm-icon {
          width: 40px;
          height: 40px;
          border-radius: 10px;
          background: #f1f5f9;
          color: #94a3b8;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
          transition: all 0.25s;
      }
      .perm-icon.active {
          background: #ecfdf5;
          color: #186D55;
      }

      /* Text block */
      .perm-info {
          flex: 1;
          min-width: 0;
      }
      .perm-name {
          font-size: 14px;
          font-weight: 700;
          color: #1e293b;
          margin-bottom: 2px;
      }
      .perm-desc {
          font-size: 12.5px;
          color: #94a3b8;
          font-weight: 500;
          line-height: 1.4;
      }

      /* ── Toggle Switch ── */
      .perm-toggle {
          position: relative;
          display: inline-block;
          width: 44px;
          height: 24px;
          flex-shrink: 0;
          cursor: pointer;
      }
      .perm-toggle .perm-checkbox {
          opacity: 0;
          width: 0;
          height: 0;
          position: absolute;
      }
      .perm-slider {
          position: absolute;
          inset: 0;
          background: #cbd5e1;
          border-radius: 24px;
          transition: background 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      }
      .perm-slider::before {
          content: '';
          position: absolute;
          width: 18px;
          height: 18px;
          left: 3px;
          top: 3px;
          background: #ffffff;
          border-radius: 50%;
          transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
          box-shadow: 0 1px 3px rgba(0,0,0,0.15);
      }
      .perm-checkbox:checked + .perm-slider {
          background: #186D55;
      }
      .perm-checkbox:checked + .perm-slider::before {
          transform: translateX(20px);
      }
      .perm-checkbox:focus + .perm-slider {
          box-shadow: 0 0 0 3px rgba(24, 109, 85, 0.15);
      }

      /* Saving state indicator */
      .perm-row.saving {
          opacity: 0.6;
          pointer-events: none;
      }

      @media (max-width: 768px) {
          .perm-row {
              gap: 12px;
              padding: 14px 0;
          }
          .perm-icon {
              width: 36px;
              height: 36px;
              border-radius: 8px;
          }
          .perm-name {
              font-size: 13px;
          }
          .perm-desc {
              font-size: 11.5px;
          }
      }
  </style>

</div><!-- /.dashboard-container -->

<script>
    // Interactive Ribbon Navigator for Detail View
    function scrollManageNavRight() {
        const scroller = document.getElementById('manageNavScroller');
        if (scroller) {
            scroller.scrollBy({ left: 160, behavior: 'smooth' });
        }
    }
</script>

<!-- AJAX Permission Toggle Engine -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.perm-checkbox');

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', function() {
            const permId = this.getAttribute('data-perm-id');
            const action = this.checked ? 'grant' : 'revoke';
            const row = this.closest('.perm-row');
            const iconEl = row.querySelector('.perm-icon');
            const feedback = document.getElementById('perm-save-feedback');

            // Visual: saving state
            row.classList.add('saving');

            // Toggle icon color immediately
            if (action === 'grant') {
                iconEl.classList.add('active');
            } else {
                iconEl.classList.remove('active');
            }

            // AJAX POST
            const formData = new FormData();
            formData.append('toggle_permission', '1');
            formData.append('permission_id', permId);
            formData.append('action', action);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                row.classList.remove('saving');
                if (data.success) {
                    feedback.style.display = 'block';
                    feedback.style.background = '#ecfdf5';
                    feedback.style.color = '#186D55';
                    feedback.textContent = action === 'grant'
                        ? '✓ Permission granted successfully'
                        : '✓ Permission revoked successfully';
                    setTimeout(() => { feedback.style.display = 'none'; }, 2500);
                }
            })
            .catch(() => {
                row.classList.remove('saving');
                // Revert checkbox on error
                cb.checked = !cb.checked;
                if (cb.checked) iconEl.classList.add('active');
                else iconEl.classList.remove('active');

                feedback.style.display = 'block';
                feedback.style.background = '#fef2f2';
                feedback.style.color = '#dc2626';
                feedback.textContent = '✕ Failed to update permission. Try again.';
                setTimeout(() => { feedback.style.display = 'none'; }, 3000);
            });
        });
    });
});
</script>

<?php include_once "../includes/footer.php"; ?>
