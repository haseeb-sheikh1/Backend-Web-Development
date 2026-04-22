<?php
    session_start();

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }

    // ── Handle "Give Salary" POST action ──────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['give_salary'])) {
        $emp_index = (int)$_POST['emp_index'];
        // In production: update DB status to 'Paid' for this employee's payroll record
        // e.g. $conn->query("UPDATE payroll SET status='Paid' WHERE employee_id=$emp_id AND period='2026-04'");
        $_SESSION['salary_given'][$emp_index] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    // ── Handle "Reset" (for demo purposes) ───────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_demo'])) {
        unset($_SESSION['salary_given']);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    $user_role   = "admin";
    $current_page = "payroll";
    $extra_css   = "payroll";
    $title       = "Payroll Management - CodeXentric";
    include_once "../includes/header.php";

    $payroll_summary = [
        ["label" => "Gross Total",      "value" => "Rs 5,20,000", "sub" => "Total gross salaries",     "icon" => "gross",   "trend" => "+8.2% vs last month"],
        ["label" => "Total Deductions", "value" => "Rs 34,800",   "sub" => "Combined deductions",       "icon" => "deduct",  "trend" => "-2.1% vs last month"],
        ["label" => "Net Payable",      "value" => "Rs 4,85,200", "sub" => "Final disbursement amount", "icon" => "net",     "trend" => "+9.1% vs last month"],
    ];

    $salary_breakdown = [
        ["id" => 1, "name" => "Hammad Ali",  "bank" => "HBL – 123456789",       "base" => "85,000",  "allowance" => "5,000",  "deduction" => "4,200", "net" => "85,800",  "status_default" => "Draft"],
        ["id" => 2, "name" => "Abdullah",    "bank" => "Meezan – 987654321",     "base" => "65,000",  "allowance" => "2,000",  "deduction" => "1,500", "net" => "65,500",  "status_default" => "Draft"],
        ["id" => 3, "name" => "Khurum",      "bank" => "Alfalah – 456789123",    "base" => "55,000",  "allowance" => "0",      "deduction" => "8,000", "net" => "47,000",  "status_default" => "Calculated"],
    ];
?>

<style>
/* ════════════════════════════════════════════════
   Payroll — Production Grade  |  CodeXentric HRM
════════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&display=swap');

:root {
  --blue:       #1E6FD9;
  --blue-dark:  #1559B5;
  --blue-light: #EBF2FC;
  --green:      #059669;
  --green-bg:   #D1FAE5;
  --red:        #DC2626;
  --red-bg:     #FEE2E2;
  --amber:      #D97706;
  --amber-bg:   #FEF3C7;
  --slate:      #64748B;
  --border:     #E2E8F0;
  --surface:    #F8FAFC;
  --card:       #ffffff;
  --text-h:     #0F172A;
  --text-b:     #374151;
  --text-m:     #64748B;
  --radius:     12px;
  --shadow-sm:  0 1px 4px rgba(15,23,42,0.06);
  --shadow-md:  0 4px 20px rgba(15,23,42,0.09);
  --shadow-lg:  0 8px 32px rgba(21,89,181,0.14);
}

/* ── Page wrapper ── */
.pr-page { display: flex; flex-direction: column; gap: 28px; }

/* ── Page header ── */
.pr-header { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
.pr-header-left h1 { font-family: 'Nunito', sans-serif; font-size: 22px; font-weight: 800; color: var(--text-h); margin: 0 0 4px; }
.pr-breadcrumb { font-size: 12.5px; color: var(--text-m); display: flex; align-items: center; gap: 5px; }
.pr-breadcrumb a { color: var(--blue); text-decoration: none; }
.pr-breadcrumb a:hover { text-decoration: underline; }

/* Period pill */
.pr-period-wrap { display: flex; align-items: center; gap: 10px; }
.pr-period-label { font-size: 12.5px; font-weight: 600; color: var(--text-m); text-transform: uppercase; letter-spacing: 0.5px; }
.pr-period-select {
  height: 38px; padding: 0 36px 0 14px;
  border: 1.5px solid var(--border); border-radius: 8px;
  font-size: 13.5px; font-weight: 600; color: var(--text-h);
  background: var(--card); cursor: pointer; outline: none;
  appearance: none; -webkit-appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L6 6L11 1' stroke='%2364748B' stroke-width='1.8' stroke-linecap='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 12px center;
  transition: border-color .18s, box-shadow .18s;
}
.pr-period-select:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(30,111,217,.1); }

