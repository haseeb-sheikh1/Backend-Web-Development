<?php
session_start();
    $current_page = "manage_employees";
    $extra_css    = "manage_employee";
    $title        = "Manage Employee - CodeXentric";
    include_once "../includes/header.php";
    require_once '../pages/Database.php';
    require_once '../pages/Employee.php';
    $db = new Database();
    $connection = $db->getConnection();
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
    if (isset($_POST['deactivate'])) {
        $employeeId = $_GET['id'];
        if ($employeeObj->deleteEmployee($employeeId)) {
            ?>
            <div class="feedback-card-container">
                <div class="feedback-card success">
                    <div class="feedback-icon-wrapper">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <h2>Employee Deleted</h2>
                    <p>The employee record has been successfully deleted and removed from the system.</p>
                    <a href="employees_list.php" class="feedback-btn success">
                        Back to Employee List
                    </a>
                </div>
            </div>
            <style>
            .feedback-card-container {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: calc(100vh - 200px);
                background: #f8fafc;
                padding: 24px;
            }
            .feedback-card {
                background: #ffffff;
                border-radius: 16px;
                padding: 40px 32px;
                width: 100%;
                max-width: 420px;
                text-align: center;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
                border: 1px solid #e2e8f0;
                animation: scaleUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
            }
            @keyframes scaleUp {
                from { transform: scale(0.95); opacity: 0; }
                to { transform: scale(1); opacity: 1; }
            }
            .feedback-icon-wrapper {
                width: 64px;
                height: 64px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 24px auto;
            }
            .feedback-card.success .feedback-icon-wrapper {
                background: #e8f5e9;
                color: #186D55;
            }
            .feedback-card.error .feedback-icon-wrapper {
                background: #ffebee;
                color: #d32f2f;
            }
            .feedback-icon-wrapper svg {
                width: 28px;
                height: 28px;
            }
            .feedback-card h2 {
                font-size: 20px;
                font-weight: 700;
                color: #1e293b;
                margin-bottom: 12px;
            }
            .feedback-card p {
                font-size: 14px;
                color: #64748b;
                line-height: 1.6;
                margin-bottom: 30px;
            }
            .feedback-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                height: 44px;
                padding: 0 24px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                text-decoration: none;
                transition: all 0.2s ease;
                width: 100%;
            }
            .feedback-btn.success {
                background: #186D55;
                color: #ffffff;
                box-shadow: 0 4px 12px rgba(24, 109, 85, 0.2);
            }
            .feedback-btn.success:hover {
                background: #125542;
                transform: translateY(-1px);
                box-shadow: 0 6px 16px rgba(24, 109, 85, 0.25);
            }
            .feedback-btn.error {
                background: #d32f2f;
                color: #ffffff;
                box-shadow: 0 4px 12px rgba(211, 47, 47, 0.2);
            }
            .feedback-btn.error:hover {
                background: #b71c1c;
                transform: translateY(-1px);
                box-shadow: 0 6px 16px rgba(211, 47, 47, 0.25);
            }
            </style>
            </main>
            </div>
            <?php
            include_once "../includes/footer.php";
            exit();
        } else {
            ?>
            <div class="feedback-card-container">
                <div class="feedback-card error">
                    <div class="feedback-icon-wrapper">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </div>
                    <h2>Deletion Failed</h2>
                    <p>There was an unexpected error processing this employee deletion. Please try again.</p>
                    <a href="employees_list.php" class="feedback-btn error">
                        Back to Employee List
                    </a>
                </div>
            </div>
            <style>
            .feedback-card-container {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: calc(100vh - 200px);
                background: #f8fafc;
                padding: 24px;
            }
            .feedback-card {
                background: #ffffff;
                border-radius: 16px;
                padding: 40px 32px;
                width: 100%;
                max-width: 420px;
                text-align: center;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
                border: 1px solid #e2e8f0;
                animation: scaleUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
            }
            @keyframes scaleUp {
                from { transform: scale(0.95); opacity: 0; }
                to { transform: scale(1); opacity: 1; }
            }
            .feedback-icon-wrapper {
                width: 64px;
                height: 64px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 24px auto;
            }
            .feedback-card.success .feedback-icon-wrapper {
                background: #e8f5e9;
                color: #186D55;
            }
            .feedback-card.error .feedback-icon-wrapper {
                background: #ffebee;
                color: #d32f2f;
            }
            .feedback-icon-wrapper svg {
                width: 28px;
                height: 28px;
            }
            .feedback-card h2 {
                font-size: 20px;
                font-weight: 700;
                color: #1e293b;
                margin-bottom: 12px;
            }
            .feedback-card p {
                font-size: 14px;
                color: #64748b;
                line-height: 1.6;
                margin-bottom: 30px;
            }
            .feedback-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                height: 44px;
                padding: 0 24px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                text-decoration: none;
                transition: all 0.2s ease;
                width: 100%;
            }
            .feedback-btn.success {
                background: #186D55;
                color: #ffffff;
                box-shadow: 0 4px 12px rgba(24, 109, 85, 0.2);
            }
            .feedback-btn.success:hover {
                background: #125542;
                transform: translateY(-1px);
                box-shadow: 0 6px 16px rgba(24, 109, 85, 0.25);
            }
            .feedback-btn.error {
                background: #d32f2f;
                color: #ffffff;
                box-shadow: 0 4px 12px rgba(211, 47, 47, 0.2);
            }
            .feedback-btn.error:hover {
                background: #b71c1c;
                transform: translateY(-1px);
                box-shadow: 0 6px 16px rgba(211, 47, 47, 0.25);
            }
            </style>
            </main>
            </div>
            <?php
            include_once "../includes/footer.php";
            exit();
        }
    }
    
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
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: #186D55;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    font-weight: 700;
    border: 1px solid #e2e8f0;
    margin-bottom: 20px;
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
    margin-bottom: 24px;
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
    margin-bottom: 24px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    scroll-margin-top: 20px;
}

