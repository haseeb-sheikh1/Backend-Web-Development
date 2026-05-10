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

$alert_banner = "";

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
    $employment_type = $_POST['employment_type'];
    $base_salary_rs = isset($_POST['base_salary_rs']) && $_POST['base_salary_rs'] !== '' ? (float)$_POST['base_salary_rs'] : null;

    if (!empty($user_id)) {

        $updateEmployee = $employeeObj->updateEmployeeProfile($first_name, $last_name, $email, $position_title, $department, $home_address, $status, $base_salary_rs, $employment_type, $bank_name, $bank_account_number, $user_id);
      
        if ($updateEmployee) {
            // Re-fetch employee details to show updated data in form
            $employee = $employeeObj->getEmployeeDetailsById($user_id);
            $alert_banner = "
            <div class='alert-banner-modern success' style='background: #ecfdf5; border: 1px solid #10b981; border-radius: 12px; padding: 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08); animation: slideDown 0.3s ease both;'>
                <div style='display: flex; align-items: center; gap: 14px;'>
                    <div style='background: #10b981; color: #fff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;'>
                        <svg viewBox='0 0 24 24' style='width: 22px; height: 22px; stroke: currentColor; fill: none; stroke-width: 3;'><polyline points='20 6 9 17 4 12'/></svg>
                    </div>
                    <div>
                        <h4 style='margin: 0; color: #065f46; font-size: 15px; font-weight: 800;'>Profile Updated Successfully</h4>
                        <p style='margin: 3px 0 0 0; color: #047857; font-size: 13px; font-weight: 600;'>The employee details have been securely synchronized with the database.</p>
                    </div>
                </div>
                <a href='manage_employee.php?id=" . htmlspecialchars($user_id) . "' style='background: #10b981; color: #fff; text-decoration: none; padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.2); transition: all 0.2s;'>
                    Back to Profile
                    <svg viewBox='0 0 24 24' style='width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 2.5;'><polyline points='9 18 15 12 9 6'/></svg>
                </a>
            </div>";
        } else {
            $err = isset($employeeObj->errors['general']) ? $employeeObj->errors['general'] : 'Employee profile update error.';
            $alert_banner = "
            <div class='alert-banner-modern error' style='background: #fef2f2; border: 1px solid #ef4444; border-radius: 12px; padding: 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 14px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.08); animation: slideDown 0.3s ease both;'>
                <div style='background: #ef4444; color: #fff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;'>
                    <svg viewBox='0 0 24 24' style='width: 22px; height: 22px; stroke: currentColor; fill: none; stroke-width: 3;'><line x1='18' y1='6' x2='6' y2='18'/><line x1='6' y1='6' x2='18' y2='18'/></svg>
                </div>
                <div>
                    <h4 style='margin: 0; color: #991b1b; font-size: 15px; font-weight: 800;'>Profile Update Failed</h4>
                    <p style='margin: 3px 0 0 0; color: #b91c1c; font-size: 13px; font-weight: 600;'>" . htmlspecialchars($err) . "</p>
                </div>
            </div>";
        } 
        
    } else {
        $alert_banner = "
        <div class='alert-banner-modern error' style='background: #fef2f2; border: 1px solid #ef4444; border-radius: 12px; padding: 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 14px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.08); animation: slideDown 0.3s ease both;'>
            <div style='background: #ef4444; color: #fff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;'>
                <svg viewBox='0 0 24 24' style='width: 22px; height: 22px; stroke: currentColor; fill: none; stroke-width: 3;'><line x1='18' y1='6' x2='6' y2='18'/><line x1='6' y1='6' x2='18' y2='18'/></svg>
            </div>
            <div>
                <h4 style='margin: 0; color: #991b1b; font-size: 15px; font-weight: 800;'>Missing User ID</h4>
                <p style='margin: 3px 0 0 0; color: #b91c1c; font-size: 13px; font-weight: 600;'>Cannot update because User ID is missing.</p>
            </div>
        </div>";
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
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: #f1f5f9;
    padding: 4px;
    border: 1px solid #e2e8f0;
    margin-bottom: 12px;
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
    margin-bottom: 16px;
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
    margin-bottom: 16px;
    overflow: hidden;
}

.section-header {
    padding: 14px 20px;
    border-bottom: 1px solid #f1f5f9;
}

.section-header h3 {
    font-size: 16px;
    font-weight: 700;
    color: #334155;
    margin: 0;
}

.section-body {
    padding: 16px 20px;
}

/* ── Modern Form Controls ── */
.modern-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.form-field {
    margin-bottom: 12px;
}

.form-field.full {
    grid-column: span 2;
}

