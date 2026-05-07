<?php
    session_start();
    if (!isset($_SESSION['email'])) {
        header("Location: login.php");
        exit();
    }

    $title = "Salary Invoice - CodeXentric";
    $extra_css = "salary_invoice";
    $current_page = "payroll";
    include_once "../includes/header.php";

    require_once "../pages/Database.php";
    require_once "../pages/Employee.php";
    require_once "../pages/Payroll.php";

    $db = new Database();
    $connection = $db->getConnection();
    $employeeObj = new Employee($connection);
    $payrollObj = new Payroll($connection);

    $selected_user_id = isset($_GET['employeeId']) ? (int)$_GET['employeeId'] : null;
    $selected_month   = isset($_GET['month']) ? $_GET['month'] : null;

    $employee_name = '';
    $employee_role = '';
    $employee_bank = '';
    $standard_allowance = 0;
    $monthly_data  = null;

    if ($selected_user_id && $selected_month) {
        $real_emp_id = $payrollObj->getEmployeeIdByUserId($selected_user_id);
        
        $employees = $employeeObj->getBasicEmployeeDetails();
        foreach ($employees as $emp) {
            if ($emp['user_id'] == $selected_user_id) {
                $employee_name = trim($emp['first_name'] . ' ' . $emp['last_name']);
                $employee_role = $emp['position_title'];
                break;
            }
        }
        
        $empDetails = $employeeObj->getAllEmployeesPayrollDetails();
        foreach ($empDetails as $ed) {
            if ($ed['user_id'] == $selected_user_id) {
                $employee_bank = $ed['bank_name'] . ' - ' . $ed['bank_account_number'];
                $standard_allowance = isset($ed['allowances_rs']) ? (float)$ed['allowances_rs'] : 0;
                break;
            }
        }

        if ($real_emp_id) {
            $payroll_month_str = $selected_month . '-01';
            $monthly_data = $payrollObj->getFullSalaryRecord($real_emp_id, $payroll_month_str);
        }
    }
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&display=swap');

.inv-wrap * { box-sizing: border-box; }

.inv-wrap {
    max-width: 760px;
    margin: 0 auto 64px;
    font-family: 'Geist', 'Helvetica Neue', sans-serif;
    color: #111;
}

/* Remove top gap from main-content shell */
.main-content { padding-top: 0 !important; }

