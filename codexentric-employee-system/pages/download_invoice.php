<?php
session_start();

// 1. Role Protection
if (!isset($_SESSION['email'])) {
    die("Access Denied.");
}

// 2. Load mPDF (Assumes Composer installation)
if (!file_exists('../vendor/autoload.php')) {
    die("Error: mPDF is not installed. Please run 'composer require mpdf/mpdf' in the project root.");
}

require_once '../vendor/autoload.php';
require_once "../pages/Database.php";
require_once "../pages/Employee.php";
require_once "../pages/Payroll.php";

$db = new Database();
$connection = $db->getConnection();
$employeeObj = new Employee($connection);
$payrollObj = new Payroll($connection);

// 3. Pull query params
$selected_user_id = isset($_GET['employeeId']) ? (int)$_GET['employeeId'] : null;
$selected_month   = isset($_GET['month'])      ? $_GET['month']             : date('Y-m');

// Role Lock: Non-admins can only download their own slips
if ($_SESSION['role_id'] != '1' && $selected_user_id != $_SESSION['user_id']) {
    die("Access Denied: Security Restriction.");
}

if (!$selected_user_id) {
    die("Invalid Employee ID.");
}

// 4. Fetch Data (Mirror logic from salary_invoice.php)
$employee_name = '';
$employee_role = '';
$employee_bank = '';
$monthly_data  = null;

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
        break;
    }
}

$real_emp_id = $payrollObj->getEmployeeIdByUserId($selected_user_id);
if ($real_emp_id) {
    $payroll_month_str = $selected_month . '-01';
    $monthly_data = $payrollObj->getFullSalaryRecord($real_emp_id, $payroll_month_str);
}

if (!$monthly_data) {
    die("No salary record found for this month.");
}

// 5. Build HTML for mPDF
$total_earnings = $monthly_data['base_salary'];
foreach ($monthly_data['bonuses'] as $b)    $total_earnings += $b['amount'];
foreach ($monthly_data['allowances'] as $a) $total_earnings += $a['amount'];
$total_deductions = 0;
if (!empty($monthly_data['deductions']))
    foreach ($monthly_data['deductions'] as $d) $total_deductions += $d['amount'];