.form-field label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 4px;
}

.form-field label .req {
    color: #ef4444;
    margin-left: 2px;
}

.input-group {
    position: relative;
}

.modern-input, .modern-select {
    width: 100%;
    height: 38px;
    padding: 0 20px; /* Relaxed padding for pill architecture */
    border: 1px solid #e2e8f0;
    border-radius: 20px; /* True capsule/pill container shape */
    font-size: 13.5px;
    color: #1e293b;
    background-color: #ffffff;
    font-family: inherit;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-sizing: border-box;
}

.modern-input:focus, .modern-select:focus {
    border-color: var(--brand-green);
    box-shadow: 0 0 0 3px rgba(24, 109, 85, 0.12);
    outline: none;
}

.modern-select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    background-size: 15px;
    padding-right: 38px;
    cursor: pointer;
}

/* ── Calendar Picker Indicator Styling ── */
.modern-input[type="date"] {
    position: relative;
    cursor: pointer;
}

.modern-input[type="date"]::-webkit-calendar-picker-indicator {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23186D55' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-size: 16px;
    background-position: center;
    cursor: pointer;
    width: 18px;
    height: 18px;
    opacity: 0.85;
    transition: opacity 0.15s ease, transform 0.15s ease;
}

.modern-input[type="date"]::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
    transform: scale(1.05);
}

.modern-btn-primary {
    background: var(--brand-green);
    color: #fff;
    border: none;
    border-radius: 25px; /* Matching pills exactly */
    padding: 12px 28px;
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
    color: var(--brand-green); /* Replicate 'Reset' green text */
    border: 1.5px solid var(--brand-green); /* Replicate 'Reset' thin border */
    border-radius: 25px;
    padding: 12px 28px;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
}

.modern-btn-secondary:hover {
    background: #f8fafc;
}

/* ── Breadcrumb Pills ── */
.emp-breadcrumb {
  display: flex; 
  align-items: center; 
  gap: 8px; 
  margin-bottom: 24px;
}
.emp-breadcrumb a {
  background: #f1f5f9;
  color: #64748b;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s ease;
}
.emp-breadcrumb a:hover {
  background: #e2e8f0;
  color: #334155;
}
.emp-breadcrumb span {
  background: #e8f3f0;
  color: #186D55;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
}

