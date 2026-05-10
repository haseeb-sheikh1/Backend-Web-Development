<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$title     = "Salary Invoice";
$extra_css = "salary_reports";
$current_page = "payroll"; // Keep the payroll sidebar item active
include_once "../includes/header.php";

require_once "../pages/Database.php";
require_once "../pages/Employee.php";
require_once "../pages/Payroll.php";

$db = new Database();
$connection = $db->getConnection();
$employeeObj = new Employee($connection);
$payrollObj = new Payroll($connection);

// Fetch employee list for details
$employees = $employeeObj->getBasicEmployeeDetails();

// Pull query params
$selected_user_id = isset($_GET['employeeId']) ? (int)$_GET['employeeId'] : null;
$selected_month   = isset($_GET['month'])      ? $_GET['month']             : date('Y-m');

// Security Gate: Non-admins can ONLY view their own IDs
if ($_SESSION['role_id'] != '1' && $selected_user_id != $_SESSION['user_id']) {
    echo "<div style='padding:100px; text-align:center; font-family:sans-serif;'><h2>Unauthorized Access Locked</h2><p>You do not have privilege to access this statement.</p><a href='employee_dashboard.php'>Return Dashboard</a></div>";
    exit();
}

$employee_name = '';
$employee_role = '';
$employee_bank = '';
$monthly_data  = null;

if ($selected_user_id) {
    $real_emp_id = $payrollObj->getEmployeeIdByUserId($selected_user_id);
    
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
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

.inv-wrap * { box-sizing: border-box; }

.inv-wrap {
    max-width: 800px;
    margin: 0 auto 64px;
    font-family: 'Inter', sans-serif;
    color: #1e293b;
}

/* Remove top gap from main-content shell */
.main-content { padding-top: 0 !important; }

/* Toolbar */
.inv-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}
.inv-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 700;
    color: #475569;
    text-decoration: none;
    padding: 10px 18px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    transition: all 0.2s;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}
.inv-back:hover { border-color: #cbd5e1; background: #f8fafc; color: #0f172a; transform: translateY(-1px); }

/* Minimal Invoice Card */
.inv-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.02);
    position: relative;
}

/* Thick Top Brand Bar */
.inv-card-top-bar {
    height: 12px;
    background: #186D55;
    width: 100%;
}

