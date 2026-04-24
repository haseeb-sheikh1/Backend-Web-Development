<?php 
    session_start();

    if (!isset($_SESSION['email'])) {
        header("Location: login.php");
        exit();
    }

    $current_page = "employee_settings";
    $extra_css = "settings";
    $title = "Employee Settings - CodeXentric";

    include_once "../includes/header.php";
    include_once "../includes/sidebar.php";
?>

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
            <!-- Profile & Personal Information Card -->
            <div class="setting-card">
                <div class="card-header">
                    <div>
                        <h3>Personal Information</h3>
                        <p>Update your profile details and personal information.</p>
                    </div>
                </div>

                <form class="settings-form" action="#" method="POST">
                    <div class="form-row">
                        <div class="input-group">
                            <label for="first-name">First Name</label>
                            <input type="text" id="first-name" name="first_name" placeholder="Enter your first name" required>
                        </div>
                        <div class="input-group">
                            <label for="last-name">Last Name</label>
                            <input type="text" id="last-name" name="last_name" placeholder="Enter your last name" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="input-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email address" required>
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

            <!-- Password & Security Card -->
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

            <!-- Preferences Card -->
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

<?php include_once "../includes/footer.php"; ?>
