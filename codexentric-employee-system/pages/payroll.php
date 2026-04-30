<?php
    session_start();

    // ── 1. Authentication Check ──────────────────────────────────────────────
    if (!isset($_SESSION['email'])) {
        header("Location: login.php");
        exit();
    }

    // ── 2. Handle POST Request: "Process Salary" ─────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_salary'])) {
        $emp_id = (int)$_POST['emp_id'];
        $payroll_month = $_POST['payroll_month'];
        
        // Gather new earnings & dynamic allowances
        $bonus = (float)($_POST['bonus_amount'] ?? 0);
        $allowance_names = $_POST['allowance_names'] ?? [];
        $allowance_amounts = $_POST['allowance_amounts'] ?? [];
        
        // Gather dynamic deductions
        $deduction_names = $_POST['deduction_names'] ?? [];
        $deduction_amounts = $_POST['deduction_amounts'] ?? [];
        
        // --- PRODUCTION DATABASE LOGIC HERE ---
        // 1. Calculate Allowances Total: array_sum($allowance_amounts)
        // 2. Calculate Deductions Total: array_sum($deduction_amounts)
        // 3. Calculate Net = Base + Bonus + Total Allowances - Total Deductions
        // 4. INSERT into `salaries` table.
        // 5. INSERT into `salary_allowances` and `salary_deductions` tables using the arrays.
        // --------------------------------------

        $_SESSION['salary_given'][$emp_id] = true;
        $_SESSION['just_paid'] = true;
        
        header("Location: payroll.php?emp_id=" . $emp_id);
        exit();
    }

    // ── Handle Demo Reset ──
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_demo'])) {
        unset($_SESSION['salary_given']);
        header("Location: payroll.php");
        exit();
    }

    // ── 3. Fetch Data (Mocked for Demo, Replace with SQL) ────────────────────
    $salary_breakdown = [
        [
            "id" => 1, "name" => "Hammad Awan", "bank" => "HBL – 123456789", 
            "base_salary" => "85000", "tax_default" => "3000", "deduction_default" => "1200",
            "allowances_list" => [ ["name" => "Transport Allowance", "amount" => "5000"] ]
        ],
        [
            "id" => 2, "name" => "Shahzad Awan", "bank" => "Meezan – 987654321", 
            "base_salary" => "65000", "tax_default" => "1500", "deduction_default" => "0",
            "allowances_list" => [ ["name" => "Fuel Allowance", "amount" => "2000"] ]
        ],
        [
            "id" => 3, "name" => "Zeeshan Ali", "bank" => "Alfalah – 456789123", 
            "base_salary" => "55000", "tax_default" => "5000", "deduction_default" => "3000",
            "allowances_list" => [] 
        ],
    ];

    // ── 4. Catch Selected Employee from URL ──────────────────────────────────
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

    // ── Dashboard Variables & Includes ───────────────────────────────────────
    $current_page = "payroll"; 
    $extra_css    = "admin_dashboard";
    $title        = "Payroll Workspace - Admin";

    include_once "../includes/header.php";
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&display=swap');

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

/* Base Wrapper */
.dash { display: flex; flex-direction: column; gap: 28px; width: 100%; box-sizing: border-box; }