/* Header Content */
.inv-head-container {
    padding: 40px 50px 30px 50px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.inv-title {
    font-size: 32px;
    font-weight: 800;
    color: #334155;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin: 0;
}

/* Circular Logo */
.inv-logo-circle {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    background: #186D55;
    color: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(24, 109, 85, 0.15);
}
.inv-logo-text {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin: 0;
}
.inv-logo-subtext {
    font-size: 8px;
    font-weight: 600;
    opacity: 0.8;
    margin-top: 2px;
}

/* Metadata Block */
.inv-meta-block {
    padding: 0 50px 30px 50px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.inv-sender-info {
    font-size: 13px;
    line-height: 1.6;
    color: #64748b;
}
.inv-sender-info strong {
    color: #1e293b;
    font-size: 14px;
}

.inv-date-no {
    text-align: right;
    font-size: 13px;
    color: #64748b;
    line-height: 1.6;
}
.inv-date-no strong {
    color: #1e293b;
}

/* Billing Side-by-Side */
.inv-billing-details {
    padding: 20px 50px;
    border-top: 1px solid #f1f5f9;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    background: #fafafa;
}

.billing-col h4 {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #186D55;
    margin: 0 0 10px 0;
}
.billing-col p {
    font-size: 13px;
    line-height: 1.6;
    color: #475569;
    margin: 0;
}
.billing-col p strong {
    color: #0f172a;
}

/* Status Pill */
.status-pill-invoice {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
    padding: 2px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}
.status-pill-invoice::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #059669;
}

/* Solid Minimal Table */
.inv-table-container {
    padding: 30px 50px;
}

.invoice-minimal-table {
    width: 100%;
    border-collapse: collapse;
}

.invoice-minimal-table th {
    background: #186D55;
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 12px 16px;
    text-align: left;
}
.invoice-minimal-table th:last-child {
    text-align: right;
}

.invoice-minimal-table td {
    padding: 14px 16px;
    font-size: 13px;
    color: #475569;
    border-bottom: 1px solid #f1f5f9;
}
.invoice-minimal-table td:last-child {
    text-align: right;
    font-weight: 700;
    color: #1e293b;
}

.invoice-minimal-table tr:hover td {
    background: #f8fafc;
}

/* Totals Section matching template exactly */
.inv-totals-container {
    padding: 20px 50px 40px 50px;
    display: flex;
    justify-content: flex-end;
}

.inv-totals-table {
    width: 280px;
}
.inv-totals-table td {
    padding: 8px 0;
    font-size: 13px;
    color: #64748b;
}
.inv-totals-table td:last-child {
    text-align: right;
    font-weight: 600;
    color: #1e293b;
}

/* Bottom Highlight - Balance Due Style */
.balance-due-row td {
    font-size: 15px !important;
    font-weight: 800 !important;
    color: #186D55 !important;
    border-top: 2px solid #186D55;
    border-bottom: 2px double #186D55;
    padding: 12px 0 !important;
}
.balance-due-row td:last-child {
    color: #186D55 !important;
}

/* Bottom Brand Bar */
.inv-card-bottom-bar {
    height: 12px;
    background: #186D55;
    width: 100%;
}

/* Print Styles */
@media print {
    body * { visibility: hidden; }
    .inv-card, .inv-card * { visibility: visible; }
    .inv-card { position: absolute; inset: 0; border: none; box-shadow: none; }
    .inv-bar { display: none; }
}

/* Mobile View Responsiveness */
@media (max-width: 600px) {
    .inv-wrap {
        padding: 0 8px;
        margin: 4px auto 16px;
    }
    .inv-bar {
        margin-bottom: 12px;
    }
    .inv-back {
        padding: 6px 12px;
        font-size: 11px;
    }
    .inv-card-top-bar {
        height: 6px;
    }
    .inv-head-container {
        padding: 12px 14px 8px 14px;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .inv-title {
        font-size: 18px;
    }
    .inv-logo-image-wrapper {
        position: static !important;
    }
    .inv-logo-image-wrapper svg {
        width: 120px !important;
        height: 38px !important;
    }
    .inv-meta-block {
        padding: 0 14px 8px 14px;
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
    }
    .inv-sender-info, .inv-date-no {
        font-size: 9.5px;
        line-height: 1.3;
    }
    .inv-sender-info strong, .inv-date-no strong {
        font-size: 10px;
    }
    .inv-date-no {
        text-align: right;
    }
    .inv-billing-details {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        padding: 8px 14px;
    }
    .billing-col h4 {
        font-size: 8.5px;
        margin-bottom: 3px;
    }
    .billing-col p {
        font-size: 9.5px;
        line-height: 1.3;
    }
    .status-pill-invoice {
        font-size: 8px;
        padding: 1px 4px;
    }
    .status-pill-invoice::before {
        width: 4px;
        height: 4px;
    }
    .inv-table-container {
        padding: 8px 14px;
        overflow-x: visible;
    }
    .invoice-minimal-table {
        min-width: 100% !important;
    }
    .invoice-minimal-table th:nth-child(2),
    .invoice-minimal-table td:nth-child(2),
    .invoice-minimal-table th:nth-child(3),
    .invoice-minimal-table td:nth-child(3) {
        display: none; /* Hide Category & Unit Price on mobile to make it fit beautifully */
    }
    .invoice-minimal-table th, .invoice-minimal-table td {
        padding: 6px 8px;
        font-size: 10px;
    }
    .inv-totals-container {
        padding: 6px 14px 12px 14px;
        justify-content: flex-end;
    }
    .inv-totals-table {
        width: 100%;
    }
    .inv-totals-table td {
        padding: 3px 0;
        font-size: 10px;
    }
    .balance-due-row td {
        font-size: 11px !important;
        padding: 6px 0 !important;
    }
    .inv-card-bottom-bar {
        height: 6px;
    }
}
</style>

<div class="inv-wrap">

    <div class="inv-bar">
        <?php if ($_SESSION['role_id'] == '1'): ?>
        <a href="salary_reports.php" class="inv-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Reports
        </a>
        <?php else: ?>
        <a href="employee_payroll.php" class="inv-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to History
        </a>
        <?php endif; ?>
        <a href="download_invoice.php?employeeId=<?php echo $selected_user_id; ?>&month=<?php echo $selected_month; ?>" class="inv-back" style="background: var(--brand-green); color: #fff; border-color: var(--brand-green);">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            Download HD PDF
        </a>
    </div>

    <?php if ($monthly_data): ?>
    <?php
        $total_earnings = $monthly_data['base_salary'];
        foreach ($monthly_data['bonuses'] as $b)    $total_earnings += $b['amount'];
        foreach ($monthly_data['allowances'] as $a) $total_earnings += $a['amount'];
        $total_deductions = 0;
        if (!empty($monthly_data['deductions']))
            foreach ($monthly_data['deductions'] as $d) $total_deductions += $d['amount'];
    ?>
    <div class="inv-card">
        
        <!-- Top Green Brand Bar -->
        <div class="inv-card-top-bar"></div>

        <!-- Header Row -->
        <div class="inv-head-container">
            <div>
                <h1 class="inv-title">Salary Report</h1>
            </div>
            
            <!-- Official Brand Vector Logo -->
            <div class="inv-logo-image-wrapper" style="position: relative; left: 5px; top: 5px;">
                <svg viewBox="0 0 260 80" width="190" height="58" style="display: block;">
                    <!-- Left Chevron -->
                    <path d="M 25 25 L 10 40 L 25 55" fill="none" stroke="#f97316" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round" />
                    
                    <!-- "code" text -->
                    <text x="36" y="49" font-family="'Inter', sans-serif" font-weight="800" font-size="28" fill="#186D55" letter-spacing="-0.5">code</text>
                    
                    <!-- Stylized "X" -->
                    <!-- Teal slash -->
                    <line x1="110" y1="28" x2="128" y2="52" stroke="#186D55" stroke-width="6.5" stroke-linecap="round" />
                    <!-- Orange slash -->
                    <line x1="110" y1="52" x2="128" y2="24" stroke="#f97316" stroke-width="6.5" stroke-linecap="round" />
                    
                    <!-- "entric" text -->
                    <text x="132" y="49" font-family="'Inter', sans-serif" font-weight="800" font-size="28" fill="#186D55" letter-spacing="-0.5">entric</text>
                    
                    <!-- Right Chevron -->
                    <path d="M 235 25 L 250 40 L 235 55" fill="none" stroke="#f97316" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>

        <!-- Metadata Block -->
        <div class="inv-meta-block">
            <div class="inv-sender-info">
                <strong>codeXentric</strong><br>
                First Floor, Sardar Plaza, Qilla road,<br>
                Muzaffarabad, Azad Jammu & Kashmir<br>
                hr@codexentric.com
            </div>
            <div class="inv-date-no">
                <strong>DATE:</strong> <?php echo date('F d, Y'); ?><br>
                <strong>PAYSLIP NO:</strong> EMP-<?php echo str_pad($selected_user_id, 4, '0', STR_PAD_LEFT); ?>-<?php echo date('mY', strtotime($selected_month . '-01')); ?><br>
                <strong>PERIOD:</strong> <?php echo date('F Y', strtotime($selected_month . '-01')); ?>
            </div>
        </div>

        <!-- Billing details (Side-by-Side) -->
        <div class="inv-billing-details">
            <div class="billing-col">
                <h4>Bill To (Employee)</h4>
                <p>
                    <strong><?php echo htmlspecialchars($employee_name); ?></strong><br>
                    Designation: <?php echo htmlspecialchars($employee_role); ?><br>
                    Email: <?php echo htmlspecialchars($_SESSION['email'] ?? 'employee@codexentric.com'); ?>
                </p>
            </div>
            <div class="billing-col">
                <h4>Ship To (Disbursement)</h4>
                <p>
                    Bank Account: <?php echo htmlspecialchars($employee_bank ?: 'Not Provided'); ?><br>
                    Status: <span class="status-pill-invoice"><?php echo htmlspecialchars($monthly_data['status']); ?></span><br>
                    Disbursed On: <?php echo date('d M, Y'); ?>
                </p>
            </div>
        </div>

        <!-- Solid Minimal Table -->
        <div class="inv-table-container">
            <table class="invoice-minimal-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Category</th>
                        <th style="text-align: right;">Amount (Rs)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Basic Base Salary Disbursement</td>
                        <td>Base Salary</td>
                        <td style="text-align: right;"><strong>Rs <?php echo number_format($monthly_data['base_salary'], 2); ?></strong></td>
                    </tr>
                    <?php foreach ($monthly_data['bonuses'] as $b): ?>
                    <tr>
                        <td>Performance Bonus - <?php echo htmlspecialchars($b['name']); ?></td>
                        <td>Bonus</td>
                        <td style="text-align: right;"><strong>Rs <?php echo number_format($b['amount'], 2); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php foreach ($monthly_data['allowances'] as $a): ?>
                    <tr>
                        <td>Approved Allowance - <?php echo htmlspecialchars($a['name']); ?></td>
                        <td>Allowance</td>
                        <td style="text-align: right;"><strong>Rs <?php echo number_format($a['amount'], 2); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!empty($monthly_data['deductions'])): 
                        foreach ($monthly_data['deductions'] as $d): ?>
                    <tr>
                        <td>Deduction Retainment - <?php echo htmlspecialchars($d['name']); ?></td>
                        <td>Deduction</td>
                        <td style="text-align: right; color: var(--danger);"><strong>- Rs <?php echo number_format($d['amount'], 2); ?></strong></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals calculations matching screenshot -->
        <div class="inv-totals-container">
            <table class="inv-totals-table">
                <tr>
                    <td>SUBTOTAL (Gross Earnings)</td>
                    <td>Rs <?php echo number_format($total_earnings, 2); ?></td>
                </tr>
                <tr>
                    <td>DEDUCTIONS RETAINED</td>
                    <td style="color: var(--danger);">- Rs <?php echo number_format($total_deductions, 2); ?></td>
                </tr>
                <tr class="balance-due-row">
                    <td>Balance Due (Net Payable)</td>
                    <td>Rs <?php echo number_format($total_earnings - $total_deductions, 2); ?></td>
                </tr>
            </table>
        </div>

        <!-- Bottom Green Brand Bar -->
        <div class="inv-card-bottom-bar"></div>

    </div>
    <?php else: ?>
    <div class="inv-empty" style="padding: 100px 40px; text-align: center; background: #fff; border: 1px solid #e2e8f0; border-radius: 4px;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="margin-bottom:16px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <h2 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0;">Payslip Not Found</h2>
        <p style="font-size: 14px; color: #64748b; margin: 0;">No payroll record found for the selected period. It may not have been processed yet.</p>
    </div>
    <?php endif; ?>

</div>

<?php include_once "../includes/footer.php"; ?>
