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

// ─── 4. ADDITIONAL: CHRONOLOGICAL LEDGER LOGIC (For itemized audit table) ───

// Fetch all raw expenses
$exp_query = "SELECT e.*, ec.category_name FROM expenses e JOIN expense_categories ec ON e.category_id = ec.id WHERE YEAR(e.bill_date) = ? ORDER BY e.bill_date ASC";
$stmt3 = $conn->prepare($exp_query); $stmt3->bind_param("i", $selected_year); $stmt3->execute();
$expenses_raw = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC); $stmt3->close();

// Fetch all payroll disbursements
$pay_query = "SELECT p.*, u.first_name, u.last_name FROM payroll p JOIN employees emp ON p.employee_id = emp.id JOIN users u ON emp.user_id = u.user_id WHERE YEAR(p.payroll_month) = ? ORDER BY p.payroll_month ASC";
$stmt4 = $conn->prepare($pay_query); $stmt4->bind_param("i", $selected_year); $stmt4->execute();
$payrolls_raw = $stmt4->get_result()->fetch_all(MYSQLI_ASSOC); $stmt4->close();

// Combine into chronologically sorted ledger map
$months_map = [];
for($m = 1; $m <= 12; $m++) {
    $key = sprintf('%02d', $m);
    $months_map[$key] = ['name' => date('F', mktime(0, 0, 0, $m, 1)), 'records' => [], 'month_total' => 0];
}
foreach ($expenses_raw as $exp) {
    $m = date('m', strtotime($exp['bill_date']));
    $months_map[$m]['records'][] = ['date' => $exp['bill_date'], 'title' => $exp['category_name'], 'desc' => $exp['description'], 'type' => 'Expense', 'amount' => floatval($exp['amount'])];
    $months_map[$m]['month_total'] += floatval($exp['amount']);
}
foreach ($payrolls_raw as $p) {
    $m = date('m', strtotime($p['payroll_month']));
    $months_map[$m]['records'][] = ['date' => $p['payroll_month'], 'title' => 'Payroll Disbursement', 'desc' => 'Salary for ' . htmlspecialchars($p['first_name'] . ' ' . $p['last_name']), 'type' => 'Payroll', 'amount' => floatval($p['net_payable_rs'])];
    $months_map[$m]['month_total'] += floatval($p['net_payable_rs']);
}
foreach ($months_map as &$mdata) {
    usort($mdata['records'], function($a, $b) { return strtotime($a['date']) - strtotime($b['date']); });
}
unset($mdata);
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

