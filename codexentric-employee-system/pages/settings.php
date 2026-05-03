<?php 
    session_start();
    
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }
    
    // Determine user role
    $is_admin = isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
    $current_page = "settings";
    $extra_css = "settings";
    $title = $is_admin ? "Admin Settings - CodeXentric" : "Employee Settings - CodeXentric";

    require_once "../pages/Database.php";
    require_once "../pages/Users.php";
    $db = new Database();
    $user = new Users($db->getConnection());
    
    // Setting the email so the object knows who to update
    $user->email = $_SESSION['email']; 

    // Handle Profile Update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        
        $updateResult = $user->updateProfile($_SESSION['user_id'], $first_name, $last_name, $email);
        if ($updateResult === true) {
            $success_msg = "Profile updated successfully.";
        } else {
            $error_msg = $updateResult;
        }
    }

    // Handle Password Update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];
        
        if ($new_password !== $confirm_new_password) {
            $error_msg = "New passwords do not match.";
        } elseif (strlen($new_password) < 8) {
            $error_msg = "Password must be at least 8 characters.";
        } else {
            $passResult = $user->updatePassword($_SESSION['user_id'], $current_password, $new_password);
            if ($passResult === true) {
                $success_msg = "Password updated successfully.";
            } else {
                $error_msg = $passResult;
            }
        }
    }

    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $result = $user->updateProfileImage($_FILES['profile_image']);
        if ($result === true) {
            header("Location: settings.php?success=1");
            exit();
        } else {
            $error_msg = $result;
        }
    }

    if (isset($_GET['success']) && $_GET['success'] == 1) {
        $success_msg = "Profile image updated successfully.";
    }

    // Fetch employee data if not admin
    $employee_data = null;
    if (!$is_admin) {
        require_once "../pages/Employee.php";
        $employeeObj = new Employee($db->getConnection());
        $employee_data = $employeeObj->getEmployeeDetailsById($_SESSION['user_id']);
    }

    include_once "../includes/header.php";
?>

<div class="app-body">
    <?php include_once "../includes/sidebar.php"; ?>

