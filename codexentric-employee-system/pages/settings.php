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

    
    // image upload
    require_once "../pages/Database.php";
    require_once "../pages/Users.php";
    $db = new Database();
    $user = new Users($db->getConnection());
    
    // Setting the email so the object knows who to update
    $user->email = $_SESSION['email']; 

    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $result = $user->updateProfileImage($_FILES['profile_image']);
        
        if ($result === true) {
            header("Location: settings.php?success=1");
            exit();
        } else {
            // $result contains the error message string
            echo "<div class='error-message'>" . $result . "</div>";
        }
    }

    include_once "../includes/header.php";
?>

<div class="app-body">
    <?php include_once "../includes/sidebar.php"; ?>

<?php if ($is_admin): ?>
    <!-- ADMIN SETTINGS -->
    <style> .nav-links { display: none; } </style>

    <main class="dashboard-content">
        <header class="content-header">
            <h2>Admin Settings</h2>
        </header>

        <section class="settings-grid">
            <!-- Admin Profile & Security Card -->
            <div class="setting-card">
                <div class="card-header">
                    <div>
                        <h3>Profile & Security</h3>
                        <p>Update admin contact details and password settings.</p>
                    </div>
                </div>

                <form class="settings-form" action="" method="POST" enctype="multipart/form-data">
                    <!-- Profile Image Upload -->
                    <div class="form-row" style="margin-bottom: 20px;">
                        <div class="image-upload-group">
                            <div class="profile-image-preview">
                                <img name="profile_image" src="../assets/default-profile.png" alt="Profile Picture" style="width: 120px; height: 120px; border-radius: 8px; object-fit: cover; display: block;">
                            </div>
                            <div class="upload-input-wrapper">
                                <label for="admin-profile-image">Change Profile Picture</label>
                                <input type="file" id="admin-profile-image" name="profile_image" accept="image/*" onchange="previewImage(event, 'admin-preview')">
                                <p style="font-size: 12px; color: #666; margin-top: 5px;">JPG, PNG or GIF (Max 5MB)</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="input-group">
                            <label for="admin-name">Administrator Name</label>
                            <input type="text" id="admin-name" name="admin_name" placeholder="<?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?>" value="<?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?>" required>
                        </div>
                        <div class="input-group">
                            <label for="admin-email">Email Address</label>
                            <input type="email" id="admin-email" name="admin_email" placeholder="<?php echo $_SESSION['email']; ?>" value="<?php echo $_SESSION['email']; ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="input-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="••••••••" required>
                        </div>
                        <div class="input-group">
                            <label for="confirm-password">Confirm Password</label>
                            <input type="password" id="confirm-password" name="confirm_password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn-primary">Save Profile Settings</button>
                    </div>
                </form>
            </div>

            <!-- Admin System Preferences Card -->
            <div class="setting-card">
                <div class="card-header">
                    <div>
                        <h3>System Preferences</h3>
                        <p>Control defaults and notification options for the admin panel.</p>
                    </div>
                </div>

                <div class="setting-list">
                    <div class="setting-item">
                        <div>
                            <h4>Enable email alerts</h4>
                            <p>Receive updates for payroll and employee requests.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="setting-item">
                        <div>
                            <h4>Share weekly reports</h4>
                            <p>Automatically generate a summary for stakeholders.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="setting-item">
                        <div>
                            <h4>Dark mode</h4>
                            <p>Switch dashboard appearance for low-light comfort.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <div class="form-footer">
                    <button type="button" class="btn-primary">Update System Preferences</button>
                </div>
            </div>
        </section>
    </main>
</div>

