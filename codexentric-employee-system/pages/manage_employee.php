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
        $employeeId = $_GET['id'];
        $employee = $employeeObj->getEmployeeDetailsById($employeeId);
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
    



?>

<style>

@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&display=swap');

:root {
  --blue:       #1E6FD9;
  --blue-dark:  #1559B5;
  --blue-light: #EBF2FC;
  --blue-xl:    #F0F6FF;
  --green:      #059669;
  --green-bg:   #D1FAE5;
  --amber:      #D97706;
  --amber-bg:   #FEF3C7;
  --red:        #DC2626;
  --red-bg:     #FEE2E2;
  --border:     #E2E8F0;
  --surface:    #F8FAFC;
  --card:       #ffffff;
  --text-h:     #0F172A;
  --text-b:     #374151;
  --text-m:     #64748B;
  --text-s:     #94A3B8;
  --radius:     12px;
  --shadow-xs:  0 1px 3px rgba(15,23,42,0.05);
  --shadow-sm:  0 1px 8px rgba(15,23,42,0.07);
  --shadow-md:  0 4px 20px rgba(15,23,42,0.09);
  --shadow-bl:  0 6px 24px rgba(21,89,181,0.16);
}

/* ── Page wrapper ── */
.emp-page { display: flex; flex-direction: column; gap: 24px; }

/* ── Breadcrumb ── */
.emp-breadcrumb {
  font-size: 12.5px; color: var(--text-s);
  display: flex; align-items: center; gap: 5px; margin-bottom: -8px;
}
.emp-breadcrumb a { color: var(--blue); text-decoration: none; }
.emp-breadcrumb a:hover { text-decoration: underline; }

