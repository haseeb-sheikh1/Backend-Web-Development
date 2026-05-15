<?php
    session_start();

    if (!isset($_SESSION['email'])) {
        header("Location: login.php");
        exit();
    }

    require_once 'Database.php';
    require_once 'Employee.php';
    require_once 'Payroll.php';

    $db = new Database();
    $conn = $db->getConnection();
    $employeeObj = new Employee($conn);
    $payrollObj = new Payroll($conn);

    $user_id = $_SESSION['user_id'] ?? 0;
    $details = $employeeObj->getEmployeeDetailsById($user_id);

    if (!$details) {
        echo "<div style='padding:50px; text-align:center;'><h3>System could not locate your profile. Please contact support.</h3></div>";
        exit();
    }

    $real_emp_id = $details['employee_id'];
    
    // Filtering logic
    $filter_month = $_GET['month'] ?? '';
    $filter_year = $_GET['year'] ?? '';
    
    // Fetch salary history with optional filters
    $history = $payrollObj->getSalaryHistory($real_emp_id, $filter_month, $filter_year);

    $current_page = "employee_payroll";
    $title = "My Payroll History";
    include_once "../includes/header.php";
    include_once "../includes/sidebar.php";
?>

<style>
:root {
  --bg: #f8fafc;
  --card-bg: #ffffff;
  --border: #eef2f6;
  --text-main: #334155;
  --text-muted: #64748b;
  --brand-green: #186D55;
}

.payroll-layout {
    padding: 32px;
    max-width: 1400px;
    margin: 0 auto;
}

/* Shared Widget Eco-System */
.widget-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.02);
    overflow: hidden;
    margin-bottom: 32px;
}
.widget-header {
    padding: 16px 24px;
    background: #ffffff; 
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.widget-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13.5px;
    font-weight: 800;
    color: var(--brand-green);
    text-transform: uppercase;
    letter-spacing: 0.8px;
}
.widget-body {
    padding: 24px 28px;
}

/* Overview Cards for total earnings (simplified) */
.payroll-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}
.summary-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.02);
    transition: all 0.2s ease;
}
.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.04), 0 4px 6px -2px rgba(0,0,0,0.02);
    border-color: #cbd5e1;
}
.sc-icon-wrap {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(24, 109, 85, 0.08) 0%, rgba(24, 109, 85, 0.03) 100%);
    border: 1px solid rgba(24, 109, 85, 0.1);
    color: var(--brand-green);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: inset 0 2px 4px rgba(255,255,255,0.8);
}
.sc-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.summary-card-title {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
}
.summary-card-val {
    font-size: 24px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
    line-height: 1;
}

/* Consistent Minimal Table styling */
.payroll-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 12px;
}
.payroll-table th {
    text-align: left;
    font-size: 12px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 0 24px 8px 24px;
}
.payroll-table td {
    background: #ffffff;
    border-top: 1px solid rgba(0,0,0,0.03);
    border-bottom: 1px solid rgba(0,0,0,0.03);
    padding: 18px 24px;
    font-size: 14px;
    color: #334155;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,0,0,0.01);
}
.payroll-table td:first-child {
    border-left: 1px solid rgba(0,0,0,0.03);
    border-top-left-radius: 20px;
    border-bottom-left-radius: 20px;
}
.payroll-table td:last-child {
    border-right: 1px solid rgba(0,0,0,0.03);
    border-top-right-radius: 20px;
    border-bottom-right-radius: 20px;
}

.status-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
    background: #eefdf4;
    color: #16a34a;
    padding: 4px 12px;
    border-radius: 20px;
}

.download-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--brand-green);
    color: #fff;
    padding: 9px 16px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 700;
    font-size: 13px;
    box-shadow: 0 4px 12px rgba(24, 109, 85, 0.2);
    transition: all 0.25s ease;
}
.download-btn:hover {
    transform: translateY(-1px);
    background: #11523F;
    box-shadow: 0 6px 18px rgba(24, 109, 85, 0.3);
    color: #fff;
}

