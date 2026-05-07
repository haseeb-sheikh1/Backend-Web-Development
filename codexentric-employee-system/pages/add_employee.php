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
    
    <!-- Welcome Header -->
    <div class="dash-header">
      <div class="dash-header-left">
        <h1>Add New Employee</h1>
        <p class="dash-subtitle">Register a new team member to the system</p>
      </div>
      <div class="header-actions">
        <a href="administrator_dashboard.php" class="btn-minimal">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          Back to Dashboard
        </a>
      </div>
    </div>

    <?php if (isset($employee->errors['general'])): ?>
      <div class="alert alert-danger">
          <p>• <?php echo htmlspecialchars($employee->errors['general']); ?></p>
      </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success_msg'])): ?>
      <div class="alert alert-success">
          <p>• <?php echo htmlspecialchars($_SESSION['success_msg']); unset($_SESSION['success_msg']); ?></p>
      </div>
    <?php endif; ?>

    <form action="add_employee.php" method="POST" id="create_employee_form" novalidate>
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
                <input type="text" name="home_address" class="form-input" placeholder="Street, City, Country" value="<?php echo isset($_POST['home_address']) ? htmlspecialchars($_POST['home_address']) : ''; ?>">
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
                  <option value="Quality Assurance" <?php echo (isset($_POST['department']) && $_POST['department'] === 'Quality Assurance') ? 'selected' : ''; ?>>Quality Assurance</option>
                  <option value="Human Resources" <?php echo (isset($_POST['department']) && $_POST['department'] === 'Human Resources') ? 'selected' : ''; ?>>Human Resources</option>
                </select>
                <?php if (isset($employee->errors['department'])): ?>
                  <small class="error-msg"><?php echo htmlspecialchars($employee->errors['department']); ?></small>
                <?php endif; ?>
              </div>
              <div class="form-group">
                <label>Position</label>
                <input type="text" name="position_title" class="form-input" placeholder="e.g. Developer" value="<?php echo isset($_POST['position_title']) ? htmlspecialchars($_POST['position_title']) : ''; ?>">
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
                  <option value="Active" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                  <option value="Onboarding" <?php echo (isset($_POST['status']) && $_POST['status'] === 'Onboarding') ? 'selected' : ''; ?>>Onboarding</option>
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
                <input type="email" name="email" class="form-input" placeholder="email@company.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                <?php if (isset($employee->errors['email'])): ?>
                  <small class="error-msg"><?php echo htmlspecialchars($employee->errors['email']); ?></small>
                <?php endif; ?>
              </div>
              <div class="form-group span-full">
                <label>Password <span class="req">*</span></label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
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
                <label>Allowances (Rs)</label>
                <input type="number" name="allowances" class="form-input" placeholder="0.00" value="<?php echo isset($_POST['allowances']) ? htmlspecialchars($_POST['allowances']) : '0'; ?>">
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
  </div>
<?php include_once "../includes/footer.php"; ?>
