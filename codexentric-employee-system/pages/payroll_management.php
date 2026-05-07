<?php
    session_start();

    if (!isset($_SESSION['email'])) {
        header("Location: login.php");
        exit();
    }

    require_once '../pages/Database.php';
    require_once '../pages/Employee.php';
    require_once '../pages/Payroll.php';
    $db = new Database();
    $connection = $db->getConnection();
    $employeeObj = new Employee($connection);
    $payrollObj = new Payroll($connection);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_salary'])) {
        $emp_id = (int)$_POST['emp_id'];
        $payroll_month = $_POST['payroll_month'];
        
        $bonus_names   = $_POST['bonus_names'] ?? [];
        $bonus_amounts = $_POST['bonus_amounts'] ?? [];
        
        $allowance_names = $_POST['allowance_names'] ?? [];
        $allowance_amounts = $_POST['allowance_amounts'] ?? [];
        
        $deduction_names = $_POST['deduction_names'] ?? [];
        $deduction_amounts = $_POST['deduction_amounts'] ?? [];
        
        $empDetails = $employeeObj->getEmployeeDetailsById($emp_id);
        $base_salary = $empDetails ? (float)$empDetails['base_salary_rs'] : 0;
        
        $result = $payrollObj->processSalary(
            $emp_id, $payroll_month, $base_salary,
            $bonus_names, $bonus_amounts,
            $allowance_names, $allowance_amounts,
            $deduction_names, $deduction_amounts
        );
        
        if ($result === true) {
            $_SESSION['pr_success'] = "Salary processed successfully!";
        } else {
            $_SESSION['pr_error'] = $result;
        }
        
        header("Location: payroll_management.php?emp_id=" . $emp_id);
        exit();
    }

    $employeesFromDB = $employeeObj->getAllEmployeesPayrollDetails();
    $salary_components = $employeeObj->getSalaryComponents();
    

    
    $current_month = date('Y-m-01');
    $monthly_stats = $payrollObj->getMonthlyPayrollStats($current_month);
    
    $salary_breakdown = [];
    foreach ($employeesFromDB as $emp) {
        $bank_info = !empty($emp['bank_name']) ? $emp['bank_name'] : "No Bank";
        if (!empty($emp['bank_account_number'])) {
            $bank_info .= " - " . $emp['bank_account_number'];
        }
        
        $allowances_list = [];
        if (!empty($emp['allowances_rs']) && $emp['allowances_rs'] > 0) {
            $allowances_list[] = ["name" => "Standard Allowance", "amount" => $emp['allowances_rs']];
        }

        $salary_breakdown[] = [
            "id" => (int)$emp['user_id'],
            "name" => trim($emp['first_name'] . ' ' . $emp['last_name']),
            "bank" => $bank_info,
            "base_salary" => $emp['base_salary_rs'] ?: "0",
            "allowances_list" => $allowances_list
        ];
    }

    $selected_emp_id = null;
    $selected_employee_data = null;

    if (isset($_GET['emp_id'])) {
        $selected_emp_id = (int)$_GET['emp_id'];
        foreach ($salary_breakdown as $emp) {
            if ($emp['id'] === $selected_emp_id) {
                $selected_employee_data = $emp;
                break;
            }
        }
    }

    $current_page = "payroll"; 
    $extra_css    = "admin_dashboard";
    $title        = "Payroll Management";

    include_once "../includes/header.php";
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap');

