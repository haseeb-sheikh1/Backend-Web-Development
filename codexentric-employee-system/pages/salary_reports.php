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
/* Standardized Premium Architecture Transplanted from Employees List */
@import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap');

:root {
  --bg: #f4f6f9;
  --card-bg: #ffffff;
  --border: rgba(0, 0, 0, 0.05);
  --text-main: #334155;
  --text-muted: #64748b;
  --brand-green: #186D55;
  --font-body: 'Nunito Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
}

.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px 24px 24px;
  font-family: var(--font-body);
}

/* ── Search Card ── */
.search-card {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.02);
  overflow: visible;
  position: relative;
  z-index: 100;
  margin-bottom: 24px;
}

.search-card-header {
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  background: #ffffff;
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
  gap: 10px;
}

.search-card-header svg {
  color: var(--brand-green);
}

#directory-heading {
  font-size: 15px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.search-card-body {
  padding: 24px;
}

.search-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 20px;
}

.search-field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.search-field label {
  font-size: 13px;
  font-weight: 600;
  color: #64748b;
  display: block;
}

.search-field input,
.search-field select {
  width: 100%;
  height: 44px;
  padding: 0 16px;
  background: #fcfdfd;
  border: 1.5px solid #e2e8f0;
  border-radius: 22px;
  font-size: 13.5px;
  font-weight: 500;
  color: #1e293b;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  box-sizing: border-box;
}

.search-field input:hover,
.search-field select:hover {
  border-color: #cbd5e1;
  background: #ffffff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}

.search-field input:focus,
.search-field select:focus {
  border-color: var(--brand-green);
  background: #ffffff;
  box-shadow: 0 0 0 4px rgba(24, 109, 85, 0.12), 0 4px 12px rgba(24, 109, 85, 0.05);
  outline: none;
}

.search-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.btn-reset {
  background: #ffffff;
  color: var(--brand-green);
  border: 1px solid var(--brand-green);
  border-radius: 20px;
  padding: 10px 28px;
  font-size: 13.5px;
  font-weight: 700;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  transition: all 0.2s;
  cursor: pointer;
}

.btn-reset:hover {
  background: rgba(24, 109, 85, 0.05);
  transform: translateY(-1px);
}

.btn-search {
  background: var(--brand-green);
  color: #ffffff;
  border: none;
  border-radius: 20px;
  padding: 10px 28px;
  font-size: 13.5px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-search:hover {
  background: #11523f;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(24, 109, 85, 0.15);
}

/* ── Custom Table Layout Overhaul ── */
.table-wrapper {
  background: transparent;
  border: none;
  box-shadow: none;
  overflow: visible;
}

.employees-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 12px;
}

.employees-table th {
  padding: 12px 24px;
  text-align: left;
  font-size: 12.5px;
  font-weight: 700;
  color: #64748b;
  letter-spacing: 0.5px;
}

.employees-table td {
  padding: 16px 24px;
  color: #475569;
  background: #ffffff;
  border-top: 1px solid rgba(0, 0, 0, 0.04);
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
  vertical-align: middle;
  font-size: 13.5px;
  font-weight: 600;
  transition: all 0.2s;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.015);
}

.employees-table td:first-child {
  border-left: 1px solid rgba(0, 0, 0, 0.04);
  border-top-left-radius: 25px;
  border-bottom-left-radius: 25px;
  padding-left: 28px;
  color: #1e293b;
  font-weight: 700;
}

.employees-table td:last-child {
  border-right: 1px solid rgba(0, 0, 0, 0.04);
  border-top-right-radius: 25px;
  border-bottom-right-radius: 25px;
  padding-right: 28px;
}

.employees-table tbody tr:hover td {
  background: #fafbfc;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.025);
}

