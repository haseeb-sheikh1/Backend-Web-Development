<?php
    session_start();
    if (!isset($_SESSION['email'])) {
        header("Location: login.php");
        exit();
    }

    $title     = "Salary Reports";
    $extra_css = "salary_reports";
    $current_page = "salary_reports"; // Keep the payroll reports sidebar item active
    include_once "../includes/header.php";

    require_once "../pages/Database.php";
    require_once "../pages/Employee.php";
    require_once "../pages/Payroll.php";

    $db = new Database();
    $connection = $db->getConnection();
    $employeeObj = new Employee($connection);
    $payrollObj = new Payroll($connection);

    // ── Fetch employee list for dropdown ──
    $employees = $employeeObj->getBasicEmployeeDetails();

    // ── Pull query params ──
    $selected_user_id = isset($_GET['employeeId']) ? ($_GET['employeeId'] === 'all' ? 'all' : (int)$_GET['employeeId']) : 'all';
    $selected_year    = isset($_GET['year'])       ? $_GET['year']             : date('Y');
    $selected_month   = isset($_GET['month'])      ? $_GET['month']            : date('Y-m');

    $employee_name = '';
    $yearly_data   = null;
    $all_employees_data = null;

    if ($selected_user_id === 'all') {
        $query_month = $selected_month . '-01';
        $stmt_all = $connection->prepare("
            SELECT 
                u.first_name, u.last_name, u.user_id,
                p.id as payroll_id, p.base_salary_rs, p.net_payable_rs, p.status, p.payroll_month,
                COALESCE((SELECT SUM(amount) FROM bonus_allowance WHERE payroll_id = p.id), 0) as total_bonuses,
                COALESCE((SELECT SUM(amount_rs) FROM payroll_allowances WHERE payroll_id = p.id), 0) as total_allowances,
                COALESCE((SELECT SUM(amount_rs) FROM payroll_deductions WHERE payroll_id = p.id), 0) as total_deductions
            FROM payroll p
            JOIN employees e ON p.employee_id = e.employee_id
            JOIN users u ON e.user_id = u.user_id
            WHERE p.payroll_month = ?
            ORDER BY u.first_name ASC, u.last_name ASC
        ");
        $stmt_all->bind_param("s", $query_month);
        $stmt_all->execute();
        $res_all = $stmt_all->get_result();
        $all_employees_data = [];
        while ($row_all = $res_all->fetch_assoc()) {
            $all_employees_data[] = $row_all;
        }
        $stmt_all->close();
    } elseif ($selected_user_id) {
        $real_emp_id = $payrollObj->getEmployeeIdByUserId($selected_user_id);
        
        // Get employee name and allowance
        $empDetails = $employeeObj->getAllEmployeesPayrollDetails();

        foreach ($employees as $emp) {
            if ($emp['user_id'] == $selected_user_id) {
                $employee_name = trim($emp['first_name'] . ' ' . $emp['last_name']);
                break;
            }
        }

        if ($real_emp_id) {
            $yearly_data = $payrollObj->getYearlySalaryRecord($real_emp_id, $selected_year);
        }
    }
?>

<style>
/* ══════════════════════════════════════════
   Salary Reports — CodeXentric Theme
   'Widget-Card' & 'Pill-Row' Standard
══════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

:root {
  --brand-green: #186D55;
  --brand-green-hover: #125542;
  --text-main: #1e293b;
  --text-muted: #64748b;
  --border: #e2e8f0;
  --bg-dull: #f1f5f9;
}

.dashboard-container {
    background: var(--bg-dull);
    min-height: calc(100vh - 60px);
    padding: 0 30px 30px 30px;
}

/* Minimal Header Styles */
.dash-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  padding: 10px 0;
}

#page-title {
  font-size: 22px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.btn-minimal {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  color: #475569;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.2s;
}

.btn-minimal:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
  color: #1e293b;
}

/* ── Widget Card (Search) ── */
.rep-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    margin-bottom: 24px;
    overflow: hidden;
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
    color: var(--brand-green);
    display: flex; align-items: center;
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

/* ── Form Styling ── */
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