.section-header {
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
}

.section-header h3 {
    font-size: 14px;
    font-weight: 700;
    color: #334155;
    margin: 0;
}

.section-body {
    padding: 24px;
}

/* ── Modern Grid & Forms ── */
.modern-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.form-field {
    margin-bottom: 20px;
}

.form-field.full {
    grid-column: span 2;
}

.form-field label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 8px;
}

.modern-input {
    width: 100%;
    height: 44px;
    padding: 0 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    color: #1e293b;
    background: #f8fafc;
    transition: all 0.2s;
    font-family: inherit;
    cursor: not-allowed;
    border-style: dashed;
}

.btn-hero {
  height: 40px;
  padding: 0 16px;
  border-radius: 8px;
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
  background: var(--brand-orange);
  color: #fff;
  border: none;
  box-shadow: 0 4px 12px rgba(255, 123, 29, 0.15);
}

.btn-hero-primary:hover {
  background: var(--brand-orange-hover);
  transform: translateY(-1px);
}

.btn-hero-warn {
  background: #fff;
  color: #ef4444;
  border: 1px solid #fee2e2;
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
  margin-bottom: 24px;
}

.emp-stat {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 18px 24px;
  display: flex;
  flex-direction: column;
  gap: 4px;
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
@media (max-width: 900px) {
    .profile-container {
        flex-direction: column;
        gap: 24px;
    }
    .profile-sidebar {
        width: 100%;
        position: relative;
        top: 0;
    }
}

@media (max-width: 600px) {
    .modern-grid {
        grid-template-columns: 1fr;
    }
    .form-field.full {
        grid-column: span 1;
    }
    .emp-stats {
        grid-template-columns: 1fr;
    }
    .dashboard-container {
        padding: 15px;
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
          <h2><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></h2>
          <p><?php echo htmlspecialchars($employee['position_title']); ?></p>
          
          <nav class="profile-nav">
              <a href="#personal-section" class="profile-nav-link active">Personal Details</a>
              <a href="#financial-section" class="profile-nav-link">Financial & Banking</a>
              <a href="#history-section" class="profile-nav-link">Employment History</a>
              <a href="#actions-section" class="profile-nav-link">Quick Actions</a>
          </nav>

          <div style="width: 100%; border-top: 1px solid var(--border); margin-top: 20px; padding-top: 20px; display: flex; flex-direction: column; gap: 10px;">
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

          <!-- SECTION 4: QUICK ACTIONS -->
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
              });

              cancelBtn.addEventListener('click', () => {
                  deleteModal.style.display = 'none';
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
                  if (e.target === deleteModal) deleteModal.style.display = 'none';
              });
          }
      });
  </script>

  <!-- Production Modal Template -->
  <div id="delete-modal" class="custom-modal-overlay" style="display:none;">
      <div class="custom-modal-card">
          <div class="custom-modal-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </div>
          <h3 class="custom-modal-title">Delete Employee?</h3>
          <p class="custom-modal-desc">
              You are about to permanently delete <strong><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></strong>. 
              All records will be erased and this action cannot be reversed.
          </p>
          <div class="custom-modal-actions">
              <button type="button" class="c-btn-cancel" id="cancel-delete-btn">Keep Employee</button>
              <button type="button" class="c-btn-danger" id="confirm-delete-btn">Confirm Delete</button>
          </div>
      </div>
  </div>

  <style>
      .custom-modal-overlay {
          position: fixed; top: 0; left: 0; width: 100%; height: 100%;
          background: rgba(15, 23, 42, 0.65);
          backdrop-filter: blur(6px);
          display: flex; align-items: center; justify-content: center;
          z-index: 9999;
          animation: cFadeIn 0.2s ease-out;
          font-family: 'Nunito Sans', sans-serif;
      }
      .custom-modal-card {
          background: #ffffff; border-radius: 16px; padding: 32px;
          width: 100%; max-width: 400px; text-align: center;
          box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
          border: 1px solid rgba(255,255,255,0.1);
          animation: cSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
          margin: 20px;
      }
      .custom-modal-icon {
          width: 60px; height: 60px; border-radius: 50%;
          background: #fef2f2; color: #dc2626;
          display: flex; align-items: center; justify-content: center;
          margin: 0 auto 20px auto;
      }
      .custom-modal-title {
          font-size: 20px; font-weight: 800; color: #0f172a;
          margin: 0 0 10px 0;
      }
      .custom-modal-desc {
          font-size: 14px; color: #64748b; line-height: 1.6;
          margin: 0 0 28px 0;
      }
      .custom-modal-actions {
          display: flex; gap: 12px;
      }
      .c-btn-cancel, .c-btn-danger {
          flex: 1; height: 46px; border-radius: 10px; font-size: 14px;
          font-weight: 700; border: none; cursor: pointer;
          transition: all 0.2s ease;
      }
      .c-btn-cancel {
          background: #f1f5f9; color: #475569;
      }
      .c-btn-cancel:hover {
          background: #e2e8f0; color: #1e293b;
      }
      .c-btn-danger {
          background: #dc2626; color: #ffffff;
          box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
      }
      .c-btn-danger:hover {
          background: #b91c1c; transform: translateY(-1px);
          box-shadow: 0 6px 15px rgba(220, 38, 38, 0.35);
      }
      @keyframes cFadeIn { from { opacity: 0; } to { opacity: 1; } }
      @keyframes cSlideUp {
          from { opacity: 0; transform: translateY(20px) scale(0.95); }
          to { opacity: 1; transform: translateY(0) scale(1); }
      }
  </style>

</div><!-- /.dashboard-container -->

<?php include_once "../includes/footer.php"; ?>