/* Custom Financial Values for Salary Reports */
.val-net { color: var(--brand-green); font-weight: 800; }
.val-positive { color: #059669; }
.val-negative { color: #ef4444; }

/* Total Calculation Row (Preserving Footer Stylistics) */
.employees-table tfoot tr td {
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    font-weight: 800 !important;
    color: #334155 !important;
    box-shadow: none !important;
}

.employees-table tfoot tr td:first-child { border-radius: 25px 0 0 25px !important; border-right: none !important;}
.employees-table tfoot tr td:last-child { border-radius: 0 25px 25px 0 !important; border-left: none !important;}

/* Action Circle Standard */
.action-trigger-group {
  display: flex;
  justify-content: flex-end;
  gap: 8px; /* Space between multiple icons */
}

.action-icon-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #475569;
  text-decoration: none;
  border: none;
  cursor: pointer;
  transition: all 0.25s ease;
}

.action-icon-btn:hover {
  background: #eaf5f2;
  color: var(--brand-green);
  transform: scale(1.08);
}

/* Empty State Banner */
.rep-empty {
    text-align: center;
    padding: 56px 24px;
    color: #94a3b8;
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(0,0,0,0.05);
    margin-top: 10px;
}
.rep-empty strong { display: block; font-size: 16px; font-weight: 700; color: #475569; margin-bottom: 6px; }
.rep-empty p { font-size: 14px; margin: 0; }

@keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.search-card, .results-container { animation: fadeUp 0.3s ease both; }

@media (max-width: 768px) {
  .dashboard-container {
    padding: 0 12px 12px 12px !important;
  }
  
  .search-card-header {
    padding: 16px 15px !important;
  }
  .search-card-body {
    padding: 18px 16px !important;
  }
  
  .search-grid {
    grid-template-columns: 1fr !important;
    gap: 16px !important;
  }
  
  /* Mobile Card Table Transformation */
  .employees-table, .employees-table thead, .employees-table tbody, .employees-table tr, .employees-table td {
    display: block !important;
    width: 100% !important;
    box-sizing: border-box !important;
  }
  
  .employees-table thead { display: none !important; }
  
  .employees-table tr {
    background: #ffffff !important;
    border: 1px solid #eef2f6 !important;
    border-radius: 20px !important;
    margin-bottom: 16px !important;
    padding: 20px !important;
    position: relative !important;
    box-shadow: 0 4px 20px rgba(0,0,0,0.02) !important;
  }
  
  .employees-table tr:hover td { background: transparent !important; transform: none !important; }
  
  .employees-table td {
    border: none !important;
    padding: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    margin-bottom: 12px !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-start !important;
    border-radius: 0 !important;
  }
  
  .employees-table td[data-label]::before {
    content: attr(data-label);
    font-size: 10.5px !important;
    font-weight: 800 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.6px !important;
    margin-bottom: 4px !important;
  }
  
  /* Elevate top identifier simply without heavy coloring */
  .employees-table td:first-child {
    border-bottom: 1px solid #f1f5f9 !important;
    padding-bottom: 12px !important;
    margin-bottom: 16px !important;
    font-size: 16px !important;
    font-weight: 800 !important;
    color: #1e293b !important;
  }
  
  /* Float actions in default card corner */
  .employees-table td:last-child {
    position: absolute !important;
    top: 20px !important;
    right: 20px !important;
    width: auto !important;
    margin: 0 !important;
  }
  .employees-table td:last-child::before { display: none !important; }
  
  /* Flatten Total Calculation TFoot */
  .employees-table tfoot, .employees-table tfoot tr, .employees-table tfoot td {
    display: block !important;
    width: 100% !important;
  }
  /* Clean Slate for Footer Card container */
  .employees-table tfoot tr {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    padding: 0 !important; /* Standard Padding handled by sub-items */
    box-shadow: 0 8px 30px rgba(0,0,0,0.04) !important;
    border-radius: 20px !important;
    margin-top: 28px !important;
    overflow: hidden !important; /* Ensures header strip follows boundary perfectly */
  }
  
  /* High Specificity Reset to Kill intrusive desktop overrides */
  .employees-table tfoot tr td {
    margin-bottom: 0 !important;
    padding: 14px 24px !important;
    border: none !important; /* KILL THE BOX OUTLINES */
    border-radius: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
    display: flex !important;
    flex-direction: row !important;
    justify-content: space-between !important;
    align-items: center !important;
    font-size: 14px !important;
  }
  
  /* Simple, clean row separator line, only beneath standard data entries */
  .employees-table tfoot tr td:not(:first-child):not(.val-net):not(:last-child) {
    border-bottom: 1px solid #f1f5f9 !important;
  }
  
  .employees-table tfoot tr td[data-label]::before {
    content: attr(data-label);
    display: block !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    color: #64748b !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
  }

  /* Match the top header band WITHOUT pulling margins - let overflow:hidden mask it */
  .employees-table tfoot tr td:first-child {
    background: rgba(24, 109, 85, 0.06) !important;
    color: var(--brand-green) !important;
    border-bottom: 1px solid rgba(24, 109, 85, 0.08) !important;
    padding: 16px 24px !important;
    font-size: 15px !important;
    font-weight: 900 !important;
    letter-spacing: 0.8px !important;
    text-transform: uppercase;
    width: 100% !important;
    margin-bottom: 8px !important;
    border-radius: 0 !important; /* REMOVES CLIPPING OVERLAP */
  }
  
  /* Final Row Elevation inside the card padding matrix */
  .employees-table tfoot tr td.val-net {
    border: none !important;
    margin: 12px 20px 20px 20px !important;
    padding: 16px !important;
    background: #f8fafc !important;
    border-radius: 12px !important;
    font-size: 17px !important;
    font-weight: 800 !important;
    width: auto !important; /* Auto constraint inside margins */
  }
  .employees-table tfoot tr td.val-net::before {
    color: #1e293b !important;
    font-weight: 700 !important;
  }
  .employees-table tfoot tr td:last-child { display: none !important; }
}
</style>

<div class="dashboard-container">

    <!-- Minimal Header -->

    <!-- Search Card Architecture -->
    <div class="search-card">
        <div class="search-card-header">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <h2 id="directory-heading">Salary Records Search</h2>
        </div>
        <div class="search-card-body">
            <form method="GET" action="salary_reports.php">
                <div class="search-grid">
                    <div class="search-field">
                        <label>Employee Name</label>
                        <select name="employeeId" required onchange="toggleFields(this.value)">
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
                    <div class="search-field" id="year-field" style="<?php echo ($selected_user_id === 'all') ? 'display:none;' : ''; ?>">
                        <label>Report Year</label>
                        <input type="number" name="year" min="2000" max="2100" value="<?php echo htmlspecialchars($selected_year); ?>">
                    </div>
                    <div class="search-field" id="month-field" style="<?php echo ($selected_user_id === 'all') ? '' : 'display:none;'; ?>">
                        <label>Report Month</label>
                        <input type="month" name="month" value="<?php echo htmlspecialchars($selected_month); ?>">
                    </div>
                </div>
                <div class="search-actions">
                    <a href="salary_reports.php" class="btn-reset">Reset</a>
                    <button type="submit" class="btn-search">Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Architecture -->
    <?php if ($selected_user_id): ?>
    <div class="results-container">
        <!-- Top contextual bar inline -->
        <div style="display:flex; align-items:center; justify-content:space-between; margin-top: 32px; margin-bottom: 16px; padding: 0 4px;">
            <div style="display:flex; align-items:center; gap:10px; font-weight:700; color:#1e293b; font-size:15px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                <?php echo ($selected_user_id === 'all') ? 'All Processed Employees' : htmlspecialchars($employee_name); ?>
            </div>
            <div style="font-size:13.5px; font-weight:600; color:#64748b;">
                (<?php echo ($selected_user_id === 'all') ? 'Month: ' . date('F Y', strtotime($selected_month . '-01')) : 'Year: ' . htmlspecialchars($selected_year); ?>)
            </div>
        </div>

        <div class="table-wrapper">
            <?php if ($selected_user_id === 'all'): ?>
                <!-- ALL EMPLOYEES MONTHLY VIEW -->
                <?php if (!empty($all_employees_data)): ?>
                    <table class="employees-table">
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
                                    $row_net = ($row['base_salary_rs'] + $row['total_bonuses'] + $row['total_allowances'] - $row['total_deductions']);
                                    $sum_base += $row['base_salary_rs'];
                                    $sum_bonuses += $row['total_bonuses'];
                                    $sum_allowances += $row['total_allowances'];
                                    $sum_deductions += $row['total_deductions'];
                                    $sum_net += $row_net;
                            ?>
                            <tr>
                                <td data-label="Employee"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td data-label="Base Salary">Rs <?php echo number_format($row['base_salary_rs']); ?></td>
                                <td data-label="Bonuses" class="val-positive">+ Rs <?php echo number_format($row['total_bonuses']); ?></td>
                                <td data-label="Allowances" class="val-positive">+ Rs <?php echo number_format($row['total_allowances']); ?></td>
                                <td data-label="Deductions" class="val-negative">- Rs <?php echo number_format($row['total_deductions']); ?></td>
                                <td data-label="Net Payout" class="val-net">Rs <?php echo number_format($row_net); ?></td>
                                <td>
                                    <div class="action-trigger-group">
                                        <a href="salary_invoice.php?employeeId=<?php echo urlencode($row['user_id']); ?>&month=<?php echo urlencode(date('Y-m', strtotime($row['payroll_month']))); ?>" class="action-icon-btn" title="View Details" target="_blank">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <a href="download_invoice.php?employeeId=<?php echo urlencode($row['user_id']); ?>&month=<?php echo urlencode(date('Y-m', strtotime($row['payroll_month']))); ?>" class="action-icon-btn" title="Download HD PDF">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>MONTH TOTAL</td>
                                <td data-label="Total Base">Rs <?php echo number_format($sum_base); ?></td>
                                <td data-label="Total Bonuses" class="val-positive">+ Rs <?php echo number_format($sum_bonuses); ?></td>
                                <td data-label="Total Allowances" class="val-positive">+ Rs <?php echo number_format($sum_allowances); ?></td>
                                <td data-label="Total Deductions" class="val-negative">- Rs <?php echo number_format($sum_deductions); ?></td>
                                <td data-label="Grand Total Net" class="val-net">Rs <?php echo number_format($sum_net); ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php else: ?>
                    <div class="rep-empty">
                        <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.5; margin-bottom:16px;">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <strong>No Records Found</strong>
                        <p>No payroll data found for the month of <?php echo date('F Y', strtotime($selected_month . '-01')); ?>.</p>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- YEARLY VIEW -->
                <?php if (!empty($yearly_data)): ?>
                    <table class="employees-table">
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
                                <td data-label="Month"><?php echo date('F Y', strtotime($row['payroll_month'])); ?></td>
                                <td data-label="Base Salary">Rs <?php echo number_format($row['base_salary']); ?></td>
                                <td data-label="Bonuses" class="val-positive">+ Rs <?php echo number_format($row['total_bonuses']); ?></td>
                                <td data-label="Allowances" class="val-positive">+ Rs <?php echo number_format($row_allowances); ?></td>
                                <td data-label="Deductions" class="val-negative">- Rs <?php echo number_format($row['total_deductions']); ?></td>
                                <td data-label="Net Payable" class="val-net">Rs <?php echo number_format($row['base_salary'] + $row['total_bonuses'] + $row_allowances - $row['total_deductions']); ?></td>
                                <td>
                                    <div class="action-trigger-group">
                                        <a href="salary_invoice.php?employeeId=<?php echo urlencode($selected_user_id); ?>&month=<?php echo urlencode(date('Y-m', strtotime($row['payroll_month']))); ?>" class="action-icon-btn" title="View Details" target="_blank">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <a href="download_invoice.php?employeeId=<?php echo urlencode($selected_user_id); ?>&month=<?php echo urlencode(date('Y-m', strtotime($row['payroll_month']))); ?>" class="action-icon-btn" title="Download HD PDF">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>YEAR TOTAL</td>
                                <td data-label="Total Base">Rs <?php echo number_format($sum_base); ?></td>
                                <td data-label="Total Bonuses" class="val-positive">+ Rs <?php echo number_format($sum_bonuses); ?></td>
                                <td data-label="Total Allowances" class="val-positive">+ Rs <?php echo number_format($sum_allowances); ?></td>
                                <td data-label="Total Deductions" class="val-negative">- Rs <?php echo number_format($sum_deductions); ?></td>
                                <td data-label="Grand Total Net" class="val-net">Rs <?php echo number_format($sum_net); ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php else: ?>
                    <div class="rep-empty">
                        <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.5; margin-bottom:16px;">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
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
