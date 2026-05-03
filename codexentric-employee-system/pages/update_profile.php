<?php 
session_start();
   if (!isset($_SESSION['email'])) {
        header("Location: login.php");
        exit();
    }
    $user_role = "admin"; 
    $current_page = "manage_employees"; 
    $title = "Update Employee Profile";
    $extra_css = "add_employee"; 
    include_once "../includes/header.php"; 
require_once '../pages/Database.php'; 
require_once '../pages/Employee.php';

$db = new Database();
$employeeObj = new Employee($db->getConnection());
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $user_id = $_GET['id'];
    $employee = $employeeObj->getEmployeeDetailsById($user_id);
    
    if (!$employee) {
        die("Error: Employee not found in the database.");
    }
  

} else {

    die("Error: No Employee ID provided.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    
    $bank_name = trim($_POST['bank_name']);
    $bank_account_number = trim($_POST['bank_account_number']);
    $user_id = trim($_POST['user_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $position_title = trim($_POST['position_title']);
    $department = trim($_POST['department']);
    $home_address = trim($_POST['home_address']);
    $status = trim($_POST['status']);
    $base_salary_rs = $_POST['base_salary_rs'];
    $allowances = $_POST['allowances'];
    $employment_type = $_POST['employment_type'];

    if (!empty($user_id)) {

        $updateEmployee = $employeeObj->updateEmployeeProfile($first_name, $last_name, $email, $position_title, $department, $home_address, $status, $base_salary_rs, $allowances, $employment_type, $bank_name, $bank_account_number, $user_id);
      
        if ($updateEmployee) {
            echo "<p style='color:green; text-align:center; margin-top:40px;'>Employee profile updated successfully.</p>";
            echo "<p style='text-align:center;'><a href='manage_employee.php?id={$user_id}'>Back to Profile</a></p>";
        } else {
          echo "<p style='color:red;'>Error: " . $employeeObj->errors['general'] . "</p>";
            echo "<p style='color:red; text-align:center; margin-top:40px;'>Employee profile update error.</p>";
        } 
        
    } else {
       
        echo "<p style='color:red; text-align:center; margin-top:40px;'>Error: Missing User ID. Cannot update.</p>";
    }  
}
?>


  <div class="dashboard-container">
    
    <!-- Welcome Header -->
    <div class="dash-welcome">
      <div class="dash-welcome-text">
        <h1>Update Employee Profile</h1>
        <p>Editing: <strong><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></strong> &nbsp;|&nbsp; <?php echo htmlspecialchars($employee['position_title']); ?> &nbsp;|&nbsp; ID: CEMS-<?php echo htmlspecialchars($employee['user_id']); ?></p>
      </div>
      <div class="dash-welcome-actions">
        <a href="manage_employee.php?id=<?php echo htmlspecialchars($employee['user_id']); ?>" class="btn-wh" aria-label="Return to profile">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          Back to Profile
        </a>
        <button type="submit" form="update_profile_form" name="update_profile" class="btn-wh primary-wh">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Update Details
        </button>
      </div>
    </div>

    <form action="" method="POST" id="update_profile_form" novalidate>
      <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($employee['user_id']); ?>">

      <div class="emp-info-grid">
        
        <!-- Personal Information Section -->
        <div class="emp-card">
          <div class="emp-card-head">
            <div class="emp-card-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
            <h3 class="emp-card-title"><span class="dot"></span>Personal Information</h3>
          </div>
          <div class="emp-card-body">
            <div class="form-group-row">
              <div class="form-group">
                <label>First Name <span class="req">*</span></label>
                <input type="text" id="first_name" name="first_name" class="form-input" value="<?php echo htmlspecialchars($employee['first_name']); ?>" required>
              </div>
              <div class="form-group">
                <label>Last Name <span class="req">*</span></label>
                <input type="text" id="last_name" name="last_name" class="form-input" value="<?php echo htmlspecialchars($employee['last_name']); ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label>Address</label>
              <input type="text" id="home_address" name="home_address" class="form-input" value="<?php echo htmlspecialchars($employee['home_address']); ?>" placeholder="123 Main Street, City, Country">
            </div>
          </div>
        </div>

        <!-- Employment Information Section -->
        <div class="emp-card">
          <div class="emp-card-head">
            <div class="emp-card-icon" style="background:#F3E8FF; color:#7E22CE;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></div>
            <h3 class="emp-card-title"><span class="dot" style="background:linear-gradient(135deg,#7E22CE,#9333EA)"></span>Employment Information</h3>
          </div>
          <div class="emp-card-body">
            <div class="form-group-row">
              <div class="form-group">
                <label>Email Address <span class="req">*</span></label>
                <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($employee['email']); ?>" required>
              </div>
              <div class="form-group">
                <label>Position Title <span class="req">*</span></label>
                <input type="text" id="position_role" name="position_title" class="form-input" value="<?php echo htmlspecialchars($employee['position_title']); ?>" required>
              </div>
            </div>
            <div class="form-group-row">
              <div class="form-group">
                <label>Department <span class="req">*</span></label>
                <select id="department" name="department" class="form-select" required>
                  <option value="Software Engineering" <?php echo ($employee['department'] == 'Software Engineering') ? 'selected' : ''; ?>>Software Engineering</option>
                  <option value="Marketing"            <?php echo ($employee['department'] == 'Marketing')            ? 'selected' : ''; ?>>Marketing</option>
                  <option value="Design"               <?php echo ($employee['department'] == 'Design')               ? 'selected' : ''; ?>>Design</option>
                  <option value="Human Resources"      <?php echo ($employee['department'] == 'Human Resources')      ? 'selected' : ''; ?>>Human Resources</option>
                  <option value="Quality Assurance"    <?php echo ($employee['department'] == 'Quality Assurance')    ? 'selected' : ''; ?>>Quality Assurance</option>
                </select>
              </div>
              <div class="form-group">
                <label>Employment Type <span class="req">*</span></label>
                <select id="employment_type" name="employment_type" class="form-select" required>
                  <option value="Full-time"  <?php echo ($employee['employment_type'] == 'Full-time')  ? 'selected' : ''; ?>>Full-time</option>
                  <option value="Part-time"  <?php echo ($employee['employment_type'] == 'Part-time')  ? 'selected' : ''; ?>>Part-time</option>
                  <option value="Contract"   <?php echo ($employee['employment_type'] == 'Contract')   ? 'selected' : ''; ?>>Contract</option>
                  <option value="Temporary"  <?php echo ($employee['employment_type'] == 'Temporary')  ? 'selected' : ''; ?>>Temporary</option>
                </select>
              </div>
            </div>
            <div class="form-group-row">
              <div class="form-group">
                <label>Employee ID</label>
                <input type="text" id="employee_id" name="employee_id" class="form-input" value="<?php echo htmlspecialchars($employee['employee_id']); ?>" readonly>
              </div>
              <div class="form-group">
                <label>Employment Status <span class="req">*</span></label>
                <select id="status" name="status" class="form-select" required>
                  <option value="ACTIVE"     <?php echo ($employee['status'] == 'ACTIVE')     ? 'selected' : ''; ?>>Active</option>
                  <option value="ON_LEAVE"   <?php echo ($employee['status'] == 'ON_LEAVE')   ? 'selected' : ''; ?>>On Leave</option>
                  <option value="TERMINATED" <?php echo ($employee['status'] == 'TERMINATED') ? 'selected' : ''; ?>>Terminated</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Compensation & Banking Section -->
        <div class="emp-card">
          <div class="emp-card-head">
            <div class="emp-card-icon" style="background:var(--green-bg, #ECFDF5); color:var(--green, #10B981);"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
            <h3 class="emp-card-title"><span class="dot" style="background:linear-gradient(135deg,#059669,#10b981)"></span>Compensation & Banking</h3>
          </div>
          <div class="emp-card-body">
            <div class="form-group-row">
              <div class="form-group">
                <label>Base Salary (Rs) <span class="req">*</span></label>
                <input type="number" id="base_salary_rs" name="base_salary_rs" class="form-input" value="<?php echo htmlspecialchars($employee['base_salary_rs']); ?>" placeholder="monthly salary in Rs" min="0" required>
              </div>
              <div class="form-group">
                <label>Allowances (Rs)</label>
                <input type="number" id="allowances" name="allowances" class="form-input" value="<?php echo htmlspecialchars($employee['allowances'] ?? ''); ?>" placeholder="Additional monthly allowances in Rs" min="0">
              </div>
            </div>
            <div class="form-group-row">
              <div class="form-group">
                <label>Bank Name</label>
                <input type="text" id="bank" name="bank_name" class="form-input" value="<?php echo htmlspecialchars($employee['bank_name']); ?>" placeholder="HBL, Meezan, Alfalah, etc.">
              </div>
              <div class="form-group">
                <label>Account Number (IBAN)</label>
                <input type="text" id="account" name="bank_account_number" class="form-input" value="<?php echo htmlspecialchars($employee['bank_account_number']); ?>" placeholder="XXXX XXXX XXXX XXXX">
              </div>
            </div>
          </div>
        </div>

      </div>
    </form>
  </div>


<?php include_once "../includes/footer.php" ; ?>