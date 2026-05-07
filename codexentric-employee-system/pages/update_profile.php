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


<style>
/* ── Profile Redesign CSS ── */
.profile-container {
    display: flex;
    gap: 30px;
    padding: 20px 0;
    max-width: 1200px;
    margin: 0 auto;
}

/* ── Sidebar (Left) ── */
.profile-sidebar {
    width: 280px;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 30px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    align-self: flex-start;
    position: sticky;
    top: 20px;
}

.profile-avatar-wrapper {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: #f1f5f9;
    padding: 5px;
    border: 1px solid #e2e8f0;
    margin-bottom: 20px;
    overflow: hidden;
}

.profile-avatar-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.profile-sidebar h2 {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    text-align: center;
    margin: 0 0 5px 0;
    line-height: 1.2;
}

.profile-sidebar p {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 30px;
}

.profile-nav {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.profile-nav-link {
    display: block;
    padding: 12px 16px;
    border-radius: 8px;
    color: #475569;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.profile-nav-link:hover {
    background: #f8fafc;
    color: var(--brand-green);
}

.profile-nav-link.active {
    background: #f1f5f9;
    color: #1e293b;
}

/* ── Main Content (Right) ── */
.profile-main {
    flex: 1;
}

.section-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    margin-bottom: 24px;
    overflow: hidden;
}

.section-header {
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
}

.section-header h3 {
    font-size: 16px;
    font-weight: 700;
    color: #334155;
    margin: 0;
}

.section-body {
    padding: 24px;
}

/* ── Modern Form Controls ── */
.modern-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.form-field {
    margin-bottom: 20px;
}

.form-field.full {
    grid-column: span 2;
}

.form-field label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 8px;
}

.form-field label .req {
    color: #ef4444;
    margin-left: 2px;
}

.input-group {
    position: relative;
}

.modern-input {
    width: 100%;
    height: 44px;
    padding: 0 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    color: #1e293b;
    font-family: inherit;
    transition: all 0.2s;
}

.modern-input:focus {
    border-color: var(--brand-green);
    box-shadow: 0 0 0 3px rgba(24, 109, 85, 0.1);
    outline: none;
}

.modern-select {
    width: 100%;
    height: 44px;
    padding: 0 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    color: #1e293b;
    background: #fff;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
}

.modern-select:focus {
    border-color: var(--brand-green);
    outline: none;
}

