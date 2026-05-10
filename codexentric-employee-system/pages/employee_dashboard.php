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
        echo "<div style='padding:50px; text-align:center;'><h3>Error locating verified employee profile. Please reach support.</h3><a href='logout.php'>Logout</a></div>";
        exit();
    }

    $real_emp_id = $details['employee_id'];
    // Pull top 3 recent history for quick dashboard preview
    $all_history = $payrollObj->getSalaryHistory($real_emp_id);
    $recent_history = array_slice($all_history, 0, 3);

    $current_page = "employee_dashboard";
    $title = "Overview";
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

.dash-layout {
    padding: 32px;
    max-width: 1400px;
    margin: 0 auto;
}

/* ── Greeting ── */
.greet-box {
    margin-bottom: 32px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}
.greet-box h1 {
    font-size: 24px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.5px;
    margin: 0;
}
.greet-box p {
    font-size: 14.5px;
    color: #64748b;
    margin-top: 6px;
    font-weight: 500;
}

/* ── Card Grid ── */
.dash-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 32px;
}
@media (max-width: 950px) {
    .dash-grid { grid-template-columns: 1fr; }
}

.widget-card {
    background: #ffffff;
    border: 1px solid #eef2f6;
    border-radius: 24px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.03);
    overflow: hidden;
}

.widget-header {
    padding: 22px 28px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.widget-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15.5px;
    font-weight: 700;
    color: #1e293b;
}
.widget-body {
    padding: 28px;
}

/* Info Rows */
.info-strip {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid #f8fafc;
}
.info-strip:last-child { border-bottom: none; }
.info-label {
    font-size: 13.5px;
    font-weight: 600;
    color: #64748b;
}
.info-val {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
}

/* Minimal Table styles inherited from admin */
.mini-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 12px;
}
.mini-table th {
    text-align: left;
    font-size: 12px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 0 24px 8px 24px;
}
.mini-table td {
    background: #ffffff;
    border-top: 1px solid rgba(0,0,0,0.03);
    border-bottom: 1px solid rgba(0,0,0,0.03);
    padding: 18px 24px;
    font-size: 14px;
    color: #334155;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,0,0,0.01);
}
.mini-table td:first-child {
    border-left: 1px solid rgba(0,0,0,0.03);
    border-top-left-radius: 20px;
    border-bottom-left-radius: 20px;
}
.mini-table td:last-child {
    border-right: 1px solid rgba(0,0,0,0.03);
    border-top-right-radius: 20px;
    border-bottom-right-radius: 20px;
}

.status-tag {
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
</style>

<div class="dash-layout">
    
    <div class="greet-box">
        <div>
            <h1>Welcome back, <?php echo htmlspecialchars($details['first_name']); ?>!</h1>
            <p><?php echo htmlspecialchars($details['position_title'] ?: 'System Member'); ?> • CodeXentric Organization</p>
        </div>
        <div>
            <a href="employee_payroll.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: var(--brand-green); color: #fff; border-radius: 25px; font-size: 14px; font-weight: 700; text-decoration: none; box-shadow: 0 4px 14px rgba(24, 109, 85, 0.25); transition: 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                 <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                 View All Slips
            </a>
        </div>
    </div>

    <div class="dash-grid">
        <!-- Profile Quick Info -->
        <div class="widget-card">
            <div class="widget-header">
                <div class="widget-title">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="var(--brand-green)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Professional Profile
                </div>
            </div>
            <div class="widget-body">
                <div class="info-strip">
                    <span class="info-label">Department</span>
                    <span class="info-val"><?php echo htmlspecialchars($details['department'] ?: 'Unassigned'); ?></span>
                </div>
                <div class="info-strip">
                    <span class="info-label">Employment Type</span>
                    <span class="info-val"><?php echo htmlspecialchars($details['employment_type'] ?: 'Permanent'); ?></span>
                </div>
                <div class="info-strip">
                    <span class="info-label">Date of Joining</span>
                    <span class="info-val"><?php echo date("d M Y", strtotime($details['date_of_joining'])); ?></span>
                </div>
                <div class="info-strip">
                    <span class="info-label">Official Email</span>
                    <span class="info-val" style="color: var(--brand-green);"><?php echo htmlspecialchars($details['email']); ?></span>
                </div>
            </div>
        </div>

        <!-- Financial Overview -->
        <div class="widget-card">
            <div class="widget-header">
                <div class="widget-title">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="var(--brand-green)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    Salary & Bank Details
                </div>
            </div>
            <div class="widget-body">
                <div class="info-strip">
                    <span class="info-label">Contracted Base Salary</span>
                    <span class="info-val">Rs <?php echo number_format($details['base_salary_rs'] ?? 0); ?></span>
                </div>
                <div class="info-strip">
                    <span class="info-label">Designated Institution</span>
                    <span class="info-val"><?php echo htmlspecialchars($details['bank_name'] ?: 'N/A'); ?></span>
                </div>
                <div class="info-strip">
                    <span class="info-label">Account Identification</span>
                    <span class="info-val">**** <?php echo substr($details['bank_account_number'], -4) ?: '****'; ?></span>
                </div>
                <div class="info-strip">
                    <span class="info-label">Verification Status</span>
                    <span class="info-val"><span style="color:#16a34a;">Active System Payout</span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Pay Runs table mimicking admin roster table -->
    <h2 style="font-size: 16px; font-weight: 800; color: #1e293b; margin-bottom: 8px; padding-left: 8px; display: flex; align-items: center; gap: 10px;">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#64748b" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Recent Salary Runs
    </h2>

    <div style="overflow-x: auto;">
        <?php if (empty($recent_history)): ?>
            <div style="background: #ffffff; border-radius: 20px; padding: 40px; text-align: center; color: #64748b; font-weight: 600; border: 1px solid #eef2f6;">
                No processed salary cycles located yet.
            </div>
        <?php else: ?>
            <table class="mini-table">
                <thead>
                    <tr>
                        <th>Payroll Month</th>
                        <th>Base Salary</th>
                        <th>Net Disbursed</th>
                        <th>Cycle Status</th>
                        <th style="text-align:right;">Operations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_history as $row): ?>
                    <tr>
                        <td><?php echo date("F Y", strtotime($row['payroll_month'] . '-01')); ?></td>
                        <td>Rs <?php echo number_format($row['base_salary']); ?></td>
                        <td style="color: var(--brand-green); font-weight: 800;">Rs <?php echo number_format($row['net_payable']); ?></td>
                        <td>
                            <span class="status-tag">
                                <span style="width:6px; height:6px; background:#16a34a; border-radius:50%;"></span>
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                        <td style="text-align:right;">
                             <a href="salary_invoice.php?employeeId=<?php echo $details['user_id'] ?? $real_emp_id; ?>&month=<?php echo substr($row['payroll_month'], 0, 7); ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; color: #475569; padding: 8px 14px; border-radius: 12px; text-decoration: none; font-size: 13px; transition: 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                                 <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
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

<?php include_once "../includes/footer.php"; ?>