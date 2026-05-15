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
/* ─── Ultimate Dashboard Hero ─── */
.greet-box {
    background: #ffffff;
    padding: 24px 32px;
    border-radius: 28px;
    border: 1px solid #eef2f6;
    box-shadow: 0 12px 35px rgba(0,0,0,0.02);
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
    position: relative;
    overflow: hidden;
}
.gh-avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--brand-green), #11523F);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    font-weight: 800;
    color: #fff;
    border: 4px solid #ffffff;
    box-shadow: 0 8px 24px rgba(24, 109, 85, 0.18);
    overflow: hidden;
    flex-shrink: 0;
}
.gh-avatar img {
    width: 100%; height: 100%; object-fit: cover;
}
.gh-name {
    font-size: 24px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.5px;
    margin: 0;
    line-height: 1.2;
}
.gh-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    padding: 5px 14px;
    background: rgba(24, 109, 85, 0.07);
    color: var(--brand-green);
    font-size: 13px;
    font-weight: 700;
    border-radius: 30px;
    letter-spacing: 0.2px;
}
.gh-badge::before {
    content: ''; width: 6px; height: 6px; background: var(--brand-green); border-radius: 50%; opacity: 0.7;
}
.gh-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: var(--brand-green);
    color: #fff;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(24, 109, 85, 0.25);
    transition: all 0.25s ease;
}
.gh-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(24, 109, 85, 0.3);
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
    padding: 18px 28px;
    background: #f5f9f8; /* Subtly tinted backdrop matching client capture */
    border-bottom: 1px solid #e6eeec;
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

/* Specialized Action Control */
.slip-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--brand-green);
    color: #fff;
    padding: 9px 16px;
    border-radius: 14px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(24, 109, 85, 0.2);
    transition: all 0.25s ease;
}
.slip-btn:hover {
    transform: translateY(-1px);
    background: #11523F;
    box-shadow: 0 6px 18px rgba(24, 109, 85, 0.3);
    color: #ffffff;
}

