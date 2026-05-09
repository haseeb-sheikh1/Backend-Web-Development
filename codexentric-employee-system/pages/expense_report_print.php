<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role_id'] != '1') {
    header("Location: login.php");
    exit();
}

require_once '../pages/database.php';
$db = new Database();
$conn = $db->getConnection();

$report_type    = isset($_GET['report_type']) ? $_GET['report_type']       : 'monthly';
$selected_month = isset($_GET['month'])       ? $_GET['month']             : date('Y-m');
$selected_year  = isset($_GET['year'])        ? intval($_GET['year'])      : intval(date('Y'));

$current_page = "expense_reports";
include_once "../includes/header.php";
include_once "../includes/sidebar.php";

// ─── DATA FETCHING ───
$detailed_bills = [];
$yearly_months_summary = [];
$total_amount = 0;

if ($report_type === 'monthly') {
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
        $total_amount += $row['amount'];
    }
    $stmt->close();
} else {
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

        $yearly_months_summary[] = [
            'month_label' => $month_label,
            'op_expense'  => $op_expense,
            'salaries'    => $salaries,
            'total_flow'  => $op_expense + $salaries
        ];
        $total_amount += ($op_expense + $salaries);
    }
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

.inv-wrap * { box-sizing: border-box; }

.inv-wrap {
    width: 210mm;
    min-height: 297mm;
    margin: 0 auto;
    padding: 0;
    font-family: 'Inter', sans-serif;
    color: #1e293b;
    background: white;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
}

@media print {
    .sidebar, .inv-bar, .header-minimal { display: none !important; }
    .main-content { padding: 0 !important; margin: 0 !important; }
    .inv-wrap { margin: 0 !important; border: none !important; box-shadow: none !important; }
}

.main-content {
    background: #f1f5f9;
    padding: 60px 0;
    display: flex;
    justify-content: center;
}

@media print {
    body { background: white; }
}

/* Toolbar */
.inv-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding: 20px 0;
    width: 100%;
    position: absolute;
    top: -80px;
    left: 0;
}
.inv-btn {
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
    cursor: pointer;
}
.inv-btn:hover { border-color: #cbd5e1; background: #f8fafc; color: #0f172a; transform: translateY(-1px); }

/* Minimal Invoice Card */
.inv-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.02);
    flex: 1;
    display: flex;
    flex-direction: column;
}

.inv-card-top-bar { height: 12px; background: #186D55; width: 100%; }

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
    text-transform: uppercase;
    margin: 0;
}

.inv-meta-block {
    padding: 0 50px 30px 50px;
    display: flex;
    justify-content: space-between;
}

