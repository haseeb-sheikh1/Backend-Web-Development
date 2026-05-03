<?php
    session_start();

    if (!isset($_SESSION['email'])) {
        header("Location: login.php");
        exit();
    }

    require_once '../pages/database.php';
    require_once '../pages/Employee.php';
    $db = new Database();
    $connection = $db->getConnection();
    $employeeObj = new Employee($connection);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_salary'])) {
        $emp_id = (int)$_POST['emp_id'];
        $payroll_month = $_POST['payroll_month'];
        
        $bonus_names   = $_POST['bonus_names'] ?? [];
        $bonus_amounts = $_POST['bonus_amounts'] ?? [];
        
        $allowance_names = $_POST['allowance_names'] ?? [];
        $allowance_amounts = $_POST['allowance_amounts'] ?? [];
        
        $deduction_names = $_POST['deduction_names'] ?? [];
        $deduction_amounts = $_POST['deduction_amounts'] ?? [];
        
        // --- PRODUCTION DATABASE LOGIC HERE ---
        // 1. array_sum($bonus_amounts), array_sum($allowance_amounts), array_sum($deduction_amounts)
        // 2. Net = Base + Bonuses + Allowances - Deductions
        // 3. INSERT into `salaries`, `salary_bonuses`, `salary_allowances`, `salary_deductions`
        // --------------------------------------

        $_SESSION['salary_given'][$emp_id] = true;
        $_SESSION['just_paid'] = true;
        
        header("Location: payroll_management.php?emp_id=" . $emp_id);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_demo'])) {
        unset($_SESSION['salary_given']);
        header("Location: payroll_management.php");
        exit();
    }

    $employeesFromDB = $employeeObj->getAllEmployeesPayrollDetails();
    $salary_components = $employeeObj->getSalaryComponents();
    
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
    $title        = "Payroll Workspace - Admin";

    include_once "../includes/header.php";
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

:root {
  --blue:        #1E6FD9;
  --blue-dark:   #1559B5;
  --blue-light:  #EBF2FC;
  --blue-xlight: #F0F6FF;
  --green:       #059669;
  --green-bg:    #D1FAE5;
  --amber:       #D97706;
  --amber-bg:    #FEF3C7;
  --red:         #DC2626;
  --red-bg:      #FEE2E2;
  --border:      #E2E8F0;
  --surface:     #F8FAFC;
  --card:        #ffffff;
  --text-h:      #0F172A;
  --text-b:      #374151;
  --text-m:      #64748B;
  --text-s:      #94A3B8;
  --radius:      12px;
  --radius-sm:   8px;
  --shadow-xs:   0 1px 3px rgba(15,23,42,0.05);
  --shadow-sm:   0 1px 6px rgba(15,23,42,0.07);
  --shadow-md:   0 4px 20px rgba(15,23,42,0.09);
}



