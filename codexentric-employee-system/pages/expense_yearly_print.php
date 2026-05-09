<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role_id'] != '1') {
    header("Location: login.php");
    exit();
}

require_once '../pages/Database.php';
$db = new Database();
$conn = $db->getConnection();

$selected_year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));
$current_page = "expense_reports";

include_once "../includes/header.php";
include_once "../includes/sidebar.php";

// ─── DATA FETCHING ───

// 1. Category-wise Breakdown
$cat_query = "SELECT ec.category_name, SUM(e.amount) as category_total 
              FROM expenses e 
              JOIN expense_categories ec ON e.category_id = ec.id 
              WHERE YEAR(e.bill_date) = ? 
              GROUP BY ec.category_name 
              ORDER BY category_total DESC";
$stmt1 = $conn->prepare($cat_query);
$stmt1->bind_param("i", $selected_year);
$stmt1->execute();
$category_breakdown = $stmt1->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt1->close();

// 2. Annual Payroll Total
$payroll_query = "SELECT SUM(net_payable_rs) as yearly_payroll FROM payroll WHERE YEAR(payroll_month) = ?";
$stmt2 = $conn->prepare($payroll_query);
$stmt2->bind_param("i", $selected_year);
$stmt2->execute();
$payroll_total = $stmt2->get_result()->fetch_assoc()['yearly_payroll'] ?? 0;
$stmt2->close();

// 3. Grand Total
$operating_total = array_sum(array_column($category_breakdown, 'category_total'));
$grand_total = $operating_total + $payroll_total;

?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

body { background: #f1f5f9; margin: 0; padding: 0; }

.main-content {
    background: #f1f5f9;
    padding: 60px 0;
    display: flex;
    justify-content: center;
}

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
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
    border: 1.5px solid #e2e8f0;
    background: white;
    color: #475569;
    cursor: pointer;
}
.inv-btn:hover { background: #f8fafc; border-color: #cbd5e1; }

.inv-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.inv-card-top-bar { height: 12px; background: #186D55; width: 100%; }

.inv-head-container { padding: 40px 50px 30px 50px; }
.inv-header-flex { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
.inv-title { font-size: 28px; font-weight: 800; color: #1e293b; letter-spacing: -1px; text-transform: uppercase; margin: 0; }

.inv-meta-block { padding: 0 50px 30px; display: grid; grid-template-columns: 1.2fr 1fr; gap: 40px; }
.inv-sender-info { font-size: 13px; line-height: 1.6; color: #64748b; }
.inv-date-no { font-size: 13px; line-height: 1.8; text-align: right; color: #1e293b; }

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
.invoice-minimal-table td { padding: 14px 16px; font-size: 13px; border-bottom: 1px solid #f1f5f9; color: #475569; }

.inv-totals-container { padding: 40px 50px; display: flex; justify-content: flex-end; }
.inv-totals-table { width: 350px; }
.inv-totals-table td { padding: 8px 0; font-size: 13px; color: #64748b; }
.inv-totals-table td:last-child { text-align: right; font-weight: 700; color: #1e293b; width: 150px; }

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
    .sidebar, .inv-bar, .header-minimal { display: none !important; }
    .main-content { padding: 0 !important; margin: 0 !important; }
    .inv-wrap { margin: 0 !important; border: none !important; box-shadow: none !important; }
    body { background: white; }
}
</style>

<div class="main-content">
<div class="inv-wrap" style="position: relative;">
    <div class="inv-bar">
        <a href="expense_reports.php?report_type=yearly&year=<?php echo $selected_year; ?>" class="inv-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to Dashboard
        </a>
        <button onclick="window.print()" class="inv-btn" style="background: #186D55; color: #fff; border-color: #186D55;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print Annual Report
        </button>
    </div>

    <div class="inv-card">
        <div class="inv-card-top-bar"></div>
        <div class="inv-head-container">
            <div class="inv-header-flex">
                <h1 class="inv-title">Annual Financial Statement</h1>
                <svg width="180" height="50" viewBox="0 0 260 80" xmlns="http://www.w3.org/2000/svg">
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
                <strong>REPORT ID:</strong> ANN-<?php echo $selected_year; ?><br>
                <strong>FISCAL PERIOD:</strong> Jan - Dec <?php echo $selected_year; ?>
            </div>
        </div>

        <div style="padding: 20px 50px; border-top: 1px solid #f1f5f9; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; background: #fafafa;">
            <div class="billing-col">
                <h4 style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #186D55; margin: 0 0 10px 0;">Reporting Unit</h4>
                <p style="font-size: 13px; line-height: 1.6; color: #475569; margin: 0;">
                    <strong>Corporate Finance</strong><br>
                    Annual Expense Breakdown<br>
                    Audit Level: Final Year End
                </p>
            </div>
            <div class="billing-col">
                <h4 style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #186D55; margin: 0 0 10px 0;">Audited Summary</h4>
                <p style="font-size: 13px; line-height: 1.6; color: #475569; margin: 0;">
                    Status: <span style="display: inline-flex; align-items: center; gap: 6px; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; padding: 2px 10px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase;">● VERIFIED</span><br>
                    Generated On: <?php echo date('d M, Y'); ?>
                </p>
            </div>
        </div>

        <div class="inv-table-container">
            <table class="invoice-minimal-table">
                <thead>
                    <tr>
                        <th>Expense Category</th>
                        <th style="text-align: right;">Annual Total (Rs)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($category_breakdown)): ?>
                        <tr><td colspan="2" style="text-align:center;">No expenses recorded for this year.</td></tr>
                    <?php else: ?>
                        <?php foreach ($category_breakdown as $cat): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($cat['category_name']); ?></strong></td>
                                <td style="text-align: right;"><strong>Rs <?php echo number_format($cat['category_total'], 2); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <tr>
                        <td><br><strong>Total Payroll Disbursement</strong></td>
                        <td style="text-align: right;"><br><strong>Rs <?php echo number_format($payroll_total, 2); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="inv-totals-container">
            <table class="inv-totals-table">
                <tr>
                    <td>Cumulative Operating Expenses</td>
                    <td>Rs <?php echo number_format($operating_total, 2); ?></td>
                </tr>
                <tr>
                    <td>Cumulative Payroll Outflow</td>
                    <td>Rs <?php echo number_format($payroll_total, 2); ?></td>
                </tr>
                <tr class="balance-due-row">
                    <td style="line-height: 1.2;">Grand Total<br>(Net Annual Outflow)</td>
                    <td style="vertical-align: middle;">Rs <?php echo number_format($grand_total, 2); ?></td>
                </tr>
            </table>
        </div>

        <div class="inv-card-bottom-bar"></div>
    </div>
</div>
</div>

<?php include_once "../includes/footer.php"; ?>
