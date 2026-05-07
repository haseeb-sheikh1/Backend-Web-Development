<?php
session_start();

// 1. Role Protection - Only accessible to Admin (role_id == '1')
if (!isset($_SESSION['email']) || $_SESSION['role_id'] != '1') {
    header("Location: login.php");
    exit();
}

$current_page = "expense_reports";
$extra_css    = "expenses";
$title        = "Expense Reports";

require_once '../pages/database.php';
$db = new Database();
$conn = $db->getConnection();

$upload_dir = '../assets/uploads/receipts/';

$success_message = "";
$error_message = "";

// Delete Action on Reports page
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    // Get file attachment first to delete it
    $file_stmt = $conn->prepare("SELECT attachment_path FROM expenses WHERE id = ?");
    $file_stmt->bind_param("i", $delete_id);
    $file_stmt->execute();
    $file_res = $file_stmt->get_result();
    if ($file_res->num_rows > 0) {
        $file_path = $file_res->fetch_assoc()['attachment_path'];
        if ($file_path && file_exists($upload_dir . $file_path)) {
            unlink($upload_dir . $file_path);
        }
    }
    $file_stmt->close();

    $del_stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $del_stmt->bind_param("i", $delete_id);
    if ($del_stmt->execute()) {
        $success_message = "Expense deleted successfully.";
    } else {
        $error_message = "Failed to delete expense.";
    }
    $del_stmt->close();
}

// ── Pull query params matching Salary Reports design ──
$report_type    = isset($_GET['report_type']) ? $_GET['report_type']       : 'monthly';
$selected_month = isset($_GET['month'])       ? $_GET['month']             : date('Y-m');
$selected_year  = isset($_GET['year'])        ? intval($_GET['year'])      : intval(date('Y'));

$report_generated = isset($_GET['report_type']);

// ─── CALCULATE SUMMARY METRICS (FOR TOP CARDS) ───
$monthly_spend = 0;
$pending_payments = 0;
$highest_expense_cat = "N/A";
$highest_expense_amt = 0;
$burn_rate = 0;