body { background: #f1f5f9; margin: 0; padding: 0; }

.main-content {
    background: #f1f5f9;
    padding: 60px 0;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.inv-wrap {
    width: 210mm;
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

.inv-card-bottom-bar { height: 12px; background: #186D55; width: 100%; }

/* ── Premium Bubble Component Ported From Dashboard ── */
.premium-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 24px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.03);
    border: 1px solid #f1f5f9;
    display: flex;
    flex-direction: column;
    height: 100%;
    box-sizing: border-box;
}
.prem-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
.prem-title { font-size: 18px; font-weight: 800; color: #1e293b; margin: 0; }
.prem-subtitle { font-size: 12px; color: #94a3b8; font-weight: 500; margin-top: 2px; }
.bubble-pack-container {
    position: relative;
    height: 140px;
    margin: 10px 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.prem-bubble {
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    position: absolute;
    box-shadow: 0 8px 20px rgba(0,0,0,0.02);
}
.b-main { width: 90px; height: 90px; left: 8%; top: 50%; transform: translateY(-50%); font-size: 18px; z-index: 4; }
.b-second { width: 68px; height: 68px; right: 18%; top: 5%; font-size: 14px; z-index: 3; }
.b-third { width: 55px; height: 55px; right: 28%; bottom: 5%; font-size: 11px; z-index: 5; }
.b-fourth { width: 35px; height: 35px; right: 8%; top: 45%; font-size: 8px; z-index: 2; }
.prem-legend {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: auto;
    border-top: 1px dashed #f1f5f9;
    padding-top: 12px;
}
.leg-item { display: flex; align-items: flex-start; gap: 6px; }
.leg-dot { width: 8px; height: 8px; border-radius: 50%; margin-top: 3px; }
.leg-info { display: flex; flex-direction: column; }
.leg-cat { font-size: 10.5px; font-weight: 700; }
.leg-val { font-size: 9.5px; font-weight: 700; color: #94a3b8; }

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

        <div style="padding: 25px 50px; border-top: 1px solid #f1f5f9; display: grid; grid-template-columns: 1fr 1.2fr; gap: 30px; background: #fafafa;">
            <!-- Left Side: Core Statistics -->
            <div class="summary-stats-col" style="display: flex; flex-direction: column; justify-content: center;">
                <h4 style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #186D55; margin: 0 0 20px 0;">Cumulative Financial Statement Overview</h4>
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 12px; color: #64748b; padding-bottom: 8px;">Operating Expense Core</td>
                            <td style="text-align: right; font-weight: 700; color: #1e293b; font-size: 13px;">Rs <?php echo number_format($operating_total, 2); ?></td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px; color: #64748b; padding-bottom: 12px; border-bottom: 1px dashed #e2e8f0;">Net Payroll Disbursement</td>
                            <td style="text-align: right; font-weight: 700; color: #1e293b; font-size: 13px; border-bottom: 1px dashed #e2e8f0;">Rs <?php echo number_format($payroll_total, 2); ?></td>
                        </tr>
                        <tr>
                            <td style="font-size: 13px; font-weight: 800; color: #186D55; padding-top: 12px;">NET FISCAL OUTFLOW</td>
                            <td style="text-align: right; font-weight: 800; color: #186D55; padding-top: 12px; font-size: 15px;">Rs <?php echo number_format($grand_total, 2); ?></td>
                        </tr>
                    </table>
                </div>
                <p style="font-size: 11px; color: #94a3b8; margin-top: 15px; line-height: 1.5;">
                    <span style="display: inline-flex; align-items: center; gap: 4px; background: #ecfdf5; color: #059669; padding: 2px 8px; border-radius: 4px; font-weight: 700; text-transform: uppercase; font-size: 9px; border: 1px solid #a7f3d0;">● System Audited</span>
                    This auto-generated fiscal report encompasses total operating allocations and validated compensation ledgers.
                </p>
            </div>

            <!-- Right Side: PORTED PREMIUM CHART COMPONENT -->
            <div class="premium-chart-col">
                <div class="premium-card">
                    <div class="prem-header">
                        <div>
                            <h2 class="prem-title">Category Distribution</h2>
                            <p class="prem-subtitle">Top 4 Operational Segments in <?php echo $selected_year; ?></p>
                        </div>
                    </div>

                    <div class="bubble-pack-container">
                        <?php 
                            // Dynamically generate Top 4 from category_breakdown
                            $top4 = array_slice($category_breakdown, 0, 4);
                            
                            $bubble_classes = ['b-main', 'b-second', 'b-third', 'b-fourth'];
                            $bubble_colors = [
                                ['bg'=>'#EEF2FF', 'text'=>'#6366F1', 'dot'=>'#6366F1'], // Blue/Purple
                                ['bg'=>'#ECFDF5', 'text'=>'#10B981', 'dot'=>'#10B981'], // Green
                                ['bg'=>'#FDF2F8', 'text'=>'#EC4899', 'dot'=>'#EC4899'], // Pink
                                ['bg'=>'#FFFBEB', 'text'=>'#F59E0B', 'dot'=>'#F59E0B']  // Orange
                            ];
                            
                            $legend_data = [];
                            $idx = 0;
                            foreach ($top4 as $cat_row):
                                $val = floatval($cat_row['category_total']);
                                $perc = ($operating_total > 0) ? round(($val / $operating_total) * 100) : 0;
                                $style = $bubble_colors[$idx];
                                $class = $bubble_classes[$idx];
                                
                                $legend_data[] = [
                                    'label' => $cat_row['category_name'],
                                    'val'   => $val,
                                    'dot'   => $style['dot']
                                ];
                        ?>
                            <div class="prem-bubble <?php echo $class; ?>" 
                                 style="background: <?php echo $style['bg']; ?>; color: <?php echo $style['text']; ?>;">
                                <?php echo $perc; ?>%
                            </div>
                        <?php 
                            $idx++; 
                            endforeach; 
                            if (empty($top4)):
                        ?>
                            <div style="color:#94a3b8; font-size:12px; font-weight:600; border: 1px dashed #e2e8f0; border-radius: 50%; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; text-align: center;">No Activity</div>
                        <?php endif; ?>
                    </div>

                    <div class="prem-legend">
                        <?php foreach ($legend_data as $leg): ?>
                        <div class="leg-item">
                            <div class="leg-dot" style="background: <?php echo $leg['dot']; ?>;"></div>
                            <div class="leg-info">
                                <span class="leg-cat" style="color: <?php echo $leg['dot']; ?>;"><?php echo htmlspecialchars($leg['label']); ?></span>
                                <span class="leg-val">Rs <?php echo number_format($leg['val']); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="inv-table-container" style="border-top: 1px solid #f1f5f9; padding-top: 30px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #1e293b; margin: 0 0 15px 0; text-transform: uppercase; letter-spacing: 0.5px;">Chronological Itemized Transaction Ledger</h3>
            <table class="invoice-minimal-table" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th style="width: 15%; padding: 10px 12px; font-size: 10px;">Date</th>
                        <th style="width: 25%; padding: 10px 12px; font-size: 10px;">Title / Source</th>
                        <th style="width: 35%; padding: 10px 12px; font-size: 10px;">Specifics</th>
                        <th style="width: 10%; padding: 10px 12px; font-size: 10px;">Class</th>
                        <th style="width: 15%; padding: 10px 12px; font-size: 10px; text-align: right;">Amount (Rs)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $has_data = false;
                    foreach ($months_map as $mcode => $mdata): 
                        if (empty($mdata['records'])) continue;
                        $has_data = true;
                    ?>
                        <tr style="background: #f8fafc;">
                            <td colspan="5" style="font-weight: 800; color: #186D55; border-bottom: 2px solid #e2e8f0; padding: 10px 12px; font-size: 12px; text-transform: uppercase;">
                                <?php echo $mdata['name'] . ' ' . $selected_year; ?>
                            </td>
                        </tr>
                        
                        <?php foreach ($mdata['records'] as $rec): ?>
                            <tr>
                                <td style="font-weight: 600; color: #334155;"><?php echo date('d M, Y', strtotime($rec['date'])); ?></td>
                                <td><span style="font-weight: 700; color: #475569;"><?php echo $rec['title']; ?></span></td>
                                <td style="color: #64748b; font-size: 11.5px; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo $rec['desc']; ?></td>
                                <td>
                                    <span style="font-size: 9px; font-weight: 800; padding: 2px 6px; border-radius: 4px; <?php echo $rec['type'] == 'Payroll' ? 'background: #e0f2fe; color: #0369a1;' : 'background: #f1f5f9; color: #475569;'; ?>">
                                        <?php echo strtoupper($rec['type']); ?>
                                    </span>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #1e293b;">Rs <?php echo number_format($rec['amount'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <tr style="background: #fafafa;">
                            <td colspan="4" style="text-align: right; font-weight: 700; font-size: 11px; color: #64748b;">Subtotal for <?php echo $mdata['name']; ?></td>
                            <td style="text-align: right; font-weight: 800; color: #186D55; border-bottom: 1px solid #e2e8f0;">
                                Rs <?php echo number_format($mdata['month_total'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$has_data): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 30px; color: #94a3b8; font-style: italic;">No system transaction records for selected fiscal year.</td></tr>
                    <?php endif; ?>
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
