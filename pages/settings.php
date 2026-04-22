<?php 
    session_start();

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: login.php");
        exit();
    }

    $user_role = "admin";
    $current_page = "settings";
    $extra_css = "settings";
    $title = "Admin Settings - CodeXentric";

    include_once "../includes/header.php";
?>
<style> .nav-links { display: none; } </style>

    <main class="dashboard-content">
        <header class="content-header">
            <h2>Admin Settings</h2>
        </header>

        <section class="settings-grid">
            <div class="setting-card">
                <div class="card-header">
                    <div>
                        <h3>Profile & Security</h3>
                        <p>Update admin contact details and password settings.</p>
                    </div>
                </div>

                <form class="settings-form" action="#" method="POST">
                    <div class="form-row">
                        <div class="input-group">
                            <label for="admin-name">Administrator Name</label>
                            <input type="text" id="admin-name" name="admin_name" placeholder="Ayesha Khan" value="Ayesha Khan" required>
                        </div>
                        <div class="input-group">
                            <label for="admin-email">Email Address</label>
                            <input type="email" id="admin-email" name="admin_email" placeholder="admin@codexentric.com" value="admin@codexentric.com" required>
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

<?php include_once "../includes/footer.php"; ?>
