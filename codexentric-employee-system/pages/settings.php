<?php 
    session_start();
    
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }
    
    // Determine user role
    $is_admin = isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
    $current_page = "settings";
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

<style>
/* ── Profile Theme Styles (from update_profile.php) ── */
.profile-container {
    display: flex;
    align-items: flex-start;
    gap: 30px;
    padding: 0 0 20px 0;
    max-width: 1200px;
    margin: 0 auto;
}

.profile-sidebar {
    width: 280px;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    align-self: flex-start;
    position: sticky;
    top: 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
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
    position: relative;
}

.profile-avatar-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.profile-sidebar h2 {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    text-align: center;
    margin: 0 0 5px 0;
}

.profile-sidebar p {
    font-size: 11px;
    color: #64748b;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
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

.profile-main {
    flex: 1;
}

.section-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    margin-bottom: 16px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    scroll-margin-top: 20px;
}

.section-header {
    padding: 14px 20px;
    border-bottom: 1px solid #f1f5f9;
}

.section-header h3 {
    font-size: 14px;
    font-weight: 700;
    color: #334155;
    margin: 0;
}

.section-body {
    padding: 16px 20px;
}

/* ── Modern Grid & Forms ── */
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

.modern-input {
    width: 100%;
    height: 38px;
    padding: 0 20px; /* Extended horizontal padding for capsule shape */
    border: 1px solid #e2e8f0;
    border-radius: 20px; /* Pill shaped fields like reference */
    font-size: 13.5px;
    color: #1e293b;
    background: #fff;
    transition: all 0.2s;
    font-family: inherit;
}

.modern-input:focus {
    border-color: var(--brand-green);
    box-shadow: 0 0 0 3px rgba(24, 109, 85, 0.1);
    outline: none;
}

.modern-input:disabled {
    background: #f8fafc;
    cursor: not-allowed;
    border-style: dashed;
}

.modern-btn-primary {
    background: var(--brand-green);
    color: #fff;
    border: none;
    border-radius: 25px;
    padding: 12px 28px; /* Widened padding slightly for nicer pill balance */
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

/* ── Toggle Switch ── */
.setting-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    border-bottom: 1px solid #f1f5f9;
}

.setting-item:last-child {
    border-bottom: none;
}

.setting-item-text h4 {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 2px;
}

.setting-item-text p {
    font-size: 12.5px;
    color: #64748b;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #e2e8f0;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: var(--brand-green);
}

input:checked + .slider:before {
    transform: translateX(20px);
}