.modern-btn-primary {
    background: var(--brand-green);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.modern-btn-primary:hover {
    background: #125542;
    transform: translateY(-1px);
}

.modern-btn-secondary {
    background: #fff;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
}

.modern-btn-secondary:hover {
    background: #f8fafc;
}
</style>

<div class="dashboard-container">
    <div class="profile-container">
        
        <!-- Sidebar -->
        <aside class="profile-sidebar">
            <div class="profile-avatar-wrapper">
                <?php 
                    $initials = strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1));
                    echo "<div style='width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#186D55; color:#fff; font-size:40px; font-weight:700;'>$initials</div>";
                ?>
            </div>
            <h2><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></h2>
            <p><?php echo htmlspecialchars($employee['position_title']); ?></p>
            
            <nav class="profile-nav">
                <a href="#" class="profile-nav-link active">Personal Details</a>
                <a href="#" class="profile-nav-link">Contact Details</a>
                <a href="#" class="profile-nav-link">Job</a>
                <a href="#" class="profile-nav-link">Salary</a>
                <a href="#" class="profile-nav-link">Banking</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="profile-main">
            <form action="" method="POST" id="update_profile_form" novalidate>
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($employee['user_id']); ?>">

                <!-- Personal Details Section -->
                <div class="section-card">
                    <div class="section-header">
                        <h3>Personal Details</h3>
                    </div>
                    <div class="section-body">
                        <div class="modern-grid">
                            <div class="form-field">
                                <label>First Name <span class="req">*</span></label>
                                <input type="text" name="first_name" class="modern-input" value="<?php echo htmlspecialchars($employee['first_name']); ?>" required>
                            </div>
                            <div class="form-field">
                                <label>Last Name <span class="req">*</span></label>
                                <input type="text" name="last_name" class="modern-input" value="<?php echo htmlspecialchars($employee['last_name']); ?>" required>
                            </div>
                            <div class="form-field full">
                                <label>Home Address</label>
                                <input type="text" name="home_address" class="modern-input" value="<?php echo htmlspecialchars($employee['home_address']); ?>" placeholder="123 Main Street, City, Country">
                            </div>
                            <div class="form-field">
                                <label>Email Address <span class="req">*</span></label>
                                <input type="email" name="email" class="modern-input" value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Job Information Section -->
                <div class="section-card">
                    <div class="section-header">
                        <h3>Job Information</h3>
                    </div>
                    <div class="section-body">
                        <div class="modern-grid">
                            <div class="form-field">
                                <label>Position Title <span class="req">*</span></label>
                                <input type="text" name="position_title" class="modern-input" value="<?php echo htmlspecialchars($employee['position_title']); ?>" required>
                            </div>
                            <div class="form-field">
                                <label>Department <span class="req">*</span></label>
                                <select name="department" class="modern-select" required>
                                    <option value="Software Engineering" <?php echo ($employee['department'] == 'Software Engineering') ? 'selected' : ''; ?>>Software Engineering</option>
                                    <option value="Marketing"            <?php echo ($employee['department'] == 'Marketing')            ? 'selected' : ''; ?>>Marketing</option>
                                    <option value="Design"               <?php echo ($employee['department'] == 'Design')               ? 'selected' : ''; ?>>Design</option>
                                    <option value="Human Resources"      <?php echo ($employee['department'] == 'Human Resources')      ? 'selected' : ''; ?>>Human Resources</option>
                                    <option value="Quality Assurance"    <?php echo ($employee['department'] == 'Quality Assurance')    ? 'selected' : ''; ?>>Quality Assurance</option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>Employment Type <span class="req">*</span></label>
                                <select name="employment_type" class="modern-select" required>
                                    <option value="Full-time"  <?php echo ($employee['employment_type'] == 'Full-time')  ? 'selected' : ''; ?>>Full-time</option>
                                    <option value="Part-time"  <?php echo ($employee['employment_type'] == 'Part-time')  ? 'selected' : ''; ?>>Part-time</option>
                                    <option value="Contract"   <?php echo ($employee['employment_type'] == 'Contract')   ? 'selected' : ''; ?>>Contract</option>
                                    <option value="Temporary"  <?php echo ($employee['employment_type'] == 'Temporary')  ? 'selected' : ''; ?>>Temporary</option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>Employment Status <span class="req">*</span></label>
                                <select name="status" class="modern-select" required>
                                    <option value="ACTIVE"     <?php echo ($employee['status'] == 'ACTIVE')     ? 'selected' : ''; ?>>Active</option>
                                    <option value="ON_LEAVE"   <?php echo ($employee['status'] == 'ON_LEAVE')   ? 'selected' : ''; ?>>On Leave</option>
                                    <option value="TERMINATED" <?php echo ($employee['status'] == 'TERMINATED') ? 'selected' : ''; ?>>Terminated</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Compensation & Banking Section -->
                <div class="section-card">
                    <div class="section-header">
                        <h3>Compensation & Banking</h3>
                    </div>
                    <div class="section-body">
                        <div class="modern-grid">
                            <div class="form-field">
                                <label>Base Salary (Rs) <span class="req">*</span></label>
                                <input type="number" name="base_salary_rs" class="modern-input" value="<?php echo htmlspecialchars($employee['base_salary_rs']); ?>" required>
                            </div>
                            <div class="form-field">
                                <label>Allowances (Rs)</label>
                                <input type="number" name="allowances" class="modern-input" value="<?php echo htmlspecialchars($employee['allowances'] ?? ''); ?>">
                            </div>
                            <div class="form-field">
                                <label>Bank Name</label>
                                <input type="text" name="bank_name" class="modern-input" value="<?php echo htmlspecialchars($employee['bank_name']); ?>">
                            </div>
                            <div class="form-field">
                                <label>Account Number (IBAN)</label>
                                <input type="text" name="bank_account_number" class="modern-input" value="<?php echo htmlspecialchars($employee['bank_account_number']); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <a href="manage_employee.php?id=<?php echo htmlspecialchars($employee['user_id']); ?>" class="modern-btn-secondary">Cancel</a>
                    <button type="submit" name="update_profile" class="modern-btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Save Changes
                    </button>
                </div>
            </main>
        </form>
    </div>
</div>


<?php include_once "../includes/footer.php" ; ?>