<?php if ($is_admin): ?>
    <!-- ADMIN SETTINGS -->
    <style> .nav-links { display: none; } </style>

        <div class="dashboard-container">
            
            <div class="dash-welcome">
                <div class="dash-welcome-text">
                    <h1>Admin Settings</h1>
                    <p>Manage your profile and system preferences.</p>
                </div>
            </div>

            <?php if (isset($error_msg)): ?>
                <div style="background:var(--red-bg);color:var(--red);padding:12px 16px;border-radius:8px;font-weight:600;font-size:14px;border:1px solid rgba(220,38,38,0.2);">
                    <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($success_msg)): ?>
                <div style="background:var(--green-bg);color:var(--green);padding:12px 16px;border-radius:8px;font-weight:600;font-size:14px;border:1px solid rgba(5,150,105,0.2);">
                    <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>


            <section class="settings-grid">
                <!-- Admin Profile & Security Card -->
                <div class="emp-card">
                    <div class="emp-card-head">
                        <div class="emp-card-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <h3 class="emp-card-title"><span class="dot"></span>Profile & Security</h3>
                    </div>
                    
                    <div class="emp-card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="form-group" style="margin-bottom: 24px;">
                                <div class="image-upload-group">
                                    <div class="profile-image-preview">
                                        <img name="profile_image" id="admin-preview" src="../assets/default-profile.png" alt="Profile Picture">
                                    </div>
                                    <div class="upload-input-wrapper">
                                        <label for="admin-profile-image">Change Profile Picture</label>
                                        <input type="file" id="admin-profile-image" name="profile_image" accept="image/*" onchange="previewImage(event, 'admin-preview')">
                                        <p>JPG, PNG or GIF (Max 5MB)</p>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group-row" style="margin-bottom: 16px;">
                                <div class="form-group">
                                    <label for="admin-first-name">First Name</label>
                                    <input type="text" class="form-input" id="admin-first-name" name="first_name" placeholder="First Name" value="<?php echo htmlspecialchars($_SESSION['first_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="admin-last-name">Last Name</label>
                                    <input type="text" class="form-input" id="admin-last-name" name="last_name" placeholder="Last Name" value="<?php echo htmlspecialchars($_SESSION['last_name']); ?>" required>
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom: 16px;">
                                <label for="admin-email">Email Address</label>
                                <input type="email" class="form-input" id="admin-email" name="email" placeholder="Email Address" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
                            </div>

                            <div class="form-footer">
                                <button type="submit" name="update_profile" class="btn-primary">Save Profile Settings</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="emp-card">
                    <div class="emp-card-head">
                        <div class="emp-card-icon" style="background:var(--amber-bg, #FFFBEB); color:var(--amber, #F59E0B);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <h3 class="emp-card-title"><span class="dot" style="background:linear-gradient(135deg,#D97706,#F59E0B)"></span>Password & Security</h3>
                    </div>
                    
                    <div class="emp-card-body">
                        <form action="" method="POST">
                            <div class="form-group" style="margin-bottom: 16px;">
                                <label for="current-password">Current Password</label>
                                <input type="password" class="form-input" id="current-password" name="current_password" placeholder="Enter your current password" required>
                            </div>

                            <div class="form-group-row">
                                <div class="form-group">
                                    <label for="password">New Password</label>
                                    <input type="password" class="form-input" id="password" name="new_password" placeholder="••••••••" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm-password">Confirm Password</label>
                                    <input type="password" class="form-input" id="confirm-password" name="confirm_new_password" placeholder="••••••••" required>
                                </div>
                            </div>

                            <div class="form-footer">
                                <button type="submit" name="update_password" class="btn-primary">Update Password</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Admin System Preferences Card -->
                <div class="emp-card">
                    <div class="emp-card-head">
                        <div class="emp-card-icon" style="background:#F3E8FF; color:#7E22CE;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        </div>
                        <h3 class="emp-card-title"><span class="dot" style="background:linear-gradient(135deg,#7E22CE,#9333EA)"></span>System Preferences</h3>
                    </div>

                    <div class="emp-card-body">
                        <div class="setting-list">
                            <div class="setting-item">
                                <div class="setting-item-text">
                                    <h4>Enable email alerts</h4>
                                    <p>Receive updates for payroll and employee requests.</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="setting-item">
                                <div class="setting-item-text">
                                    <h4>Share weekly reports</h4>
                                    <p>Automatically generate a summary for stakeholders.</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="setting-item">
                                <div class="setting-item-text">
                                    <h4>Dark mode</h4>
                                    <p>Switch dashboard appearance for low-light comfort.</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-footer" style="margin-top: 16px;">
                            <button type="button" class="btn-primary">Update System Preferences</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>

<?php else: ?>
    <!-- EMPLOYEE SETTINGS -->
        <div class="dashboard-container">
            <div class="dash-welcome">
                <div class="dash-welcome-text">
                    <h1>Employee Settings</h1>
                    <p>Manage your profile and account preferences</p>
                </div>
            </div>

            <?php if (isset($error_msg)): ?>
                <div style="background:var(--red-bg);color:var(--red);padding:12px 16px;border-radius:8px;font-weight:600;font-size:14px;border:1px solid rgba(220,38,38,0.2);">
                    <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($success_msg)): ?>
                <div style="background:var(--green-bg);color:var(--green);padding:12px 16px;border-radius:8px;font-weight:600;font-size:14px;border:1px solid rgba(5,150,105,0.2);">
                    <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>

            <section class="settings-grid">
                <!-- Employee Personal Information Card -->
                <div class="emp-card">
                    <div class="emp-card-head">
                        <div class="emp-card-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <h3 class="emp-card-title"><span class="dot"></span>Personal Information</h3>
                    </div>

                    <div class="emp-card-body">
                        <form action="#" method="POST" enctype="multipart/form-data">
                            <!-- Profile Image Upload -->
                            <div class="form-group" style="margin-bottom: 24px;">
                                <div class="image-upload-group">
                                    <div class="profile-image-preview">
                                        <img id="employee-preview" src="../assets/default-profile.png" alt="Profile Picture">
                                    </div>
                                    <div class="upload-input-wrapper">
                                        <label for="employee-profile-image">Change Profile Picture</label>
                                        <input type="file" id="employee-profile-image" name="profile_image" accept="image/*" onchange="previewImage(event, 'employee-preview')">
                                        <p>JPG, PNG or GIF (Max 5MB)</p>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group-row" style="margin-bottom: 16px;">
                                <div class="form-group">
                                    <label for="first-name">First Name</label>
                                    <input type="text" class="form-input" id="first-name" name="first_name" placeholder="Enter your first name" value="<?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="last-name">Last Name</label>
                                    <input type="text" class="form-input" id="last-name" name="last_name" placeholder="Enter your last name" value="<?php echo htmlspecialchars($_SESSION['last_name'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="form-group-row" style="margin-bottom: 16px;">
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-input" id="email" name="email" placeholder="Enter your email address" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="form-group-row" style="margin-bottom: 16px;">
                                <div class="form-group">
                                    <label for="department">Department</label>
                                    <input type="text" class="form-input" id="department" name="department" placeholder="Your department" value="<?php echo htmlspecialchars($employee_data['department'] ?? ''); ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label for="position">Position</label>
                                    <input type="text" class="form-input" id="position" name="position" placeholder="Your job title" value="<?php echo htmlspecialchars($employee_data['position_title'] ?? ''); ?>" disabled>
                                </div>
                            </div>

                            <div class="form-group-row">
                                <div class="form-group">
                                    <label for="employee-id">Employee ID</label>
                                    <input type="text" class="form-input" id="employee-id" name="employee_id" placeholder="Your employee ID" value="<?php echo htmlspecialchars($employee_data['employee_id'] ?? ''); ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label for="hire-date">Date of Hire</label>
                                    <input type="date" class="form-input" id="hire-date" name="hire_date" value="<?php echo htmlspecialchars($employee_data['date_of_joining'] ?? ''); ?>" disabled>
                                </div>
                            </div>

                            <div class="form-footer">
                                <button type="submit" name="update_profile" class="btn-primary">Save Profile Changes</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Employee Password & Security Card -->
                <div class="emp-card">
                    <div class="emp-card-head">
                        <div class="emp-card-icon" style="background:var(--amber-bg, #FFFBEB); color:var(--amber, #F59E0B);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <h3 class="emp-card-title"><span class="dot" style="background:linear-gradient(135deg,#D97706,#F59E0B)"></span>Password & Security</h3>
                    </div>

                    <div class="emp-card-body">
                        <form action="#" method="POST">
                            <div class="form-group" style="margin-bottom: 16px;">
                                <label for="current-password">Current Password</label>
                                <input type="password" class="form-input" id="current-password" name="current_password" placeholder="Enter your current password" required>
                            </div>

                            <div class="form-group-row">
                                <div class="form-group">
                                    <label for="new-password">New Password</label>
                                    <input type="password" class="form-input" id="new-password" name="new_password" placeholder="Enter a new password" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm-new-password">Confirm New Password</label>
                                    <input type="password" class="form-input" id="confirm-new-password" name="confirm_new_password" placeholder="Confirm your new password" required>
                                </div>
                            </div>

                            <div class="form-footer">
                                <button type="submit" name="update_password" class="btn-primary">Update Password</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Employee Preferences Card -->
                <div class="emp-card">
                    <div class="emp-card-head">
                        <div class="emp-card-icon" style="background:#F3E8FF; color:#7E22CE;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        </div>
                        <h3 class="emp-card-title"><span class="dot" style="background:linear-gradient(135deg,#7E22CE,#9333EA)"></span>Preferences</h3>
                    </div>

                    <div class="emp-card-body">
                        <div class="setting-list">
                            <div class="setting-item">
                                <div class="setting-item-text">
                                    <h4>Email Notifications</h4>
                                    <p>Receive email alerts for important updates and leave approvals.</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="setting-item">
                                <div class="setting-item-text">
                                    <h4>Attendance Reminders</h4>
                                    <p>Get reminded to mark your daily attendance.</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="setting-item">
                                <div class="setting-item-text">
                                    <h4>Dark Mode</h4>
                                    <p>Switch dashboard appearance for low-light comfort.</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-footer" style="margin-top: 16px;">
                            <button type="button" class="btn-primary">Save Preferences</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>

<?php endif; ?>

<!-- Image Preview Script -->
<script>
    function previewImage(event, previewElementId) {
        const file = event.target.files[0];
        const preview = document.getElementById(previewElementId);
        
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
</script>

</div><!-- end app-body -->

<?php include_once "../includes/footer.php"; ?>