$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body { 
            font-family: "Helvetica", "Arial", sans-serif; 
            color: #1e293b; 
            line-height: 1.4; 
            margin: 0;
            padding: 0;
        }
        .top-bar { height: 12px; background: #186D55; width: 100%; }
        .bottom-bar { height: 12px; background: #186D55; width: 100%; position: absolute; bottom: 0; }
        
        .container { padding: 40px 50px; }
        
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .header-title { font-size: 36px; font-family: "Arial Black", "Helvetica", sans-serif; font-weight: 900; color: #1e293b; letter-spacing: -1.5px; text-transform: uppercase; }
        
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .sender-info { font-size: 13px; color: #475569; line-height: 1.4; }
        .sender-info strong { color: #1e293b; font-size: 15px; font-family: "Arial Black", sans-serif; font-weight: 900; }
        .invoice-details { text-align: right; font-size: 11px; color: #64748b; line-height: 1.6; }
        .invoice-details strong { color: #334155; font-weight: 900; }
        
        .billing-table { width: 100%; border-collapse: collapse; background: #f8fafc; margin-bottom: 30px; }
        .billing-col { width: 50%; padding: 20px 25px; vertical-align: top; }
        .billing-col h4 { font-size: 11px; font-weight: 900; text-transform: uppercase; color: #186D55; margin: 0 0 8px 0; }
        .billing-col p { font-size: 13px; color: #334155; margin: 0; line-height: 1.5; }
        .billing-col strong { color: #0f172a; font-weight: 900; }
        
        .status-pill { 
            display: inline-block;
            background: #ffffff; 
            color: #059669; 
            border: 1.5px solid #a7f3d0; 
            padding: 2px 8px; 
            border-radius: 4px; 
            font-size: 9px; 
            font-weight: 800; 
        }
        .status-dot { color: #059669; font-size: 14px; margin-right: 4px; vertical-align: middle; }

        .main-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .main-table th { 
            background: #186D55; 
            color: #ffffff; 
            font-size: 10px; 
            font-weight: 800; 
            text-transform: uppercase; 
            padding: 12px 16px; 
            text-align: left;
            border-right: 1px solid rgba(255,255,255,0.2);
        }
        .main-table th:last-child { border-right: none; }
        .main-table td { padding: 14px 16px; font-size: 12px; color: #475569; border-bottom: 1px solid #f1f5f9; }
        .main-table td.amount { text-align: right; font-weight: 700; color: #1e293b; }
        
        .totals-container { width: 100%; margin-top: 25px; text-align: right; }
        .totals-wrapper { width: 220px; display: inline-block; text-align: right; }
        .totals-table { width: 100%; border-collapse: collapse; }
        .totals-table td { padding: 6px 0; font-size: 11px; color: #64748b; font-weight: 700; text-align: right; }
        .totals-table td.val { font-weight: 800; color: #1e293b; font-size: 13px; width: 100px; }
        
        .balance-box { 
            border-top: 2.5px solid #186D55; 
            border-bottom: 2.5px solid #186D55; 
            margin-top: 10px;
            padding: 12px 0;
            color: #186D55;
        }
        .balance-table { width: 100%; border-collapse: collapse; }
        .balance-table td { color: #186D55; font-weight: 900; font-size: 13px; line-height: 1.2; text-align: right; vertical-align: top; }
        .balance-label { text-align: right !important; padding-right: 15px; }
        .balance-currency { font-size: 11px; width: 80px; padding-bottom: 5px; }
        .balance-amount { font-size: 22px; font-family: "Arial Black", sans-serif; width: 80px; }
        
        .footer { text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 15px; }
        .clear { clear: both; }
    </style>
</head>
<body>
    <htmlpagefooter name="myfooter">
        <div class="footer">
            <p style="margin-bottom: 5px;">This is a computer-generated salary invoice. No signature required.</p>
            <p>&copy; ' . date('Y') . ' CodeXentric HRM. All rights reserved.</p>
        </div>
    </htmlpagefooter>
    <sethtmlpagefooter name="myfooter" value="on" />

    <div class="top-bar"></div>
    
    <div class="container">
        <table class="header-table" style="margin-bottom: 40px;">
            <tr>
                <td class="header-title">SALARY REPORT</td>
                <td style="text-align: right;">
                    <svg viewBox="0 0 260 80" width="160" height="50">
                        <path d="M 25 25 L 10 40 L 25 55" fill="none" stroke="#f97316" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
                        <text x="36" y="49" font-family="Helvetica" font-weight="bold" font-size="28" fill="#186D55">code</text>
                        <line x1="110" y1="28" x2="128" y2="52" stroke="#186D55" stroke-width="7" stroke-linecap="round" />
                        <line x1="110" y1="52" x2="128" y2="24" stroke="#f97316" stroke-width="7" stroke-linecap="round" />
                        <text x="132" y="49" font-family="Helvetica" font-weight="bold" font-size="28" fill="#186D55">entric</text>
                        <path d="M 235 25 L 250 40 L 235 55" fill="none" stroke="#f97316" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </td>
            </tr>
        </table>
        
        <table class="meta-table">
            <tr>
                <td class="sender-info">
                    <strong>codeXentric</strong><br>
                    First Floor, Sardar Plaza, Qilla road,<br>
                    Muzaffarabad, Azad Jammu & Kashmir<br>
                    hr@codexentric.com
                </td>
                <td class="invoice-details">
                    <strong>DATE:</strong> ' . date('M d, Y') . '<br>
                    <strong>PAYSLIP NO:</strong> EMP-' . str_pad($selected_user_id, 4, '0', STR_PAD_LEFT) . '-' . date('mY', strtotime($selected_month . '-01')) . '<br>
                    <strong>PERIOD:</strong> ' . date('M Y', strtotime($selected_month . '-01')) . '
                </td>
            </tr>
        </table>
        
        <table class="billing-table">
            <tr>
                <td class="billing-col" style="border-right: 1px solid #e2e8f0;">
                    <h4>BILL TO (EMPLOYEE)</h4>
                    <p>
                        <strong>' . htmlspecialchars($employee_name) . '</strong><br>
                        Designation: ' . htmlspecialchars($employee_role) . '<br>
                        Email: ' . htmlspecialchars($_SESSION['email'] ?? 'employee@codexentric.com') . '
                    </p>
                </td>
                <td class="billing-col">
                    <h4>SHIP TO (DISBURSEMENT)</h4>
                    <p>
                        Bank Account: ' . htmlspecialchars($employee_bank ?: 'Not Provided') . '<br>
                        Status: <span class="status-pill"><span class="status-dot">&bull;</span>' . htmlspecialchars($monthly_data['status']) . '</span><br>
                        Disbursed On: ' . date('d M, Y') . '
                    </p>
                </td>
            </tr>
        </table>
        
        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 50%;">DESCRIPTION</th>
                    <th style="width: 25%;">CATEGORY</th>
                    <th style="width: 25%; text-align: right;">AMOUNT (RS)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Basic Base Salary Disbursement</td>
                    <td>Base Salary</td>
                    <td class="amount">Rs ' . number_format($monthly_data['base_salary'], 2) . '</td>
                </tr>';

foreach ($monthly_data['bonuses'] as $b) {
    $html .= '<tr>
                <td>Performance Bonus - ' . htmlspecialchars($b['name']) . '</td>
                <td>Bonus</td>
                <td class="amount">Rs ' . number_format($b['amount'], 2) . '</td>
              </tr>';
}

foreach ($monthly_data['allowances'] as $a) {
    $html .= '<tr>
                <td>Approved Allowance - ' . htmlspecialchars($a['name']) . '</td>
                <td>Allowance</td>
                <td class="amount">Rs ' . number_format($a['amount'], 2) . '</td>
              </tr>';
}


if (!empty($monthly_data['deductions'])) {
    foreach ($monthly_data['deductions'] as $d) {
        $html .= '<tr>
                    <td>Deduction Retainment - ' . htmlspecialchars($d['name']) . '</td>
                    <td>Deduction</td>
                    <td class="amount" style="color: #ef4444;">- Rs ' . number_format($d['amount'], 2) . '</td>
                  </tr>';
    }
}

$html .= '  </tbody>
        </table>
        
        <div class="totals-container">
            <div class="totals-wrapper">
                <table class="totals-table">
                    <tr>
                        <td>SUBTOTAL (Gross Earnings)</td>
                        <td class="val">Rs ' . number_format($total_earnings, 2) . '</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 5px;">DEDUCTIONS RETAINED</td>
                        <td class="val" style="color: #ef4444; padding-bottom: 5px;">- Rs ' . number_format($total_deductions, 2) . '</td>
                    </tr>
                </table>
                <div class="balance-box">
                    <table class="balance-table">
                        <tr>
                            <td class="balance-label" style="font-family: \'Arial Black\', sans-serif;">Balance Due<br>(Net Payable)</td>
                            <td class="balance-currency">Rs</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="balance-amount">' . number_format($total_earnings - $total_deductions, 2) . '</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    
    <div class="bottom-bar"></div>
</body>
</html>';

// 6. Initialize mPDF and Generate
try {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 30, // Room for footer
    ]);

    $mpdf->SetDisplayMode('fullpage');
    $mpdf->WriteHTML($html);
    
    $filename = "Salary_Invoice_" . str_replace(' ', '_', $employee_name) . "_" . $selected_month . ".pdf";
    $mpdf->Output($filename, 'D'); // 'D' forces download
} catch (\Exception $e) {
    die("PDF Generation Error: " . $e->getMessage());
}


