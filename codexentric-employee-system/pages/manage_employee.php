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
            echo "<p style='color:green; text-align:center; margin-top:40px;'>Employee account deactivated successfully.</p>";
            echo "<p style='text-align:center;'><a href='employees_list.php'>Back to Employee List</a></p>";
            exit();
        } else {
            echo "<p style='color:red; text-align:center; margin-top:40px;'>Failed to deactivate employee account.</p>";
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



/* ── Breadcrumb ── */
.emp-breadcrumb {
  font-size: 13px; 
  color: var(--text-muted); 
  font-weight: 500;
  display: flex; 
  align-items: center; 
  gap: 5px; 
  margin-bottom: 20px;
}
.emp-breadcrumb a { color: var(--brand-orange); text-decoration: none; font-weight: 700; }
.emp-breadcrumb a:hover { text-decoration: underline; }

.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px;
  font-family: var(--font-body);
}

/* ── Minimal Header ── */
.emp-hero {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 24px 30px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}

.emp-hero-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.emp-avatar-xl {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  background: var(--bg);
  color: var(--brand-green);
  font-size: 20px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--border);
}

.emp-hero-info h1 {
  font-size: 20px;
  font-weight: 800;
  color: var(--text-main);
  margin: 0 0 4px;
  letter-spacing: -0.5px;
}

.emp-hero-info p {
  font-size: 14px;
  color: var(--text-muted);
  margin: 0 0 10px;
}

.emp-hero-meta {
  display: flex;
  align-items: center;
  gap: 12px;
}

.hero-badge {
  font-size: 12px;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 20px;
  background: #f0fdf4;
  color: #166534;
  border: 1px solid #dcfce7;
  display: flex;
  align-items: center;
  gap: 6px;
}

.hero-badge.onb {
  background: #fffbeb;
  color: #92400e;
  border-color: #fef3c7;
}

.hero-badge::before {
  content: '';
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
}

.hero-meta-chip {
  font-size: 13px;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
}

.hero-meta-chip svg {
  color: #94a3b8;
}

/* ── Action Buttons ── */
.emp-hero-actions {
  display: flex;
  gap: 10px;
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

/* ── Detail Cards ── */
.emp-section-label {
  font-size: 13px;
  font-weight: 800;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 12px;
  padding-left: 5px;
}

.emp-info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 24px;
}

.emp-card {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.02);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.emp-card-head {
  padding: 14px 20px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  gap: 12px;
  background: #fcfcfd;
}

.emp-card-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: var(--bg);
  color: var(--text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
}

.emp-card-title {
  font-size: 14px;
  font-weight: 700;
  color: var(--text-main);
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.emp-card-title .dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--brand-orange);
}

.emp-card-body { padding: 6px 0; }

/* ── Info rows (dl) ── */
.emp-info-list { margin: 0; padding: 0; }
.emp-info-row {
  display: flex; align-items: flex-start;
  padding: 13px 22px;
  border-bottom: 1px solid #f1f5f9;
  gap: 16px;
  transition: background .12s;
}
.emp-info-row:last-child { border-bottom: none; }
.emp-info-row:hover { background: var(--surface); }

.emp-info-dt {
  width: 130px; flex-shrink: 0;
  font-size: 12px; font-weight: 600;
  color: var(--text-s); text-transform: uppercase; letter-spacing: 0.5px;
  padding-top: 1px;
}
.emp-info-dd {
  font-size: 14px; font-weight: 600; color: var(--text-b);
  margin: 0; word-break: break-word;
}
.emp-info-link {
  color: var(--blue); text-decoration: none;
  transition: color .14s;
}
.emp-info-link:hover { color: var(--blue-dark); text-decoration: underline; }

.sal-val { font-family: var(--font); font-size: 16px; font-weight: 700; color: var(--text-h); }
.sal-per { font-size: 12.5px; color: var(--text-s); margin-left: 2px; font-weight: 500; }

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
</style>