:root {
    --ox: #186D55;           /* CodeXentric Brand Green */
    --ox-light: #E8F3F0;
    --ox-hover: #125542;
    --green: #00B8A3;
    --green-bg: #E6FAF8;
    --red: #EB4034;
    --red-bg: #FEF0EF;
    --text-h: #1D2532;
    --text-m: #6B7280;
    --text-s: #9CA3AF;
    --border: #E5E7EB;
    --bg: #F6F7FB;
    --card: #ffffff;
    --radius: 8px;
    --shadow: 0 1px 4px rgba(0,0,0,0.06);
    --shadow-md: 0 4px 16px rgba(0,0,0,0.08);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body, .payroll-root * {
    font-family: 'DM Sans', sans-serif;
}

.payroll-root {
    background: var(--bg);
    min-height: calc(100vh - 60px);
    padding: 0;
}

/* Remove main-content shell padding for seamless header integration */
.main-content { padding-top: 0 !important; }

/* ── Orange top header bar (like OrangeHRM page header) ── */
.pr-page-header {
    background: var(--ox);
    padding: 18px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.pr-page-header h1 {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    letter-spacing: -0.2px;
}
.pr-page-header p {
    font-size: 12.5px;
    color: rgba(255,255,255,0.75);
    margin-top: 2px;
}
.pr-month-badge {
    background: rgba(255,255,255,0.18);
    border: 1px solid rgba(255,255,255,0.3);
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    padding: 6px 16px;
    border-radius: 20px;
    letter-spacing: 0.3px;
}

/* ── Body layout ── */
.pr-body {
    padding: 24px 32px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.pr-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}
@media (max-width: 1024px) { .pr-stats { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .pr-stats { grid-template-columns: 1fr; } }

.pr-stat {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.2s ease;
}
.pr-stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.pr-stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #f0f4f8;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.pr-stat-icon svg    { width: 22px; height: 22px; stroke: #475569 !important; }

.pr-stat-info {}
.pr-stat-label { font-size: 11.5px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.6px; }
.pr-stat-value { font-size: 22px; font-weight: 700; color: #1e293b; margin-top: 2px; line-height: 1; }
.pr-stat-sub   { font-size: 11.5px; color: #94a3b8; margin-top: 3px; }

/* ── Main two-column split ── */
.pr-split {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 20px;
    align-items: start;
}
@media (max-width: 960px) { .pr-split { grid-template-columns: 1fr; } }

/* ── Card shell ── */
.pr-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

/* ── Card header ── */
.pr-card-head {
    background: #FAFAFA;
    border-bottom: 1px solid var(--border);
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.pr-card-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-h);
    display: flex;
    align-items: center;
    gap: 8px;
}
.pr-card-title svg {
    width: 17px;
    height: 17px;
    stroke: var(--ox);
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.pr-count-pill {
    background: var(--ox-light);
    color: var(--ox);
    font-size: 11px;
    font-weight: 700;
    padding: 2px 9px;
    border-radius: 12px;
}

/* ── Employee list ── */
.pr-emp-list { max-height: 580px; overflow-y: auto; }
.pr-emp-list::-webkit-scrollbar { width: 3px; }
.pr-emp-list::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 3px; }

.pr-emp-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 18px;
    border-bottom: 1px solid #F3F4F6;
    text-decoration: none;
    transition: background 0.15s;
    cursor: pointer;
}
.pr-emp-item:hover { background: var(--ox-light); }
.pr-emp-item.active { background: var(--ox-light); border-left: 3px solid var(--ox); }

.pr-emp-left { display: flex; align-items: center; gap: 12px; }
.pr-av {
    width: 36px; height: 36px; border-radius: 50%;
    background: var(--ox); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 13px; flex-shrink: 0;
}
.pr-emp-item.active .pr-av { background: var(--ox-hover); }
.pr-emp-name { font-size: 14px; font-weight: 600; color: var(--text-h); }
.pr-emp-bank { font-size: 11.5px; color: var(--text-s); margin-top: 1px; }

/* ── Status badges (OrangeHRM style) ── */
.badge {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11.5px; font-weight: 600; padding: 3px 10px;
    border-radius: 20px; white-space: nowrap;
}
.badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
.badge-paid   { background: var(--green-bg); color: var(--green); }
.badge-draft  { background: #F3F4F6; color: var(--text-m); }
.badge-paid::before   { background: var(--green); }
.badge-draft::before  { background: var(--text-s); }

/* ── Detail pane ── */
.pr-detail-body { padding: 28px 32px; }

.pr-form-section {
    margin-bottom: 28px;
}
.pr-form-section-title {
    font-size: 12px;
    font-weight: 700;
    color: var(--text-m);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.pr-form-section-title svg {
    width: 14px; height: 14px;
    stroke: var(--ox); fill: none;
    stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
}

/* Base salary display box */
.pr-base-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #FAFAFA;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 16px 20px;
}
.pr-base-label { font-size: 14px; font-weight: 500; color: var(--text-m); }
.pr-base-amount { font-size: 18px; font-weight: 700; color: var(--text-h); }

/* Dynamic input rows */
.pr-dyn-row {
    display: grid;
    grid-template-columns: 1fr 160px 40px;
    gap: 10px;
    align-items: center;
    margin-bottom: 10px;
    animation: rowIn 0.18s ease both;
}

.pr-input {
    width: 100%;
    height: 40px;
    padding: 0 14px;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    color: var(--text-h);
    background: #fff;
    transition: border-color 0.15s, box-shadow 0.15s;
    outline: none;
}
.pr-input:focus {
    border-color: var(--ox);
    box-shadow: 0 0 0 3px rgba(255,107,0,0.1);
}
.pr-input[readonly], .pr-input:disabled {
    background: #F9FAFB;
    color: var(--text-m);
    cursor: not-allowed;
}
select.pr-input {
    appearance: none; -webkit-appearance: none; cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg width='12' height='7' viewBox='0 0 12 7' fill='none' stroke='%239CA3AF' stroke-width='2' stroke-linecap='round' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 12px center;
    padding-right: 32px;
}
.pr-amount-wrap { position: relative; }
.pr-amount-prefix {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    font-size: 13px; font-weight: 600; color: var(--text-m); pointer-events: none;
}
.pr-amount-wrap .pr-input { padding-left: 36px; }

/* Icon buttons */
.pr-btn-sq {
    width: 38px; height: 38px; border-radius: 6px; border: none;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: all 0.15s;
}
.pr-btn-sq svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 2.2; stroke-linecap: round; }
.pr-btn-add { background: var(--ox-light); color: var(--ox); }
.pr-btn-add:hover { background: var(--ox); color: #fff; }
.pr-btn-rem { background: var(--red-bg); color: var(--red); }
.pr-btn-rem:hover { background: var(--red); color: #fff; }

/* Add row trigger link */
.pr-add-link {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 13px; font-weight: 600; color: var(--ox);
    cursor: pointer; border: none; background: none;
    padding: 4px 0; text-decoration: none;
    transition: opacity 0.15s;
    margin-top: 4px;
}
.pr-add-link:hover { opacity: 0.75; }
.pr-add-link svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2.5; stroke-linecap: round; }

/* Process button */
.pr-process-btn {
    width: 100%; height: 46px;
    background: var(--ox); color: #fff;
    border: none; border-radius: var(--radius);
    font-family: 'DM Sans', sans-serif;
    font-size: 14px; font-weight: 700;
    cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: background 0.15s, transform 0.1s;
    margin-top: 24px;
    box-shadow: 0 4px 12px rgba(24, 109, 85, 0.25);
}
.pr-process-btn:hover { background: var(--ox-hover); transform: translateY(-1px); }
.pr-process-btn svg { width: 18px; height: 18px; stroke: #fff; fill: none; stroke-width: 2.5; stroke-linecap: round; }

/* Locked state */
.pr-locked {
    text-align: center;
    padding: 60px 32px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}
.pr-locked-icon {
    width: 64px; height: 64px; border-radius: 50%;
    background: var(--green-bg);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 4px;
}
.pr-locked-icon svg { width: 28px; height: 28px; stroke: var(--green); fill: none; stroke-width: 1.8; stroke-linecap: round; }
.pr-locked h3 { font-size: 17px; font-weight: 700; color: var(--text-h); }
.pr-locked p { font-size: 14px; color: var(--text-m); max-width: 300px; line-height: 1.6; }
.pr-invoice-btn {
    display: inline-flex; align-items: center; gap: 8px;
    margin-top: 8px; padding: 10px 24px;
    background: var(--green-bg); color: var(--green);
    border: 1.5px solid var(--green); border-radius: var(--radius);
    font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 700;
    text-decoration: none; transition: all 0.15s;
}
.pr-invoice-btn:hover { background: var(--green); color: #fff; }

/* Empty state */
.pr-empty {
    text-align: center;
    padding: 80px 32px;
    display: flex; flex-direction: column; align-items: center; gap: 12px;
}
.pr-empty-icon {
    width: 72px; height: 72px; border-radius: 50%;
    background: var(--ox-light);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 4px;
}
.pr-empty-icon svg { width: 32px; height: 32px; stroke: var(--ox); fill: none; stroke-width: 1.6; stroke-linecap: round; }
.pr-empty h3 { font-size: 16px; font-weight: 700; color: var(--text-h); }
.pr-empty p  { font-size: 14px; color: var(--text-m); max-width: 280px; line-height: 1.6; }

/* Toast */
.pr-toast {
    position: fixed; bottom: 28px; right: 28px;
    background: #1D2532; color: #fff;
    padding: 14px 22px; border-radius: var(--radius);
    font-size: 14px; font-weight: 600;
    display: flex; align-items: center; gap: 12px;
    box-shadow: var(--shadow-md);
    transform: translateY(100px); opacity: 0;
    transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    z-index: 9999;
}
.pr-toast.show { transform: translateY(0); opacity: 1; }
.pr-toast svg { width: 18px; height: 18px; flex-shrink: 0; stroke: var(--green); fill: none; stroke-width: 2.5; stroke-linecap: round; }

/* Detail header info strip */
.pr-detail-info {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 8px;
}
.pr-detail-name { font-size: 16px; font-weight: 700; color: var(--text-h); }
.pr-detail-meta { font-size: 12.5px; color: var(--text-m); margin-top: 2px; }

@keyframes rowIn { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:translateY(0); } }
@keyframes fadeUp { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
.pr-stats { animation: fadeUp .3s ease both; }
.pr-split  { animation: fadeUp .3s .1s ease both; }
</style>

<div class="payroll-root">


    <div class="pr-body">

        <!-- ── Stats ── -->
        <div class="pr-stats">
            <div class="pr-stat">
                <div class="pr-stat-icon brand">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#186D55" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <div class="pr-stat-info">
                    <div class="pr-stat-label">Total Employees</div>
                    <div class="pr-stat-value"><?php echo count($salary_breakdown); ?></div>
                    <div class="pr-stat-sub">On payroll this month</div>
                </div>
            </div>
            <div class="pr-stat">
                <div class="pr-stat-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#186D55" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <div class="pr-stat-info">
                    <div class="pr-stat-label">Total Disbursed</div>
                    <div class="pr-stat-value">Rs <?php echo number_format($monthly_stats['net_payable'] ?? 0); ?></div>
                    <div class="pr-stat-sub">Net payable for <?php echo date('F'); ?></div>
                </div>
            </div>
            <div class="pr-stat">
                <div class="pr-stat-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#186D55" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <div class="pr-stat-info">
                    <div class="pr-stat-label">Processed</div>
                    <div class="pr-stat-value"><?php echo $monthly_stats['processed_count'] ?? 0; ?></div>
                    <div class="pr-stat-sub">Salaries finalized</div>
                </div>
            </div>
            <div class="pr-stat">
                <div class="pr-stat-icon gray">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div class="pr-stat-info">
                    <div class="pr-stat-label">Pending</div>
                    <div class="pr-stat-value"><?php echo count($salary_breakdown) - ($monthly_stats['processed_count'] ?? 0); ?></div>
                    <div class="pr-stat-sub">Awaiting action</div>
                </div>
            </div>
        </div>

        <!-- ── Main split ── -->
        <div class="pr-split">

            <!-- Employee list panel -->
            <div class="pr-card">
                <div class="pr-card-head">
                    <span class="pr-card-title">
                        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Employees
                    </span>
                    <span class="pr-count-pill"><?php echo count($salary_breakdown); ?></span>
                </div>
                <div class="pr-emp-list">
                    <?php foreach ($salary_breakdown as $row):
                        $is_active = ($selected_emp_id == $row['id']) ? 'active' : '';
                        $real_row_emp_id = $payrollObj->getEmployeeIdByUserId($row['id']);
                        $is_paid   = $payrollObj->isSalaryProcessed($real_row_emp_id, date('Y-m-01'));
                    ?>
                    <a href="payroll_management.php?emp_id=<?php echo $row['id']; ?>" class="pr-emp-item <?php echo $is_active; ?>">
                        <div class="pr-emp-left">
                            <div class="pr-av"><?php echo strtoupper(substr($row['name'], 0, 1)); ?></div>
                            <div>
                                <div class="pr-emp-name"><?php echo htmlspecialchars($row['name']); ?></div>
                                <div class="pr-emp-bank"><?php echo htmlspecialchars($row['bank']); ?></div>
                            </div>
                        </div>
                        <?php if ($is_paid): ?>
                            <span class="badge badge-paid">Paid</span>
                        <?php else: ?>
                            <span class="badge badge-draft">Draft</span>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Detail / form pane -->
            <div class="pr-card">
                <?php if ($selected_employee_data):
                    $real_emp_id = $payrollObj->getEmployeeIdByUserId($selected_employee_data['id']);
                    $current_payroll_month = date('Y-m-01');
                    $already_paid = $payrollObj->isSalaryProcessed($real_emp_id, $current_payroll_month);
                    $saved_breakdown = false;
                    if ($already_paid) {
                        $saved_breakdown = $payrollObj->getSavedSalaryBreakdown($real_emp_id, $current_payroll_month);
                    }
                ?>
                    <div class="pr-card-head">
                        <div class="pr-detail-info">
                            <div>
                                <div class="pr-detail-name"><?php echo htmlspecialchars($selected_employee_data['name']); ?></div>
                                <div class="pr-detail-meta"><?php echo htmlspecialchars($selected_employee_data['bank']); ?></div>
                            </div>
                            <?php if ($already_paid): ?>
                                <span class="badge badge-paid" style="padding:5px 14px;font-size:12px;">✓ Salary Disbursed</span>
                            <?php else: ?>
                                <span class="badge badge-draft" style="padding:5px 14px;font-size:12px;">Pending</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!$already_paid): ?>
                    <div class="pr-detail-body">
                        <form method="POST" id="salaryForm">
                            <input type="hidden" name="emp_id" value="<?php echo $selected_employee_data['id']; ?>">
                            <input type="hidden" name="payroll_month" value="<?php echo $current_payroll_month; ?>">

                            <!-- Base Salary -->
                            <div class="pr-form-section">
                                <div class="pr-form-section-title">
                                    <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="18" rx="2"/><path d="M2 10h20"/></svg>
                                    Base Salary
                                </div>
                                <div class="pr-base-box">
                                    <span class="pr-base-label">Monthly Contracted Pay</span>
                                    <span class="pr-base-amount">Rs <?php echo number_format($selected_employee_data['base_salary']); ?></span>
                                </div>
                            </div>

                            <!-- Bonuses -->
                            <div class="pr-form-section">
                                <div class="pr-form-section-title">
                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                                    Bonuses
                                </div>
                                <div id="bonuses-container">
                                    <div class="pr-dyn-row">
                                        <select class="pr-input" name="bonus_names[]">
                                            <option value="" disabled selected>Select bonus type…</option>
                                            <?php foreach($salary_components['bonuses'] as $b): ?>
                                                <option value="<?php echo htmlspecialchars($b['id']); ?>"><?php echo htmlspecialchars($b['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="pr-amount-wrap">
                                            <span class="pr-amount-prefix">Rs</span>
                                            <input type="number" class="pr-input" name="bonus_amounts[]" placeholder="0" min="0">
                                        </div>
                                        <button type="button" class="pr-btn-sq pr-btn-add" onclick="addRow('bonuses-container','bonus')" title="Add row">
                                            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Allowances -->
                            <div class="pr-form-section">
                                <div class="pr-form-section-title">
                                    <svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    Allowances
                                </div>
                                <div id="allowances-container">
                                    <div class="pr-dyn-row">
                                        <select class="pr-input" name="allowance_names[]">
                                            <option value="" disabled selected>Select allowance…</option>
                                            <?php foreach($salary_components['allowances'] as $a): ?>
                                                <option value="<?php echo htmlspecialchars($a['id']); ?>"><?php echo htmlspecialchars($a['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="pr-amount-wrap">
                                            <span class="pr-amount-prefix">Rs</span>
                                            <input type="number" class="pr-input" name="allowance_amounts[]" placeholder="0" min="0">
                                        </div>
                                        <button type="button" class="pr-btn-sq pr-btn-add" onclick="addRow('allowances-container','allowance')" title="Add row">
                                            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Deductions -->
                            <div class="pr-form-section">
                                <div class="pr-form-section-title">
                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                                    Deductions
                                </div>
                                <div id="deductions-container">
                                    <div class="pr-dyn-row">
                                        <select class="pr-input" name="deduction_names[]">
                                            <option value="" disabled selected>Select deduction…</option>
                                            <?php foreach($salary_components['deductions'] as $d): ?>
                                                <option value="<?php echo htmlspecialchars($d['id']); ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="pr-amount-wrap">
                                            <span class="pr-amount-prefix">Rs</span>
                                            <input type="number" class="pr-input" name="deduction_amounts[]" placeholder="0" min="0">
                                        </div>
                                        <button type="button" class="pr-btn-sq pr-btn-add" onclick="addRow('deductions-container','deduction')" title="Add row">
                                            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" name="process_salary" class="pr-process-btn"
                                onclick="return confirm('Process salary for <?php echo htmlspecialchars($selected_employee_data['name']); ?>? This will lock the record for <?php echo date('F Y'); ?>.');">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                Process & Finalize Salary
                            </button>
                        </form>
                    </div>

                    <?php else: ?>
                    <div class="pr-locked">
                        <div class="pr-locked-icon">
                            <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <h3>Record Locked</h3>
                        <p>Salary for <strong><?php echo htmlspecialchars($selected_employee_data['name']); ?></strong> has been processed and disbursed for <?php echo date('F Y'); ?>.</p>
                        <a href="salary_invoice.php?employeeId=<?php echo urlencode($selected_employee_data['id']); ?>&month=<?php echo urlencode(date('Y-m', strtotime($current_payroll_month))); ?>" class="pr-invoice-btn">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            View Invoice
                        </a>
                    </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="pr-empty">
                        <div class="pr-empty-icon">
                            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <h3>No Employee Selected</h3>
                        <p>Choose a team member from the list on the left to process their monthly payroll.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div><!-- /.pr-split -->

    </div><!-- /.pr-body -->

</div><!-- /.payroll-root -->

<!-- Toast notification -->
<div class="pr-toast" id="prToast">
    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="toastMsg"></span>
</div>

<script>
function showToast(msg) {
    const t = document.getElementById('prToast');
    document.getElementById('toastMsg').innerText = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 4000);
}
</script>

<?php if (isset($_SESSION['pr_success'])): ?>
<script>document.addEventListener('DOMContentLoaded', () => showToast('<?php echo addslashes($_SESSION['pr_success']); ?>'));</script>
<?php unset($_SESSION['pr_success']); endif; ?>

<?php if (isset($_SESSION['pr_error'])): ?>
<script>document.addEventListener('DOMContentLoaded', () => showToast('<?php echo addslashes($_SESSION['pr_error']); ?>'));</script>
<?php unset($_SESSION['pr_error']); endif; ?>

<script>
const salaryComponents = <?php echo json_encode($salary_components); ?>;

const rowConfig = {
    bonus: {
        selectName: 'bonus_names[]', inputName: 'bonus_amounts[]',
        options: salaryComponents.bonuses.map(b => [b.id, b.name])
    },
    allowance: {
        selectName: 'allowance_names[]', inputName: 'allowance_amounts[]',
        options: salaryComponents.allowances.map(a => [a.id, a.name])
    },
    deduction: {
        selectName: 'deduction_names[]', inputName: 'deduction_amounts[]',
        options: salaryComponents.deductions.map(d => [d.id, d.name])
    }
};

function addRow(containerId, type) {
    const cfg = rowConfig[type];
    const container = document.getElementById(containerId);
    const opts = cfg.options.map(([val, label]) => `<option value="${val}">${label}</option>`).join('');

    const row = document.createElement('div');
    row.className = 'pr-dyn-row';
    row.innerHTML = `
        <select class="pr-input" name="${cfg.selectName}">
            <option value="" disabled selected>Select…</option>${opts}
        </select>
        <div class="pr-amount-wrap">
            <span class="pr-amount-prefix">Rs</span>
            <input type="number" class="pr-input" name="${cfg.inputName}" placeholder="0" min="0">
        </div>
        <button type="button" class="pr-btn-sq pr-btn-rem"
                onclick="this.closest('.pr-dyn-row').remove()" title="Remove">
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
    `;
    container.appendChild(row);
    row.querySelector('select').focus();
}
</script>

<?php include_once "../includes/footer.php"; ?>
