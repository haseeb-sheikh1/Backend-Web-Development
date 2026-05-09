<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
$user_role = "admin";
$current_page = "add_employee";
$extra_css = "add_employee"; 
$title = "Add Employee - Admin";

include_once "../includes/header.php";
include_once "../pages/database.php";
include_once "../pages/Employee.php";

$db = new Database();
$connection = $db->getConnection();
$employee = new Employee($connection);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_employee'])) {
    $employee->createEmployee();
}
?>

  <div class="dashboard-container">
    
    <?php if (isset($_SESSION['success_msg']) || isset($employee->errors['success'])): 
        $msg = isset($_SESSION['success_msg']) ? $_SESSION['success_msg'] : $employee->errors['success'];
        unset($_SESSION['success_msg']);
    ?>
        <div class="feedback-card-container">
            <div class="feedback-card success">
                <div class="feedback-icon-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <h2>Employee Registered</h2>
                <p><?php echo htmlspecialchars($msg); ?></p>
                <div class="feedback-btn-group">
                    <a href="employees_list.php" class="feedback-btn outline">
                        Back to Employee List
                    </a>
                    <a href="add_employee.php" class="feedback-btn success">
                        Add Another Employee
                    </a>
                </div>
            </div>
        </div>
        <style>
        .feedback-card-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 200px);
            background: #f8fafc;
            padding: 24px;
        }
        .feedback-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 40px 32px;
            width: 100%;
            max-width: 440px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            animation: scaleUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }
        @keyframes scaleUp {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .feedback-icon-wrapper {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px auto;
            background: #e8f5e9;
            color: #186D55;
        }
        .feedback-icon-wrapper svg {
            width: 28px;
            height: 28px;
        }
        .feedback-card h2 {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }
        .feedback-card p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .feedback-btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .feedback-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 44px;
            padding: 0 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            flex: 1;
        }
        .feedback-btn.success {
            background: #186D55;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(24, 109, 85, 0.2);
        }
        .feedback-btn.success:hover {
            background: #125542;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(24, 109, 85, 0.25);
        }
        .feedback-btn.outline {
            background: #ffffff;
            color: #64748b;
            border: 1px solid #cbd5e1;
        }
        .feedback-btn.outline:hover {
            background: #f8fafc;
            color: #334155;
            border-color: #94a3b8;
        }
        </style>
    <?php else: ?>

        <?php if (isset($employee->errors['general'])): ?>
          <div class="alert alert-danger">
              <p>• <?php echo htmlspecialchars($employee->errors['general']); ?></p>
          </div>
        <?php endif; ?>

        <form action="add_employee.php" method="POST" id="create_employee_form" autocomplete="off" novalidate>
      <div class="dashboard-grid">

        <!-- Personal Information -->
        <div class="widget-card">
          <div class="widget-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Personal Details
          </div>
          <div class="widget-body">
            <div class="form-grid">
              <div class="form-group">
                <label>First Name <span class="req">*</span></label>
                <input type="text" name="first_name" class="form-input" placeholder="e.g. John" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                <?php if (isset($employee->errors['first_name'])): ?>
                  <small class="error-msg"><?php echo htmlspecialchars($employee->errors['first_name']); ?></small>
                <?php endif; ?>
              </div>
              <div class="form-group">
                <label>Last Name <span class="req">*</span></label>
                <input type="text" name="last_name" class="form-input" placeholder="e.g. Doe" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                <?php if (isset($employee->errors['last_name'])): ?>
                  <small class="error-msg"><?php echo htmlspecialchars($employee->errors['last_name']); ?></small>
                <?php endif; ?>
              </div>
              <div class="form-group span-full">
                <label>Residential Address</label>
                <input type="text" name="home_address" class="form-input" placeholder="Street, City, Country" value="<?php echo isset($_POST['home_address']) ? htmlspecialchars($_POST['home_address']) : ''; ?>" autocomplete="off">
              </div>
            </div>
          </div>
        </div>

        <!-- Employment Information -->
        <div class="widget-card">
          <div class="widget-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            Employment Info
          </div>
          <div class="widget-body">
            <div class="form-grid">
              <div class="form-group">
                <label>Department</label>
                <select name="department" class="form-select">
                  <option value="" disabled <?php echo empty($_POST['department']) ? 'selected' : ''; ?>>Select Department</option>
                  <option value="Software Engineering" <?php echo (isset($_POST['department']) && $_POST['department'] === 'Software Engineering') ? 'selected' : ''; ?>>Software Engineering</option>
                  <option value="Design" <?php echo (isset($_POST['department']) && $_POST['department'] === 'Design') ? 'selected' : ''; ?>>Design</option>
                </select>
                <?php if (isset($employee->errors['department'])): ?>
                  <small class="error-msg"><?php echo htmlspecialchars($employee->errors['department']); ?></small>
                <?php endif; ?>
              </div>
              <div class="form-group">
                <label>Position</label>
                <select name="position_title" class="form-select">
                  <option value="" disabled <?php echo empty($_POST['position_title']) ? 'selected' : ''; ?>>Select Position</option>
                  <option value="Backend Developer" <?php echo (isset($_POST['position_title']) && $_POST['position_title'] === 'Backend Developer') ? 'selected' : ''; ?>>Backend Developer</option>
                  <option value="Frontend Developer" <?php echo (isset($_POST['position_title']) && $_POST['position_title'] === 'Frontend Developer') ? 'selected' : ''; ?>>Frontend Developer</option>
                  <option value="Backend intern" <?php echo (isset($_POST['position_title']) && $_POST['position_title'] === 'Backend intern') ? 'selected' : ''; ?>>Backend intern</option>
                  <option value="Senior Backend Developer" <?php echo (isset($_POST['position_title']) && $_POST['position_title'] === 'Senior Backend Developer') ? 'selected' : ''; ?>>Senior Backend Developer</option>
                </select>
                <?php if (isset($employee->errors['position_title'])): ?>
                  <small class="error-msg"><?php echo htmlspecialchars($employee->errors['position_title']); ?></small>
                <?php endif; ?>
              </div>
              <div class="form-group">
                <label>Joining Date</label>
                <input type="date" name="date_of_joining" class="form-input" value="<?php echo isset($_POST['date_of_joining']) ? htmlspecialchars($_POST['date_of_joining']) : ''; ?>">
              </div>
              <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-select">
                  <option value="Active" <?php echo (!isset($_POST['status']) || $_POST['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                  <option value="De-activated" <?php echo (isset($_POST['status']) && $_POST['status'] === 'De-activated') ? 'selected' : ''; ?>>De-activated</option>
                </select>
              </div>
              <div class="form-group">
                <label>Employment Type</label>
                <select name="employment_type" class="form-select">
                  <option value="Full-time" <?php echo (!isset($_POST['employment_type']) || $_POST['employment_type'] === 'Full-time') ? 'selected' : ''; ?>>Full-time</option>
                  <option value="Part-time" <?php echo (isset($_POST['employment_type']) && $_POST['employment_type'] === 'Part-time') ? 'selected' : ''; ?>>Part-time</option>
                  <option value="Contract" <?php echo (isset($_POST['employment_type']) && $_POST['employment_type'] === 'Contract') ? 'selected' : ''; ?>>Contract</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Account Security -->
        <div class="widget-card">
          <div class="widget-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            System Access
          </div>
          <div class="widget-body">
            <div class="form-grid">
              <div class="form-group span-full">
                <label>Work Email <span class="req">*</span></label>
                <input type="email" name="email" class="form-input" placeholder="email@company.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required autocomplete="off">
                <?php if (isset($employee->errors['email'])): ?>
                  <small class="error-msg"><?php echo htmlspecialchars($employee->errors['email']); ?></small>
                <?php endif; ?>
              </div>
              <div class="form-group span-full">
                <label>Password <span class="req">*</span></label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required autocomplete="new-password">
                <?php if (isset($employee->errors['password'])): ?>
                  <small class="error-msg"><?php echo htmlspecialchars($employee->errors['password']); ?></small>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Compensation -->
        <div class="widget-card">
          <div class="widget-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Financial Info
          </div>
          <div class="widget-body">
            <div class="form-grid">
              <div class="form-group">
                <label>Base Salary (Rs)</label>
                <input type="number" name="base_salary" class="form-input" placeholder="0.00" value="<?php echo isset($_POST['base_salary']) ? htmlspecialchars($_POST['base_salary']) : ''; ?>">
                <?php if (isset($employee->errors['base_salary'])): ?>
                  <small class="error-msg"><?php echo htmlspecialchars($employee->errors['base_salary']); ?></small>
                <?php endif; ?>
              </div>
              <div class="form-group">
                <label>Bank Name</label>
                <input type="text" name="bank_name" class="form-input" placeholder="Bank name" value="<?php echo isset($_POST['bank_name']) ? htmlspecialchars($_POST['bank_name']) : ''; ?>">
              </div>
              <div class="form-group">
                <label>Account Number</label>
                <input type="text" name="bank_account_number" class="form-input" placeholder="IBAN / Account #" value="<?php echo isset($_POST['bank_account_number']) ? htmlspecialchars($_POST['bank_account_number']) : ''; ?>">
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="form-footer">
        <button type="submit" name="create_employee" class="btn-brand">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>
          Register New Employee
        </button>
      </div>
    </form>
    <?php endif; ?>
  </div>
<?php include_once "../includes/footer.php"; ?>