/* ── Responsiveness (Mobile View tabs & side-by-side avatar) ── */
@media (max-width: 768px) {
    .profile-container {
        flex-direction: column;
        gap: 20px;
        padding: 0;
    }
    .profile-sidebar {
        display: grid;
        grid-template-columns: auto 1fr;
        align-items: center;
        gap: 20px;
        padding: 20px;
        width: 100%;
        position: relative;
        top: 0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .profile-avatar-wrapper {
        width: 80px;
        height: 80px;
        margin-bottom: 0;
    }
    .profile-meta-info {
        display: flex;
        flex-direction: column;
        text-align: left;
    }
    .profile-sidebar h2 {
        text-align: left;
        margin: 0;
        font-size: 18px;
    }
    .profile-sidebar p {
        text-align: left;
        margin: 4px 0 0 0;
    }
    .profile-nav {
        grid-column: span 2;
        display: flex;
        flex-direction: row;
        gap: 10px;
        overflow-x: auto;
        padding: 8px 0;
        margin-top: 15px;
        border-top: 1px solid #f1f5f9;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .profile-nav::-webkit-scrollbar {
        display: none;
    }
    .profile-nav-link {
        white-space: nowrap;
        flex-shrink: 0;
        padding: 8px 16px !important;
        border-radius: 20px !important;
        background: #f1f5f9 !important;
        color: #475569 !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        border: none !important;
        transition: all 0.2s;
    }
    .profile-nav-link.active {
        background: var(--brand-green) !important;
        color: #ffffff !important;
    }
    .profile-main {
        width: 100%;
    }
    .modern-grid {
        grid-template-columns: 1fr;
    }
    .form-field.full {
        grid-column: span 1;
    }
}
</style>

<div class="dashboard-container">
    <!-- ── Breadcrumb ── -->
    <nav class="emp-breadcrumb" aria-label="Breadcrumb">
        <a href="administrator_dashboard.php">Dashboard</a>
        <a href="employees_list.php">Employees</a>
        <a href="manage_employee.php?id=<?php echo htmlspecialchars($employee['user_id']); ?>"><?php echo htmlspecialchars($employee['first_name']); ?></a>
        <span>Update Profile</span>
    </nav>

    <?php echo $alert_banner; ?>

    <div class="profile-container">
        
        <!-- Sidebar -->
        <aside class="profile-sidebar">
            <div class="profile-avatar-wrapper">
                <?php 
                    $initials = strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1));
                    echo "<div style='width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#186D55; color:#fff; font-size:40px; font-weight:700;'>$initials</div>";
                ?>
            </div>
            <div class="profile-meta-info">
                <h2><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></h2>
                <p><?php echo htmlspecialchars($employee['position_title']); ?></p>
            </div>
            
            <nav class="profile-nav">
                <a href="#personal-section" class="profile-nav-link active">Personal Details</a>
                <a href="#job-section" class="profile-nav-link">Job Information</a>
                <a href="#compensation-section" class="profile-nav-link">Compensation & Banking</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="profile-main">
            <form action="" method="POST" id="update_profile_form" novalidate>
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($employee['user_id']); ?>">

                <!-- Personal Details Section -->
                <div class="section-card" id="personal-section">
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
                <div class="section-card" id="job-section">
                    <div class="section-header">
                        <h3>Job Information</h3>
                    </div>
                    <div class="section-body">
                        <div class="modern-grid">
                            <div class="form-field">
                                <label>Position Title <span class="req">*</span></label>
                                <select name="position_title" class="modern-select" required>
                                    <option value="Backend Developer" <?php echo ($employee['position_title'] == 'Backend Developer') ? 'selected' : ''; ?>>Backend Developer</option>
                                    <option value="Frontend Developer" <?php echo ($employee['position_title'] == 'Frontend Developer') ? 'selected' : ''; ?>>Frontend Developer</option>
                                    <option value="Backend intern" <?php echo ($employee['position_title'] == 'Backend intern') ? 'selected' : ''; ?>>Backend intern</option>
                                    <option value="Senior Backend Developer" <?php echo ($employee['position_title'] == 'Senior Backend Developer') ? 'selected' : ''; ?>>Senior Backend Developer</option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>Department <span class="req">*</span></label>
                                <select name="department" class="modern-select" required>
                                    <option value="Software Engineering" <?php echo ($employee['department'] == 'Software Engineering') ? 'selected' : ''; ?>>Software Engineering</option>
                                    <option value="Design"               <?php echo ($employee['department'] == 'Design')               ? 'selected' : ''; ?>>Design</option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>Employment Type <span class="req">*</span></label>
                                <select name="employment_type" class="modern-select" required>
                                    <option value="Full-time"  <?php echo ($employee['employment_type'] == 'Full-time')  ? 'selected' : ''; ?>>Full-time</option>
                                    <option value="Part-time"  <?php echo ($employee['employment_type'] == 'Part-time')  ? 'selected' : ''; ?>>Part-time</option>
                                    <option value="Contract"   <?php echo ($employee['employment_type'] == 'Contract')   ? 'selected' : ''; ?>>Contract</option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>Employment Status <span class="req">*</span></label>
                                <select name="status" class="modern-select" required>
                                    <option value="Active"       <?php echo (strtolower($employee['status']) == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="De-activated" <?php echo (strtolower($employee['status']) == 'de-activated' || strtolower($employee['status']) == 'deactivated') ? 'selected' : ''; ?>>De-activated</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Compensation & Banking Section -->
                <div class="section-card" id="compensation-section">
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


<script>
    // Scroll handling and mobile tabs switcher for update profile
    document.addEventListener('DOMContentLoaded', function() {
        const links = document.querySelectorAll('.profile-nav-link');
        const sections = document.querySelectorAll('.section-card');

        // Click handler for mobile tab switching & smooth scrolling on desktop
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                const targetId = link.getAttribute('href').substring(1);
                
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    
                    // Toggle active pill
                    links.forEach(l => l.classList.remove('active'));
                    link.classList.add('active');
                    
                    // Show only target section
                    sections.forEach(section => {
                        if (section.getAttribute('id') === targetId) {
                            section.style.display = 'block';
                        } else {
                            section.style.display = 'none';
                        }
                    });
                }
            });
        });

        // Initialize display states based on screen width
        function initLayout() {
            if (window.innerWidth <= 768) {
                const activeLink = document.querySelector('.profile-nav-link.active') || links[0];
                if (activeLink) {
                    const targetId = activeLink.getAttribute('href').substring(1);
                    sections.forEach(section => {
                        if (section.getAttribute('id') === targetId) {
                            section.style.display = 'block';
                        } else {
                            section.style.display = 'none';
                        }
                    });
                }
            } else {
                // Ensure all sections are visible on desktop
                sections.forEach(section => {
                    section.style.display = 'block';
                });
            }
        }

        window.addEventListener('resize', initLayout);
        initLayout();
    });
</script>

<?php include_once "../includes/footer.php" ; ?>