/* ─── Advanced Mobile Architecture ─── */
@media (max-width: 768px) {
    .payroll-layout {
        padding: 16px 12px !important;
    }
    .payroll-summary {
        gap: 16px !important;
    }
    .summary-card {
        padding: 18px !important;
        border-radius: 20px !important;
        gap: 16px !important;
    }
    .sc-icon-wrap {
        width: 46px !important;
        height: 46px !important;
    }
    .sc-icon-wrap svg {
        width: 20px !important; height: 20px !important;
    }
    .summary-card-val {
        font-size: 20px !important;
    }
    .widget-card {
        border-radius: 20px !important;
    }
    .widget-header {
        padding: 16px 20px !important;
    }
    .widget-body {
        padding: 20px 16px !important;
    }
    
    /* Cardification Transformation */
    .payroll-table thead { display: none; }
    .payroll-table, .payroll-table tbody, .payroll-table tr, .payroll-table td {
        display: block;
        width: 100%;
        box-sizing: border-box;
    }
    .payroll-table tr {
        background: #ffffff;
        border: 1px solid #eef2f6;
        border-radius: 20px;
        padding: 18px;
        margin-bottom: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }
    .payroll-table td {
        display: flex !important;
        justify-content: space-between;
        align-items: center;
        background: none !important;
        border: none !important;
        box-shadow: none !important;
        padding: 10px 0 !important;
        font-size: 13.5px !important;
        text-align: right;
    }
    .payroll-table td::before {
        content: attr(data-label);
        font-weight: 700;
        color: #94a3b8;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .payroll-table td:first-child {
        border-bottom: 1px solid #f1f5f9 !important;
        padding-bottom: 12px !important;
        margin-bottom: 4px;
        font-size: 15px !important;
        font-weight: 700;
    }
    .payroll-table td:last-child {
        border-top: 1px solid #f1f5f9 !important;
        margin-top: 12px;
        padding-top: 16px !important;
        justify-content: center !important;
    }
    .payroll-table td:last-child::before {
        display: none !important; /* Kills collision label */
    }
    .download-btn {
        width: 100% !important;
        justify-content: center !important;
        padding: 12px !important;
        box-sizing: border-box !important;
    }
}