/* Welcome Banner */
.dash-welcome {
  background: linear-gradient(135deg, #0f1c2e 0%, #1252cc 60%, #1a6eff 100%);
  border-radius: var(--radius-lg, 12px);
  padding: 32px 36px;
  display: flex; align-items: center; justify-content: space-between;
  position: relative; overflow: hidden;
  box-shadow: var(--shadow-bl);
}
@media (max-width: 760px) {
  .dash-welcome { flex-direction: column; align-items: flex-start; gap: 20px; padding: 24px; }
}
.dash-welcome::before {
  content: ''; position: absolute;
  width: 300px; height: 300px; border-radius: 50%;
  background: rgba(255,255,255,0.04);
  top: -100px; right: -60px; pointer-events: none;
}
.dash-welcome::after {
  content: ''; position: absolute;
  width: 160px; height: 160px; border-radius: 50%;
  background: rgba(255,255,255,0.03);
  bottom: -50px; right: 200px; pointer-events: none;
}
.dash-welcome-text { position: relative; z-index: 1; }
.dash-welcome-text h1 { font-family: 'Inter', sans-serif; font-size: 26px; font-weight: 800; color: #fff; margin: 0 0 6px 0; letter-spacing: -0.3px; }
.dash-welcome-text p { font-size: 14.5px; color: rgba(255,255,255,0.8); margin: 0; }

.dash-welcome-actions { position: relative; z-index: 1; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.dash-welcome-actions .btn-reset, .dash-welcome-actions .pr-period-select {
  background-color: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.25);
  box-shadow: none;
}
.dash-welcome-actions .btn-reset:hover {
  background-color: rgba(255,255,255,0.2); border-color: rgba(255,255,255,0.35); color: #fff;
}
.dash-welcome-actions .pr-period-label { color: rgba(255,255,255,0.7); }
.dash-welcome-actions .pr-period-select { 
  background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 14px center;
}
.dash-welcome-actions .pr-period-select:focus { border-color: rgba(255,255,255,0.4); }
.dash-welcome-actions .pr-period-select option { background: #fff; color: #111827; }

.pr-period-wrap { display: flex; align-items: center; gap: 12px; }
.pr-period-label { font-size: 11px; font-weight: 800; color: var(--text-s); text-transform: uppercase; letter-spacing: 0.8px; }
.pr-period-select {
    height: 38px; padding: 0 36px 0 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);
    font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 700; color: var(--text-h);
    background: var(--card); cursor: pointer; outline: none; box-shadow: var(--shadow-xs);
    appearance: none; -webkit-appearance: none; 
    background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' stroke='%2394A3B8' stroke-width='2' stroke-linecap='round' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 14px center;
}
.pr-period-select:focus { border-color: var(--blue); }

.btn-reset {
    height: 38px; padding: 0 14px; background: transparent; color: var(--text-m); border: 1.5px solid var(--border); border-radius: var(--radius-sm); 
    font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-xs); display: inline-flex; align-items: center; gap: 6px;
}
.btn-reset:hover { background: var(--surface); color: var(--text-h); border-color: var(--text-m); }

.dash-section-label { font-size: 11px; font-weight: 800; color: var(--text-s); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.dash-section-label::after { content: ''; flex: 1; height: 1px; background: var(--border); }

.dash-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
@media (max-width: 860px) { .dash-stats { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 520px)  { .dash-stats { grid-template-columns: 1fr; } }

.stat-card {
  background: var(--card); border: 1.5px solid var(--border); border-radius: var(--radius);
  padding: 22px 22px 18px; display: flex; flex-direction: column; gap: 14px;
  position: relative; overflow: hidden; transition: box-shadow .2s, transform .2s; box-shadow: var(--shadow-xs);
}
.stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
.stat-card::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--blue-dark), var(--blue)); opacity: 0; transition: opacity .2s; }
.stat-card:hover::after { opacity: 1; }
.stat-card.deduct::after { background: linear-gradient(90deg, #DC2626, #EF4444); }
.stat-card.net::after    { background: linear-gradient(90deg, #059669, #10B981); }

.stat-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.stat-label { font-size: 12px; font-weight: 700; color: var(--text-m); text-transform: uppercase; letter-spacing: 0.55px; margin: 0; }
.stat-icon-wrap { width: 42px; height: 42px; border-radius: 10px; background: var(--blue-light); color: var(--blue); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-card.deduct .stat-icon-wrap { background: var(--red-bg); color: var(--red); }
.stat-card.net .stat-icon-wrap { background: var(--green-bg); color: var(--green); }

.stat-value { font-family: 'Inter', sans-serif; font-size: 30px; font-weight: 900; color: var(--text-h); line-height: 1; letter-spacing: -0.5px; margin: 0; }
.stat-footer { display: flex; align-items: center; justify-content: space-between; }
.stat-sub { font-size: 12px; color: var(--text-s); }
.stat-trend { font-size: 11.5px; font-weight: 700; padding: 3px 8px; border-radius: 20px; white-space: nowrap; }
.stat-trend.neutral { color: var(--text-m); background: var(--surface); }
.stat-trend.warn { background: var(--red-bg); color: var(--red); }
.stat-trend.good { background: var(--green-bg); color: var(--green); }

.dash-split { display: grid; grid-template-columns: 340px 1fr; gap: 20px; align-items: start; }
@media (max-width: 980px) { .dash-split { grid-template-columns: 1fr; } }

.dash-card { background: var(--card); border: 1.5px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-xs); overflow: hidden; }
.dash-card-head { padding: 16px 20px; border-bottom: 1.5px solid var(--border); background: var(--surface); display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.dash-card-title { font-family: 'Inter', sans-serif; font-size: 14.5px; font-weight: 800; color: var(--text-h); display: flex; align-items: center; gap: 8px; margin: 0; }
.dash-card-title .dot { width: 8px; height: 8px; border-radius: 50%; background: linear-gradient(135deg, var(--blue-dark), var(--blue)); }

.sidebar-list { height: calc(100vh - 350px); min-height: 400px; overflow-y: auto; padding: 6px 0; }
.emp-item { display: flex; gap: 12px; align-items: center; justify-content: space-between; padding: 12px 20px; border-bottom: 1px solid #F1F5F9; text-decoration: none; transition: background .12s; border-left: 3px solid transparent; }
.emp-item:last-child { border-bottom: none; }
.emp-item:hover { background: #F8FAFD; }
.emp-item.active { background: var(--blue-xlight); border-left-color: var(--blue); }

.emp-cell { display: flex; align-items: center; gap: 11px; }
.emp-av { width: 36px; height: 36px; border-radius: 9px; flex-shrink: 0; background: linear-gradient(135deg, var(--blue-dark), var(--blue)); color: #fff; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 800; display: flex; align-items: center; justify-content: center; }
.emp-item.active .emp-av { box-shadow: 0 4px 10px rgba(30,111,217,0.25); }
.emp-name-txt { font-weight: 700; color: var(--text-h); font-size: 13.5px; }
.emp-role-txt { font-size: 12px; color: var(--text-s); margin-top: 1px; }

.st-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
.st-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.badge-active { background: var(--green-bg); color: var(--green); }
.badge-draft { background: var(--surface); color: var(--text-m); border: 1px solid var(--border); padding: 3px 9px; }

.detail-body { padding: 32px; }
.salary-component { margin-bottom: 32px; }
.salary-component > .comp-label { font-size: 12.5px; font-weight: 800; color: var(--text-m); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 12px; }

.amt-box { background: var(--surface); border: 1.5px solid var(--border); border-radius: var(--radius-sm); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; font-family: 'Inter', sans-serif; font-size: 16px; font-weight: 800; color: var(--text-h); }
.allowance-item { background: var(--surface); border: 1.5px solid var(--border); border-radius: var(--radius-sm); padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.allowance-name { font-weight: 700; color: var(--text-b); font-size: 14.5px; }
.allowance-amt  { font-family: 'Inter', sans-serif; font-weight: 800; color: var(--green); font-size: 15px; }
.deduction-amt  { font-family: 'Inter', sans-serif; font-weight: 800; color: var(--red);   font-size: 15px; }

/* Dynamic rows */
.dyn-row { display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; align-items: center; }

.input-wrap label { font-size: 12px; color: var(--text-s); font-weight: 700; margin-bottom: 6px; display: block; }
.input-rel { position: relative; }
.input-prefix { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--text-m); font-size: 14px; pointer-events: none; }
.dash-input {
  width: 100%; height: 44px; padding: 0 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);
  font-family: 'Inter', sans-serif; font-size: 15px; font-weight: 700; color: var(--text-h);
  background: #fff; box-sizing: border-box; transition: border-color .2s, box-shadow .2s;
}
.dash-input.with-prefix { padding-left: 40px; font-weight: 800; }
.dash-input.green-text  { color: var(--green); }
.dash-input.red-text    { color: var(--red); }
.dash-input:focus { border-color: var(--blue); outline: none; box-shadow: 0 0 0 3px var(--blue-light); }
.dash-input[readonly], .dash-input:disabled { background: transparent; border-style: dashed; color: var(--text-m); cursor: not-allowed; }

select.dash-input {
    cursor: pointer; appearance: none; -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' stroke='%2394A3B8' stroke-width='2' stroke-linecap='round' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 14px center;
}

.btn-icon-sq { width: 44px; height: 44px; border-radius: var(--radius-sm); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; flex-shrink: 0; }
.btn-add-sq  { background: var(--blue-light); color: var(--blue); }
.btn-add-sq:hover  { background: var(--blue); color: #fff; box-shadow: 0 4px 10px rgba(30,111,217,0.25); transform: translateY(-1px); }
.btn-rem-sq  { background: var(--red-bg); color: var(--red); }
.btn-rem-sq:hover  { background: var(--red); color: #fff; box-shadow: 0 4px 10px rgba(220,38,38,0.25); transform: translateY(-1px); }

.btn-primary-lg { width: 100%; height: 50px; margin-top: 20px; background: var(--green); color: #fff; border: none; border-radius: var(--radius-sm); font-size: 16px; font-weight: 800; font-family: 'Inter', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background .2s, transform .1s; box-shadow: var(--shadow-sm); }
.btn-primary-lg:hover { background: #047857; transform: translateY(-1px); box-shadow: var(--shadow-md); }
.btn-locked-lg  { width: 100%; height: 50px; margin-top: 20px; background: var(--surface); color: var(--green); border: 2px solid var(--green-bg); border-radius: var(--radius-sm); font-size: 15px; font-weight: 800; font-family: 'Inter', sans-serif; cursor: default; display: flex; align-items: center; justify-content: center; gap: 8px; }

.dash-empty { text-align: center; padding: 80px 24px; color: var(--text-s); }
.dash-empty svg { opacity: .35; margin-bottom: 12px; }
.dash-empty strong { display: block; font-size: 16px; color: var(--text-h); margin-bottom: 4px; font-family: 'Inter', sans-serif; font-weight: 800; }
.dash-empty p { font-size: 13.5px; max-width: 300px; margin: 0 auto; line-height: 1.5; }

.pr-toast { position: fixed; bottom: 32px; right: 32px; background: var(--text-h); color: #fff; padding: 16px 24px; border-radius: var(--radius-sm); font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 12px; box-shadow: var(--shadow-md); transform: translateY(120px); opacity: 0; transition: all .4s cubic-bezier(0.175, 0.885, 0.32, 1.275); z-index: 9999; }
.pr-toast.show { transform: translateY(0); opacity: 1; }

/* Section dividers inside form */
.form-section-divider { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
.form-section-divider span { font-size: 12.5px; font-weight: 800; color: var(--text-m); text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
.form-section-divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

@keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
.dash-welcome { animation: fadeUp .35s ease both; }
.dash-stats      { animation: fadeUp .35s .1s ease both; }
.dash-split      { animation: fadeUp .35s .2s ease both; }

/* Row enter animation */
@keyframes rowIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
.dyn-row { animation: rowIn .2s ease both; }
</style>

<div class="dashboard-container">

    <!-- ══ Header ══ -->
    <div class="dash-welcome">
        <div class="dash-welcome-text">
            <h1>Payroll Workspace</h1>
            <p>Process monthly salaries and manage allowances.</p>
        </div>
        <div class="dash-welcome-actions pr-period-wrap">
            <form method="POST" style="display:inline;">
                <button type="submit" name="reset_demo" class="btn-reset">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    Reset Demo
                </button>
            </form>
            <span class="pr-period-label">Period</span>
            <select class="pr-period-select" form="salaryForm" name="payroll_month">
                <option value="2026-04-01">April 2026</option>
                <option value="2026-03-01">March 2026</option>
            </select>
        </div>
    </div>

    <!-- ══ Stats ══ -->
    <div>
        <div class="dash-section-label">Overview</div>
        <div class="dash-stats">
            <div class="stat-card">
                <div class="stat-card-top">
                    <p class="stat-label">Gross Total</p>
                    <div class="stat-icon-wrap">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                </div>
                <div class="stat-value">Rs 5,20,000</div>
                <div class="stat-footer">
                    <span class="stat-sub">Across all active employees</span>
                    <span class="stat-trend neutral">3 Total</span>
                </div>
            </div>
            <div class="stat-card deduct">
                <div class="stat-card-top">
                    <p class="stat-label">Total Deductions</p>
                    <div class="stat-icon-wrap">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                    </div>
                </div>
                <div class="stat-value">Rs 34,800</div>
                <div class="stat-footer">
                    <span class="stat-sub">Incl. Taxes & Adjustments</span>
                    <span class="stat-trend warn">Pending check</span>
                </div>
            </div>
            <div class="stat-card net">
                <div class="stat-card-top">
                    <p class="stat-label">Net Payable</p>
                    <div class="stat-icon-wrap">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                </div>
                <div class="stat-value">Rs 4,85,200</div>
                <div class="stat-footer">
                    <span class="stat-sub">Ready for Disbursement</span>
                    <span class="stat-trend good">Cleared</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ Workspace ══ -->
    <div>
        <div class="dash-section-label">Payroll Processing</div>
        <div class="dash-split">

            <!-- Sidebar -->
            <div class="dash-card">
                <div class="dash-card-head">
                    <h2 class="dash-card-title"><span class="dot"></span>Select Employee</h2>
                </div>
                <div class="sidebar-list">
                    <?php foreach ($salary_breakdown as $row):
                        $is_active = ($selected_emp_id == $row['id']) ? 'active' : '';
                        $is_paid   = isset($_SESSION['salary_given'][$row['id']]);
                    ?>
                        <a href="payroll_management.php?emp_id=<?php echo $row['id']; ?>" class="emp-item <?php echo $is_active; ?>">
                            <div class="emp-cell">
                                <div class="emp-av"><?php echo strtoupper(substr($row['name'], 0, 1)); ?></div>
                                <div>
                                    <div class="emp-name-txt"><?php echo htmlspecialchars($row['name']); ?></div>
                                    <div class="emp-role-txt"><?php echo htmlspecialchars($row['bank']); ?></div>
                                </div>
                            </div>
                            <?php if ($is_paid): ?>
                                <span class="st-badge badge-active">Paid</span>
                            <?php else: ?>
                                <span class="st-badge badge-draft">Draft</span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Detail Pane -->
            <div class="dash-card">
                <?php if ($selected_employee_data):
                    $already_paid = isset($_SESSION['salary_given'][$selected_employee_data['id']]);
                ?>
                    <div class="dash-card-head">
                        <div>
                            <h2 class="dash-card-title"><?php echo htmlspecialchars($selected_employee_data['name']); ?></h2>
                            <p style="margin: 2px 0 0; font-size: 13px; color: var(--text-s);">Bank: <?php echo htmlspecialchars($selected_employee_data['bank']); ?></p>
                        </div>
                        <?php if ($already_paid): ?>
                            <span class="st-badge badge-active" style="padding:6px 14px;font-size:12px;">✓ Salary Disbursed</span>
                        <?php endif; ?>
                    </div>

                    <div class="detail-body">
                        <form method="POST" id="salaryForm">
                            <input type="hidden" name="emp_id" value="<?php echo $selected_employee_data['id']; ?>">

                            <!-- ── Base Salary ── -->
                            <div class="salary-component">
                                <div class="form-section-divider"><span>Base Contracted Pay</span></div>
                                <div class="amt-box">
                                    <span>Monthly Base Salary</span>
                                    <span>Rs <?php echo number_format($selected_employee_data['base_salary']); ?></span>
                                </div>
                            </div>

                            <!-- ── Bonuses ── -->
                            <div class="salary-component">
                                <div class="form-section-divider"><span>Bonuses</span></div>

                                <?php if ($already_paid): ?>
                                    <div style="text-align:center;padding:16px;background:var(--surface);border-radius:var(--radius-sm);color:var(--text-s);font-size:13.5px;border:1.5px dashed var(--border);">
                                        No bonuses recorded for this period.
                                    </div>
                                <?php else: ?>
                                    <div id="bonuses-container" style="display:flex;flex-direction:column;gap:12px;">
                                        <!-- First bonus row with + button -->
                                        <div class="dyn-row">
                                            <select class="dash-input" name="bonus_names[]">
                                                <option value="" disabled selected>Select Bonus Type...</option>
                                                <?php foreach($salary_components['bonuses'] as $b): ?>
                                                    <option value="<?php echo htmlspecialchars($b['id']); ?>"><?php echo htmlspecialchars($b['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="input-rel">
                                                <span class="input-prefix">Rs</span>
                                                <input type="number" class="dash-input with-prefix green-text" name="bonus_amounts[]" placeholder="Amount" min="0">
                                            </div>
                                            <button type="button" class="btn-icon-sq btn-add-sq" onclick="addRow('bonuses-container', 'bonus')" title="Add Bonus">
                                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- ── Allowances ── -->
                            <div class="salary-component">
                                <div class="form-section-divider"><span>Allowances</span></div>

                                <?php if ($already_paid): ?>
                                    <?php if (empty($selected_employee_data['allowances_list'])): ?>
                                        <div style="text-align:center;padding:16px;background:var(--surface);border-radius:var(--radius-sm);color:var(--text-s);font-size:13.5px;border:1.5px dashed var(--border);">
                                            No allowances added for this period.
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($selected_employee_data['allowances_list'] as $al): ?>
                                            <div class="allowance-item">
                                                <span class="allowance-name"><?php echo htmlspecialchars($al['name']); ?></span>
                                                <span class="allowance-amt">+ Rs <?php echo number_format($al['amount']); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div id="allowances-container" style="display:flex;flex-direction:column;gap:12px;">
                                        <!-- First allowance row with + button -->
                                        <div class="dyn-row">
                                            <select class="dash-input" name="allowance_names[]">
                                                <option value="" disabled selected>Select Allowance...</option>
                                                <?php foreach($salary_components['allowances'] as $a): ?>
                                                    <option value="<?php echo htmlspecialchars($a['id']); ?>"><?php echo htmlspecialchars($a['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="input-rel">
                                                <span class="input-prefix">Rs</span>
                                                <input type="number" class="dash-input with-prefix green-text" name="allowance_amounts[]" placeholder="Amount" min="0">
                                            </div>
                                            <button type="button" class="btn-icon-sq btn-add-sq" onclick="addRow('allowances-container', 'allowance')" title="Add Allowance">
                                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- ── Deductions ── -->
                            <div class="salary-component">
                                <div class="form-section-divider"><span>Deductions & Adjustments</span></div>

                                <?php if ($already_paid): ?>
                                    <div style="text-align:center;padding:16px;background:var(--surface);border-radius:var(--radius-sm);color:var(--text-s);font-size:13.5px;border:1.5px dashed var(--border);">
                                        No deductions recorded for this period.
                                    </div>
                                <?php else: ?>
                                    <div id="deductions-container" style="display:flex;flex-direction:column;gap:12px;">
                                        <!-- First deduction row with + button -->
                                        <div class="dyn-row">
                                            <select class="dash-input" name="deduction_names[]">
                                                <option value="" disabled selected>Select Deduction...</option>
                                                <?php foreach($salary_components['deductions'] as $d): ?>
                                                    <option value="<?php echo htmlspecialchars($d['id']); ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="input-rel">
                                                <span class="input-prefix">Rs</span>
                                                <input type="number" class="dash-input with-prefix red-text" name="deduction_amounts[]" placeholder="Amount" min="0">
                                            </div>
                                            <button type="button" class="btn-icon-sq btn-add-sq" onclick="addRow('deductions-container', 'deduction')" title="Add Deduction">
                                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- ── Submit ── -->
                            <?php if ($already_paid): ?>
                                <div class="btn-locked-lg">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    Record Locked for Selected Month
                                </div>
                            <?php else: ?>
                                <button type="submit" name="process_salary" class="btn-primary-lg"
                                    onclick="return confirm('Process this salary? This will log the payment and lock the record for the selected month.');">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    Process & Finalize Salary
                                </button>
                            <?php endif; ?>

                        </form>
                    </div>

                <?php else: ?>
                    <div class="dash-empty">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <strong>No Employee Selected</strong>
                        <p>Select a team member from the directory on the left to process their monthly payroll.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

</div><!-- /.dashboard-container -->

<!-- Toast -->
<div class="pr-toast" id="prToast">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    <span>Salary Processed Successfully!</span>
</div>

<script>
    <?php if (isset($_SESSION['just_paid'])): unset($_SESSION['just_paid']); ?>
        document.addEventListener('DOMContentLoaded', function () {
            const toast = document.getElementById('prToast');
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 4000);
        });
    <?php endif; ?>

    const salaryComponents = <?php echo json_encode($salary_components); ?>;

    const rowConfig = {
        bonus: {
            selectName: 'bonus_names[]',
            inputName:  'bonus_amounts[]',
            inputClass: 'green-text',
            options: salaryComponents.bonuses.map(b => [b.id, b.name])
        },
        allowance: {
            selectName: 'allowance_names[]',
            inputName:  'allowance_amounts[]',
            inputClass: 'green-text',
            options: salaryComponents.allowances.map(a => [a.id, a.name])
        },
        deduction: {
            selectName: 'deduction_names[]',
            inputName:  'deduction_amounts[]',
            inputClass: 'red-text',
            options: salaryComponents.deductions.map(d => [d.id, d.name])
        }
    };

    function addRow(containerId, type) {
        const cfg       = rowConfig[type];
        const container = document.getElementById(containerId);

        // Build options HTML
        const opts = cfg.options.map(([val, label]) =>
            `<option value="${val}">${label}</option>`
        ).join('');

        const row = document.createElement('div');
        row.className = 'dyn-row';
        row.innerHTML = `
            <select class="dash-input" name="${cfg.selectName}">
                <option value="" disabled selected>Select...</option>
                ${opts}
            </select>
            <div class="input-rel">
                <span class="input-prefix">Rs</span>
                <input type="number" class="dash-input with-prefix ${cfg.inputClass}"
                       name="${cfg.inputName}" placeholder="Amount" min="0">
            </div>
            <button type="button" class="btn-icon-sq btn-rem-sq"
                    onclick="this.closest('.dyn-row').remove()" title="Remove">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
            </button>
        `;

        container.appendChild(row);
        // Focus the new select for quick UX
        row.querySelector('select').focus();
    }
</script>

<?php include_once "../includes/footer.php"; ?>