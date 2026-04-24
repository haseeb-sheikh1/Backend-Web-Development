<?php 
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

    $current_page = "administrator_dashboard";
    $extra_css    = "admin_dashboard";
    $title        = "Admin Dashboard";  
    
    $stats = [
        ["label" => "Total Headcount", "value" => "14",        "sub" => "Across all departments", "trend" => "+2 this month"],
        ["label" => "Current Revenue", "value" => "Rs 1.2M",   "sub" => "Monthly recurring",      "trend" => "+12.4% vs last"],
        ["label" => "Monthly Payroll", "value" => "Rs 485k",   "sub" => "Net disbursement",        "trend" => "3 pending"]
    ];

    $team_members = [
        ["name" => "Hammad Ali", "role" => "Senior Backend Developer", "salary" => "Rs 85,000", "status" => "Active",     "badge" => "badge-active"],
        ["name" => "Abdullah",   "role" => "Backend Developer",        "salary" => "Rs 65,000", "status" => "Active",     "badge" => "badge-active"],
        ["name" => "Khurum",     "role" => "Frontend Developer",       "salary" => "Rs 55,000", "status" => "Onboarding", "badge" => "badge-onboarding"]
    ];

    include_once "../includes/header.php";
    include_once "../includes/sidebar.php";

?>


<style>

@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&display=swap');

:root {
  --blue:        #1E6FD9;
  --blue-dark:   #1559B5;
  --blue-light:  #EBF2FC;
  --blue-xlight: #F0F6FF;
  --green:       #059669;
  --green-bg:    #D1FAE5;
  --amber:       #D97706;
  --amber-bg:    #FEF3C7;
  --red:         #DC2626;
  --red-bg:      #FEE2E2;
  --border:      #E2E8F0;
  --surface:     #F8FAFC;
  --card:        #ffffff;
  --text-h:      #0F172A;
  --text-b:      #374151;
  --text-m:      #64748B;
  --text-s:      #94A3B8;
  --radius:      12px;
  --radius-sm:   8px;
  --shadow-xs:   0 1px 3px rgba(15,23,42,0.05);
  --shadow-sm:   0 1px 6px rgba(15,23,42,0.07);
  --shadow-md:   0 4px 20px rgba(15,23,42,0.09);
  --shadow-blue: 0 6px 24px rgba(21,89,181,0.16);
}

/* ── Outer wrapper ── */
.dash { display: flex; flex-direction: column; gap: 28px; }

