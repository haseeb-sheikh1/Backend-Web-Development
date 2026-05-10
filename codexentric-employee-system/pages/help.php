<?php
session_start();

// Role Protection
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != '1') {
    header("Location: employee_dashboard.php");
    exit();
}

$current_page = "help";
$title = "Help & System Overview";
$extra_css = "help"; // We'll create this CSS

require_once '../pages/database.php';
include_once "../includes/header.php";
include_once "../includes/sidebar.php";
?>


<div class="support-viewport" style="padding: 40px 32px; background: #f8fafc; min-height: calc(100vh - 120px); display: flex; justify-content: center;">
    <div class="support-container" style="background: #ffffff; border-radius: 25px; border: 1px solid #eef2f6; padding: 45px 50px; box-shadow: 0 4px 30px rgba(0,0,0,0.025); max-width: 1000px; width: 100%;">
        
        <h1 style="font-size: 20px; font-weight: 700; color: #334155; margin: 0 0 16px 0; letter-spacing: -0.3px;">Getting Started with CodeXentric HRM</h1>
        
        <div style="width: 100%; height: 1px; background: #f1f5f9; margin-bottom: 28px;"></div>

        <p style="font-size: 14.5px; color: #64748b; line-height: 1.8; margin-bottom: 24px; font-weight: 500;">
            Learning how to use a new specialized resource engine can be challenging. At CodeXentric, we are committed to providing you with the necessary framework and functional automation required to fully utilize the application, thereby allowing you to quickly and efficiently manage your dynamic organizational workflows.
        </p>

        <p style="font-size: 14.5px; color: #64748b; line-height: 1.8; margin-bottom: 35px; font-weight: 500;">
            The following technical assistance conduit is available to help reinforce your system deployment:
        </p>

        <div style="display: flex; align-items: flex-start; gap: 22px;">
            <!-- High-Fidelity Recreation of Reference Double-Bubble Icon -->
            <div style="position: relative; width: 56px; height: 56px; flex-shrink: 0;">
                <!-- Orange Question Bubble -->
                <div style="position: absolute; top: 0; left: 0; width: 42px; height: 42px; background: #ff8c1a; border-radius: 50% 50% 50% 8px; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#ffffff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" style="display:none"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <!-- Green Followup Bubble -->
                <div style="position: absolute; bottom: 2px; right: 0px; width: 26px; height: 26px; background: #22c55e; border: 2.5px solid #ffffff; border-radius: 50% 50% 8px 50%;">
                </div>
            </div>
            
            <div>
                <h3 style="font-size: 17px; font-weight: 700; color: #334155; margin: 0 0 8px 0;">Direct Customer Support</h3>
                <p style="font-size: 14.5px; color: #64748b; line-height: 1.8; margin: 0; font-weight: 500;">
                    Should you experience any deployment blockers, please do not hesitate to contact our technical intervention unit on 
                    <a href="mailto:support@codexentric.com" style="color: #ff7b1d; font-weight: 700; text-decoration: none; border-bottom: 1.5px solid transparent; transition: border 0.2s;" onmouseover="this.style.borderBottomColor='#ff7b1d'" onmouseout="this.style.borderBottomColor='transparent'">support@codexentric.com</a>. 
                    We will be delighted to provide rapid assistance.
                </p>
            </div>
        </div>

        <div style="margin-top: 45px; border-top: 1px solid #f1f5f9; padding-top: 20px; text-align: right;">
             <a href="administrator_dashboard.php" style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: var(--brand-green); text-decoration: none; padding: 8px 20px; border-radius: 20px; background: rgba(24, 109, 85, 0.08);">
                 <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                 Return to Portal
             </a>
        </div>

    </div>
</div>

<?php include_once "../includes/footer.php"; ?>