/* ── Header Area ── */
.dash-header-bar { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
.dash-header-bar h1 { font-family: 'Nunito', sans-serif; font-size: 24px; font-weight: 900; color: var(--text-h); margin: 0; }
.dash-header-sub { font-size: 13.5px; color: var(--text-m); margin: 4px 0 0 0; }

.pr-period-wrap { display: flex; align-items: center; gap: 12px; }
.pr-period-label { font-size: 11px; font-weight: 800; color: var(--text-s); text-transform: uppercase; letter-spacing: 0.8px; }
.pr-period-select {
    height: 38px; padding: 0 36px 0 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);
    font-family: 'Nunito', sans-serif; font-size: 14px; font-weight: 700; color: var(--text-h);
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

/* ── Section label ── */
.dash-section-label { font-size: 11px; font-weight: 800; color: var(--text-s); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.dash-section-label::after { content: ''; flex: 1; height: 1px; background: var(--border); }

/* ── Stats Grid ── */
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

.stat-value { font-family: 'Nunito', sans-serif; font-size: 30px; font-weight: 900; color: var(--text-h); line-height: 1; letter-spacing: -0.5px; margin: 0; }
.stat-footer { display: flex; align-items: center; justify-content: space-between; }
.stat-sub { font-size: 12px; color: var(--text-s); }
.stat-trend { font-size: 11.5px; font-weight: 700; padding: 3px 8px; border-radius: 20px; white-space: nowrap; }
.stat-trend.neutral { color: var(--text-m); background: var(--surface); }
.stat-trend.warn { background: var(--red-bg); color: var(--red); }
.stat-trend.good { background: var(--green-bg); color: var(--green); }

/* ── Split Layout Workspace ── */
.dash-split { display: grid; grid-template-columns: 340px 1fr; gap: 20px; align-items: start; }
@media (max-width: 980px) { .dash-split { grid-template-columns: 1fr; } }

/* Shared Card style for workspace */
.dash-card { background: var(--card); border: 1.5px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-xs); overflow: hidden; }
.dash-card-head { padding: 16px 20px; border-bottom: 1.5px solid var(--border); background: var(--surface); display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.dash-card-title { font-family: 'Nunito', sans-serif; font-size: 14.5px; font-weight: 800; color: var(--text-h); display: flex; align-items: center; gap: 8px; margin: 0; }
.dash-card-title .dot { width: 8px; height: 8px; border-radius: 50%; background: linear-gradient(135deg, var(--blue-dark), var(--blue)); }

/* Sidebar List */
.sidebar-list { height: calc(100vh - 350px); min-height: 400px; overflow-y: auto; padding: 6px 0; }
.emp-item { display: flex; gap: 12px; align-items: center; justify-content: space-between; padding: 12px 20px; border-bottom: 1px solid #F1F5F9; text-decoration: none; transition: background .12s; border-left: 3px solid transparent; }
.emp-item:last-child { border-bottom: none; }
.emp-item:hover { background: #F8FAFD; }
.emp-item.active { background: var(--blue-xlight); border-left-color: var(--blue); }

.emp-cell { display: flex; align-items: center; gap: 11px; }
.emp-av { width: 36px; height: 36px; border-radius: 9px; flex-shrink: 0; background: linear-gradient(135deg, var(--blue-dark), var(--blue)); color: #fff; font-family: 'Nunito', sans-serif; font-size: 14px; font-weight: 800; display: flex; align-items: center; justify-content: center; }
.emp-item.active .emp-av { box-shadow: 0 4px 10px rgba(30,111,217,0.25); }
.emp-name-txt { font-weight: 700; color: var(--text-h); font-size: 13.5px; }
.emp-role-txt { font-size: 12px; color: var(--text-s); margin-top: 1px; }

/* Status badges */
.st-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
.st-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.badge-active { background: var(--green-bg); color: var(--green); }
.badge-draft { background: var(--surface); color: var(--text-m); border: 1px solid var(--border); padding: 3px 9px; }

/* Detail Pane */
.detail-body { padding: 32px; }
.salary-component { margin-bottom: 32px; }
.salary-component > label { font-size: 12.5px; font-weight: 800; color: var(--text-m); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 12px; }

.amt-box { background: var(--surface); border: 1.5px solid var(--border); border-radius: var(--radius-sm); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; font-family: 'Nunito', sans-serif; font-size: 16px; font-weight: 800; color: var(--text-h); }
.allowance-item { background: var(--surface); border: 1.5px solid var(--border); border-radius: var(--radius-sm); padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.allowance-name { font-weight: 700; color: var(--text-b); font-size: 14.5px; }
.allowance-amt { font-family: 'Nunito', sans-serif; font-weight: 800; color: var(--green); font-size: 15px; }
.deduction-amt { font-family: 'Nunito', sans-serif; font-weight: 800; color: var(--red); font-size: 15px; }

/* Form Inputs */
.input-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; background: var(--surface); border: 1.5px solid var(--border); padding: 20px; border-radius: var(--radius-sm); }
.input-wrap label { font-size: 12px; color: var(--text-s); font-weight: 700; text-transform: none; letter-spacing: normal; margin-bottom: 6px; display: block;}
.input-rel { position: relative; }
.input-prefix { position: absolute; left: 14px; top: 12px; font-weight: 700; color: var(--text-m); font-size: 14px; pointer-events: none; }
.dash-input {
  width: 100%; height: 44px; padding: 0 14px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);
  font-family: 'Nunito', sans-serif; font-size: 15px; font-weight: 700; color: var(--text-h);
  background: #fff; box-sizing: border-box; transition: border-color .2s, box-shadow .2s;
}
.dash-input.with-prefix { padding-left: 40px; font-weight: 800; }
.dash-input.red-text { color: var(--red); }
.dash-input:focus { border-color: var(--blue); outline: none; box-shadow: 0 0 0 3px var(--blue-light); }
.dash-input[readonly], .dash-input:disabled { background: transparent; border-style: dashed; color: var(--text-m); cursor: not-allowed; }

select.dash-input { cursor: pointer; appearance: none; -webkit-appearance: none; background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' stroke='%2394A3B8' stroke-width='2' stroke-linecap='round' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1l5 5 5-5'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; }

/* Dynamic Add/Remove Buttons */
.btn-icon-sq { width: 44px; height: 44px; border-radius: var(--radius-sm); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; flex-shrink: 0; }
.btn-add-sq { background: var(--blue-light); color: var(--blue); }
.btn-add-sq:hover { background: var(--blue); color: #fff; box-shadow: 0 4px 10px rgba(30,111,217,0.25); transform: translateY(-1px);}
.btn-rem-sq { background: var(--red-bg); color: var(--red); }
.btn-rem-sq:hover { background: var(--red); color: #fff; box-shadow: 0 4px 10px rgba(220,38,38,0.25); transform: translateY(-1px);}

/* Action Buttons */
.btn-primary-lg { width: 100%; height: 50px; margin-top: 20px; background: var(--green); color: #fff; border: none; border-radius: var(--radius-sm); font-size: 16px; font-weight: 800; font-family: 'Nunito', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background .2s, transform .1s; box-shadow: var(--shadow-sm); }
.btn-primary-lg:hover { background: #047857; transform: translateY(-1px); box-shadow: var(--shadow-md); }
.btn-locked-lg { width: 100%; height: 50px; margin-top: 20px; background: var(--surface); color: var(--green); border: 2px solid var(--green-bg); border-radius: var(--radius-sm); font-size: 15px; font-weight: 800; font-family: 'Nunito', sans-serif; cursor: default; display: flex; align-items: center; justify-content: center; gap: 8px; }

/* Empty State */
.dash-empty { text-align: center; padding: 80px 24px; color: var(--text-s); }
.dash-empty svg { opacity: .35; margin-bottom: 12px; }
.dash-empty strong { display: block; font-size: 16px; color: var(--text-h); margin-bottom: 4px; font-family: 'Nunito', sans-serif; font-weight: 800; }
.dash-empty p { font-size: 13.5px; max-width: 300px; margin: 0 auto; line-height: 1.5; }

/* Toast */
.pr-toast { position: fixed; bottom: 32px; right: 32px; background: var(--text-h); color: #fff; padding: 16px 24px; border-radius: var(--radius-sm); font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 12px; box-shadow: var(--shadow-md); transform: translateY(120px); opacity: 0; transition: all .4s cubic-bezier(0.175, 0.885, 0.32, 1.275); z-index: 9999; }
.pr-toast.show { transform: translateY(0); opacity: 1; }

/* Animations */
@keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
.dash-header-bar { animation: fadeUp .35s ease both; }
.dash-stats      { animation: fadeUp .35s .1s ease both; }
.dash-split      { animation: fadeUp .35s .2s ease both; }
</style>

<div class="dash">

    <!-- ══ Header Area ══ -->
    <div class="dash-header-bar">
        <div>
            <h1>Payroll Workspace</h1>
            <p class="dash-header-sub">Process monthly salaries and manage allowances.</p>
        </div>
        <div class="pr-period-wrap">
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

    <!-- ══ Top Stats ══ -->
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

    <!-- ══ Workspace (Split Layout) ══ -->
    <div>
        <div class="dash-section-label">Payroll Processing</div>
        
        <div class="dash-split">
            
            <!-- Sidebar: Employee List -->
            <div class="dash-card">
                <div class="dash-card-head">
                    <h2 class="dash-card-title"><span class="dot"></span>Select Employee</h2>
                </div>
                <div class="sidebar-list">
                    <?php foreach ($salary_breakdown as $row): 
                        $is_active = ($selected_emp_id == $row['id']) ? 'active' : '';
                        $is_paid = isset($_SESSION['salary_given'][$row['id']]);
                    ?>
                        <a href="payroll.php?emp_id=<?php echo $row['id']; ?>" class="emp-item <?php echo $is_active; ?>">
                            <div class="emp-cell">
                                <div class="emp-av" aria-hidden="true"><?php echo strtoupper(substr($row['name'], 0, 1)); ?></div>
                                <div>
                                    <div class="emp-name-txt"><?php echo htmlspecialchars($row['name']); ?></div>
                                    <div class="emp-role-txt"><?php echo htmlspecialchars($row['bank']); ?></div>
                                </div>
                            </div>
                            <?php if($is_paid): ?>
                                <span class="st-badge badge-active" aria-label="Status: Paid">Paid</span>
                            <?php else: ?>
                                <span class="st-badge badge-draft" aria-label="Status: Draft">Draft</span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Detail Pane: Calculations -->
            <div class="dash-card">
                <?php if ($selected_employee_data): 
                    $already_paid = isset($_SESSION['salary_given'][$selected_employee_data['id']]);
                ?>
                    <div class="dash-card-head">
                        <div>
                            <h2 class="dash-card-title"><?php echo htmlspecialchars($selected_employee_data['name']); ?></h2>
                            <p class="dash-card-sub" style="margin-top:2px;">Bank: <?php echo htmlspecialchars($selected_employee_data['bank']); ?></p>
                        </div>
                        <?php if($already_paid): ?>
                            <span class="st-badge badge-active" style="padding: 6px 14px; font-size:12px;">✓ Salary Disbursed</span>
                        <?php endif; ?>
                    </div>

                    <div class="detail-body">
                        <form method="POST" id="salaryForm">
                            <input type="hidden" name="emp_id" value="<?php echo $selected_employee_data['id']; ?>">

                            <!-- Base Salary -->
                            <div class="salary-component">
                                <label>Base Contracted Pay</label>
                                <div class="amt-box">
                                    <span>Monthly Base Salary</span>
                                    <span>Rs <?php echo number_format($selected_employee_data['base_salary']); ?></span>
                                </div>
                            </div>

                            <!-- Earnings (Bonus & Allowances) -->
                            <div class="salary-component">
                                <label>Earnings & Allowances</label>
                                
                                <!-- Bonus Box -->
                                <div class="input-grid" style="margin-bottom: 20px;">
                                    <div class="input-wrap">
                                        <label>Performance Bonus</label>
                                        <div class="input-rel">
                                            <span class="input-prefix">Rs</span>
                                            <input type="number" class="dash-input with-prefix" style="color: var(--green);" name="bonus_amount" 
                                                   value="0" 
                                                   <?php echo $already_paid ? 'readonly' : ''; ?>>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; color: var(--text-s); font-size: 13px;">
                                        Add a one-time performance or target bonus for this month.
                                    </div>
                                </div>

                                <!-- Dynamic Allowances -->
                                <div>
                                    <label style="font-size: 12px; color: var(--text-s); font-weight: 700; text-transform: none; letter-spacing: normal; margin-bottom: 8px;">Select Allowances</label>
                                    
                                    <div id="allowances-container" style="display: flex; flex-direction: column; gap: 12px;">
                                        
                                        <?php if ($already_paid): ?>
                                            <!-- Read-only View for locked records -->
                                            <?php if (empty($selected_employee_data['allowances_list'])): ?>
                                                <div style="text-align: center; padding: 16px; background: var(--surface); border-radius: var(--radius-sm); color: var(--text-s); font-size: 13.5px; border: 1.5px dashed var(--border);">
                                                    No allowances added for this period.
                                                </div>
                                            <?php else: ?>
                                                <?php foreach($selected_employee_data['allowances_list'] as $allowance): ?>
                                                    <div class="allowance-item">
                                                        <span class="allowance-name"><?php echo htmlspecialchars($allowance['name']); ?></span>
                                                        <span class="allowance-amt">+ Rs <?php echo htmlspecialchars($allowance['amount']); ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>

                                        <?php else: ?>
                                            <!-- Editable View: load existing ones if any -->
                                            <?php if (!empty($selected_employee_data['allowances_list'])): ?>
                                                <?php foreach($selected_employee_data['allowances_list'] as $allowance): ?>
                                                    <div class="allowance-row" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; align-items: center;">
                                                        <select class="dash-input" name="allowance_names[]" required>
                                                            <option value="<?php echo htmlspecialchars($allowance['name']); ?>" selected><?php echo htmlspecialchars($allowance['name']); ?></option>
                                                            <option value="House Rent Allowance">House Rent Allowance</option>
                                                            <option value="Medical Allowance">Medical Allowance</option>
                                                            <option value="Transport Allowance">Transport Allowance</option>
                                                            <option value="Fuel Allowance">Fuel Allowance</option>
                                                            <option value="Other">Other</option>
                                                        </select>
                                                        <div class="input-rel">
                                                            <span class="input-prefix" style="top: 12px; font-size: 14px;">Rs</span>
                                                            <input type="number" class="dash-input with-prefix" style="color: var(--green);" name="allowance_amounts[]" value="<?php echo htmlspecialchars(str_replace(',', '', $allowance['amount'])); ?>" required>
                                                        </div>
                                                        <button type="button" class="btn-icon-sq btn-rem-sq" onclick="this.parentElement.remove()" title="Remove Allowance">
                                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                        </button>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            
                                            <!-- Dynamic Add Row -->
                                            <div class="allowance-row" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; align-items: center;">
                                                <select class="dash-input" name="allowance_names[]">
                                                    <option value="" disabled selected>Select Allowance Category...</option>
                                                    <option value="House Rent Allowance">House Rent Allowance</option>
                                                    <option value="Medical Allowance">Medical Allowance</option>
                                                    <option value="Transport Allowance">Transport Allowance</option>
                                                    <option value="Fuel Allowance">Fuel Allowance</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                                <div class="input-rel">
                                                    <span class="input-prefix" style="top: 12px; font-size: 14px;">Rs</span>
                                                    <input type="number" class="dash-input with-prefix" style="color: var(--green);" name="allowance_amounts[]" placeholder="Amount">
                                                </div>
                                                <button type="button" class="btn-icon-sq btn-add-sq" onclick="addAllowanceField()" title="Add Allowance">
                                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                </button>
                                            </div>
                                        <?php endif; ?>

                                    </div>
                                </div>
                            </div>

                            <!-- Deductions -->
                            <div class="salary-component">
                                <label>Deductions & Adjustments</label>
                                
                                <div id="deductions-container" style="display: flex; flex-direction: column; gap: 12px;">
                                    <?php if ($already_paid): ?>
                                        <!-- Read-only View for Deductions -->
                                        <div class="allowance-item">
                                            <span class="allowance-name">Income Tax</span>
                                            <span class="deduction-amt">- Rs <?php echo htmlspecialchars($selected_employee_data['tax_default']); ?></span>
                                        </div>
                                        <div class="allowance-item">
                                            <span class="allowance-name">Other Deductions</span>
                                            <span class="deduction-amt">- Rs <?php echo htmlspecialchars($selected_employee_data['deduction_default']); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <!-- Default Income Tax Row (Pre-filled) -->
                                        <div class="deduction-row" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; align-items: center;">
                                            <select class="dash-input" name="deduction_names[]" required>
                                                <option value="Income Tax" selected>Income Tax</option>
                                                <option value="Provident Fund">Provident Fund</option>
                                                <option value="Unpaid Leave">Unpaid Leave</option>
                                                <option value="Loan Repayment">Loan Repayment</option>
                                                <option value="Other">Other</option>
                                            </select>
                                            <div class="input-rel">
                                                <span class="input-prefix" style="top: 12px; font-size: 14px;">Rs</span>
                                                <input type="number" class="dash-input with-prefix red-text" name="deduction_amounts[]" value="<?php echo htmlspecialchars($selected_employee_data['tax_default']); ?>" required>
                                            </div>
                                            <button type="button" class="btn-icon-sq" style="background: transparent; color: transparent; pointer-events: none;"></button> <!-- Spacer to keep alignment -->
                                        </div>

                                        <!-- Default Other Deduction Row (Pre-filled if value > 0) -->
                                        <?php if ((int)$selected_employee_data['deduction_default'] > 0): ?>
                                            <div class="deduction-row" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; align-items: center;">
                                                <select class="dash-input" name="deduction_names[]" required>
                                                    <option value="Other" selected>Other Deductions</option>
                                                    <option value="Income Tax">Income Tax</option>
                                                    <option value="Provident Fund">Provident Fund</option>
                                                    <option value="Unpaid Leave">Unpaid Leave</option>
                                                    <option value="Loan Repayment">Loan Repayment</option>
                                                </select>
                                                <div class="input-rel">
                                                    <span class="input-prefix" style="top: 12px; font-size: 14px;">Rs</span>
                                                    <input type="number" class="dash-input with-prefix red-text" name="deduction_amounts[]" value="<?php echo htmlspecialchars($selected_employee_data['deduction_default']); ?>" required>
                                                </div>
                                                <button type="button" class="btn-icon-sq btn-rem-sq" onclick="this.parentElement.remove()" title="Remove Deduction">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Dynamic Add Row for Deductions -->
                                        <div class="deduction-row" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; align-items: center;">
                                            <select class="dash-input" name="deduction_names[]">
                                                <option value="" disabled selected>Select Deduction Category...</option>
                                                <option value="Income Tax">Income Tax</option>
                                                <option value="Provident Fund">Provident Fund</option>
                                                <option value="Unpaid Leave">Unpaid Leave</option>
                                                <option value="Loan Repayment">Loan Repayment</option>
                                                <option value="Other">Other</option>
                                            </select>
                                            <div class="input-rel">
                                                <span class="input-prefix" style="top: 12px; font-size: 14px;">Rs</span>
                                                <input type="number" class="dash-input with-prefix red-text" name="deduction_amounts[]" placeholder="Amount">
                                            </div>
                                            <button type="button" class="btn-icon-sq btn-add-sq" onclick="addDeductionField()" title="Add Deduction">
                                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Submit Action -->
                            <?php if($already_paid): ?>
                                <div class="btn-locked-lg">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    Record Locked for Selected Month
                                </div>
                            <?php else: ?>
                                <button type="submit" name="process_salary" class="btn-primary-lg" onclick="return confirm('Process this salary? This will log the payment and lock the record for the selected month.');">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    Process & Finalize Salary
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- Empty State -->
                    <div class="dash-empty">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <strong>No Employee Selected</strong>
                        <p>Select a team member from the directory on the left to process their monthly payroll.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

</div><!-- /.dash -->

<!-- ── Toast Notification ── -->
<div class="pr-toast" id="prToast">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    <span>Salary Processed Successfully!</span>
</div>

<script>
    <?php if (isset($_SESSION['just_paid'])): unset($_SESSION['just_paid']); ?>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('prToast');
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 4000);
        });
    <?php endif; ?>

    // JavaScript to handle dynamic allowance rows
    function addAllowanceField() {
        const container = document.getElementById('allowances-container');
        const row = document.createElement('div');
        row.className = 'allowance-row';
        row.style.display = 'grid';
        row.style.gridTemplateColumns = '1fr 1fr auto';
        row.style.gap = '12px';
        row.style.alignItems = 'center';
        
        row.innerHTML = `
            <select class="dash-input" name="allowance_names[]" required>
                <option value="" disabled selected>Select Allowance Category...</option>
                <option value="House Rent Allowance">House Rent Allowance</option>
                <option value="Medical Allowance">Medical Allowance</option>
                <option value="Transport Allowance">Transport Allowance</option>
                <option value="Fuel Allowance">Fuel Allowance</option>
                <option value="Other">Other</option>
            </select>
            <div class="input-rel">
                <span class="input-prefix" style="top: 12px; font-size: 14px;">Rs</span>
                <input type="number" class="dash-input with-prefix" style="color: var(--green);" name="allowance_amounts[]" placeholder="Amount" required>
            </div>
            <button type="button" class="btn-icon-sq btn-rem-sq" onclick="this.parentElement.remove()" title="Remove Allowance">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
        `;
        
        // Append right before the add button row
        const addButtonRow = container.lastElementChild;
        container.insertBefore(row, addButtonRow);
    }

    // JavaScript to handle dynamic deduction rows
    function addDeductionField() {
        const container = document.getElementById('deductions-container');
        const row = document.createElement('div');
        row.className = 'deduction-row';
        row.style.display = 'grid';
        row.style.gridTemplateColumns = '1fr 1fr auto';
        row.style.gap = '12px';
        row.style.alignItems = 'center';
        
        row.innerHTML = `
            <select class="dash-input" name="deduction_names[]" required>
                <option value="" disabled selected>Select Deduction Category...</option>
                <option value="Income Tax">Income Tax</option>
                <option value="Provident Fund">Provident Fund</option>
                <option value="Unpaid Leave">Unpaid Leave</option>
                <option value="Loan Repayment">Loan Repayment</option>
                <option value="Other">Other</option>
            </select>
            <div class="input-rel">
                <span class="input-prefix" style="top: 12px; font-size: 14px;">Rs</span>
                <input type="number" class="dash-input with-prefix red-text" name="deduction_amounts[]" placeholder="Amount" required>
            </div>
            <button type="button" class="btn-icon-sq btn-rem-sq" onclick="this.parentElement.remove()" title="Remove Deduction">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
        `;
        
        // Append right before the add button row
        const addButtonRow = container.lastElementChild;
        container.insertBefore(row, addButtonRow);
    }
</script>

<?php include_once "../includes/footer.php"; ?>