/* ════ HERO BANNER ════ */
.emp-hero {
  background: linear-gradient(125deg, #1248A0 0%, #1559B5 40%, #1E6FD9 75%, #2B87F0 100%);
  border-radius: var(--radius);
  padding: 28px 32px;
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 20px;
  position: relative; overflow: hidden;
  box-shadow: var(--shadow-bl);
}
.emp-hero::before {
  content: ''; position: absolute;
  width: 300px; height: 300px; border-radius: 50%;
  background: rgba(255,255,255,0.06);
  top: -100px; right: -60px; pointer-events: none;
}
.emp-hero::after {
  content: ''; position: absolute;
  width: 160px; height: 160px; border-radius: 50%;
  background: rgba(255,255,255,0.04);
  bottom: -50px; right: 200px; pointer-events: none;
}

.emp-hero-left { display: flex; align-items: center; gap: 20px; position: relative; z-index: 1; }

.emp-avatar-xl {
  width: 64px; height: 64px; border-radius: 16px; flex-shrink: 0;
  background: rgba(255,255,255,0.18);
  border: 2px solid rgba(255,255,255,0.35);
  backdrop-filter: blur(6px);
  font-family: 'Nunito', sans-serif;
  font-size: 26px; font-weight: 900; color: #fff;
  display: flex; align-items: center; justify-content: center;
}

.emp-hero-info h1 {
  font-family: 'Nunito', sans-serif;
  font-size: 20px; font-weight: 900; color: #fff; margin: 0 0 4px;
}
.emp-hero-info p {
  font-size: 13.5px; color: rgba(255,255,255,0.78); margin: 0 0 10px;
}
.emp-hero-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

.hero-badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 11px; border-radius: 20px;
  font-size: 12px; font-weight: 700;
  background: rgba(255,255,255,0.18);
  color: #fff; border: 1px solid rgba(255,255,255,0.28);
  backdrop-filter: blur(4px);
}
.hero-badge::before {
  content: ''; width: 6px; height: 6px; border-radius: 50%;
  background: #34D399;
}
.hero-badge.onb::before { background: #FCD34D; }

.hero-meta-chip {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 11px; border-radius: 20px;
  font-size: 12px; font-weight: 600;
  background: rgba(255,255,255,0.10);
  color: rgba(255,255,255,0.85);
  border: 1px solid rgba(255,255,255,0.18);
}

/* Hero action buttons */
.emp-hero-actions {
  display: flex; align-items: center; gap: 10px;
  position: relative; z-index: 1; flex-wrap: wrap;
}
.btn-hero {
  height: 38px; padding: 0 18px;
  border-radius: 7px; font-size: 13px; font-weight: 700;
  font-family: 'Source Sans 3', sans-serif;
  cursor: pointer; display: inline-flex; align-items: center; gap: 7px;
  text-decoration: none; white-space: nowrap; border: none;
  transition: opacity .18s, transform .12s;
}
.btn-hero:hover { opacity: .88; transform: translateY(-1px); }
.btn-hero-warn {
  background: rgba(239,68,68,0.18);
  color: #FCA5A5;
  border: 1.5px solid rgba(239,68,68,0.35);
}
.btn-hero-primary {
  background: #fff; color: var(--blue-dark);
}
.btn-hero-primary:hover { background: #f0f6ff; }

/* ════ STATS ROW ════ */
.emp-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}
@media (max-width: 860px) { .emp-stats { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 480px)  { .emp-stats { grid-template-columns: 1fr 1fr; } }

.emp-stat {
  background: var(--card);
  border: 1.5px solid var(--border);
  border-radius: var(--radius);
  padding: 18px 20px;
  display: flex; flex-direction: column; gap: 6px;
  box-shadow: var(--shadow-xs);
  transition: box-shadow .2s, transform .2s;
  position: relative; overflow: hidden;
}
.emp-stat:hover { box-shadow: var(--shadow-bl); transform: translateY(-2px); }
.emp-stat::before {
  content: ''; position: absolute; top:0; left:0; right:0; height:3px;
  background: linear-gradient(90deg, var(--blue-dark), var(--blue));
  opacity: 0; transition: opacity .2s;
}
.emp-stat:hover::before { opacity: 1; }
.emp-stat-label { font-size: 11px; font-weight: 700; color: var(--text-s); text-transform: uppercase; letter-spacing: 0.55px; }
.emp-stat-value { font-family: 'Nunito', sans-serif; font-size: 20px; font-weight: 900; color: var(--text-h); }
.emp-stat-value.green { color: var(--green); }
.emp-stat-sub { font-size: 11.5px; color: var(--text-s); }

/* ════ SECTION LABEL ════ */
.emp-section-label {
  font-size: 11px; font-weight: 800; color: var(--text-s);
  text-transform: uppercase; letter-spacing: 0.8px;
  margin-bottom: 14px;
  display: flex; align-items: center; gap: 8px;
}
.emp-section-label::after { content:''; flex:1; height:1px; background:var(--border); }

/* ════ INFO GRID ════ */
.emp-info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}
@media (max-width: 760px) { .emp-info-grid { grid-template-columns: 1fr; } }

/* ── Card shell ── */
.emp-card {
  background: var(--card);
  border: 1.5px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow-xs);
  overflow: hidden;
  transition: box-shadow .2s;
}
.emp-card:hover { box-shadow: var(--shadow-md); }