.inv-sender-info { font-size: 13px; color: #64748b; line-height: 1.6; }
.inv-sender-info strong { color: #1e293b; font-size: 14px; }

.inv-date-no { text-align: right; font-size: 13px; color: #64748b; line-height: 1.6; }
.inv-date-no strong { color: #1e293b; }

.inv-table-container { padding: 20px 50px; }
.invoice-minimal-table { width: 100%; border-collapse: collapse; }
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
.invoice-minimal-table td { padding: 14px 16px; font-size: 13px; color: #475569; border-bottom: 1px solid #f1f5f9; }
.invoice-minimal-table td:last-child { text-align: right; font-weight: 700; color: #1e293b; }

.inv-totals-container { padding: 20px 50px 40px 50px; display: flex; justify-content: flex-end; }
.inv-totals-table { width: 300px; }
.inv-totals-table td { padding: 8px 0; font-size: 13px; color: #64748b; }
.inv-totals-table td:last-child { text-align: right; font-weight: 600; color: #1e293b; }
.balance-due-row td {
    font-size: 15px !important;
    font-weight: 800 !important;
    color: #186D55 !important;
    border-top: 2px solid #186D55;
    border-bottom: 2px double #186D55;
    padding: 12px 0 !important;
}

.inv-card-bottom-bar { height: 12px; background: #186D55; width: 100%; margin-top: auto; }

@media print {
    body * { visibility: hidden; }
    .inv-card, .inv-card * { visibility: visible; }
    .inv-card { position: absolute; inset: 0; border: none; box-shadow: none; }
    .inv-bar { display: none; }
}
</style>

<div class="main-content">
<div class="inv-wrap" style="position: relative;">
    <div class="inv-bar">
        <a href="expense_reports.php?report_type=<?php echo $report_type; ?>&month=<?php echo $selected_month; ?>&year=<?php echo $selected_year; ?>" class="inv-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Reports
        </a>
    </div>

    <div class="inv-card">
        <div class="inv-card-top-bar"></div>

        <div class="inv-head-container">
            <div>
                <h1 class="inv-title"><?php echo ($report_type === 'monthly') ? 'Monthly Expense Report' : 'Yearly Financial Summary'; ?></h1>
            </div>
            <div class="inv-logo-image-wrapper">
                <svg viewBox="0 0 260 80" width="190" height="58">
                    <path d="M 15 25 L 5 40 L 15 55" fill="none" stroke="#f97316" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
                    <text x="30" y="49" font-family="'Inter', sans-serif" font-weight="800" font-size="28" fill="#186D55" letter-spacing="-0.5">code</text>
                    <line x1="105" y1="28" x2="123" y2="52" stroke="#186D55" stroke-width="7" stroke-linecap="round" />
                    <line x1="105" y1="52" x2="123" y2="28" stroke="#f97316" stroke-width="7" stroke-linecap="round" />
                    <text x="128" y="49" font-family="'Inter', sans-serif" font-weight="800" font-size="28" fill="#186D55" letter-spacing="-0.5">entric</text>
                    <path d="M 245 25 L 255 40 L 245 55" fill="none" stroke="#f97316" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>

        <div class="inv-meta-block">
            <div class="inv-sender-info">
                <strong>codeXentric</strong><br>
                First Floor, Sardar Plaza, Qilla road,<br>
                Muzaffarabad, Azad Jammu & Kashmir<br>
                finance@codexentric.com
            </div>
            <div class="inv-date-no">
                <strong>DATE:</strong> <?php echo date('F d, Y'); ?><br>
                <strong>INVOICE NO:</strong> EXP-<?php echo ($report_type === 'monthly') ? date('mY', strtotime($selected_month . '-01')) : $selected_year; ?><br>
                <strong>PERIOD:</strong> <?php echo ($report_type === 'monthly') ? date('F Y', strtotime($selected_month . '-01')) : "Year " . $selected_year; ?>
            </div>
        </div>

        <!-- Billing details matching image layout -->
        <div style="padding: 20px 50px; border-top: 1px solid #f1f5f9; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; background: #fafafa;">
            <div class="billing-col">
                <h4 style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #186D55; margin: 0 0 10px 0;">Reporting Unit</h4>
                <p style="font-size: 13px; line-height: 1.6; color: #475569; margin: 0;">
                    <strong>Operations Department</strong><br>
                    Internal Corporate Expenses<br>
                    Category: <?php echo ($report_type === 'monthly') ? 'Monthly Operations' : 'Annual Summary'; ?>
                </p>
            </div>
            <div class="billing-col">
                <h4 style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #186D55; margin: 0 0 10px 0;">Payment Summary</h4>
                <p style="font-size: 13px; line-height: 1.6; color: #475569; margin: 0;">
                    Status: <span style="display: inline-flex; align-items: center; gap: 6px; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; padding: 2px 10px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase;">● AUDITED</span><br>
                    Generated On: <?php echo date('d M, Y'); ?>
                </p>
            </div>
        </div>

        <div class="inv-table-container">
            <table class="invoice-minimal-table">
                <thead>
                    <?php if ($report_type === 'monthly'): ?>
                        <tr>
                            <th>Bill Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th style="text-align: right;">Amount (Rs)</th>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <th>Month</th>
                            <th>Operating Expenses</th>
                            <th>Payroll (Salaries)</th>
                            <th style="text-align: right;">Total Outflow (Rs)</th>
                        </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php if ($report_type === 'monthly'): ?>
                        <?php if (empty($detailed_bills)): ?>
                            <tr><td colspan="4" style="text-align:center;">No records found for this period.</td></tr>
                        <?php else: ?>
                            <?php foreach ($detailed_bills as $exp): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($exp['bill_date'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($exp['category_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($exp['description']); ?></td>
                                    <td style="text-align: right;"><strong>Rs <?php echo number_format($exp['amount'], 2); ?></strong></td>
                                </tr>
<?php endforeach; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php foreach ($yearly_months_summary as $row): ?>
                            <tr>
                                <td><?php echo $row['month_label']; ?></td>
                                <td>Rs <?php echo number_format($row['op_expense'], 2); ?></td>
                                <td>Rs <?php echo number_format($row['salaries'], 2); ?></td>
                                <td style="text-align: right;"><strong>Rs <?php echo number_format($row['total_flow'], 2); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="inv-totals-container">
            <table class="inv-totals-table">
                <?php if ($report_type === 'monthly'): ?>
                    <tr>
                        <td>SUBTOTAL (Direct Expenses)</td>
                        <td>Rs <?php echo number_format($total_amount, 2); ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td>Annual Operating Expenses</td>
                        <td>Rs <?php echo number_format(array_sum(array_column($yearly_months_summary, 'op_expense')), 2); ?></td>
                    </tr>
                    <tr>
                        <td>Annual Payroll Disbursement</td>
                        <td>Rs <?php echo number_format(array_sum(array_column($yearly_months_summary, 'salaries')), 2); ?></td>
                    </tr>
                <?php endif; ?>
                <tr class="balance-due-row">
                    <td style="line-height: 1.2;">Balance Total<br>(Net Outflow)</td>
                    <td style="vertical-align: middle;">Rs <?php echo number_format($total_amount, 2); ?></td>
                </tr>
            </table>
        </div>

        <div class="inv-card-bottom-bar"></div>
    </div>
</div>
</div>

<?php include_once "../includes/footer.php"; ?>