/* ── Welcome Banner ── */
.dash-welcome {
  background: linear-gradient(125deg, #1248A0 0%, #1559B5 40%, #1E6FD9 75%, #2B87F0 100%);
  border-radius: var(--radius);
  padding: 28px 32px;
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 20px;
  position: relative; overflow: hidden;
  box-shadow: var(--shadow-blue);
}
.dash-welcome::before {
  content: ''; position: absolute;
  width: 320px; height: 320px; border-radius: 50%;
  background: rgba(255,255,255,0.06);
  top: -120px; right: -80px; pointer-events: none;
}
.dash-welcome::after {
  content: ''; position: absolute;
  width: 180px; height: 180px; border-radius: 50%;
  background: rgba(255,255,255,0.04);
  bottom: -60px; right: 120px; pointer-events: none;
}
.dash-welcome-text { position: relative; z-index: 1; }
.dash-welcome-text h1 {
  font-family: 'Nunito', sans-serif;
  font-size: 22px; font-weight: 900; color: #fff; margin: 0 0 5px;
}
.dash-welcome-text p { font-size: 13.5px; color: rgba(255,255,255,0.78); margin: 0; }
.dash-welcome-actions { display: flex; align-items: center; gap: 10px; position: relative; z-index: 1; flex-wrap: wrap; }

.btn-wh {
  height: 38px; padding: 0 18px;
  background: rgba(255,255,255,0.15); color: #fff;
  border: 1.5px solid rgba(255,255,255,0.3); border-radius: 7px;
  font-size: 13px; font-weight: 600;
  font-family: 'Source Sans 3', sans-serif;
  cursor: pointer; display: inline-flex; align-items: center; gap: 7px;
  text-decoration: none; white-space: nowrap;
  backdrop-filter: blur(6px);
  transition: background .18s;
}
.btn-wh:hover { background: rgba(255,255,255,0.25); }
.btn-wh-solid {
  background: #fff; color: var(--blue-dark);
  border-color: #fff;
}
.btn-wh-solid:hover { background: #f0f6ff; }

/* ── Section label ── */
.dash-section-label {
  font-size: 11px; font-weight: 800;
  color: var(--text-s); text-transform: uppercase; letter-spacing: 0.8px;
  margin-bottom: 14px;
  display: flex; align-items: center; gap: 8px;
}
.dash-section-label::after {
  content: ''; flex: 1; height: 1px; background: var(--border);
}

/* ── Stats Grid ── */
.dash-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
}
@media (max-width: 860px) { .dash-stats { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 520px)  { .dash-stats { grid-template-columns: 1fr; } }

.stat-card {
  background: var(--card);
  border: 1.5px solid var(--border);
  border-radius: var(--radius);
  padding: 22px 22px 18px;
  display: flex; flex-direction: column; gap: 14px;
  position: relative; overflow: hidden;
  transition: box-shadow .2s, transform .2s;
  box-shadow: var(--shadow-xs);
}
.stat-card:hover { box-shadow: var(--shadow-blue); transform: translateY(-2px); }
.stat-card::after {
  content: ''; position: absolute;
  bottom: 0; left: 0; right: 0; height: 3px;
  background: linear-gradient(90deg, var(--blue-dark), var(--blue));
  opacity: 0; transition: opacity .2s;
}
.stat-card:hover::after { opacity: 1; }

.stat-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.stat-label {
  font-size: 12px; font-weight: 700; color: var(--text-m);
  text-transform: uppercase; letter-spacing: 0.55px; margin: 0;
}
.stat-icon-wrap {
  width: 42px; height: 42px; border-radius: 10px;
  background: var(--blue-light); color: var(--blue);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.stat-icon-wrap.revenue { background: #FEF3C7; color: #D97706; }
.stat-icon-wrap.payroll { background: #EDE9FE; color: #7C3AED; }

.stat-value {
  font-family: 'Nunito', sans-serif;
  font-size: 30px; font-weight: 900;
  color: var(--text-h); line-height: 1;
  letter-spacing: -0.5px; margin: 0;
}
.stat-footer { display: flex; align-items: center; justify-content: space-between; }
.stat-sub { font-size: 12px; color: var(--text-s); }
.stat-trend {
  font-size: 11.5px; font-weight: 700;
  padding: 3px 8px; border-radius: 20px;
  background: var(--green-bg); color: var(--green);
  white-space: nowrap;
}
.stat-trend.warn { background: var(--amber-bg); color: var(--amber); }

/* ── Quick Links ── */
.dash-quicklinks {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
}
@media (max-width: 760px) { .dash-quicklinks { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 400px) { .dash-quicklinks { grid-template-columns: 1fr; } }

.ql-card {
  background: var(--card);
  border: 1.5px solid var(--border);
  border-radius: var(--radius);
  padding: 18px 16px;
  display: flex; flex-direction: column; align-items: flex-start; gap: 10px;
  text-decoration: none;
  transition: box-shadow .18s, border-color .18s, transform .18s;
  box-shadow: var(--shadow-xs);
}
.ql-card:hover { box-shadow: var(--shadow-md); border-color: var(--blue); transform: translateY(-2px); }
.ql-icon {
  width: 40px; height: 40px; border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
}
.ql-icon.emp  { background: var(--blue-light);  color: var(--blue); }
.ql-icon.pay  { background: #EDE9FE;             color: #7C3AED; }
.ql-icon.att  { background: var(--green-bg);     color: var(--green); }
.ql-icon.rep  { background: var(--amber-bg);     color: var(--amber); }
.ql-label { font-size: 13px; font-weight: 700; color: var(--text-h); }
.ql-desc  { font-size: 12px; color: var(--text-s); margin-top: -4px; }

/* ── Bottom grid: table + activity ── */
.dash-bottom {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 20px;
  align-items: start;
}
@media (max-width: 980px) { .dash-bottom { grid-template-columns: 1fr; } }

/* ── Table card ── */
.dash-card {
  background: var(--card);
  border: 1.5px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow-xs);
  overflow: hidden;
}
.dash-card-head {
  padding: 16px 22px;
  border-bottom: 1.5px solid var(--border);
  background: var(--surface);
  display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
}
.dash-card-title {
  font-family: 'Nunito', sans-serif;
  font-size: 14.5px; font-weight: 800; color: var(--text-h);
  display: flex; align-items: center; gap: 8px; margin: 0;
}
.dash-card-title .dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: linear-gradient(135deg, var(--blue-dark), var(--blue));
}
.dash-card-sub { font-size: 12px; color: var(--text-s); margin-top: 1px; }

.btn-sm-outline {
  height: 32px; padding: 0 14px;
  background: transparent; color: var(--blue);
  border: 1.5px solid var(--blue-light); border-radius: 6px;
  font-size: 12.5px; font-weight: 700;
  font-family: 'Source Sans 3', sans-serif;
  text-decoration: none; cursor: pointer;
  display: inline-flex; align-items: center; gap: 5px;
  transition: background .15s, border-color .15s;
}
.btn-sm-outline:hover { background: var(--blue-light); border-color: var(--blue); }

/* Table */
.dash-table-wrap { overflow-x: auto; }
.dash-table { width: 100%; border-collapse: collapse; font-size: 13.5px; min-width: 560px; }
.dash-table thead tr { background: var(--surface); border-bottom: 2px solid var(--border); }
.dash-table thead th {
  padding: 11px 18px; text-align: left;
  font-size: 11px; font-weight: 700; color: var(--text-m);
  text-transform: uppercase; letter-spacing: 0.55px; white-space: nowrap;
}
.dash-table tbody tr { border-bottom: 1px solid #F1F5F9; transition: background .12s; }
.dash-table tbody tr:last-child { border-bottom: none; }
.dash-table tbody tr:hover { background: #F8FAFD; }
.dash-table td { padding: 14px 18px; color: var(--text-b); vertical-align: middle; }

.emp-cell { display: flex; align-items: center; gap: 11px; }
.emp-av {
  width: 36px; height: 36px; border-radius: 9px; flex-shrink: 0;
  background: linear-gradient(135deg, var(--blue-dark), var(--blue));
  color: #fff; font-family: 'Nunito', sans-serif;
  font-size: 14px; font-weight: 800;
  display: flex; align-items: center; justify-content: center;
}
.emp-name-txt { font-weight: 700; color: var(--text-h); font-size: 13.5px; }
.emp-role-txt { font-size: 12px; color: var(--text-s); margin-top: 1px; }

.sal-amt  { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 13.5px; color: var(--text-h); }
.sal-per  { font-size: 11px; color: var(--text-s); margin-left: 1px; }

/* Status badges */
.st-badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 10px; border-radius: 20px;
  font-size: 12px; font-weight: 700; white-space: nowrap;
}
.st-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.badge-active     { background: var(--green-bg); color: var(--green); }
.badge-onboarding { background: var(--amber-bg); color: var(--amber); }

/* Action btn */
.btn-manage {
  height: 30px; padding: 0 13px;
  border: 1.5px solid var(--border); border-radius: 6px;
  background: #fff; color: var(--text-m);
  font-size: 12px; font-weight: 600;
  font-family: 'Source Sans 3', sans-serif;
  cursor: pointer; display: inline-flex; align-items: center; gap: 5px;
  text-decoration: none;
  transition: border-color .14s, color .14s, background .14s;
}
.btn-manage:hover { border-color: var(--blue); color: var(--blue); background: var(--blue-xlight); }

/* ── Activity sidebar ── */
.act-list { padding: 6px 0; }
.act-item {
  display: flex; gap: 13px; align-items: flex-start;
  padding: 13px 20px; border-bottom: 1px solid #F1F5F9;
  transition: background .12s;
}
.act-item:last-child { border-bottom: none; }
.act-item:hover { background: var(--surface); }
.act-dot {
  width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
}
.act-dot.join  { background: var(--green-bg);  color: var(--green); }
.act-dot.pay   { background: #EDE9FE;           color: #7C3AED; }
.act-dot.alert { background: var(--amber-bg);   color: var(--amber); }
.act-dot.edit  { background: var(--blue-light); color: var(--blue); }
.act-text  { font-size: 13px; color: var(--text-b); line-height: 1.45; }
.act-text strong { color: var(--text-h); font-weight: 700; }
.act-time  { font-size: 11.5px; color: var(--text-s); margin-top: 3px; }

/* Empty state */
.dash-empty { text-align: center; padding: 48px 24px; color: var(--text-s); }
.dash-empty svg { opacity: .35; margin-bottom: 12px; }
.dash-empty strong { display: block; font-size: 15px; color: var(--text-m); margin-bottom: 4px; }
.dash-empty p { font-size: 13px; }

/* Animations */
@keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
.dash-welcome   { animation: fadeUp .35s ease both; }
.stat-card      { animation: fadeUp .35s ease both; }
.stat-card:nth-child(1) { animation-delay: .05s; }
.stat-card:nth-child(2) { animation-delay: .10s; }
.stat-card:nth-child(3) { animation-delay: .15s; }
.ql-card        { animation: fadeUp .35s ease both; }
.ql-card:nth-child(1) { animation-delay: .18s; }
.ql-card:nth-child(2) { animation-delay: .22s; }
.ql-card:nth-child(3) { animation-delay: .26s; }
.ql-card:nth-child(4) { animation-delay: .30s; }
.dash-card      { animation: fadeUp .35s .32s ease both; }
</style>

<div class="dash">

  <!-- ══ Welcome Banner ══ -->
  <div class="dash-welcome">
    <div class="dash-welcome-text">
      <h1>
        <?php
          $hour = (int)date('H');
          echo $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
          echo ', ' . (isset($_SESSION['user_name']) ? htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) : 'Admin');
        ?>
      </h1>
      <p>Here's what's happening at CodeXentric today — <?php echo date('l, d F Y'); ?></p>
    </div>
    <div class="dash-welcome-actions">
      <a href="manage_employee.php" class="btn-wh">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
        Add Employee
      </a>
      <a href="payroll.php" class="btn-wh btn-wh-solid">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        Run Payroll
      </a>
    </div>
  </div>

  <!-- ══ Stats ══ -->
  <div>
    <div class="dash-section-label">Overview</div>
    <div class="dash-stats">
      <?php foreach ($stats as $s): ?>
      <div class="stat-card">
        <div class="stat-card-top">
          <p class="stat-label"><?php echo htmlspecialchars($s['label']); ?></p>
          <div class="stat-icon-wrap <?php echo $s['label'] === 'Current Revenue' ? 'revenue' : ($s['label'] === 'Monthly Payroll' ? 'payroll' : ''); ?>">
            <?php if ($s['label'] === 'Total Headcount'): ?>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <?php elseif ($s['label'] === 'Current Revenue'): ?>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <?php else: ?>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            <?php endif; ?>
          </div>
        </div>
        <div class="stat-value"><?php echo htmlspecialchars($s['value']); ?></div>
        <div class="stat-footer">
          <span class="stat-sub"><?php echo htmlspecialchars($s['sub']); ?></span>
          <span class="stat-trend <?php echo strpos($s['trend'], 'pending') !== false ? 'warn' : ''; ?>">
            <?php echo htmlspecialchars($s['trend']); ?>
          </span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ══ Quick Links ══ -->
  <div>
    <div class="dash-section-label">Quick Access</div>
    <div class="dash-quicklinks">
      <a href="manage_employee.php" class="ql-card">
        <div class="ql-icon emp">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <span class="ql-label">Employees</span>
        <span class="ql-desc">Manage team members</span>
      </a>
      <a href="payroll.php" class="ql-card">
        <div class="ql-icon pay">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        </div>
        <span class="ql-label">Payroll</span>
        <span class="ql-desc">Process & disburse</span>
      </a>
      <a href="attendance_record.php" class="ql-card">
        <div class="ql-icon att">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <span class="ql-label">Attendance</span>
        <span class="ql-desc">View records</span>
      </a>
      <a href="settings.php" class="ql-card">
        <div class="ql-icon rep">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        </div>
        <span class="ql-label">Settings</span>
        <span class="ql-desc">System preferences</span>
      </a>
    </div>
  </div>

  <!-- ══ Bottom: Table + Activity ══ -->
  <div class="dash-bottom">

    <!-- Team Directory -->
    <div class="dash-card">
      <div class="dash-card-head">
        <div>
          <h2 class="dash-card-title"><span class="dot"></span>Team Directory</h2>
          <p class="dash-card-sub">Manage and monitor your team members</p>
        </div>
        <a href="manage_employee.php" class="btn-sm-outline">
          View All
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
      </div>

      <?php if (!empty($team_members)): ?>
      <div class="dash-table-wrap">
        <table class="dash-table" aria-label="Team members directory">
          <thead>
            <tr>
              <th>Employee</th>
              <th>Salary</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($team_members as $employee): ?>
            <tr class="employee-row">
              <td>
                <div class="emp-cell">
                  <div class="emp-av" aria-hidden="true"><?php echo strtoupper(substr($employee['name'], 0, 1)); ?></div>
                  <div>
                    <div class="emp-name-txt"><?php echo htmlspecialchars($employee['name']); ?></div>
                    <div class="emp-role-txt"><?php echo htmlspecialchars($employee['role']); ?></div>
                  </div>
                </div>
              </td>
              <td>
                <span class="sal-amt"><?php echo htmlspecialchars($employee['salary']); ?></span>
                <span class="sal-per">/mo</span>
              </td>
              <td>
                <span class="st-badge <?php echo htmlspecialchars($employee['badge']); ?>"
                      role="status"
                      aria-label="Status: <?php echo htmlspecialchars($employee['status']); ?>">
                  <?php echo htmlspecialchars($employee['status']); ?>
                </span>
              </td>
              <td>
                <a href="manage_employee.php"
                   class="btn-manage"
                   aria-label="Manage <?php echo htmlspecialchars($employee['name']); ?>">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Manage
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="dash-empty">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        <strong>No Team Members</strong>
        <p>There are currently no team members to display.</p>
      </div>
      <?php endif; ?>
    </div>

    <!-- Recent Activity -->
    <div class="dash-card">
      <div class="dash-card-head">
        <div>
          <h2 class="dash-card-title"><span class="dot"></span>Recent Activity</h2>
          <p class="dash-card-sub">Latest system events</p>
        </div>
      </div>
      <div class="act-list">
        <div class="act-item">
          <div class="act-dot join">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
          </div>
          <div>
            <div class="act-text"><strong>Khurum</strong> joined as Frontend Developer</div>
            <div class="act-time">Today, 09:14 AM</div>
          </div>
        </div>
        <div class="act-item">
          <div class="act-dot pay">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          </div>
          <div>
            <div class="act-text">March payroll <strong>processed</strong> — Rs 4,85,200</div>
            <div class="act-time">Yesterday, 06:30 PM</div>
          </div>
        </div>
        <div class="act-item">
          <div class="act-dot edit">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </div>
          <div>
            <div class="act-text"><strong>Hammad Ali</strong>'s allowance updated</div>
            <div class="act-time">22 Apr, 03:45 PM</div>
          </div>
        </div>
        <div class="act-item">
          <div class="act-dot alert">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </div>
          <div>
            <div class="act-text"><strong>3 salaries</strong> pending disbursement</div>
            <div class="act-time">22 Apr, 10:00 AM</div>
          </div>
        </div>
        <div class="act-item">
          <div class="act-dot join">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <div>
            <div class="act-text"><strong>Abdullah</strong> checked in at 08:52 AM</div>
            <div class="act-time">Today, 08:52 AM</div>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.dash-bottom -->

</div><!-- /.dash -->

<?php include_once "../includes/footer.php"; ?>