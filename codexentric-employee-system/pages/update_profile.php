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


<section class="form-section">
  <div class="form-container">
    <div class="form-header">
      <div class="form-header-content">
        <div class="form-header-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
        </div>
        <div class="form-header-text">
          <h1 class="form-title">Update Employee Profile</h1>
          <p class="form-subtitle">
            Editing: <strong><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></strong>
            &nbsp;|&nbsp; <?php echo htmlspecialchars($employee['position_title']); ?>
            &nbsp;|&nbsp; ID: CEMS-<?php echo htmlspecialchars($employee['user_id']); ?>
          </p>
        </div>
      </div>
    </div>

    <form action="" method="POST" class="employee-form" novalidate>
      <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($employee['user_id']); ?>">

      <!-- Personal Information Section -->
      <fieldset class="form-fieldset">
        <legend class="fieldset-legend">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <span>Personal Information</span>
        </legend>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="first_name">
                First Name
                <span class="form-required">*</span>
              </label>
              <input type="text" id="first_name" name="first_name" class="form-input"
                value="<?php echo htmlspecialchars($employee['first_name']); ?>" required>
              <p class="form-helper">Employee's first name</p>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="last_name">
                Last Name
                <span class="form-required">*</span>
              </label>
              <input type="text" id="last_name" name="last_name" class="form-input"
                value="<?php echo htmlspecialchars($employee['last_name']); ?>" required>
              <p class="form-helper">Employee's last name</p>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col form-col-full">
            <div class="form-group">
              <label class="form-label" for="home_address">
                Address
              </label>
              <input type="text" id="home_address" name="home_address" class="form-input"
                value="<?php echo htmlspecialchars($employee['home_address']); ?>"
                placeholder="123 Main Street, City, Country">
              <p class="form-helper">Residential address</p>
            </div>
          </div>
        </div>
      </fieldset>

      <!-- Employment Information Section -->
      <fieldset class="form-fieldset">
        <legend class="fieldset-legend">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
          </svg>
          <span>Employment Information</span>
        </legend>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="email">
                Email Address
                <span class="form-required">*</span>
              </label>
              <input type="email" id="email" name="email" class="form-input"
                value="<?php echo htmlspecialchars($employee['email']); ?>" required>
              <p class="form-helper">Corporate email address</p>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="position_role">
                Position Title
                <span class="form-required">*</span>
              </label>
              <input type="text" id="position_role" name="position_title" class="form-input"
                value="<?php echo htmlspecialchars($employee['position_title']); ?>" required>
              <p class="form-helper">Job title or position</p>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="department">
                Department
                <span class="form-required">*</span>
              </label>
              <select id="department" name="department" class="form-select" required>
                <option value="Software Engineering" <?php echo ($employee['department'] == 'Software Engineering') ? 'selected' : ''; ?>>Software Engineering</option>
                <option value="Marketing"            <?php echo ($employee['department'] == 'Marketing')            ? 'selected' : ''; ?>>Marketing</option>
                <option value="Design"               <?php echo ($employee['department'] == 'Design')               ? 'selected' : ''; ?>>Design</option>
              </select>
              <p class="form-helper">Department assignment</p>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="employment_type">
                Employment Type
                <span class="form-required">*</span>
              </label>
              <select id="employment_type" name="employment_type" class="form-select" required>
                <option value="Full-time"  <?php echo ($employee['employment_type'] == 'Full-time')  ? 'selected' : ''; ?>>Full-time</option>
                <option value="Part-time"  <?php echo ($employee['employment_type'] == 'Part-time')  ? 'selected' : ''; ?>>Part-time</option>
                <option value="Contract"   <?php echo ($employee['employment_type'] == 'Contract')   ? 'selected' : ''; ?>>Contract</option>
                <option value="Temporary"  <?php echo ($employee['employment_type'] == 'Temporary')  ? 'selected' : ''; ?>>Temporary</option>
              </select>
              <p class="form-helper">Type of employment</p>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="employee_id">Employee ID</label>
              <input type="text" id="employee_id" name="employee_id" class="form-input"
                value="<?php echo htmlspecialchars($employee['employee_id']); ?>" readonly>
              <p class="form-helper">System-assigned, read-only</p>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="status">
                Employment Status
                <span class="form-required">*</span>
              </label>
              <select id="status" name="status" class="form-select" required>
                <option value="ACTIVE"     <?php echo ($employee['status'] == 'ACTIVE')     ? 'selected' : ''; ?>>Active</option>
                <option value="ON_LEAVE"   <?php echo ($employee['status'] == 'ON_LEAVE')   ? 'selected' : ''; ?>>On Leave</option>
                <option value="TERMINATED" <?php echo ($employee['status'] == 'TERMINATED') ? 'selected' : ''; ?>>Terminated</option>
              </select>
              <p class="form-helper">Current employment status</p>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col form-col-full">
            <div class="form-group">
              <label class="form-label" for="role">Role / Designation</label>
              <select id="role" name="employment_type" class="form-select">
                <option value="Senior Backend Developer"  <?php echo ($employee['employment_type'] == 'Senior Backend Developer')  ? 'selected' : ''; ?>>Senior Backend Developer</option>
                <option value="Senior Frontend Developer" <?php echo ($employee['employment_type'] == 'Senior Frontend Developer') ? 'selected' : ''; ?>>Senior Frontend Developer</option>
                <option value="Backend Developer"         <?php echo ($employee['employment_type'] == 'Backend Developer')         ? 'selected' : ''; ?>>Backend Developer</option>
                <option value="Frontend Developer"        <?php echo ($employee['employment_type'] == 'Frontend Developer')        ? 'selected' : ''; ?>>Frontend Developer</option>
                <option value="UI/UX Designer"            <?php echo ($employee['employment_type'] == 'UI/UX Designer')            ? 'selected' : ''; ?>>UI/UX Designer</option>
                <option value="Backend Intern"            <?php echo ($employee['employment_type'] == 'Backend Intern')            ? 'selected' : ''; ?>>Backend Intern</option>
                <option value="Frontend Intern"           <?php echo ($employee['employment_type'] == 'Frontend Intern')           ? 'selected' : ''; ?>>Frontend Intern</option>
              </select>
              <p class="form-helper">Specific role or designation within the department</p>
            </div>
          </div>
        </div>
      </fieldset>

      <!-- Compensation & Banking Section -->
      <fieldset class="form-fieldset">
        <legend class="fieldset-legend">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="1" x2="12" y2="23"/>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
          </svg>
          <span>Compensation &amp; Banking</span>
        </legend>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="base_salary_rs">
                Base Salary (Rs)
                <span class="form-required">*</span>
              </label>
              <input type="number" id="base_salary_rs" name="base_salary_rs" class="form-input"
                value="<?php echo htmlspecialchars($employee['base_salary_rs']); ?>"
                placeholder="monthy salary in Rs" min="0"  >
              <p class="form-helper">Monthly base salary in Pakistani Rupees</p>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="allowances">Allowances (Rs)</label>
              <input type="number" id="allowances" name="allowances" class="form-input"
                value="<?php echo htmlspecialchars($employee['allowances'] ?? ''); ?>"
                placeholder="Additional monthly allowances in Rs" min ="0">
              <p class="form-helper">Additional allowances (optional)</p>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="bank">Bank Name</label>
              <input type="text" id="bank" name="bank_name" class="form-input"
                value="<?php echo htmlspecialchars($employee['bank_name']); ?>"
                placeholder="HBL, Meezan, Alfalah, etc.">
              <p class="form-helper">Name of the bank</p>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="account">Account Number (IBAN)</label>
              <input type="text" id="account" name="bank_account_number" class="form-input"
                value="<?php echo htmlspecialchars($employee['bank_account_number']); ?>"
                placeholder="XXXX XXXX XXXX XXXX">
              <p class="form-helper">Bank account for salary transfer</p>
            </div>
          </div>
        </div>
      </fieldset>

      <!-- Form Actions -->
      <div class="form-actions">
        <a href="manage_employee.php?id=<?php echo htmlspecialchars($employee['user_id']); ?>" class="form-button form-button-secondary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
          </svg>
          Back to Profile
        </a>
        <button type="submit" name="update_profile" class="form-button form-button-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          Update Employee Details
        </button>
      </div>
    </form>
  </div>
</section>


<?php include_once "../includes/footer.php" ; ?>