.modern-input {
    width: 100%;
    height: 44px;
    padding: 0 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    color: #1e293b;
    transition: all 0.2s;
}

.modern-input:focus {
    border-color: var(--brand-green);
    box-shadow: 0 0 0 3px rgba(24, 109, 85, 0.1);
    outline: none;
}

.modern-select {
    width: 100%;
    height: 44px;
    padding: 0 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    background: #fff;
    cursor: pointer;
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
    padding: 24px;
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

.meta-person svg {
    color: #6366f1;
}

.meta-pill {
    background: #f1f5f9;
    color: #475569;
    padding: 6px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid #e2e8f0;
}

/* ── Results Section ── */
.rep-results-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 10px;
}
.rep-results-header .emp-tag {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}
.rep-results-header .emp-tag .badge {
    background: #EBF2FC;
    color: #1559B5;
    font-size: 12px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
}
.rep-date-tag {
    font-size: 13px;
    color: #6B7280;
    background: #F3F4F6;
    padding: 6px 14px;
    border-radius: 6px;
    font-weight: 600;
    border: 1px solid #E2E8F0;
}

/* Empty state */
.rep-empty {
    text-align: center;
    padding: 56px 24px;
    color: #9CA3AF;
}
.rep-empty svg { margin-bottom: 16px; opacity: 0.4; }
.rep-empty p { font-size: 14px; margin: 0; }
.rep-empty strong { display: block; font-size: 16px; font-weight: 700; color: #6B7280; margin-bottom: 6px; }

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

.val-net { color: var(--brand-green); font-weight: 800; }
.val-positive { color: #059669; }
.val-negative { color: #ef4444; }

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.status-paid { background: #ecfdf5; color: #059669; }
.status-pending { background: #fef3c7; color: #d97706; }

.btn-invoice {
    background: #fff;
    color: #334155;
    border: 1px solid #e2e8f0;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 700;
    font-size: 13px;
    transition: all 0.2s;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}

.btn-invoice:hover { border-color: var(--brand-green); color: var(--brand-green); transform: translateY(-1px); box-shadow: 0 2px 4px rgba(0,0,0,0.04); }

/* Breakdown Cards */
.breakdown-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 24px; }
.b-card { background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 20px; position: relative; overflow: hidden; }
.b-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; }
.b-card.base::before { background: #64748B; }
.b-card.add::before { background: #059669; }
.b-card.sub::before { background: #DC2626; }
.b-card.net::before { background: #1E6FD9; }

.b-card-label { font-size: 12px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 8px; display: block; }
.b-card-val { font-size: 24px; font-weight: 800; color: #0F172A; }

.detail-list { margin-top: 16px; display: flex; flex-direction: column; gap: 8px; }
.detail-item { display: flex; justify-content: space-between; font-size: 14px; border-bottom: 1px dashed #E2E8F0; padding-bottom: 6px; }
.detail-item:last-child { border-bottom: none; padding-bottom: 0; }
.detail-name { color: #475569; }
.detail-amt { font-weight: 600; color: #0F172A; }

/* Fade-in animation */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
.rep-card { animation: fadeUp 0.3s ease both; }
.rep-card:nth-child(2) { animation-delay: 0.08s; }
</style>

<div class="dashboard-container">

    <!-- Minimal Header -->

    <!-- Search / Filter Card -->
    <div class="rep-card">
        <div class="rep-card-header">
            <div class="icon-brand">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
            <h2>Employee Search</h2>
        </div>
        <div class="rep-card-body">
            <form method="GET" action="salary_reports.php">
                <div class="modern-grid">

                    <!-- Employee Dropdown -->
                    <div class="modern-field">
                        <label>Employee Name</label>
                        <select name="employeeId" class="modern-select" required onchange="toggleFields(this.value)">
                            <option value="" disabled <?php echo !$selected_user_id ? 'selected' : ''; ?>>-- Select Employee --</option>
                            <option value="all" <?php echo ($selected_user_id === 'all') ? 'selected' : ''; ?>>— All Employees —</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo (int)$emp['user_id']; ?>"
                                    <?php echo ($selected_user_id == $emp['user_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(trim($emp['first_name'] . ' ' . $emp['last_name'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Year Picker -->
                    <div class="modern-field" id="year-field" style="<?php echo ($selected_user_id === 'all') ? 'display:none;' : ''; ?>">
                        <label>Report Year</label>
                        <input type="number" name="year" class="modern-input" min="2000" max="2100" value="<?php echo htmlspecialchars($selected_year); ?>">
                    </div>

                    <!-- Month Picker -->
                    <div class="modern-field" id="month-field" style="<?php echo ($selected_user_id === 'all') ? '' : 'display:none;'; ?>">
                        <label>Report Month</label>
                        <input type="month" name="month" class="modern-input" value="<?php echo htmlspecialchars($selected_month); ?>">
                    </div>

                </div>

                <!-- Action Buttons -->
                <div class="modern-actions">
                    <a href="salary_reports.php" class="btn-secondary-modern">Reset</a>
                    <button type="submit" class="btn-primary-modern">Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Card -->
    <?php if ($selected_user_id): ?>
    <div class="rep-card">
        <div class="rep-card-header">
            <div class="icon-brand">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
            </div>
            <h2><?php echo ($selected_user_id === 'all') ? 'All Employees Monthly Report' : 'Yearly Report Detail'; ?></h2>
        </div>
        <div class="rep-results-meta">
            <div class="meta-person">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <?php echo ($selected_user_id === 'all') ? 'All Processed Employees' : htmlspecialchars($employee_name); ?>
            </div>
            <div class="meta-pill">
                <?php echo ($selected_user_id === 'all') ? 'Month: ' . date('F Y', strtotime($selected_month . '-01')) : 'Year: ' . htmlspecialchars($selected_year); ?>
            </div>
        </div>

        <div class="rep-card-body">

            <?php if ($selected_user_id === 'all'): ?>
                <!-- ALL EMPLOYEES MONTHLY VIEW -->
                <?php if (!empty($all_employees_data)): ?>
                    <div style="overflow-x: auto;">
                        <table class="rep-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Base Salary</th>
                                    <th>Bonuses</th>
                                    <th>Allowances</th>
                                    <th>Deductions</th>
                                    <th>Net Payout</th>
                                    <th style="text-align:right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $sum_base = 0; $sum_bonuses = 0; $sum_allowances = 0; $sum_deductions = 0; $sum_net = 0;
                                    foreach ($all_employees_data as $row): 
                                        $sum_base += $row['base_salary_rs'];
                                        $sum_bonuses += $row['total_bonuses'];
                                        $sum_allowances += $row['total_allowances'];
                                        $sum_deductions += $row['total_deductions'];
                                        $sum_net += $row['net_payable_rs'];
                                ?>
                                <tr>
                                    <td style="font-weight:700; color:#1e293b;"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td>Rs <?php echo number_format($row['base_salary_rs']); ?></td>
                                    <td class="val-positive">+ Rs <?php echo number_format($row['total_bonuses']); ?></td>
                                    <td class="val-positive">+ Rs <?php echo number_format($row['total_allowances']); ?></td>
                                    <td class="val-negative">- Rs <?php echo number_format($row['total_deductions']); ?></td>
                                    <td class="val-net">Rs <?php echo number_format($row['net_payable_rs']); ?></td>
                                    <td style="text-align:right;">
                                        <a href="salary_invoice.php?employeeId=<?php echo urlencode($row['user_id']); ?>&month=<?php echo urlencode(date('Y-m', strtotime($row['payroll_month']))); ?>" class="btn-invoice" title="View Salary Slip" style="display: inline-flex; align-items: center; justify-content: center; padding: 8px 10px;" target="_blank">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background: #f8fafc; box-shadow: 0 2px 8px rgba(0,0,0,0.03); font-weight: 800; border-radius: 12px;">
                                    <td style="color:#334155; border-radius: 50px 0 0 50px;">MONTH TOTAL</td>
                                    <td>Rs <?php echo number_format($sum_base); ?></td>
                                    <td class="val-positive">+ Rs <?php echo number_format($sum_bonuses); ?></td>
                                    <td class="val-positive">+ Rs <?php echo number_format($sum_allowances); ?></td>
                                    <td class="val-negative">- Rs <?php echo number_format($sum_deductions); ?></td>
                                    <td class="val-net" style="font-size:14px;">Rs <?php echo number_format($sum_net); ?></td>
                                    <td style="border-radius: 0 50px 50px 0;"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="rep-empty">
                        <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                            <line x1="9" y1="15" x2="15" y2="15"/>
                        </svg>
                        <strong>No Records Found</strong>
                        <p>No payroll data found for any employee in the month of <?php echo date('F Y', strtotime($selected_month . '-01')); ?>.</p>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- YEARLY VIEW -->
                <?php if (!empty($yearly_data)): ?>
                    <div style="overflow-x: auto;">
                        <table class="rep-table">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Base Salary</th>
                                    <th>Bonuses</th>
                                    <th>Allowances</th>
                                    <th>Deductions</th>
                                    <th>Net Payable</th>
                                    <th style="text-align:right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $sum_base = 0; $sum_bonuses = 0; $sum_allowances = 0; $sum_deductions = 0; $sum_net = 0;
                                    foreach ($yearly_data as $row): 
                                        $row_allowances = $row['total_allowances'];
                                        $sum_base += $row['base_salary'];
                                        $sum_bonuses += $row['total_bonuses'];
                                        $sum_allowances += $row_allowances;
                                        $sum_deductions += $row['total_deductions'];
                                        $sum_net += ($row['base_salary'] + $row['total_bonuses'] + $row_allowances - $row['total_deductions']);
                                ?>
                                <tr>
                                    <td style="font-weight:700; color:#1e293b;"><?php echo date('F Y', strtotime($row['payroll_month'])); ?></td>
                                    <td>Rs <?php echo number_format($row['base_salary']); ?></td>
                                    <td class="val-positive">+ Rs <?php echo number_format($row['total_bonuses']); ?></td>
                                    <td class="val-positive">+ Rs <?php echo number_format($row_allowances); ?></td>
                                    <td class="val-negative">- Rs <?php echo number_format($row['total_deductions']); ?></td>
                                    <td class="val-net">Rs <?php echo number_format($row['base_salary'] + $row['total_bonuses'] + $row_allowances - $row['total_deductions']); ?></td>
                                    <td style="text-align:right;">
                                        <a href="salary_invoice.php?employeeId=<?php echo urlencode($selected_user_id); ?>&month=<?php echo urlencode(date('Y-m', strtotime($row['payroll_month']))); ?>" class="btn-invoice" title="View Salary Slip" style="display: inline-flex; align-items: center; justify-content: center; padding: 8px 10px;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background: #f8fafc; box-shadow: 0 2px 8px rgba(0,0,0,0.03); font-weight: 800; border-radius: 12px;">
                                    <td style="color:#334155; border-radius: 50px 0 0 50px;">YEAR TOTAL</td>
                                    <td>Rs <?php echo number_format($sum_base); ?></td>
                                    <td class="val-positive">+ Rs <?php echo number_format($sum_bonuses); ?></td>
                                    <td class="val-positive">+ Rs <?php echo number_format($sum_allowances); ?></td>
                                    <td class="val-negative">- Rs <?php echo number_format($sum_deductions); ?></td>
                                    <td class="val-net" style="font-size:14px;">Rs <?php echo number_format($sum_net); ?></td>
                                    <td style="border-radius: 0 50px 50px 0;"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="rep-empty">
                        <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                            <line x1="9" y1="15" x2="15" y2="15"/>
                        </svg>
                        <strong>No Records Found</strong>
                        <p>No payroll data found for this employee in the year <?php echo htmlspecialchars($selected_year); ?>.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
    <?php endif; ?>

</div>

<script>
function toggleFields(val) {
    const yearField = document.getElementById('year-field');
    const monthField = document.getElementById('month-field');
    if (val === 'all') {
        yearField.style.display = 'none';
        monthField.style.display = 'block';
    } else {
        yearField.style.display = 'block';
        monthField.style.display = 'none';
    }
}
</script>

<?php include_once "../includes/footer.php"; ?>
