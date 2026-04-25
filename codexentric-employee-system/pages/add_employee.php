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
          <h1 class="form-title">Add New Employee</h1>
          <p class="form-subtitle">Fill in the employee information to create a new profile. Fields marked with * are required.</p>
        </div>
      </div>
    </div>
      <?php 
      include_once "../pages/database.php";
      include_once "../pages/Employee.php";
      $db = new Database();
      $connection = $db->getConnection();
      $employee = new Employee($connection);
      $employee->createEmployee();
 
      ?>
     <?php if (!empty($employee->errors)): ?>
    <div style="background-color: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <?php foreach ($employee->errors as $error) { echo "<p>$error</p>"; } ?>
    </div>
<?php endif; ?>
    <form action="add_employee.php" method="POST" name ="create_employee" class="employee-form" novalidate>
      
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
              <input type="text" method ="POST" id="first_name" name="first_name" class="form-input" placeholder="John" required>
              <p class="form-helper">Employee's first name</p>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="last_name">
                Last Name
                <span class="form-required">*</span>
              </label>
              <input type="text" id="last_name" name="last_name" class="form-input" placeholder="Doe" required>
              <p class="form-helper">Employee's last name</p>
            </div>
          </div>
        </div>


        <div class="form-row">
          <div class="form-col form-col-full">
            <div class="form-group">
              <label class="form-label" for="address" name ="home_address">
                Address
              </label>
              <input type="text" id="address" name="home_address" class="form-input" placeholder="123 Main Street, City, Country">
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
              <label class="form-label" for="position" name ="position_title">
                Position/Role
                <span class="form-required">*</span>
              </label>
              <input type="text" id="position" name="position_title" class="form-input" placeholder="Senior Developer" required>
              <p class="form-helper">Job title or position</p>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="department">
                Department
                <span class="form-required">*</span>
              </label>
              <select id="department" name="department" class="form-select" required>
                <option value="">Select Department</option>
                <option value="software-engineering">Software Engineering</option>
                <option value="design">Design</option>
                <option value="marketing">Marketing</option>
                <option value="sales">Sales</option>
                <option value="hr">Human Resources</option>
                <option value="finance">Finance</option>
              </select>
              <p class="form-helper">Department assignment</p>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="employment_type" name ="employee_type">
                Employment Type
                <span class="form-required">*</span>
              </label>
              <select id="employment_type" name="employee_type" class="form-select" required>
                <option value="">Select Type</option>
                <option value="full-time">Full-time</option>
                <option value="part-time">Part-time</option>e
                <option value="contract">Contract</option>
                <option value="temporary">Temporary</option>
              </select>
              <p class="form-helper">Type of employment</p>
            </div>
          </div>
       
      </fieldset>

      <!-- Account Credentials Section -->
      <fieldset class="form-fieldset">
        <legend class="fieldset-legend">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <span>Account Credentials</span>
        </legend>
        
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="email">
                Email Address
                <span class="form-required">*</span>
              </label>
              <input type="email" id="email" name="email" class="form-input" placeholder="john.doe@company.com" required>
              <p class="form-helper">Corporate email address</p>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="password">
                Password
                <span class="form-required">*</span>
              </label>
              <input type="password" id="password" name="password" class="form-input" placeholder="Enter a secure password" required>
              <p class="form-helper">Create a strong password for the employee account</p>
            </div>
          </div>
        </div>
      </fieldset>

      <!-- Compensation Information Section -->
      <fieldset class="form-fieldset">
        <legend class="fieldset-legend">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="1" x2="12" y2="23"/>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
</svg>
          <span>Compensation & Benefits</span>
        </legend>
        
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="base_salary">
                Base Salary (Rs)
                <span class="form-required">*</span>
              </label>
              <input type="number" id="base_salary" name="base_salary" class="form-input" placeholder="50000" min="0" required>
              <p class="form-helper">Monthly base salary in Pakistani Rupees</p>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="allowances">
                Allowances (Rs)
              </label>
              <input type="number" id="allowances" name="allowances" class="form-input" placeholder="5000" min="0" value="0">
              <p class="form-helper">Additional allowances (optional)</p>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="bank_account" name ="bank_account_number">
                Bank Account Number
              </label>
              <input type="text" id="bank_account" name="bank_account_number" class="form-input" placeholder="XXXX XXXX XXXX XXXX">
              <p class="form-helper">Bank account for salary transfer</p>
            </div>
          </div>
          <div class="form-col">
            <div class="form-group">
              <label class="form-label" for="bank_name">
                Bank Name
              </label>
              <input type="text" id="bank_name" name="bank_name" class="form-input" placeholder="HBL, Meezan, Alfalah, etc.">
              <p class="form-helper">Name of the bank</p>
            </div>
          </div>
        </div>
      </fieldset>
    

      <!-- Form Actions -->
      <div class="form-actions">
        <a href="administrator_dashboard.php" class="form-button form-button-secondary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="1 4 1 10 7 10"></polyline>
            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
          </svg>
          Back
        </a>
        <button type="submit" action ="POST"  class="form-button form-button-primary" name="create_employee">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          Create Employee
        </button>
      </div>
    </form>
  </div>
</section>

<?php include_once "../includes/footer.php"; ?>