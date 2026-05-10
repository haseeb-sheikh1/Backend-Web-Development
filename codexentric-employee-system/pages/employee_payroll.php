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
    // Fetch ALL salary history
    $history = $payrollObj->getSalaryHistory($real_emp_id);

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

/* Overview Cards for total earnings (simplified) */
.payroll-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.summary-card {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #eef2f6;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.02);
}
.summary-card-title {
    font-size: 12px;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.summary-card-val {
    font-size: 26px;
    font-weight: 800;
    color: #1e293b;
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
    background: #f1f5f9;
    color: #475569;
    padding: 8px 16px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 700;
    font-size: 13px;
    transition: 0.2s;
}
.download-btn:hover {
    background: #e2e8f0;
    color: #1e293b;
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

    <!-- Quick Metrics for Employee -->
    <div class="payroll-summary">
        <div class="summary-card">
            <div class="summary-card-title">Cycles Processed</div>
            <div class="summary-card-val"><?php echo $cycles_count; ?> Months</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Net Lifetime Income</div>
            <div class="summary-card-val" style="color: var(--brand-green);">Rs <?php echo number_format($total_received); ?></div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Current Base</div>
            <div class="summary-card-val">Rs <?php echo number_format($details['base_salary_rs']); ?></div>
        </div>
    </div>

    <!-- Complete History Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h2 style="font-size: 18px; font-weight: 800; color: #1e293b;">Historical Salary Record</h2>
        <span style="font-size: 13px; color: #64748b; font-weight: 600; background: #f1f5f9; padding: 4px 12px; border-radius: 15px;">Dynamic Master Roll</span>
    </div>

    <div style="overflow-x: auto;">
        <?php if (empty($history)): ?>
            <div style="background: #ffffff; border-radius: 24px; padding: 50px 30px; text-align: center; border: 1px solid #eef2f6;">
                 <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="margin-bottom: 16px;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                 <h3 style="font-size: 16px; color: #475569; font-weight: 700; margin-bottom: 4px;">No Data Found</h3>
                 <p style="color: #94a3b8; font-size: 14px;">Your account has no past processed salary records on file.</p>
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
                        <td style="font-weight: 700;"><?php echo date("F Y", strtotime($row['payroll_month'] . '-01')); ?></td>
                        <td style="color: #64748b;">Rs <?php echo number_format($row['base_salary']); ?></td>
                        <td style="font-weight: 800; color: var(--brand-green);">Rs <?php echo number_format($row['net_payable']); ?></td>
                        <td>
                            <div class="status-chip">
                                <div style="width:6px; height:6px; background:#16a34a; border-radius:50%;"></div>
                                <?php echo $row['status']; ?>
                            </div>
                        </td>
                        <td style="text-align: right;">
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

<?php include_once "../includes/footer.php"; ?>