.emp-card-head {
  padding: 15px 22px;
  border-bottom: 1.5px solid var(--border);
  background: var(--surface);
  display: flex; align-items: center; gap: 10px;
}
.emp-card-icon {
  width: 34px; height: 34px; border-radius: 8px; flex-shrink: 0;
  background: var(--blue-light); color: var(--blue);
  display: flex; align-items: center; justify-content: center;
}
.emp-card-icon.fin { background: #EDE9FE; color: #7C3AED; }
.emp-card-icon.hist { background: var(--green-bg); color: var(--green); }
.emp-card-icon.act { background: var(--amber-bg); color: var(--amber); }

.emp-card-title {
  font-family: 'Nunito', sans-serif;
  font-size: 14px; font-weight: 800; color: var(--text-h); margin: 0;
  display: flex; align-items: center; gap: 7px;
}
.emp-card-title .dot {
  width: 7px; height: 7px; border-radius: 50%;
  background: linear-gradient(135deg, var(--blue-dark), var(--blue));
}

.emp-card-body { padding: 6px 0; }

/* ── Info rows (dl) ── */
.emp-info-list { margin: 0; padding: 0; }
.emp-info-row {
  display: flex; align-items: flex-start;
  padding: 13px 22px;
  border-bottom: 1px solid #F1F5F9;
  gap: 16px;
  transition: background .12s;
}
.emp-info-row:last-child { border-bottom: none; }
.emp-info-row:hover { background: var(--surface); }

.emp-info-dt {
  width: 130px; flex-shrink: 0;
  font-size: 12px; font-weight: 700;
  color: var(--text-s); text-transform: uppercase; letter-spacing: 0.4px;
  padding-top: 1px;
}
.emp-info-dd {
  font-size: 13.5px; font-weight: 600; color: var(--text-b);
  margin: 0; word-break: break-word;
}
.emp-info-link {
  color: var(--blue); text-decoration: none;
  transition: color .14s;
}
.emp-info-link:hover { color: var(--blue-dark); text-decoration: underline; }

.sal-val { font-family: 'Nunito', sans-serif; font-size: 16px; font-weight: 900; color: var(--text-h); }
.sal-per { font-size: 12px; color: var(--text-s); margin-left: 2px; font-weight: 500; }

.mono {
  font-family: 'Courier New', monospace;
  font-size: 12.5px; background: var(--surface);
  padding: 3px 8px; border-radius: 5px;
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
  background: var(--green-bg); border: 2px solid var(--green);
  display: flex; align-items: center; justify-content: center;
  color: var(--green); z-index: 1;
}
.tl-content { flex: 1; }
.tl-title { font-size: 13.5px; font-weight: 700; color: var(--text-h); margin-bottom: 2px; }
.tl-date  { font-size: 12px; color: var(--text-s); margin-bottom: 4px; }
.tl-desc  { font-size: 12.5px; color: var(--text-m); }

/* ── Quick Actions ── */
.emp-actions-list { padding: 10px 14px; display: flex; flex-direction: column; gap: 6px; }
.emp-qa {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 14px;
  border-radius: 9px;
  background: var(--surface);
  border: 1.5px solid var(--border);
  text-decoration: none;
  transition: border-color .15s, background .15s, box-shadow .15s;
  cursor: pointer;
}
.emp-qa:hover { border-color: var(--blue); background: var(--blue-xl); box-shadow: 0 2px 10px rgba(21,89,181,0.10); }
.emp-qa-icon {
  width: 34px; height: 34px; border-radius: 8px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
}
.emp-qa-icon.payslip { background: #EDE9FE; color: #7C3AED; }
.emp-qa-icon.attend  { background: var(--green-bg); color: var(--green); }
.emp-qa-icon.review  { background: var(--amber-bg); color: var(--amber); }
.emp-qa-text-wrap { flex: 1; }
.emp-qa-label { font-size: 13.5px; font-weight: 700; color: var(--text-h); }
.emp-qa-desc  { font-size: 12px; color: var(--text-s); margin-top: 1px; }
.emp-qa-arr { color: var(--text-s); transition: transform .15s, color .15s; }
.emp-qa:hover .emp-qa-arr { transform: translateX(3px); color: var(--blue); }

/* Animations */
@keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
.emp-hero  { animation: fadeUp .3s ease both; }
.emp-stats { animation: fadeUp .3s .06s ease both; }
.emp-card  { animation: fadeUp .32s .12s ease both; }
</style>

<div class="emp-page">

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
      <button type="button" class="btn-hero btn-hero-warn" onclick="confirmDeactivation()" aria-label="Deactivate employee account">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        Deactivate
      </button>
      <a href="update_profile.php" class="btn-hero btn-hero-primary" aria-label="Update employee profile">
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
      <span class="emp-stat-value" style="font-size:16px;"><?php echo htmlspecialchars($employee['bank_name']); ?></span>
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
          <h3 class="emp-card-title"><span class="dot" style="background:linear-gradient(135deg,#6D28D9,#7C3AED)"></span>Financial &amp; Banking</h3>
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
          <h3 class="emp-card-title"><span class="dot" style="background:linear-gradient(135deg,#047857,#10b981)"></span>Employment History</h3>
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
          <h3 class="emp-card-title"><span class="dot" style="background:linear-gradient(135deg,#B45309,#D97706)"></span>Quick Actions</h3>
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

</div><!-- /.emp-page -->

<script>
function confirmDeactivation() {
  if (confirm('Deactivate <?php echo addslashes($employee['name']); ?>\'s account?\nThey will lose system access immediately.')) {
    // In production: submit a form or AJAX call to update status in DB
    alert('Account deactivated. (Wire up your DB call here.)');
  }
}
</script>

<?php include_once "../includes/footer.php"; ?>