<?php else: ?>
    <!-- EMPLOYEE SETTINGS -->
    <main class="main-content" role="main">
        <div class="dashboard-container">
            <!-- Page Header -->
            <header class="page-header" role="banner">
                <div class="header-content">
                    <h1 class="page-title">Settings</h1>
                    <p class="page-subtitle">Manage your profile and account preferences</p>
                </div>
            </header>

            <section class="settings-grid">
                <!-- Employee Personal Information Card -->
                <div class="setting-card">
                    <div class="card-header">
                        <div>
                            <h3>Personal Information</h3>
                            <p>Update your profile details and personal information.</p>
                        </div>
                    </div>

                    <form class="settings-form" action="#" method="POST" enctype="multipart/form-data">
                        <!-- Profile Image Upload -->
                        <div class="form-row" style="margin-bottom: 20px;">
                            <div class="image-upload-group">
                                <div class="profile-image-preview">
                                    <img id="employee-preview" src="../assets/default-profile.png" alt="Profile Picture" style="width: 120px; height: 120px; border-radius: 8px; object-fit: cover; display: block;">
                                </div>
                                <div class="upload-input-wrapper">
                                    <label for="employee-profile-image">Change Profile Picture</label>
                                    <input type="file" id="employee-profile-image" name="profile_image" accept="image/*" onchange="previewImage(event, 'employee-preview')">
                                    <p style="font-size: 12px; color: #666; margin-top: 5px;">JPG, PNG or GIF (Max 5MB)</p>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-group">
                                <label for="first-name">First Name</label>
                                <input type="text" id="first-name" name="first_name" placeholder="Enter your first name" value="<?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?>" required>
                            </div>
                            <div class="input-group">
                                <label for="last-name">Last Name</label>
                                <input type="text" id="last-name" name="last_name" placeholder="Enter your last name" value="<?php echo htmlspecialchars($_SESSION['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" placeholder="Enter your email address" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                            </div>
                            <div class="input-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-group">
                                <label for="department">Department</label>
                                <input type="text" id="department" name="department" placeholder="Your department" disabled>
                            </div>
                            <div class="input-group">
                                <label for="position">Position</label>
                                <input type="text" id="position" name="position" placeholder="Your job title" disabled>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-group">
                                <label for="employee-id">Employee ID</label>
                                <input type="text" id="employee-id" name="employee_id" placeholder="Your employee ID" disabled>
                            </div>
                            <div class="input-group">
                                <label for="hire-date">Date of Hire</label>
                                <input type="date" id="hire-date" name="hire_date" disabled>
                            </div>
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="btn-primary">Save Profile Changes</button>
                        </div>
                    </form>
                </div>

                <!-- Employee Password & Security Card -->
                <div class="setting-card">
                    <div class="card-header">
                        <div>
                            <h3>Password & Security</h3>
                            <p>Update your password and manage account security settings.</p>
                        </div>
                    </div>

                    <form class="settings-form" action="#" method="POST">
                        <div class="form-row">
                            <div class="input-group">
                                <label for="current-password">Current Password</label>
                                <input type="password" id="current-password" name="current_password" placeholder="Enter your current password" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-group">
                                <label for="new-password">New Password</label>
                                <input type="password" id="new-password" name="new_password" placeholder="Enter a new password" required>
                            </div>
                            <div class="input-group">
                                <label for="confirm-new-password">Confirm New Password</label>
                                <input type="password" id="confirm-new-password" name="confirm_new_password" placeholder="Confirm your new password" required>
                            </div>
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="btn-primary">Update Password</button>
                        </div>
                    </form>
                </div>

                <!-- Employee Preferences Card -->
                <div class="setting-card">
                    <div class="card-header">
                        <div>
                            <h3>Preferences</h3>
                            <p>Control notification and display options.</p>
                        </div>
                    </div>

                    <div class="setting-list">
                        <div class="setting-item">
                            <div>
                                <h4>Email Notifications</h4>
                                <p>Receive email alerts for important updates and leave approvals.</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div>
                                <h4>Attendance Reminders</h4>
                                <p>Get reminded to mark your daily attendance.</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div>
                                <h4>Dark Mode</h4>
                                <p>Switch dashboard appearance for low-light comfort.</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="button" class="btn-primary">Save Preferences</button>
                    </div>
                </div>
            </section>
        </div>
    </main>

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

<!-- Image Upload Styling -->
<style>
    .image-upload-group {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 15px 0;
    }

    .profile-image-preview {
        flex-shrink: 0;
    }

    .upload-input-wrapper {
        flex: 1;
    }

    .upload-input-wrapper label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }

    .upload-input-wrapper input[type="file"] {
        display: block;
        padding: 8px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
    }

    .upload-input-wrapper input[type="file"]:hover {
        border-color: #1559B5;
    }

    .upload-input-wrapper p {
        font-size: 12px;
        color: #999;
        margin-top: 5px;
    }
</style>

</div><!-- end app-body -->

<?php include_once "../includes/footer.php"; ?>