/* ── Summary cards ── */
.pr-summary-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}
@media (max-width: 860px) { .pr-summary-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 540px) { .pr-summary-grid { grid-template-columns: 1fr; } }

.pr-stat-card {
  background: var(--card);
  border-radius: var(--radius);
  padding: 22px 24px;
  box-shadow: var(--shadow-sm);
  border: 1.5px solid var(--border);
  display: flex; flex-direction: column; gap: 10px;
  position: relative; overflow: hidden;
  transition: box-shadow .2s, transform .2s;
}
.pr-stat-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-2px); }

/* coloured top stripe */
.pr-stat-card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
  background: linear-gradient(90deg, var(--blue-dark), var(--blue));
}
.pr-stat-card.deduct::before { background: linear-gradient(90deg, #b91c1c, #ef4444); }
.pr-stat-card.net::before    { background: linear-gradient(90deg, #047857, #10b981); }

.pr-stat-top { display: flex; align-items: center; justify-content: space-between; }
.pr-stat-label { font-size: 12.5px; font-weight: 700; color: var(--text-m); text-transform: uppercase; letter-spacing: 0.55px; }
.pr-stat-icon {
  width: 38px; height: 38px; border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
  background: var(--blue-light); color: var(--blue);
}
.pr-stat-card.deduct .pr-stat-icon { background: var(--red-bg);   color: var(--red); }
.pr-stat-card.net    .pr-stat-icon { background: var(--green-bg); color: var(--green); }

.pr-stat-value {
  font-family: 'Nunito', sans-serif;
  font-size: 26px; font-weight: 900; color: var(--text-h);
  line-height: 1.1; letter-spacing: -0.5px;
}
.pr-stat-card.deduct .pr-stat-value { color: var(--red); }
.pr-stat-card.net    .pr-stat-value { color: var(--green); }

.pr-stat-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 2px; }
.pr-stat-sub { font-size: 12px; color: var(--text-m); }
.pr-stat-trend {
  font-size: 11.5px; font-weight: 700; padding: 2px 8px;
  border-radius: 20px; background: var(--green-bg); color: var(--green);
}
.pr-stat-card.deduct .pr-stat-trend { background: var(--red-bg);   color: var(--red); }

/* ── Table card ── */
.pr-table-card {
  background: var(--card);
  border-radius: var(--radius);
  border: 1.5px solid var(--border);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}
.pr-table-head {
  padding: 18px 24px;
  border-bottom: 1.5px solid var(--border);
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
  background: var(--surface);
}
.pr-table-title {
  font-family: 'Nunito', sans-serif;
  font-size: 15px; font-weight: 800; color: var(--text-h);
  display: flex; align-items: center; gap: 9px;
}
.pr-table-title .dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: linear-gradient(135deg, var(--blue-dark), var(--blue));
}
.pr-count-badge {
  background: var(--blue-light); color: var(--blue);
  font-size: 12px; font-weight: 700;
  padding: 3px 10px; border-radius: 20px;
}

/* Responsive table */
.pr-table-scroll { overflow-x: auto; }
.pr-table {
  width: 100%; border-collapse: collapse;
  font-size: 13.5px; min-width: 700px;
}
.pr-table thead tr { background: var(--surface); border-bottom: 2px solid var(--border); }
.pr-table thead th {
  padding: 11px 16px; text-align: left;
  font-size: 11px; font-weight: 700; color: var(--text-m);
  text-transform: uppercase; letter-spacing: 0.6px; white-space: nowrap;
}
.pr-table tbody tr {
  border-bottom: 1px solid #F1F5F9;
  transition: background .12s;
}
.pr-table tbody tr:last-child { border-bottom: none; }
.pr-table tbody tr:hover { background: #F8FAFD; }
.pr-table td { padding: 15px 16px; vertical-align: middle; color: var(--text-b); }

/* Employee cell */
.emp-cell { display: flex; align-items: center; gap: 12px; }
.emp-avatar {
  width: 38px; height: 38px; border-radius: 10px;
  background: linear-gradient(135deg, var(--blue-dark) 0%, var(--blue) 100%);
  color: #fff; font-family: 'Nunito', sans-serif;
  font-size: 15px; font-weight: 800;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.emp-name { font-weight: 700; color: var(--text-h); font-size: 14px; line-height: 1.2; }
.emp-bank { font-size: 12px; color: var(--text-m); margin-top: 2px; }

/* Amount cells */
.amt { font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 14px; }
.amt-base    { color: var(--text-h); }
.amt-allow   { color: var(--green); }
.amt-deduct  { color: var(--red); }
.amt-net     { color: var(--blue-dark); font-size: 15px; }
.amt-prefix  { font-size: 11px; font-weight: 600; opacity: 0.7; }

/* Status badges */
.status-badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 11px; border-radius: 20px;
  font-size: 12px; font-weight: 700; white-space: nowrap;
}
.status-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.s-draft      { background: #F1F5F9; color: #64748B; }
.s-calculated { background: var(--blue-light); color: var(--blue-dark); }
.s-paid       { background: var(--green-bg); color: var(--green); }

/* Action buttons in table */
.tbl-actions { display: flex; align-items: center; gap: 8px; }
.btn-edit {
  height: 32px; padding: 0 14px;
  border: 1.5px solid var(--border); border-radius: 6px;
  background: #fff; color: var(--text-m);
  font-size: 12.5px; font-weight: 600;
  font-family: 'Source Sans 3', sans-serif;
  cursor: pointer; display: inline-flex; align-items: center; gap: 5px;
  text-decoration: none;
  transition: border-color .15s, color .15s;
}
.btn-edit:hover { border-color: var(--blue); color: var(--blue); }

.btn-give {
  height: 32px; padding: 0 14px;
  border: none; border-radius: 6px;
  background: linear-gradient(135deg, #047857, #10b981);
  color: #fff; font-size: 12.5px; font-weight: 700;
  font-family: 'Source Sans 3', sans-serif;
  cursor: pointer; display: inline-flex; align-items: center; gap: 5px;
  transition: opacity .15s, transform .1s;
  box-shadow: 0 2px 8px rgba(5,150,105,.25);
}
.btn-give:hover { opacity: .88; transform: translateY(-1px); }
.btn-give:active { transform: translateY(0); }

.btn-given {
  height: 32px; padding: 0 14px;
  border: 1.5px solid var(--green); border-radius: 6px;
  background: var(--green-bg); color: var(--green);
  font-size: 12.5px; font-weight: 700;
  font-family: 'Source Sans 3', sans-serif;
  cursor: default; display: inline-flex; align-items: center; gap: 5px;
}

/* ── Bottom action bar ── */
.pr-action-bar {
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 14px;
  background: var(--card);
  border: 1.5px solid var(--border);
  border-radius: var(--radius);
  padding: 16px 24px;
  box-shadow: var(--shadow-sm);
}
.pr-action-bar-left { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.btn-primary-lg {
  height: 42px; padding: 0 22px;
  background: linear-gradient(135deg, var(--blue-dark), var(--blue));
  color: #fff; border: none; border-radius: 8px;
  font-size: 14px; font-weight: 700;
  font-family: 'Source Sans 3', sans-serif;
  cursor: pointer; display: inline-flex; align-items: center; gap: 8px;
  box-shadow: 0 3px 10px rgba(21,89,181,.28);
  transition: box-shadow .18s, transform .1s;
}
.btn-primary-lg:hover { box-shadow: 0 5px 18px rgba(21,89,181,.4); transform: translateY(-1px); }

.btn-success-lg {
  height: 42px; padding: 0 22px;
  background: linear-gradient(135deg, #047857, #10b981);
  color: #fff; border: none; border-radius: 8px;
  font-size: 14px; font-weight: 700;
  font-family: 'Source Sans 3', sans-serif;
  cursor: pointer; display: inline-flex; align-items: center; gap: 8px;
  box-shadow: 0 3px 10px rgba(5,150,105,.28);
  transition: box-shadow .18s, transform .1s;
}
.btn-success-lg:hover { box-shadow: 0 5px 18px rgba(5,150,105,.4); transform: translateY(-1px); }

.btn-ghost {
  height: 42px; padding: 0 18px;
  background: transparent; color: var(--text-m);
  border: 1.5px solid var(--border); border-radius: 8px;
  font-size: 13px; font-weight: 600;
  font-family: 'Source Sans 3', sans-serif;
  cursor: pointer; display: inline-flex; align-items: center; gap: 7px;
  transition: border-color .15s, color .15s;
}
.btn-ghost:hover { border-color: var(--blue); color: var(--blue); }

.pr-paid-count {
  font-size: 13px; color: var(--text-m); font-weight: 500;
}
.pr-paid-count strong { color: var(--green); font-weight: 700; }

/* Fade in */
@keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
.pr-stat-card { animation: fadeUp .3s ease both; }
.pr-stat-card:nth-child(1) { animation-delay: .0s; }
.pr-stat-card:nth-child(2) { animation-delay: .07s; }
.pr-stat-card:nth-child(3) { animation-delay: .14s; }
.pr-table-card { animation: fadeUp .3s .2s ease both; }
.pr-action-bar { animation: fadeUp .3s .28s ease both; }

/* Toast notification */
.pr-toast {
  position: fixed; bottom: 28px; right: 28px; z-index: 9999;
  background: #0F172A; color: #fff;
  padding: 14px 20px; border-radius: 10px;
  font-size: 13.5px; font-weight: 600;
  display: flex; align-items: center; gap: 10px;
  box-shadow: 0 8px 28px rgba(0,0,0,.25);
  transform: translateY(80px); opacity: 0;
  transition: transform .35s cubic-bezier(.34,1.56,.64,1), opacity .3s;
}
.pr-toast.show { transform: translateY(0); opacity: 1; }
.pr-toast svg { color: #10b981; }
</style>

<div class="pr-page">

  <!-- ── Page Header ── -->
  <div class="pr-header">
    <div class="pr-header-left">
      <h1>Payroll Management</h1>
      <nav class="pr-breadcrumb">
        <a href="administrator_dashboard.php">Dashboard</a> ›
        <span>Payroll</span>
      </nav>
    </div>
    <div class="pr-period-wrap">
      <span class="pr-period-label">Period</span>
      <select class="pr-period-select" aria-label="Select payroll period">
        <option value="2026-04">April 2026</option>
        <option value="2026-03">March 2026</option>
        <option value="2026-02">February 2026</option>
        <option value="2026-01">January 2026</option>
      </select>
    </div>
  </div>

  <!-- ── Summary Cards ── -->
  <div class="pr-summary-grid">

    <!-- Gross -->
    <div class="pr-stat-card gross">
      <div class="pr-stat-top">
        <span class="pr-stat-label">Gross Total</span>
        <div class="pr-stat-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
          </svg>
        </div>
      </div>
      <div class="pr-stat-value">Rs 5,20,000</div>
      <div class="pr-stat-footer">
        <span class="pr-stat-sub">Total gross salaries</span>
        <span class="pr-stat-trend">↑ 8.2%</span>
      </div>
    </div>

    <!-- Deductions -->
    <div class="pr-stat-card deduct">
      <div class="pr-stat-top">
        <span class="pr-stat-label">Total Deductions</span>
        <div class="pr-stat-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/>
          </svg>
        </div>
      </div>
      <div class="pr-stat-value">Rs 34,800</div>
      <div class="pr-stat-footer">
        <span class="pr-stat-sub">Combined deductions</span>
        <span class="pr-stat-trend" style="background:var(--red-bg);color:var(--red);">↓ 2.1%</span>
      </div>
    </div>

    <!-- Net -->
    <div class="pr-stat-card net">
      <div class="pr-stat-top">
        <span class="pr-stat-label">Net Payable</span>
        <div class="pr-stat-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
      </div>
      <div class="pr-stat-value">Rs 4,85,200</div>
      <div class="pr-stat-footer">
        <span class="pr-stat-sub">Final disbursement amount</span>
        <span class="pr-stat-trend">↑ 9.1%</span>
      </div>
    </div>

  </div>

  <!-- ── Salary Breakdown Table ── -->
  <div class="pr-table-card">
    <div class="pr-table-head">
      <div class="pr-table-title">
        <span class="dot"></span>
        Employee Salary Breakdown
      </div>
      <span class="pr-count-badge"><?php echo count($salary_breakdown); ?> Employees</span>
    </div>
    <div class="pr-table-scroll">
      <table class="pr-table" aria-label="Employee salary breakdown">
        <thead>
          <tr>
            <th>Employee</th>
            <th>Base Salary</th>
            <th>Allowances</th>
            <th>Deductions</th>
            <th>Net Salary</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($salary_breakdown as $i => $row):
            $is_paid = isset($_SESSION['salary_given'][$i]) && $_SESSION['salary_given'][$i];
            if ($is_paid) {
                $status = 'Paid';
            } else {
                $status = $row['status_default'];
            }
            $badge_class = $status === 'Paid' ? 's-paid' : ($status === 'Calculated' ? 's-calculated' : 's-draft');
          ?>
          <tr>
            <!-- Employee -->
            <td>
              <div class="emp-cell">
                <div class="emp-avatar"><?php echo strtoupper(substr($row['name'], 0, 1)); ?></div>
                <div>
                  <div class="emp-name"><?php echo htmlspecialchars($row['name']); ?></div>
                  <div class="emp-bank"><?php echo htmlspecialchars($row['bank']); ?></div>
                </div>
              </div>
            </td>

            <!-- Base -->
            <td>
              <span class="amt amt-base">
                <span class="amt-prefix">Rs </span><?php echo $row['base']; ?>
              </span>
            </td>

            <!-- Allowance -->
            <td>
              <span class="amt amt-allow">
                <?php echo $row['allowance'] !== '0' ? '+' : ''; ?>
                <span class="amt-prefix">Rs </span><?php echo $row['allowance']; ?>
              </span>
            </td>

            <!-- Deduction -->
            <td>
              <span class="amt amt-deduct">
                −<span class="amt-prefix">Rs </span><?php echo $row['deduction']; ?>
              </span>
            </td>

            <!-- Net -->
            <td>
              <span class="amt amt-net">
                <span class="amt-prefix">Rs </span><?php echo $row['net']; ?>
              </span>
            </td>

            <!-- Status -->
            <td>
              <span class="status-badge <?php echo $badge_class; ?>">
                <?php echo $status; ?>
              </span>
            </td>

            <!-- Actions -->
            <td>
              <div class="tbl-actions">
                <a href="edit_adjustments.php?id=<?php echo $row['id']; ?>" class="btn-edit">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                  Edit
                </a>

                <?php if ($is_paid): ?>
                  <span class="btn-given">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Salary Given
                  </span>
                <?php else: ?>
                  <form method="POST" style="display:inline;" onsubmit="return confirmGive('<?php echo htmlspecialchars($row['name']); ?>')">
                    <input type="hidden" name="emp_index" value="<?php echo $i; ?>">
                    <button type="submit" name="give_salary" class="btn-give">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                      </svg>
                      Give Salary
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── Bottom Action Bar ── -->
  <div class="pr-action-bar">
    <div class="pr-action-bar-left">
      <button class="btn-primary-lg" onclick="window.print()">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/>
          <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10,9 9,9 8,9"/>
        </svg>
        Download PDF Report
      </button>

      <form method="POST" style="display:inline;" onsubmit="return confirm('Process all unpaid salaries?')">
        <button type="submit" name="process_all" class="btn-success-lg">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          Process All Salaries
        </button>
      </form>

      <!-- Demo reset -->
      <form method="POST" style="display:inline;">
        <button type="submit" name="reset_demo" class="btn-ghost">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.95"/>
          </svg>
          Reset Demo
        </button>
      </form>
    </div>

    <?php
      $paid_count = 0;
      foreach ($salary_breakdown as $i => $r) {
          if (isset($_SESSION['salary_given'][$i]) && $_SESSION['salary_given'][$i]) $paid_count++;
      }
    ?>
    <span class="pr-paid-count">
      <strong><?php echo $paid_count; ?></strong> / <?php echo count($salary_breakdown); ?> salaries disbursed
    </span>
  </div>

</div>

<!-- Toast -->
<div class="pr-toast" id="prToast">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
  <span id="prToastMsg">Salary marked as given.</span>
</div>

<script>
function confirmGive(name) {
  return confirm('Mark salary as given for ' + name + '?\nThis action will update their status to "Salary Given".');
}

// Show toast if salary was just given (detect via URL or session flag via PHP)
<?php if (isset($_SESSION['just_paid'])): unset($_SESSION['just_paid']); ?>
document.addEventListener('DOMContentLoaded', function() {
  const t = document.getElementById('prToast');
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3500);
});
<?php endif; ?>
</script>

<?php include_once "../includes/footer.php"; ?>