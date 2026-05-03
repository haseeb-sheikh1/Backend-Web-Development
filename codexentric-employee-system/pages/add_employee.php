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
    <div class="dash-welcome">
      <div class="dash-welcome-text">
        <h1>Add New Employee</h1>
        <p>Fill in the employee information to create a new profile.</p>
      </div>
      <div class="dash-welcome-actions">
        <a href="administrator_dashboard.php" class="btn-wh" aria-label="Return to admin dashboard">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          Back to Dashboard
        </a>
        <button type="submit" form="create_employee_form" name="create_employee" class="btn-wh primary-wh">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Create Employee
        </button>
      </div>
    </div>

    <?php if (!empty($employee->errors)): ?>
      <div class="alert alert-danger">
          <?php foreach ($employee->errors as $error) { echo "<p>• " . htmlspecialchars($error) . "</p>"; } ?>
      </div>
    <?php endif; ?>
    <?php if (isset($employee->success_message)): ?>
      <div class="alert alert-success">
          <p>• <?php echo htmlspecialchars($employee->success_message); ?></p>
      </div>
    <?php endif; ?>

    <form action="add_employee.php" method="POST" id="create_employee_form" novalidate>
      <div class="emp-info-grid">

        <!-- Personal Information -->
        <div class="emp-card">
          <div class="emp-card-head">
            <div class="emp-card-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
            <h3 class="emp-card-title"><span class="dot"></span>Personal Information</h3>
          </div>
          <div class="emp-card-body">
            <div class="form-group-row">
              <div class="form-group">
                <label>First Name <span class="req">*</span></label>
                <input type="text" name="first_name" class="form-input" placeholder="John" required>
              </div>
              <div class="form-group">
                <label>Last Name <span class="req">*</span></label>
                <input type="text" name="last_name" class="form-input" placeholder="Doe" required>
              </div>
            </div>
            <div class="form-group">
              <label>Address</label>
              <input type="text" name="home_address" class="form-input" placeholder="123 Main Street, City, Country">
            </div>
          </div>
        </div>

        <!-- Employment Information -->
        <div class="emp-card">
          <div class="emp-card-head">
            <div class="emp-card-icon" style="background:#F3E8FF; color:#7E22CE;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></div>
            <h3 class="emp-card-title"><span class="dot" style="background:linear-gradient(135deg,#7E22CE,#9333EA)"></span>Employment Information</h3>
          </div>
          <div class="emp-card-body">
            <div class="form-group-row">
              <div class="form-group">
                <label>Department <span class="req">*</span></label>
                <select name="department" class="form-select" required>
                  <option value="" disabled selected>Select Department</option>
                  <option value="Software Engineering">Software Engineering</option>
                  <option value="Design">Design</option>
                  <option value="Quality Assurance">Quality Assurance</option>
                  <option value="Human Resources">Human Resources</option>
                </select>
              </div>
              <div class="form-group">
                <label>Position Title <span class="req">*</span></label>
                <input type="text" name="position_title" class="form-input" placeholder="e.g. Senior Developer" required>
              </div>
            </div>
            <div class="form-group-row">
              <div class="form-group">
                <label>Date of Joining <span class="req">*</span></label>
                <input type="date" name="date_of_joining" class="form-input" required>
              </div>
              <div class="form-group">
                <label>Status <span class="req">*</span></label>
                <select name="status" class="form-select" required>
                  <option value="Active">Active</option>
                  <option value="Onboarding">Onboarding</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Account Security -->
        <div class="emp-card">
          <div class="emp-card-head">
            <div class="emp-card-icon" style="background:var(--amber-bg); color:var(--amber);"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
            <h3 class="emp-card-title"><span class="dot" style="background:linear-gradient(135deg,#D97706,#F59E0B)"></span>Account Security</h3>
          </div>
          <div class="emp-card-body">
            <div class="form-group">
              <label>Email Address <span class="req">*</span></label>
              <input type="email" name="email" class="form-input" placeholder="john.doe@codexentric.com" required>
            </div>
            <div class="form-group">
              <label>Password <span class="req">*</span></label>
              <input type="password" name="password" class="form-input" placeholder="Enter a secure password" required>
            </div>
          </div>
        </div>

        <!-- Compensation -->
        <div class="emp-card">
          <div class="emp-card-head">
            <div class="emp-card-icon" style="background:var(--green-bg); color:var(--green);"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
            <h3 class="emp-card-title"><span class="dot" style="background:linear-gradient(135deg,#059669,#10b981)"></span>Compensation & Banking</h3>
          </div>
          <div class="emp-card-body">
            <div class="form-group-row">
              <div class="form-group">
                <label>Base Salary (Rs) <span class="req">*</span></label>
                <input type="number" name="base_salary" class="form-input" placeholder="50000" min="0" required>
              </div>
              <div class="form-group">
                <label>Allowances (Rs)</label>
                <input type="number" name="allowances" class="form-input" placeholder="5000" min="0" value="0">
              </div>
            </div>
            <div class="form-group-row">
              <div class="form-group">
                <label>Bank Name</label>
                <input type="text" name="bank_name" class="form-input" placeholder="HBL, Meezan, Alfalah, etc.">
              </div>
              <div class="form-group">
                <label>Bank Account Number</label>
                <input type="text" name="bank_account_number" class="form-input" placeholder="XXXX XXXX XXXX XXXX">
              </div>
            </div>
          </div>
        </div>

      </div>
    </form>
  </div>
<?php include_once "../includes/footer.php"; ?>