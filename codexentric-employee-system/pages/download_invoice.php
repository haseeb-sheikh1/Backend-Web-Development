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

// 4. Fetch Data
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

// 5. Compute totals
$total_earnings = $monthly_data['base_salary'];
foreach ($monthly_data['bonuses'] as $b)    $total_earnings += $b['amount'];
foreach ($monthly_data['allowances'] as $a) $total_earnings += $a['amount'];
$total_deductions = 0;
if (!empty($monthly_data['deductions']))
    foreach ($monthly_data['deductions'] as $d) $total_deductions += $d['amount'];

$net_payable = $total_earnings - $total_deductions;

// 6. Build HTML for mPDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        /* ── Page reset ── */
        @page {
            margin-top: 0;
            margin-left: 0;
            margin-right: 0;
            margin-bottom: 30px;
        }
        body {
            font-family: "Helvetica", "Arial", sans-serif;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* ── Accent bars ── */
        .top-bar    { height: 12px; background: #186D55; width: 100%; display: block; }

        /* ── Main content wrapper ── */
        .container  { padding: 36px 50px 20px 50px; }

        /* ── Header: title + logo ── */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 36px; }
        .header-title {
            font-size: 34px;
            font-family: "Arial Black", "Helvetica", sans-serif;
            font-weight: 900;
            color: #1e293b;
            letter-spacing: -1.5px;
            text-transform: uppercase;
        }

        /* ── Sender / invoice meta ── */
        .meta-table   { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .sender-info  { font-size: 12px; color: #475569; line-height: 1.5; }
        .sender-info strong { color: #1e293b; font-size: 14px; font-weight: 900; }
        .invoice-details {
            text-align: right;
            font-size: 11px;
            color: #64748b;
            line-height: 1.8;
            vertical-align: top;
        }
        .invoice-details strong { color: #334155; font-weight: 900; }

        /* ── Billing / disbursement block ── */
        .billing-table { width: 100%; border-collapse: collapse; background: #f8fafc; margin-bottom: 28px; }
        .billing-col   { width: 50%; padding: 18px 24px; vertical-align: top; }
        .billing-col h4 {
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            color: #186D55;
            margin: 0 0 8px 0;
            letter-spacing: 0.5px;
        }
        .billing-col p  { font-size: 12px; color: #334155; margin: 0; line-height: 1.6; }
        .billing-col strong { color: #0f172a; font-weight: 900; }

        /* ── Line-items table ── */
        .main-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .main-table th {
            background: #186D55;
            color: #ffffff;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 12px 10px;
            text-align: left;
            border-left: none !important;
            border-right: none !important;
        }
        .main-table th:last-child  { text-align: right; }
        .main-table td             { padding: 12px 10px; font-size: 12px; color: #475569; border-bottom: 1px solid #f1f5f9; }
        .main-table td.amount      { text-align: right; font-weight: 700; color: #1e293b; }
        .main-table td.deduction   { text-align: right; font-weight: 700; color: #ef4444; }

        /* ── Totals block ── */
        .totals-outer { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .totals-spacer { }   /* left empty cell */
        .totals-inner {
            width: 400px;
            border-collapse: collapse;
        }
        .totals-inner td {
            font-size: 12px;
            color: #64748b;
            padding: 7px 0;
            white-space: nowrap;
        }
        .totals-inner td.lbl  { text-align: right; padding-right: 20px; width: 65%; }
        .totals-inner td.val  { text-align: right; font-weight: 700; color: #1e293b; width: 35%; }
        .totals-inner td.val-red  { text-align: right; font-weight: 700; color: #ef4444; width: 35%; }

        /* Balance Due row */
        .bal-lbl {
            text-align: right;
            padding-right: 20px;
            padding-top: 10px;
            padding-bottom: 10px;
            font-size: 14px;
            font-weight: 900;
            color: #186D55;
            border-top: 2px solid #186D55;
            border-bottom: 2.5px double #186D55;
            white-space: nowrap;
            width: 65%;
        }
        .bal-val {
            text-align: right;
            padding-top: 10px;
            padding-bottom: 10px;
            font-size: 14px;
            font-weight: 900;
            color: #186D55;
            border-top: 2px solid #186D55;
            border-bottom: 2.5px double #186D55;
            white-space: nowrap;
            width: 35%;
        }

        /* ── Page footer (mPDF named footer) ── */
        .footer-content {
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            margin-bottom: 15px;
        }

        /* ── Bottom green bar rendered as last element in body ── */
        .bottom-bar { height: 12px; background: #186D55; width: 100%; display: block; margin: 0; padding: 0; }
    </style>
</head>
<body>

    <!-- mPDF named footer -->
    <htmlpagefooter name="myfooter">
        <div style="padding-left: 50px; padding-right: 50px;">
            <div class="footer-content">
                <p style="margin:0 0 4px 0;">This is a computer-generated salary invoice. No signature required.</p>
                <p style="margin:0;">&copy; ' . date('Y') . ' CodeXentric HRM. All rights reserved.</p>
            </div>
        </div>
        <div class="bottom-bar"></div>
    </htmlpagefooter>
    <sethtmlpagefooter name="myfooter" value="on" />

    <!-- TOP GREEN BAR -->
    <div class="top-bar"></div>

    <div class="container">

        <!-- ══ HEADER ══ -->
        <table class="header-table">
            <tr>
                <td class="header-title">SALARY REPORT</td>
                <td style="text-align:right; vertical-align:middle;">
                    <!--
                        mPDF has limited SVG support. We render the logo as inline SVG.
                        If SVG does not render correctly, replace with an <img> tag
                        pointing to a pre-exported PNG logo.
                    -->
                    <svg viewBox="0 0 260 80" width="160" height="50" xmlns="http://www.w3.org/2000/svg">
                        <path d="M 25 25 L 10 40 L 25 55" fill="none" stroke="#f97316" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                        <text x="36" y="49" font-family="Helvetica" font-weight="bold" font-size="28" fill="#186D55">code</text>
                        <line x1="110" y1="28" x2="128" y2="52" stroke="#186D55" stroke-width="7" stroke-linecap="round"/>
                        <line x1="110" y1="52" x2="128" y2="24" stroke="#f97316" stroke-width="7" stroke-linecap="round"/>
                        <text x="132" y="49" font-family="Helvetica" font-weight="bold" font-size="28" fill="#186D55">entric</text>
                        <path d="M 235 25 L 250 40 L 235 55" fill="none" stroke="#f97316" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </td>
            </tr>
        </table>

        <!-- ══ SENDER + INVOICE META ══ -->
        <table class="meta-table">
            <tr>
                <td class="sender-info" style="vertical-align:top;">
                    <strong>codeXentric</strong><br>
                    First Floor, Sardar Plaza, Qilla road,<br>
                    Muzaffarabad, Azad Jammu &amp; Kashmir<br>
                    hr@codexentric.com
                </td>
                <td class="invoice-details">
                    <strong>DATE:</strong> ' . date('M d, Y') . '<br>
                    <strong>PAYSLIP NO:</strong> EMP-' . str_pad($selected_user_id, 4, '0', STR_PAD_LEFT) . '-' . date('mY', strtotime($selected_month . '-01')) . '<br>
                    <strong>PERIOD:</strong> ' . date('M Y', strtotime($selected_month . '-01')) . '
                </td>
            </tr>
        </table>

        <!-- ══ BILLING / DISBURSEMENT ══ -->
        <table class="billing-table">
            <tr>
                <td class="billing-col" style="border-right:1px solid #e2e8f0;">
                    <h4>BILL TO (EMPLOYEE)</h4>
                    <p>
                        <strong>' . htmlspecialchars($employee_name) . '</strong><br>
                        Designation: ' . htmlspecialchars($employee_role) . '<br>
                        Email: ' . htmlspecialchars($_SESSION['email'] ?? 'employee@codexentric.com') . '
                    </p>
                </td>
                <td class="billing-col">
                    <h4>SHIP TO (DISBURSEMENT)</h4>
                    <p style="margin-bottom: 4px;">
                        Bank Account: ' . htmlspecialchars($employee_bank ?: 'Not Provided') . '
                    </p>
                    <table style="border-collapse: collapse; margin-bottom: 4px;">
                        <tr>
                            <td style="font-size: 12px; color: #334155; padding-right: 6px; padding-bottom: 0; padding-top: 0;">Status:</td>
                            <td style="background: #ffffff; color: #059669; border: 1.5px solid #a7f3d0; padding: 3px 8px; border-radius: 4px; font-size: 9px; font-weight: bold;">
                                <strong>&#9679; ' . htmlspecialchars($monthly_data['status']) . '</strong>
                            </td>
                        </tr>
                    </table>
                    <p style="margin-top: 0;">
                        Disbursed On: ' . date('d M, Y') . '
                    </p>
                </td>
            </tr>
        </table>

        <!-- ══ LINE ITEMS ══ -->
        <table class="main-table">
            <thead>
                <tr>
                    <th style="width:50%;"><strong>DESCRIPTION</strong></th>
                    <th style="width:25%;"><strong>CATEGORY</strong></th>
                    <th style="width:25%; text-align:right;"><strong>AMOUNT (RS)</strong></th>
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
                    <td class="deduction">- Rs ' . number_format($d['amount'], 2) . '</td>
                  </tr>';
    }
}

$html .= '      </tbody>
        </table>

        <!-- ══ TOTALS ══
             We use a two-column outer table:
               col 1 (left spacer) = auto width
               col 2 (totals)      = 300px fixed
             This is the reliable mPDF way to right-align a block.
        -->
        <table class="totals-outer">
            <tr>
                <td class="totals-spacer">&nbsp;</td>
                <td style="width:400px; vertical-align:top; padding:0;">
                    <table class="totals-inner" style="width:100%;">
                        <tbody>
                            <tr>
                                <td class="lbl">SUBTOTAL (Gross Earnings)</td>
                                <td class="val">Rs ' . number_format($total_earnings, 2) . '</td>
                            </tr>
                            <tr>
                                <td class="lbl">DEDUCTIONS RETAINED</td>
                                <td class="val-red">- Rs ' . number_format($total_deductions, 2) . '</td>
                            </tr>
                            <tr>
                                <td class="bal-lbl"><strong>Balance Due (Net Payable)</strong></td>
                                <td class="bal-val"><strong>Rs ' . number_format($net_payable, 2) . '</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>

    </div><!-- /.container -->

</body>
</html>';

// 7. Initialize mPDF and Generate
try {
    $mpdf = new \Mpdf\Mpdf([
        'mode'          => 'utf-8',
        'format'        => 'A4',
        'margin_left'   => 0,
        'margin_right'  => 0,
        'margin_top'    => 0,
        'margin_bottom' => 30,  // Space reserved for the named footer
        'margin_footer' => 0,   // Force footer to bottom of page
    ]);

    $mpdf->SetDisplayMode('fullpage');
    $mpdf->WriteHTML($html);

    $filename = 'Salary_Invoice_' . str_replace(' ', '_', $employee_name) . '_' . $selected_month . '.pdf';
    $mpdf->Output($filename, 'D'); // 'D' = force download
} catch (\Exception $e) {
    die('PDF Generation Error: ' . $e->getMessage());
}