.alert {
    padding: 14px 20px;
    border-radius: 10px;
    margin-bottom: 24px;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-danger {
    background: #fef2f2;
    border: 1px solid #fee2e2;
    color: #991b1b;
}

.alert-success {
    background: #f0fdf4;
    border: 1px solid #dcfce7;
    color: #166534;
}

/* Page Layout Fixes */
.app-right {
    background: #f6f8fb;
}

/* ── Responsiveness (Mobile View tabs & side-by-side avatar) ── */
@media (max-width: 768px) {
    .profile-container {
        flex-direction: column;
        gap: 20px;
        padding: 0 15px;
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

    <?php if (isset($error_msg) || isset($success_msg)): ?>
    <div>
        <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($success_msg)): ?>
            <div class="alert alert-success">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <?php echo htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="profile-container">
        
        <!-- Sidebar -->
        <aside class="profile-sidebar">
            <div class="profile-avatar-wrapper">
                <?php 
                    $profile_img = !empty($_SESSION['profile_image']) ? "../assets/uploads/" . $_SESSION['profile_image'] : "../assets/default-profile.png";
                ?>
                <img id="profile-preview" src="<?php echo $profile_img; ?>" alt="Profile Picture">
            </div>
            <div class="profile-meta-info">
                <h2><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></h2>
                <p><?php echo $is_admin ? "Administrator" : htmlspecialchars($employee_data['position_title'] ?? "Employee"); ?></p>
            </div>
            
            <nav class="profile-nav">
                <a href="#profile-section" class="profile-nav-link active">Personal Settings</a>
                <a href="#password-section" class="profile-nav-link">Password & Security</a>
                <a href="#preferences-section" class="profile-nav-link">System Preferences</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="profile-main">
            
            <!-- SECTION 1: PROFILE DETAILS -->
            <div class="section-card" id="profile-section">
                <div class="section-header">
                    <h3>Personal Settings</h3>
                </div>
                <div class="section-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="form-field full">
                            <label>Profile Image</label>
                            <div style="display:flex; align-items:center; gap:14px;">
                                <input type="file" name="profile_image" accept="image/*" class="modern-input" style="padding-top:8px;" onchange="previewImage(event, 'profile-preview')">
                                <p style="font-size:11px; color:#64748b;">Recommend 500x500px JPG/PNG</p>
                            </div>
                        </div>

                        <div class="modern-grid">
                            <div class="form-field">
                                <label>First Name</label>
                                <input type="text" name="first_name" class="modern-input" value="<?php echo htmlspecialchars($_SESSION['first_name']); ?>" required>
                            </div>
                            <div class="form-field">
                                <label>Last Name</label>
                                <input type="text" name="last_name" class="modern-input" value="<?php echo htmlspecialchars($_SESSION['last_name']); ?>" required>
                            </div>
                            <div class="form-field full">
                                <label>Email Address</label>
                                <input type="email" name="email" class="modern-input" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
                            </div>
                            <?php if (!$is_admin): ?>
                                <div class="form-field">
                                    <label>Employee ID</label>
                                    <input type="text" class="modern-input" value="CEMS-<?php echo htmlspecialchars($_SESSION['user_id']); ?>" disabled>
                                </div>
                                <div class="form-field">
                                    <label>Department</label>
                                    <input type="text" class="modern-input" value="<?php echo htmlspecialchars($employee_data['department'] ?? ''); ?>" disabled>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="display:flex; justify-content:flex-end; margin-top:10px;">
                            <button type="submit" name="update_profile" class="modern-btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                Save Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SECTION 2: PASSWORD SECURITY -->
            <div class="section-card" id="password-section">
                <div class="section-header">
                    <h3>Password & Security</h3>
                </div>
                <div class="section-body">
                    <form action="" method="POST">
                        <div class="form-field full">
                            <label>Current Password</label>
                            <input type="password" name="current_password" class="modern-input" placeholder="Enter current password" required>
                        </div>
                        <div class="modern-grid">
                            <div class="form-field">
                                <label>New Password</label>
                                <input type="password" name="new_password" class="modern-input" placeholder="New password" required>
                            </div>
                            <div class="form-field">
                                <label>Confirm Password</label>
                                <input type="password" name="confirm_new_password" class="modern-input" placeholder="Confirm new password" required>
                            </div>
                        </div>
                        <div style="display:flex; justify-content:flex-end; margin-top:10px;">
                            <button type="submit" name="update_password" class="modern-btn-primary" style="background:#f59e0b;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SECTION 3: SYSTEM PREFERENCES -->
            <div class="section-card" id="preferences-section">
                <div class="section-header">
                    <h3>System Preferences</h3>
                </div>
                <div class="section-body">
                    <div class="setting-item">
                        <div class="setting-item-text">
                            <h4>Email Alerts</h4>
                            <p>Receive notifications for payroll processing and announcements.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="setting-item">
                        <div class="setting-item-text">
                            <h4>Attendance Tracking</h4>
                            <p>Enable automatic reminders for daily check-in/out.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="setting-item">
                        <div class="setting-item-text">
                            <h4>Interface Theme</h4>
                            <p>Switch between light and high-contrast appearance.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div style="display:flex; justify-content:flex-end; margin-top:20px;">
                        <button type="button" class="modern-btn-primary" style="background:#6366f1;">Apply Preferences</button>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
    function previewImage(event, previewId) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById(previewId);
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    // Scroll handling and mobile tabs switcher
    document.addEventListener('DOMContentLoaded', function() {
        const links = document.querySelectorAll('.profile-nav-link');
        const sections = document.querySelectorAll('.section-card');

        // Scroll listener for Desktop Scroll-Spy
        window.addEventListener('scroll', function() {
            if (window.innerWidth > 768) {
                let current = '';
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    if (pageYOffset >= sectionTop - 120) {
                        current = section.getAttribute('id');
                    }
                });

                links.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href').substring(1) === current) {
                        link.classList.add('active');
                    }
                });
            }
        });

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

<?php include_once "../includes/footer.php"; ?>
