<?php
session_start();

// Role Protection
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$current_page = "help";
$title = "Help & System Overview";
$extra_css = "help"; // We'll create this CSS

require_once '../pages/database.php';
include_once "../includes/header.php";
include_once "../includes/sidebar.php";
?>

<div class="help-container">
    <div class="help-header">
        <div class="header-content">
            <h1>System Help Center</h1>
            <p>Welcome to CodeXentric HRM. Here's a complete guide on how to manage your organization effectively.</p>
        </div>
        <div class="header-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
    </div>

    <div class="help-grid">
        <!-- 1. Getting Started -->
        <div class="help-card animate-fade">
            <div class="card-icon" style="background: #e0f2fe; color: #0369a1;">
                <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            <h3>Getting Started</h3>
            <p>The dashboard provides a bird's-eye view of your organization, including active employees, pending expenses, and overall burn rate.</p>
            <ul>
                <li><strong>Dashboard:</strong> Monitor key performance indicators (KPIs).</li>
                <li><strong>Profile:</strong> Keep your personal and professional details updated.</li>
            </ul>
        </div>

        <!-- 2. Employee Management -->
        <div class="help-card animate-fade" style="animation-delay: 0.1s;">
            <div class="card-icon" style="background: #f0fdf4; color: #15803d;">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h3>Employee Management</h3>
            <p>Manage your workforce with ease. From onboarding new hires to tracking their performance.</p>
            <ul>
                <li><strong>Add Employee:</strong> Use the "Add Employee" form to register new staff.</li>
                <li><strong>Manage List:</strong> View, edit, or remove employee records.</li>
                <li><strong>Status:</strong> Track "Active", "On Leave", or "Terminated" status.</li>
            </ul>
        </div>

        <!-- 3. Payroll & Salaries -->
        <div class="help-card animate-fade" style="animation-delay: 0.2s;">
            <div class="card-icon" style="background: #fff7ed; color: #c2410c;">
                <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            </div>
            <h3>Payroll & Salaries</h3>
            <p>Automate your salary processing. Generate invoices and track payments accurately.</p>
            <ul>
                <li><strong>Process Salary:</strong> Generate monthly payroll for all active employees.</li>
                <li><strong>Salary Invoices:</strong> View and print professional PDF-style invoices.</li>
                <li><strong>History:</strong> Maintain a detailed log of all past salary disbursements.</li>
            </ul>
        </div>

        <!-- 4. Expense Tracking -->
        <div class="help-card animate-fade" style="animation-delay: 0.3s;">
            <div class="card-icon" style="background: #fdf2f8; color: #be185d;">
                <svg viewBox="0 0 24 24"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/><path d="M4 6v12a2 2 0 0 0 2 2h14v-4"/><path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"/></svg>
            </div>
            <h3>Expense Tracking</h3>
            <p>Monitor company spending. Record bills, upload receipts, and categorize outflows.</p>
            <ul>
                <li><strong>Dynamic Categories:</strong> Add new expense types on the fly.</li>
                <li><strong>Receipts:</strong> Securely upload and preview bill attachments.</li>
                <li><strong>Analysis:</strong> View distribution charts and outflow trends.</li>
            </ul>
        </div>

        <!-- 5. Reports & Analytics -->
        <div class="help-card animate-fade" style="animation-delay: 0.4s;">
            <div class="card-icon" style="background: #f5f3ff; color: #6d28d9;">
                <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
            <h3>Reports & Analytics</h3>
            <p>Deep dive into your data. Generate monthly and yearly summaries for auditing.</p>
            <ul>
                <li><strong>Financial Reports:</strong> Get detailed monthly/yearly expense summaries.</li>
                <li><strong>Audit Trails:</strong> Track all financial activities within the system.</li>
                <li><strong>Export:</strong> Print or download reports for physical archiving.</li>
            </ul>
        </div>

        <!-- 6. System Settings -->
        <div class="help-card animate-fade" style="animation-delay: 0.5s;">
            <div class="card-icon" style="background: #ecfdf5; color: #047857;">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            </div>
            <h3>System Settings</h3>
            <p>Configure the system to your preference. Manage users, roles, and global parameters.</p>
            <ul>
                <li><strong>Branding:</strong> (Coming Soon) Customize logos and brand colors.</li>
                <li><strong>Security:</strong> Update your credentials and manage access levels.</li>
            </ul>
        </div>
    </div>

    <div class="help-footer">
        <p>Need further assistance? Contact your system administrator or technical support team.</p>
        <a href="administrator_dashboard.php" class="btn-back">Back to Dashboard</a>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>