if ($report_type === 'monthly') {
    // 1. Total Monthly Spend (Sum of all expenses for selected month)
    $sum_month_stmt = $conn->prepare("SELECT SUM(amount) as total FROM expenses WHERE DATE_FORMAT(bill_date, '%Y-%m') = ?");
    $sum_month_stmt->bind_param("s", $selected_month);
    $sum_month_stmt->execute();
    $monthly_spend = floatval($sum_month_stmt->get_result()->fetch_assoc()['total']);
    $sum_month_stmt->close();

    // 2. Pending Payments for the month
    $pending_stmt = $conn->prepare("SELECT SUM(amount) as total FROM expenses WHERE status = 'Unpaid' AND DATE_FORMAT(bill_date, '%Y-%m') = ?");
    $pending_stmt->bind_param("s", $selected_month);
    $pending_stmt->execute();
    $pending_payments = floatval($pending_stmt->get_result()->fetch_assoc()['total']);
    $pending_stmt->close();

    // 3. Highest Category for the month
    $highest_stmt = $conn->prepare("SELECT ec.category_name, SUM(e.amount) as total 
                                  FROM expenses e 
                                  JOIN expense_categories ec ON e.category_id = ec.id 
                                  WHERE DATE_FORMAT(e.bill_date, '%Y-%m') = ?
                                  GROUP BY e.category_id 
                                  ORDER BY total DESC LIMIT 1");
    $highest_stmt->bind_param("s", $selected_month);
    $highest_stmt->execute();
    $highest_res = $highest_stmt->get_result();
    if ($highest_res->num_rows > 0) {
        $row = $highest_res->fetch_assoc();
        $highest_expense_cat = $row['category_name'];
        $highest_expense_amt = floatval($row['total']);
    }
    $highest_stmt->close();

    // 4. Burn Rate for the month (Payroll + Operating Expenses)
    $payroll_stmt = $conn->prepare("SELECT SUM(net_payable_rs) as total FROM payroll WHERE DATE_FORMAT(processed_at, '%Y-%m') = ?");
    $payroll_stmt->bind_param("s", $selected_month);
    $payroll_stmt->execute();
    $payroll_amt = floatval($payroll_stmt->get_result()->fetch_assoc()['total']);
    $payroll_stmt->close();
    if ($payroll_amt == 0) {
        $emp_salary_stmt = $conn->query("SELECT SUM(base_salary_rs) as total FROM employees WHERE status = 'Active'");
        $payroll_amt = floatval($emp_salary_stmt->fetch_assoc()['total']);
    }
    $burn_rate = $payroll_amt + $monthly_spend;
} else {
    // Yearly Summary Cards calculation
    $year_str = $selected_year . '%';
    
    // 1. Total Spend of the Year
    $sum_year_stmt = $conn->prepare("SELECT SUM(amount) as total FROM expenses WHERE bill_date LIKE ?");
    $sum_year_stmt->bind_param("s", $year_str);
    $sum_year_stmt->execute();
    $monthly_spend = floatval($sum_year_stmt->get_result()->fetch_assoc()['total']);
    $sum_year_stmt->close();

    // 2. Total Pending Payments of the Year
    $pending_stmt = $conn->prepare("SELECT SUM(amount) as total FROM expenses WHERE status = 'Unpaid' AND bill_date LIKE ?");
    $pending_stmt->bind_param("s", $year_str);
    $pending_stmt->execute();
    $pending_payments = floatval($pending_stmt->get_result()->fetch_assoc()['total']);
    $pending_stmt->close();

    // 3. Highest category of the Year
    $highest_stmt = $conn->prepare("SELECT ec.category_name, SUM(e.amount) as total 
                                  FROM expenses e 
                                  JOIN expense_categories ec ON e.category_id = ec.id 
                                  WHERE e.bill_date LIKE ?
                                  GROUP BY e.category_id 
                                  ORDER BY total DESC LIMIT 1");
    $highest_stmt->bind_param("s", $year_str);
    $highest_stmt->execute();
    $highest_res = $highest_stmt->get_result();
    if ($highest_res->num_rows > 0) {
        $row = $highest_res->fetch_assoc();
        $highest_expense_cat = $row['category_name'];
        $highest_expense_amt = floatval($row['total']);
    }
    $highest_stmt->close();

    // 4. Yearly Burn Rate
    $payroll_stmt = $conn->prepare("SELECT SUM(net_payable_rs) as total FROM payroll WHERE DATE_FORMAT(processed_at, '%Y') = ?");
    $year_only_str = (string)$selected_year;
    $payroll_stmt->bind_param("s", $year_only_str);
    $payroll_stmt->execute();
    $payroll_amt = floatval($payroll_stmt->get_result()->fetch_assoc()['total']);
    $payroll_stmt->close();
    if ($payroll_amt == 0) {
        $emp_salary_stmt = $conn->query("SELECT SUM(base_salary_rs) as total FROM employees WHERE status = 'Active'");
        $payroll_amt = floatval($emp_salary_stmt->fetch_assoc()['total']) * 12; // annualized fallback
    }
    $burn_rate = $payroll_amt + $monthly_spend;
}

// ─── QUERY DATA BASED ON SEARCH ───
$detailed_bills = [];
$yearly_months_summary = [];

if ($report_type === 'monthly') {
    // Get all bills included in selected month
    $stmt = $conn->prepare("SELECT e.*, ec.category_name 
                            FROM expenses e 
                            JOIN expense_categories ec ON e.category_id = ec.id 
                            WHERE DATE_FORMAT(e.bill_date, '%Y-%m') = ?
                            ORDER BY e.bill_date DESC");
    $stmt->bind_param("s", $selected_month);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) {
        $detailed_bills[] = $row;
    }
    $stmt->close();
} else {
    // Get 12 months summary for the selected year
    for ($m = 1; $m <= 12; $m++) {
        $month_code = sprintf('%04d-%02d', $selected_year, $m);
        $month_label = date('F Y', strtotime($month_code . "-01"));

        // General Operating Expenses
        $stmt = $conn->prepare("SELECT SUM(amount) as total FROM expenses WHERE DATE_FORMAT(bill_date, '%Y-%m') = ?");
        $stmt->bind_param("s", $month_code);
        $stmt->execute();
        $op_expense = floatval($stmt->get_result()->fetch_assoc()['total']);
        $stmt->close();

        // Salaries
        $stmt = $conn->prepare("SELECT SUM(net_payable_rs) as total FROM payroll WHERE DATE_FORMAT(processed_at, '%Y-%m') = ?");
        $stmt->bind_param("s", $month_code);
        $stmt->execute();
        $salaries = floatval($stmt->get_result()->fetch_assoc()['total']);
        $stmt->close();
        if ($salaries == 0) {
            $emp_salary_stmt = $conn->query("SELECT SUM(base_salary_rs) as total FROM employees WHERE status = 'Active'");
            $salaries = floatval($emp_salary_stmt->fetch_assoc()['total']);
        }

        // Count pending vs paid bills for status summary
        $stmt = $conn->prepare("SELECT COUNT(*) as count, status FROM expenses WHERE DATE_FORMAT(bill_date, '%Y-%m') = ? GROUP BY status");
        $stmt->bind_param("s", $month_code);
        $stmt->execute();
        $st_res = $stmt->get_result();
        $paid_count = 0;
        $unpaid_count = 0;
        while($st = $st_res->fetch_assoc()) {
            if ($st['status'] === 'Paid') $paid_count = $st['count'];
            if ($st['status'] === 'Unpaid') $unpaid_count = $st['count'];
        }
        $stmt->close();

        $yearly_months_summary[] = [
            'month_code'  => $month_code,
            'month_label' => $month_label,
            'op_expense'  => $op_expense,
            'salaries'    => $salaries,
            'total_flow'  => $op_expense + $salaries,
            'paid_count'  => $paid_count,
            'unpaid_count'=> $unpaid_count
        ];
    }
}

// ─── CHARTS DATA AGGREGATION ───
$dist_labels = [];
$dist_values = [];
if ($report_type === 'monthly') {
    $dist_stmt = $conn->prepare("SELECT ec.category_name, SUM(e.amount) as total 
                               FROM expenses e 
                               JOIN expense_categories ec ON e.category_id = ec.id 
                               WHERE DATE_FORMAT(e.bill_date, '%Y-%m') = ?
                               GROUP BY e.category_id");
    $dist_stmt->bind_param("s", $selected_month);
} else {
    $year_str = $selected_year . '%';
    $dist_stmt = $conn->prepare("SELECT ec.category_name, SUM(e.amount) as total 
                               FROM expenses e 
                               JOIN expense_categories ec ON e.category_id = ec.id 
                               WHERE e.bill_date LIKE ?
                               GROUP BY e.category_id");
    $dist_stmt->bind_param("s", $year_str);
}
$dist_stmt->execute();
$dist_res = $dist_stmt->get_result();
while($row = $dist_res->fetch_assoc()) {
    $dist_labels[] = $row['category_name'];
    $dist_values[] = floatval($row['total']);
}
$dist_stmt->close();

// Add Salaries to Distribution Chart
if ($payroll_amt > 0) {
    $dist_labels[] = 'Salaries (Payroll)';
    $dist_values[] = $payroll_amt;
}

// 6-Month Trend Data
$trend_months = [];
for ($i = 5; $i >= 0; $i--) {
    $trend_months[] = date('Y-m', strtotime("-$i months"));
}
$trend_labels = [];
$trend_values = [];
foreach ($trend_months as $m) {
    $month_name = date('M Y', strtotime($m . "-01"));
    $trend_labels[] = $month_name;
    
    $month_sum_stmt = $conn->prepare("SELECT SUM(amount) as total FROM expenses WHERE DATE_FORMAT(bill_date, '%Y-%m') = ?");
    $month_sum_stmt->bind_param("s", $m);
    $month_sum_stmt->execute();
    $tot = floatval($month_sum_stmt->get_result()->fetch_assoc()['total']);
    $trend_values[] = $tot;
    $month_sum_stmt->close();
}

include_once "../includes/header.php";
include_once "../includes/sidebar.php";
?>

<style>
/* ══════════════════════════════════════════
   Pill-Row Table Styles matching Salary Reports
   ══════════════════════════════════════════ */
.rep-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    margin-bottom: 24px;
    overflow: hidden;
    animation: fadeUp 0.3s ease both;
}

.rep-card-header {
    padding: 18px 24px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
}

.rep-card-header .icon-brand {
    color: var(--brand-orange);
    display: flex;
    align-items: center;
}

.rep-card-header h2 {
    font-size: 13px;
    font-weight: 800;
    color: #334155;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.rep-card-body { padding: 24px; }

/* Form grid elements styled exactly like Salary Reports */
.modern-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 24px;
}

.modern-field label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #475569;
    margin-bottom: 10px;
}

.modern-input, .modern-select {
    width: 100%;
    height: 44px;
    padding: 0 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    color: #1e293b;
    background: #fff;
    transition: all 0.2s;
}

.modern-input:focus, .modern-select:focus {
    border-color: var(--brand-green);
    box-shadow: 0 0 0 3px rgba(24, 109, 85, 0.15);
    outline: none;
}

.modern-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #f1f5f9;
}

.btn-primary-modern {
    background: var(--brand-green);
    color: #fff;
    border: none;
    padding: 10px 30px;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary-modern:hover {
    background: var(--brand-green-hover);
    transform: translateY(-1px);
}

.btn-secondary-modern {
    background: #fff;
    color: #64748b;
    border: 1px solid #e2e8f0;
    padding: 10px 30px;
    border-radius: 8px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-secondary-modern:hover { background: #f8fafc; }

.rep-results-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #f1f5f9;
}

.meta-person {
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 700;
    color: #334155;
    font-size: 14px;
}

.meta-person svg { color: var(--brand-orange); }

.meta-pill {
    background: #f1f5f9;
    color: #475569;
    padding: 6px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid #e2e8f0;
}

/* ── Pill-Row Table ── */
.rep-table {
    width: 100%;
    border-spacing: 0 12px;
    border-collapse: separate;
}

.rep-table thead th {
    padding: 12px 24px;
    text-align: left;
    font-size: 11px;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.rep-table tbody tr {
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    transition: all 0.2s;
}

.rep-table tbody tr:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.rep-table td {
    padding: 18px 24px;
    border: 1px solid #e2e8f0;
    border-left: none;
    border-right: none;
    font-size: 14px;
    color: #475569;
}

.rep-table td:first-child { 
    border-left: 1px solid #e2e8f0;
    border-radius: 50px 0 0 50px; 
    padding-left: 30px;
    font-weight: 700;
    color: #1e293b;
}

.rep-table td:last-child { 
    border-right: 1px solid #e2e8f0;
    border-radius: 0 50px 50px 0; 
    padding-right: 30px;
}

.val-net { color: var(--brand-orange); font-weight: 800; }
.val-positive { color: #059669; }
.val-negative { color: #ef4444; }

.rep-empty {
    text-align: center;
    padding: 56px 24px;
    color: #9CA3AF;
}
.rep-empty svg { margin-bottom: 16px; opacity: 0.4; }
.rep-empty p { font-size: 14px; margin: 0; }
.rep-empty strong { display: block; font-size: 16px; font-weight: 700; color: #6B7280; margin-bottom: 6px; }

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

<div class="expenses-container">

  <!-- Alert System -->
  <?php if (!empty($success_message)): ?>
    <div class="alert alert-success">✓ <?php echo htmlspecialchars($success_message); ?></div>
  <?php endif; ?>
  <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger">✗ <?php echo htmlspecialchars($error_message); ?></div>
  <?php endif; ?>


  <!-- Search / Filter Card (Styled exactly like Salary Reports) -->
  <div class="rep-card" style="margin-top: 15px;">
      <div class="rep-card-header">
          <div class="icon-brand">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </div>
          <h2>Search Expense Reports</h2>
      </div>
      <div class="rep-card-body">
          <form method="GET" action="expense_reports.php">
              <div class="modern-grid">

                  <!-- Report Type Dropdown -->
                  <div class="modern-field">
                      <label>Report Type</label>
                      <select name="report_type" id="report_type" class="modern-select" onchange="toggleFilterFields()" required>
                          <option value="monthly" <?php echo ($report_type === 'monthly') ? 'selected' : ''; ?>>Monthly Detailed Report</option>
                          <option value="yearly" <?php echo ($report_type === 'yearly') ? 'selected' : ''; ?>>Yearly Summary Report</option>
                      </select>
                  </div>

                  <!-- Month Picker -->
                  <div class="modern-field" id="month_field">
                      <label>Select Month</label>
                      <input type="month" name="month" class="modern-input" value="<?php echo htmlspecialchars($selected_month); ?>">
                  </div>

                  <!-- Year Picker -->
                  <div class="modern-field" id="year_field">
                      <label>Select Year</label>
                      <input type="number" name="year" class="modern-input" min="2000" max="2100" value="<?php echo htmlspecialchars($selected_year); ?>">
                  </div>

              </div>

              <!-- Action Buttons -->
              <div class="modern-actions">
                  <a href="expense_reports.php" class="btn-secondary-modern">Reset</a>
                  <button type="submit" class="btn-primary-modern">Generate Report</button>
              </div>
          </form>
      </div>
  </div>

  <?php if ($report_generated): ?>
  <!-- Summary Cards -->
  <div class="expenses-summary-grid" style="margin-bottom: 8px;">
    <div class="summary-card">
      <div class="summary-icon-wrapper">
        <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div class="summary-details">
        <h3><?php echo ($report_type === 'monthly') ? 'Total Monthly Spend' : 'Total Yearly Spend'; ?></h3>
        <p class="value">Rs <?php echo number_format($monthly_spend, 2); ?></p>
        <p class="subtext"><?php echo ($report_type === 'monthly') ? date('F Y', strtotime($selected_month . "-01")) : "Year " . $selected_year; ?></p>
      </div>
    </div>

    <div class="summary-card">
      <div class="summary-icon-wrapper">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </div>
      <div class="summary-details">
        <h3>Pending Payments</h3>
        <p class="value" style="color: #dc2626;">Rs <?php echo number_format($pending_payments, 2); ?></p>
        <p class="subtext"><?php echo ($report_type === 'monthly') ? 'For this month' : 'For this year'; ?></p>
      </div>
    </div>

    <div class="summary-card">
      <div class="summary-icon-wrapper">
        <svg viewBox="0 0 24 24"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
      </div>
      <div class="summary-details">
        <h3>Highest Category</h3>
        <p class="value" style="font-size: 19px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;">
          <?php echo htmlspecialchars($highest_expense_cat); ?>
        </p>
        <p class="subtext">Rs <?php echo number_format($highest_expense_amt, 2); ?></p>
      </div>
    </div>

    <div class="summary-card">
      <div class="summary-icon-wrapper">
        <svg viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
      </div>
      <div class="summary-details">
        <h3>Total Outflow (Burn)</h3>
        <p class="value" style="color: #186D55;">Rs <?php echo number_format($burn_rate, 2); ?></p>
        <p class="subtext">Payroll + Operating</p>
      </div>
    </div>
  </div>

  <!-- Reporting & Visualization (Graphs) -->
  <div class="expenses-charts-grid" style="margin-top: 10px; margin-bottom: 24px;">
    
    <!-- Donut Chart Card -->
    <div class="widget-card">
      <div class="widget-header">
        <span class="widget-header-title">
          <svg viewBox="0 0 24 24"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
          Distribution Breakdown
        </span>
      </div>
      <div class="widget-body">
        <div class="chart-wrapper">
          <canvas id="distributionChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Bar Chart Card -->
    <div class="widget-card">
      <div class="widget-header">
        <span class="widget-header-title">
          <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          6-Month Outflow Trend
        </span>
      </div>
      <div class="widget-body">
        <div class="chart-wrapper">
          <canvas id="trendChart"></canvas>
        </div>
      </div>
    </div>

  </div>

  <!-- Detailed Results Card (Stlyed exactly like Salary Reports with Pill-Rows) -->
  <div class="rep-card" style="margin-top: 24px;">
      <div class="rep-card-header">
          <div class="icon-brand">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
              </svg>
          </div>
          <h2>Report Details</h2>
      </div>
      
      <div class="rep-results-meta">
          <div class="meta-person">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <polygon points="12 2 2 22 22 22 12 2"/>
              </svg>
              <?php echo ($report_type === 'monthly') ? 'Monthly Detailed Bills' : 'Yearly Month-by-Month Summary'; ?>
          </div>
          <div class="meta-pill">
              <?php echo ($report_type === 'monthly') ? date('F Y', strtotime($selected_month . "-01")) : "Year " . $selected_year; ?>
          </div>
      </div>

      <div class="rep-card-body">
          <?php if ($report_type === 'monthly'): ?>
              <!-- MONTHLY VIEW: All individual bills -->
              <?php if (count($detailed_bills) > 0): ?>
                  <div style="overflow-x: auto;">
                      <table class="rep-table">
                          <thead>
                              <tr>
                                  <th>Date</th>
                                  <th>Category</th>
                                  <th>Invoice / Ref</th>
                                  <th>Description</th>
                                  <th>Status</th>
                                  <th>Amount</th>
                                  <th style="text-align:right;">Actions</th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php 
                                  $sum_amount = 0;
                                  foreach ($detailed_bills as $exp): 
                                      $sum_amount += $exp['amount'];
                                      $display_desc = htmlspecialchars($exp['description']);
                                      if (preg_match('/^\[(.*?)\]\s*(.*)$/', $exp['description'], $match)) {
                                          $display_desc = "<strong style='color:#1e293b; background:#f1f5f9; padding:2px 6px; border-radius:4px; font-size:11px; margin-right:6px;'>" . htmlspecialchars($match[1]) . "</strong>" . htmlspecialchars($match[2]);
                                      }
                              ?>
                              <tr>
                                  <td style="font-weight:700; color:#1e293b;"><?php echo date('M d, Y', strtotime($exp['bill_date'])); ?></td>
                                  <td><span style="font-weight: 700; color: #475569;"><?php echo htmlspecialchars($exp['category_name']); ?></span></td>
                                  <td><span style="font-family: monospace; font-weight: 600; color: #64748b;"><?php echo !empty($exp['invoice_number']) ? htmlspecialchars($exp['invoice_number']) : 'N/A'; ?></span></td>
                                  <td><div style="max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 13px; color: #64748b;"><?php echo $display_desc; ?></div></td>
                                  <td>
                                      <span class="status-badge <?php echo strtolower($exp['status']); ?>">
                                          <span class="dot"></span>
                                          <?php echo htmlspecialchars($exp['status']); ?>
                                      </span>
                                  </td>
                                  <td class="val-net">Rs <?php echo number_format($exp['amount'], 2); ?></td>
                                  <td style="text-align:right;">
                                      <div class="table-action-group" style="justify-content: flex-end; display: flex; gap: 8px;">
                                          <?php if (!empty($exp['attachment_path'])): ?>
                                              <button type="button" class="table-action-btn receipt" title="View Receipt" onclick="openReceiptModal('<?php echo htmlspecialchars($exp['attachment_path']); ?>')">
                                                  <svg viewBox="0 0 24 24" style="width:16px; height:16px; fill:none; stroke:currentColor; stroke-width:2.5;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                              </button>
                                          <?php endif; ?>
                                          <a href="expenses.php?edit=<?php echo $exp['id']; ?>" class="table-action-btn" title="Edit">
                                              <svg viewBox="0 0 24 24" style="width:16px; height:16px; fill:none; stroke:currentColor; stroke-width:2.5;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                          </a>
                                          <a href="expense_reports.php?delete=<?php echo $exp['id']; ?>&report_type=monthly&month=<?php echo $selected_month; ?>" class="table-action-btn delete" title="Delete" onclick="return confirm('Are you sure you want to delete this expense?')">
                                              <svg viewBox="0 0 24 24" style="width:16px; height:16px; fill:none; stroke:currentColor; stroke-width:2.5;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                          </a>
                                      </div>
                                  </td>
                              </tr>
                              <?php endforeach; ?>
                          </tbody>
                          <tfoot>
                              <tr style="background: #f8fafc; font-weight: 800;">
                                  <td style="color:#334155; border-radius: 50px 0 0 50px;">MONTH TOTAL</td>
                                  <td></td>
                                  <td></td>
                                  <td></td>
                                  <td></td>
                                  <td class="val-net" style="font-size:14px;">Rs <?php echo number_format($sum_amount, 2); ?></td>
                                  <td style="border-radius: 0 50px 50px 0;"></td>
                              </tr>
                          </tfoot>
                      </table>
                  </div>
              <?php else: ?>
                  <div class="rep-empty">
                      <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                      </svg>
                      <strong>No Expenses Found</strong>
                      <p>No recorded bills or operating expenses found for <?php echo date('F Y', strtotime($selected_month . "-01")); ?>.</p>
                  </div>
              <?php endif; ?>

          <?php else: ?>
              <!-- YEARLY VIEW: Month-by-month summaries -->
              <div style="overflow-x: auto;">
                  <table class="rep-table">
                      <thead>
                          <tr>
                              <th>Month</th>
                              <th>Operating Expenses</th>
                              <th>Payroll Salaries</th>
                              <th>Total Outflow</th>
                              <th>Payment Status Summary</th>
                              <th style="text-align:right;">Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php 
                              $sum_op = 0; $sum_sal = 0; $sum_flow = 0;
                              foreach ($yearly_months_summary as $sum_row): 
                                  $sum_op += $sum_row['op_expense'];
                                  $sum_sal += $sum_row['salaries'];
                                  $sum_flow += $sum_row['total_flow'];
                          ?>
                          <tr>
                              <td style="font-weight:700; color:#1e293b;"><?php echo $sum_row['month_label']; ?></td>
                              <td>Rs <?php echo number_format($sum_row['op_expense'], 2); ?></td>
                              <td>Rs <?php echo number_format($sum_row['salaries'], 2); ?></td>
                              <td class="val-net">Rs <?php echo number_format($sum_row['total_flow'], 2); ?></td>
                              <td>
                                  <?php if ($sum_row['op_expense'] == 0): ?>
                                      <span style="color: #94a3b8; font-weight: 600;">No bills</span>
                                  <?php else: ?>
                                      <div style="display: flex; gap: 6px; font-size: 12px; font-weight: 700;">
                                          <?php if ($sum_row['paid_count'] > 0): ?>
                                              <span style="color: #059669; background: #ecfdf5; padding: 2px 8px; border-radius: 4px;"><?php echo $sum_row['paid_count']; ?> Paid</span>
                                          <?php endif; ?>
                                          <?php if ($sum_row['unpaid_count'] > 0): ?>
                                              <span style="color: #dc2626; background: #fef2f2; padding: 2px 8px; border-radius: 4px;"><?php echo $sum_row['unpaid_count']; ?> Unpaid</span>
                                          <?php endif; ?>
                                      </div>
                                  <?php endif; ?>
                              </td>
                              <td style="text-align:right;">
                                  <a href="expense_reports.php?report_type=monthly&month=<?php echo urlencode($sum_row['month_code']); ?>" class="btn-invoice">
                                      View Report
                                  </a>
                              </td>
                          </tr>
                          <?php endforeach; ?>
                      </tbody>
                      <tfoot>
                          <tr style="background: #f8fafc; font-weight: 800;">
                              <td style="color:#334155; border-radius: 50px 0 0 50px;">YEAR TOTAL</td>
                              <td>Rs <?php echo number_format($sum_op, 2); ?></td>
                              <td>Rs <?php echo number_format($sum_sal, 2); ?></td>
                              <td class="val-net" style="font-size:14px;">Rs <?php echo number_format($sum_flow, 2); ?></td>
                              <td></td>
                              <td style="border-radius: 0 50px 50px 0;"></td>
                          </tr>
                      </tfoot>
                  </table>
              </div>
          <?php endif; ?>
      </div>
  </div>
  <?php else: ?>
      <!-- Welcome Empty State before search -->
      <div class="rep-card" style="margin-top: 24px;">
          <div class="rep-empty" style="padding: 80px 24px;">
              <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--brand-green)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 20px;">
                  <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
              </svg>
              <strong style="color: #334155; font-size: 18px;">Ready to Generate Reports</strong>
              <p style="color: #64748b; margin-top: 6px; max-width: 450px; margin-left: auto; margin-right: auto;">Select your report criteria above and click <strong>Generate Report</strong> to analyze detailed monthly bills or yearly summary trends.</p>
          </div>
      </div>
  <?php endif; ?>

</div> <!-- End Container -->

<!-- PDF / Receipt Preview Modal -->
<div class="modal-overlay" id="receiptModal" onclick="closeReceiptModal()">
  <div class="modal-content" onclick="event.stopPropagation()">
    <div class="modal-header">
      <span class="modal-title" id="receiptModalTitle">Receipt Preview</span>
      <button class="modal-close-btn" onclick="closeReceiptModal()">
        <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body" id="receiptModalBody">
      <!-- Embedded receipt goes here -->
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  function toggleFilterFields() {
      const type = document.getElementById('report_type').value;
      const monthField = document.getElementById('month_field');
      const yearField = document.getElementById('year_field');
      if (type === 'monthly') {
          monthField.style.display = 'block';
          yearField.style.display = 'none';
      } else {
          monthField.style.display = 'none';
          yearField.style.display = 'block';
      }
  }
  document.addEventListener("DOMContentLoaded", toggleFilterFields);

  // Modal Open/Close
  function openReceiptModal(path) {
      const modal = document.getElementById('receiptModal');
      const body = document.getElementById('receiptModalBody');
      const title = document.getElementById('receiptModalTitle');
      
      const fileUrl = "../assets/uploads/receipts/" + path;
      const fileExt = path.split('.').pop().toLowerCase();
      
      title.textContent = "Receipt Preview (" + path + ")";
      
      if (fileExt === 'pdf') {
          body.innerHTML = `<iframe src="${fileUrl}"></iframe>`;
      } else {
          body.innerHTML = `<div style="display:flex; align-items:center; justify-content:center; height:100%; overflow:auto; background:#1e293b;"><img src="${fileUrl}" style="max-width:100%; max-height:100%; object-fit:contain;"></div>`;
      }
      
      modal.classList.add('show');
  }

  function closeReceiptModal() {
      document.getElementById('receiptModal').classList.remove('show');
      document.getElementById('receiptModalBody').innerHTML = '';
  }

  <?php if ($report_generated): ?>
  document.addEventListener("DOMContentLoaded", function() {
      // 1. Distribution Chart (Donut)
      const distCtx = document.getElementById('distributionChart').getContext('2d');
      const distLabels = <?php echo json_encode($dist_labels); ?>;
      const distValues = <?php echo json_encode($dist_values); ?>;
      
      new Chart(distCtx, {
          type: 'doughnut',
          data: {
              labels: distLabels,
              datasets: [{
                  data: distValues,
                  backgroundColor: [
                      '#ff7b1d', '#186D55', '#2563eb', '#f59e0b', '#3b82f6', '#10b981', '#a855f7', '#64748b'
                  ],
                  borderWidth: 2,
                  borderColor: '#ffffff'
              }]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                  legend: {
                      position: 'right',
                      labels: {
                          boxWidth: 12,
                          font: { family: 'Nunito Sans', size: 11, weight: '700' },
                          color: '#334155'
                      }
                  }
              }
          }
      });

      // 2. Trend Chart (Bar)
      const trendCtx = document.getElementById('trendChart').getContext('2d');
      const trendLabels = <?php echo json_encode($trend_labels); ?>;
      const trendValues = <?php echo json_encode($trend_values); ?>;

      new Chart(trendCtx, {
          type: 'bar',
          data: {
              labels: trendLabels,
              datasets: [{
                  label: 'General Operating Expenses',
                  data: trendValues,
                  backgroundColor: 'rgba(255, 123, 29, 0.85)',
                  hoverBackgroundColor: 'rgba(255, 123, 29, 1)',
                  borderRadius: 6,
                  borderWidth: 0
              }]
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                  legend: { display: false }
              },
              scales: {
                  y: {
                      beginAtZero: true,
                      grid: { color: '#e2e8f0' },
                      ticks: {
                          font: { family: 'Nunito Sans', size: 10, weight: '700' },
                          color: '#64748b',
                          callback: function(value) { return 'Rs ' + value.toLocaleString(); }
                      }
                  },
                  x: {
                      grid: { display: false },
                      ticks: {
                          font: { family: 'Nunito Sans', size: 11, weight: '700' },
                          color: '#64748b'
                      }
                  }
              }
          }
      });
  });
  <?php endif; ?>
</script>

<?php include_once "../includes/footer.php"; ?>