/* ─── Modern Mobile Responsive Architecture ─── */
@media (max-width: 768px) {
    .dash-layout {
        padding: 16px 12px !important;
    }
    .greet-box {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 20px !important;
        padding: 20px !important;
        border-radius: 22px !important;
    }
    .gh-avatar {
        width: 62px !important;
        height: 62px !important;
        font-size: 22px !important;
    }
    .gh-name {
        font-size: 20px !important;
    }
    .greet-static {
        display: none !important;
    }
    .greet-box > div:last-child {
        width: 100%;
    }
    .gh-action-btn {
        display: flex !important;
        justify-content: center;
        width: 100%;
        box-sizing: border-box;
    }
    .dash-grid {
        gap: 16px !important;
    }
    .widget-card {
        border-radius: 20px !important;
    }
    .widget-header {
        padding: 18px 20px !important;
    }
    .widget-body {
        padding: 20px !important;
    }
    .info-strip {
        padding: 12px 0 !important;
    }
    .info-label {
        font-size: 13px !important;
    }
    .info-val {
        font-size: 13px !important;
        max-width: 55%;
        text-align: right;
        word-break: break-word;
    }

    /* The Cardification Transform */
    .mini-table thead {
        display: none;
    }
    .mini-table, .mini-table tbody, .mini-table tr, .mini-table td {
        display: block;
        width: 100%;
        box-sizing: border-box;
    }
    .mini-table tr {
        background: #ffffff;
        border: 1px solid #eef2f6;
        border-radius: 20px;
        padding: 18px;
        margin-bottom: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }
    .mini-table td {
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
    .mini-table td::before {
        content: attr(data-label);
        font-weight: 700;
        color: #94a3b8;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .mini-table td:first-child {
        border-bottom: 1px solid #f1f5f9 !important;
        padding-bottom: 12px !important;
        margin-bottom: 4px;
        font-size: 15px !important;
        font-weight: 700;
    }
    .mini-table td:last-child {
        border-top: 1px solid #f1f5f9 !important;
        margin-top: 12px;
        padding-top: 16px !important;
        justify-content: center !important;
    }
    .mini-table td:last-child::before {
        display: none !important; /* Destroys "ACTION" text label on mobile to fix collision */
    }
    .mini-table td:last-child a {
        width: 100%;
        justify-content: center;
        box-sizing: border-box;
        padding: 12px !important; /* Comfort stretch for finger tapping */
    }
}
</style>

<div class="dash-layout">
    
    <div class="greet-box">
        <div style="display: flex; align-items: center; gap: 20px;">
            <div class="gh-avatar">
                 <?php 
                     $profile_img = $details['profile_image'] ?? ($_SESSION['profile_image'] ?? '');
                     $initials = strtoupper(substr($details['first_name'] ?? 'U', 0, 1));
                     if (!empty($profile_img)): 
                 ?>
                     <img src="../assets/uploads/<?php echo htmlspecialchars($profile_img); ?>" alt="" onerror="this.style.display='none'; this.parentElement.innerHTML='<span><?php echo $initials; ?></span>';">
                 <?php else: ?>
                     <span><?php echo $initials; ?></span>
                 <?php endif; ?>
            </div>
            <div>
                <h1 class="gh-name"><span class="greet-static">Welcome back, </span><?php echo htmlspecialchars($details['first_name']); ?></h1>
                <div class="gh-badge"><?php echo htmlspecialchars($details['position_title'] ?: 'System Member'); ?></div>
            </div>
        </div>
        <div>
            <a href="employee_payroll.php" class="gh-action-btn">
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
                    <span class="info-val"><?php echo htmlspecialchars($details['department'] ?: 'Operations'); ?></span>
                </div>
                <div class="info-strip">
                    <span class="info-label">Employment Type</span>
                    <span class="info-val"><?php echo htmlspecialchars($details['employment_type'] ?: 'Full-time'); ?></span>
                </div>
                <div class="info-strip">
                    <span class="info-label">Date of Joining</span>
                    <span class="info-val"><?php echo !empty($details['date_of_joining']) ? date("d M Y", strtotime($details['date_of_joining'])) : 'Pending'; ?></span>
                </div>
                <div class="info-strip">
                    <span class="info-label">Official Email</span>
                    <span class="info-val"><a href="mailto:<?php echo htmlspecialchars($details['email']); ?>" style="color: var(--brand-green); text-decoration: none;"><?php echo htmlspecialchars($details['email']); ?></a></span>
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
                    <span class="info-val">Rs <?php echo number_format((float)($details['base_salary_rs'] ?? 0)); ?></span>
                </div>
                <div class="info-strip">
                    <span class="info-label">Designated Institution</span>
                    <span class="info-val"><?php echo htmlspecialchars($details['bank_name'] ?: 'Not Provided'); ?></span>
                </div>
                <div class="info-strip">
                    <span class="info-label">Account Identification</span>
                    <span class="info-val"><?php 
                        if (!empty($details['bank_account_number'])) {
                            echo "**** " . substr($details['bank_account_number'], -4);
                        } else {
                            echo "Missing Data";
                        }
                    ?></span>
                </div>
                <div class="info-strip">
                    <span class="info-label">Verification Status</span>
                    <span class="info-val">
                        <?php if (!empty($details['bank_name']) && !empty($details['bank_account_number'])): ?>
                            <span style="color:#16a34a; font-weight: 700;">Active System Payout</span>
                        <?php else: ?>
                            <span style="color:#e11d48; font-weight: 700;">Action Required</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Pay Runs Widgetized Card -->
    <div class="widget-card">
        <div class="widget-header">
            <div class="widget-title">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="var(--brand-green)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Recent Salary Runs
            </div>
        </div>
        <div class="widget-body" style="overflow-x: auto;">
            <?php if (empty($recent_history)): ?>
                <div style="padding: 20px; text-align: center; color: #64748b; font-weight: 600;">
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
                            <td data-label="Payroll Month"><?php echo date("F Y", strtotime($row['payroll_month'] . '-01')); ?></td>
                            <td data-label="Base Salary">Rs <?php echo number_format($row['base_salary']); ?></td>
                            <td data-label="Net Disbursed" style="color: var(--brand-green); font-weight: 800;">Rs <?php echo number_format($row['net_payable']); ?></td>
                            <td data-label="Status">
                                <span class="status-tag">
                                    <span style="width:6px; height:6px; background:#16a34a; border-radius:50%;"></span>
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td data-label="Action">
                                 <a href="salary_invoice.php?employeeId=<?php echo $details['user_id'] ?? $real_emp_id; ?>&month=<?php echo substr($row['payroll_month'], 0, 7); ?>" target="_blank" class="slip-btn">
                                     <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
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