/* Toolbar */
.inv-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}
.inv-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #555;
    text-decoration: none;
    padding: 7px 14px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background: #fff;
    transition: border-color .15s, color .15s;
}
.inv-back:hover { border-color: #888; color: #111; }

/* Card */
.inv-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

/* ── Header ── */
.inv-head {
    padding: 36px 44px 30px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid #ebebeb;
}
.inv-company h1 {
    margin: 0 0 4px;
    font-size: 17px;
    font-weight: 700;
    letter-spacing: -.2px;
    color: #111;
}
.inv-company p {
    margin: 0;
    font-size: 12px;
    color: #999;
}
.inv-head-right { text-align: right; }
.inv-head-right h2 {
    margin: 0 0 8px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #999;
}
.inv-head-right .inv-period {
    font-size: 26px;
    font-weight: 700;
    letter-spacing: -1px;
    color: #111;
    display: block;
    line-height: 1;
}

/* ── Info grid ── */
.inv-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    border-bottom: 1px solid #ebebeb;
}
.inv-info-col {
    padding: 24px 44px;
}
.inv-info-col + .inv-info-col {
    border-left: 1px solid #ebebeb;
}
.inv-section-title {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #bbb;
    margin-bottom: 14px;
}
.inv-dl { display: flex; flex-direction: column; gap: 7px; }
.inv-dl-row { display: flex; gap: 0; font-size: 13px; }
.inv-dl-row dt { color: #999; font-weight: 400; width: 100px; flex-shrink: 0; }
.inv-dl-row dd { margin: 0; color: #111; font-weight: 500; }

.inv-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11.5px;
    font-weight: 600;
    color: #15803d;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    padding: 2px 9px;
    border-radius: 4px;
}
.inv-status::before {
    content: '';
    width: 5px; height: 5px;
    border-radius: 50%;
    background: #15803d;
    display: block;
    flex-shrink: 0;
}

/* ── Line items ── */
.inv-body { padding: 0 44px 8px; }

.inv-section-head {
    display: flex;
    justify-content: space-between;
    padding: 20px 0 9px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #bbb;
    border-bottom: 1px solid #ebebeb;
}

.inv-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f5f5f5;
    font-size: 14px;
}
.inv-line:last-child { border-bottom: none; }
.inv-line-desc { color: #333; display: flex; align-items: center; gap: 0; }
.inv-tag {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .4px;
    color: #aaa;
    background: #f5f5f5;
    padding: 2px 6px;
    border-radius: 3px;
    margin-left: 9px;
}
.inv-line-amt { font-weight: 600; font-variant-numeric: tabular-nums; }
.inv-line-amt.earn { color: #15803d; }
.inv-line-amt.ded  { color: #dc2626; }
.inv-line-amt.nil  { color: #ccc; font-style: italic; font-weight: 400; }

.inv-section-gap { height: 1px; background: #ebebeb; margin: 0; }

/* ── Totals ── */
.inv-totals {
    border-top: 1px solid #ebebeb;
    padding: 22px 44px 28px;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0;
}
.inv-sum-row {
    display: flex;
    justify-content: space-between;
    width: 260px;
    padding: 6px 0;
    font-size: 14px;
    border-bottom: 1px solid #f5f5f5;
}
.inv-sum-row:last-of-type { border-bottom: none; margin-bottom: 14px; }
.inv-sum-row span:first-child { color: #888; }
.inv-sum-row span:last-child  { color: #111; font-weight: 600; font-variant-numeric: tabular-nums; }

.inv-net {
    width: 260px;
    border: 1.5px solid #111;
    border-radius: 6px;
    padding: 13px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #111;
    color: #fff;
}
.inv-net-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; opacity: .55; }
.inv-net-amount { font-size: 19px; font-weight: 700; letter-spacing: -.5px; font-variant-numeric: tabular-nums; }

/* ── Footer ── */
.inv-foot {
    border-top: 1px solid #ebebeb;
    padding: 14px 44px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.inv-foot span { font-size: 11.5px; color: #ccc; }

/* ── Empty ── */
.inv-empty {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    text-align: center;
    padding: 80px 40px;
}
.inv-empty h2 { font-size: 18px; font-weight: 600; color: #111; margin: 0 0 8px; }
.inv-empty p  { font-size: 14px; color: #999; margin: 0; }

/* Print */
@media print {
    body * { visibility: hidden; }
    .inv-card, .inv-card * { visibility: visible; }
    .inv-card { position: absolute; inset: 0; border: none; border-radius: 0; }
    .inv-bar { display: none; }
}
</style>

<div class="inv-wrap">

    <div class="inv-bar">
        <a href="salary_reports.php" class="inv-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Reports
        </a>
        <!-- Print functionality temporarily disabled -->
        <!--
        <button onclick="window.print()" class="inv-print-btn">Print</button>
        -->
    </div>

    <?php if ($monthly_data): ?>
    <?php
        $total_earnings = $monthly_data['base_salary'];
        foreach ($monthly_data['bonuses'] as $b)    $total_earnings += $b['amount'];
        foreach ($monthly_data['allowances'] as $a) $total_earnings += $a['amount'];
        if ($standard_allowance > 0)                 $total_earnings += $standard_allowance;
        $total_deductions = 0;
        if (!empty($monthly_data['deductions']))
            foreach ($monthly_data['deductions'] as $d) $total_deductions += $d['amount'];
    ?>
    <div class="inv-card">

        <!-- Header -->
        <div class="inv-head">
            <div class="inv-company">
                <h1>CodeXentric</h1>
                <p>HR &amp; Payroll Department</p>
            </div>
            <div class="inv-head-right">
                <h2>Payslip</h2>
                <span class="inv-period"><?php echo date('F Y', strtotime($selected_month . '-01')); ?></span>
            </div>
        </div>

        <!-- Info -->
        <div class="inv-info">
            <div class="inv-info-col">
                <div class="inv-section-title">Employee</div>
                <div class="inv-dl">
                    <div class="inv-dl-row"><dt>Name</dt><dd><?php echo htmlspecialchars($employee_name); ?></dd></div>
                    <div class="inv-dl-row"><dt>Employee ID</dt><dd>EMP-<?php echo str_pad($selected_user_id, 4, '0', STR_PAD_LEFT); ?></dd></div>
                    <div class="inv-dl-row"><dt>Designation</dt><dd><?php echo htmlspecialchars($employee_role); ?></dd></div>
                </div>
            </div>
            <div class="inv-info-col">
                <div class="inv-section-title">Payment</div>
                <div class="inv-dl">
                    <div class="inv-dl-row"><dt>Bank</dt><dd><?php echo htmlspecialchars($employee_bank ?: 'Not Provided'); ?></dd></div>
                    <div class="inv-dl-row"><dt>Status</dt><dd><span class="inv-status"><?php echo htmlspecialchars($monthly_data['status']); ?></span></dd></div>
                    <div class="inv-dl-row"><dt>Processed</dt><dd><?php echo date('d M Y'); ?></dd></div>
                </div>
            </div>
        </div>

        <!-- Line items -->
        <div class="inv-body">

            <div class="inv-section-head">
                <span>Earnings</span>
                <span>Amount</span>
            </div>

            <div class="inv-line">
                <span class="inv-line-desc">Basic Salary</span>
                <span class="inv-line-amt earn">Rs <?php echo number_format($monthly_data['base_salary']); ?></span>
            </div>
            <?php foreach ($monthly_data['bonuses'] as $b): ?>
            <div class="inv-line">
                <span class="inv-line-desc"><?php echo htmlspecialchars($b['name']); ?><span class="inv-tag">Bonus</span></span>
                <span class="inv-line-amt earn">Rs <?php echo number_format($b['amount']); ?></span>
            </div>
            <?php endforeach; ?>
            <?php foreach ($monthly_data['allowances'] as $a): ?>
            <div class="inv-line">
                <span class="inv-line-desc"><?php echo htmlspecialchars($a['name']); ?><span class="inv-tag">Allowance</span></span>
                <span class="inv-line-amt earn">Rs <?php echo number_format($a['amount']); ?></span>
            </div>
            <?php endforeach; ?>
            <?php if ($standard_allowance > 0): ?>
            <div class="inv-line">
                <span class="inv-line-desc">Standard Allowance<span class="inv-tag">Allowance</span></span>
                <span class="inv-line-amt earn">Rs <?php echo number_format($standard_allowance); ?></span>
            </div>
            <?php endif; ?>

            <div class="inv-section-head" style="margin-top:4px">
                <span>Deductions</span>
                <span>Amount</span>
            </div>

            <?php if (!empty($monthly_data['deductions'])):
                foreach ($monthly_data['deductions'] as $d): ?>
            <div class="inv-line">
                <span class="inv-line-desc"><?php echo htmlspecialchars($d['name']); ?></span>
                <span class="inv-line-amt ded">Rs <?php echo number_format($d['amount']); ?></span>
            </div>
            <?php endforeach; else: ?>
            <div class="inv-line">
                <span class="inv-line-desc" style="color:#ccc; font-style:italic">No deductions this period</span>
                <span class="inv-line-amt nil">—</span>
            </div>
            <?php endif; ?>

        </div>

        <!-- Totals -->
        <div class="inv-totals">
            <div class="inv-sum-row">
                <span>Gross Earnings</span>
                <span>Rs <?php echo number_format($total_earnings); ?></span>
            </div>
            <div class="inv-sum-row">
                <span>Total Deductions</span>
                <span>Rs <?php echo number_format($total_deductions); ?></span>
            </div>
            <div class="inv-net">
                <span class="inv-net-label">Net Payable</span>
                <span class="inv-net-amount">Rs <?php echo number_format($monthly_data['net_payable']); ?></span>
            </div>
        </div>

        <!-- Footer -->
        <div class="inv-foot">
            <span>Computer-generated document &mdash; No signature required.</span>
            <span>CodeXentric &bull; EMS</span>
        </div>

    </div>
    <?php else: ?>
    <div class="inv-empty">
        <h2>Payslip Not Found</h2>
        <p>No payroll record found for the selected period. It may not have been processed yet.</p>
    </div>
    <?php endif; ?>

</div>

<?php include_once "../includes/footer.php"; ?>