<div class="dashboard-container">

  <!-- ── Breadcrumb ── -->
  <nav class="emp-breadcrumb" aria-label="Breadcrumb">
    <a href="administrator_dashboard.php">Dashboard</a>
    <span>/</span>
    <a href="employees_list.php">Employees</a>
    <span>/</span>
    <span><?php echo htmlspecialchars($employee['first_name']); ?></span>
  </nav>

  <!-- ══ Hero Banner ══ -->
  <div class="emp-hero">
    <div class="emp-hero-left">
      <div class="emp-avatar-xl" aria-hidden="true">
        <?php echo strtoupper(substr($employee['first_name'], 0, 1)); ?>
      </div>
      <div class="emp-hero-info">
        <h1><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></h1>
        <p><?php echo htmlspecialchars($employee['position_title']); ?></p>
        <div class="emp-hero-meta">
          <span class="hero-badge <?php echo strtolower($employee['status']) !== 'active' ? 'onb' : ''; ?>">
            <?php echo htmlspecialchars($employee['status']); ?>
          </span>
          <span class="hero-meta-chip">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Joined <?php echo htmlspecialchars($employee['date_of_joining']); ?>
          </span>
          <span class="hero-meta-chip">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <?php echo htmlspecialchars($employee['base_salary_rs']); ?>/mo
          </span>
        </div>
      </div>
    </div>
    <div class="emp-hero-actions">
      <form action="" method="POST">
        <button type="submit" class="btn-hero btn-hero-warn" name="deactivate" aria-label="Deactivate employee account">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
          Deactivate
        </button>
      </form>
      <a href="update_profile.php?id=<?php echo $employee['user_id']; ?>" class="btn-hero btn-hero-primary" aria-label="Update employee profile">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Update Profile
      </a>
    </div>
  </div>

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

  <!-- ══ Detail Cards ══ -->
  <div>
    <div class="emp-section-label">Employee Details</div>
    <div class="emp-info-grid">

      <!-- Personal Information -->
      <div class="emp-card">
        <div class="emp-card-head">
          <div class="emp-card-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </div>
          <h3 class="emp-card-title"><span class="dot"></span>Personal Information</h3>
        </div>
        <div class="emp-card-body">
          <dl class="emp-info-list">
            <div class="emp-info-row">
              <dt class="emp-info-dt">Email</dt>
              <dd class="emp-info-dd">
                <a href="mailto:<?php echo htmlspecialchars($employee['email']); ?>" class="emp-info-link">
                  <?php echo htmlspecialchars($employee['email']); ?>
                </a>
              </dd>
            </div>
            <div class="emp-info-row">
              <dt class="emp-info-dt">Department</dt>
              <dd class="emp-info-dd"><span class="mono"><?php echo htmlspecialchars($employee['department']); ?></span></dd>
            </div>
            <div class="emp-info-row">
              <dt class="emp-info-dt">Date Joined</dt>
              <dd class="emp-info-dd">
                <time datetime="<?php echo date('Y-m-d', strtotime($employee['date_of_joining'])); ?>">
                  <?php echo htmlspecialchars($employee['date_of_joining']); ?>
                </time>
              </dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Financial & Banking -->
      <div class="emp-card">
        <div class="emp-card-head">
          <div class="emp-card-icon fin">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
          </div>
          <h3 class="emp-card-title"><span class="dot" style="background:linear-gradient(135deg,#7E22CE,#9333EA)"></span>Financial &amp; Banking</h3>
        </div>
        <div class="emp-card-body">
          <dl class="emp-info-list">
            <div class="emp-info-row">
              <dt class="emp-info-dt">Base Salary</dt>
              <dd class="emp-info-dd">
                <span class="sal-val"><?php echo htmlspecialchars($employee['base_salary_rs']); ?></span>
                <span class="sal-per">/month</span>
              </dd>
            </div>
            <div class="emp-info-row">
              <dt class="emp-info-dt">Bank</dt>
              <dd class="emp-info-dd"><?php echo htmlspecialchars($employee['bank_name']); ?></dd>
            </div>
            <div class="emp-info-row">
              <dt class="emp-info-dt">Account No.</dt>
              <dd class="emp-info-dd"><span class="mono"><?php echo htmlspecialchars($employee['bank_account_number']); ?></span></dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Employment History -->
      <div class="emp-card">
        <div class="emp-card-head">
          <div class="emp-card-icon hist">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <h3 class="emp-card-title"><span class="dot" style="background:linear-gradient(135deg,#059669,#10b981)"></span>Employment History</h3>
        </div>
        <div class="emp-card-body">
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
              <div class="tl-dot" style="background:var(--blue-light);border-color:var(--blue);color:var(--blue);">
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

      <!-- Quick Actions -->
      <div class="emp-card">
        <div class="emp-card-head">
          <div class="emp-card-icon act">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
          </div>
          <h3 class="emp-card-title"><span class="dot" style="background:linear-gradient(135deg,#D97706,#F59E0B)"></span>Quick Actions</h3>
        </div>
        <div class="emp-card-body">
          <div class="emp-actions-list">

            <a href="#" class="emp-qa" aria-label="Generate payslip for <?php echo htmlspecialchars($employee['first_name']); ?>">
              <div class="emp-qa-icon payslip">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              </div>
              <div class="emp-qa-text-wrap">
                <div class="emp-qa-label">Generate Payslip</div>
                <div class="emp-qa-desc">Download this month's payslip</div>
              </div>
              <svg class="emp-qa-arr" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>

            <a href="attendance_record.php" class="emp-qa" aria-label="View attendance records">
              <div class="emp-qa-icon attend">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              </div>
              <div class="emp-qa-text-wrap">
                <div class="emp-qa-label">View Attendance</div>
                <div class="emp-qa-desc">Check check-in / check-out logs</div>
              </div>
              <svg class="emp-qa-arr" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>

            <a href="#" class="emp-qa" aria-label="Send performance review">
              <div class="emp-qa-icon review">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22 6 12 13 2 6"/></svg>
              </div>
              <div class="emp-qa-text-wrap">
                <div class="emp-qa-label">Performance Review</div>
                <div class="emp-qa-desc">Send review request via email</div>
              </div>
              <svg class="emp-qa-arr" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>

          </div>
        </div>
      </div>

    </div>
  </div>

</div><!-- /.dashboard-container -->

<?php include_once "../includes/footer.php"; ?>