/* ── Premium Search Card ── */
.search-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    margin-bottom: 24px;
    overflow: hidden;
}
.search-card-header {
    padding: 16px 24px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 10px;
    background: #ffffff;
}
.search-card-header h2 {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}
.search-card-body {
    padding: 24px;
    background: #ffffff;
}
.search-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
.search-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.search-field label {
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
}
.search-field input,
.search-field select {
    width: 100%;
    height: 44px;
    padding: 0 16px;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 22px;
    font-size: 13.5px;
    color: #1e293b;
    outline: none;
    transition: all 0.2s;
    box-sizing: border-box;
}
.search-field select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 14px;
}
.search-field input:focus,
.search-field select:focus {
    border-color: var(--brand-green);
    box-shadow: 0 0 0 4px rgba(24, 109, 85, 0.1);
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
    border-radius: 22px;
    padding: 10px 24px;
    font-size: 13.5px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
}
.btn-reset:hover {
    background: rgba(24, 109, 85, 0.05);
}
.btn-search {
    background: var(--brand-green);
    color: #ffffff;
    border: none;
    border-radius: 22px;
    padding: 10px 28px;
    font-size: 13.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-search:hover {
    background: #125743;
    transform: translateY(-1px);
}
@media (max-width: 768px) {
    .search-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="payroll-layout">

    <?php
        // Simple calculated summary
        $total_received = 0;
        $cycles_count = count($history);
        foreach($history as $item) {
            $total_received += $item['net_payable'];
        }
    ?>


    
    <!-- Salary Records Search Card -->
    <div class="search-card">
        <div class="search-card-header">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="var(--brand-green)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <h2>Salary Records Search</h2>
        </div>
        <div class="search-card-body">
            <form method="GET" action="employee_payroll.php">
                <div class="search-grid">
                    <div class="search-field">
                        <label>Report Month</label>
                        <input type="month" name="month" value="<?php echo htmlspecialchars($filter_month); ?>">
                    </div>
                    <div class="search-field">
                        <label>Report Year</label>
                        <select name="year">
                            <option value="">— All Years —</option>
                            <?php 
                                $currentYear = date('Y');
                                for($y = $currentYear; $y >= 2024; $y--) {
                                    $selected = ($filter_year == $y) ? 'selected' : '';
                                    echo "<option value='$y' $selected>$y</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="search-actions">
                    <?php if ($filter_month || $filter_year): ?>
                        <a href="employee_payroll.php" class="btn-reset">Reset</a>
                    <?php else: ?>
                        <button type="button" class="btn-reset" onclick="this.form.reset();">Reset</button>
                    <?php endif; ?>
                    <button type="submit" class="btn-search">Search</button>
                </div>
            </form>
        </div>
    </div>


    <div class="payroll-summary">
        <div class="summary-card">
            <div class="sc-icon-wrap">
                 <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="sc-content">
                 <div class="summary-card-title">Cycles Processed</div>
                 <div class="summary-card-val"><?php echo $cycles_count; ?> Months</div>
            </div>
        </div>
        <div class="summary-card">
            <div class="sc-icon-wrap" style="background: rgba(24, 109, 85, 0.12);">
                 <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div class="sc-content">
                 <div class="summary-card-title">Net Lifetime Income</div>
                 <div class="summary-card-val" style="color: var(--brand-green);">Rs <?php echo number_format($total_received); ?></div>
            </div>
        </div>
        <div class="summary-card">
            <div class="sc-icon-wrap">
                 <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
            <div class="sc-content">
                 <div class="summary-card-title">Current Base</div>
                 <div class="summary-card-val">Rs <?php echo number_format($details['base_salary_rs']); ?></div>
            </div>
        </div>
    </div>

    <!-- Widgetized History Archive -->
    <div class="widget-card">
        <div class="widget-header">
            <div class="widget-title">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Historical Salary Record
            </div>
            <span class="greet-static" style="font-size: 11px; color: #64748b; font-weight: 700; background: #e2edea; color: var(--brand-green); padding: 4px 10px; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Verified</span>
        </div>
        <div class="widget-body" style="overflow-x: auto;">
            <?php if (empty($history)): ?>
                <div style="padding: 40px 20px; text-align: center; color: #64748b;">
                     <svg viewBox="0 0 24 24" width="42" height="42" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="margin-bottom: 12px;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                     <h3 style="font-size: 15px; color: #475569; font-weight: 700;">No Record Found</h3>
                     <p style="font-size: 13px; color: #94a3b8;">No processed salary cycles exist currently.</p>
                </div>
            <?php else: ?>
                <table class="payroll-table">
                    <thead>
                        <tr>
                            <th>Pay Period</th>
                            <th>Base Wage</th>
                            <th>Net Disbursed</th>
                            <th>Record Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $row): ?>
                        <tr>
                            <td data-label="Pay Period" style="font-weight: 700;"><?php echo date("F Y", strtotime($row['payroll_month'] . '-01')); ?></td>
                            <td data-label="Base Wage" style="color: #64748b;">Rs <?php echo number_format($row['base_salary']); ?></td>
                            <td data-label="Net Disbursed" style="font-weight: 800; color: var(--brand-green);">Rs <?php echo number_format($row['net_payable']); ?></td>
                            <td data-label="Status">
                                <div class="status-chip">
                                    <div style="width:6px; height:6px; background:#16a34a; border-radius:50%;"></div>
                                    <?php echo $row['status']; ?>
                                </div>
                            </td>
                            <td data-label="Action">
                                <a href="salary_invoice.php?employeeId=<?php echo $details['user_id'] ?? $real_emp_id; ?>&month=<?php echo substr($row['payroll_month'], 0, 7); ?>" target="_blank" class="download-btn">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    Salary Slip
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php include_once "../includes/footer.php"; ?>
