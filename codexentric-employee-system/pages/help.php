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

<style>
@media (max-width: 768px) {
    .support-viewport {
        padding: 10px 12px 30px 12px !important;
    }
    .support-container {
        padding: 30px 16px !important;
        border-radius: 20px !important;
    }
    .support-container h1 {
        font-size: 22px !important;
        line-height: 1.3 !important;
    }
    .wf-timeline {
        padding-left: 40px !important;
    }
    /* Fix Dash Connection Line Position on compressed width */
    .wf-line {
        left: 15px !important;
    }
    .wf-circle {
        left: -39px !important;
        width: 28px !important;
        height: 28px !important;
        font-size: 12px !important;
    }
    .wf-card {
        padding: 20px 16px !important;
    }
    .wf-card h3 {
        font-size: 15px !important;
    }
    .help-footer {
        flex-direction: column !important;
        gap: 16px !important;
        align-items: center !important;
        text-align: center !important;
    }
}
</style>


<div class="support-viewport" style="padding: 40px 32px; background: #f8fafc; min-height: calc(100vh - 120px); display: flex; justify-content: center;">
    <div class="support-container" style="background: #ffffff; border-radius: 25px; border: 1px solid #eef2f6; padding: 45px 50px; box-shadow: 0 4px 30px rgba(0,0,0,0.025); max-width: 1000px; width: 100%;">
        
        <div style="margin-bottom: 40px; text-align: center;">
            <h1 style="font-size: 28px; font-weight: 800; color: #1e293b; margin: 0 0 10px 0; letter-spacing: -0.5px;">Administrative Engine Workflow</h1>
            <p style="font-size: 15px; color: #64748b; max-width: 600px; margin: 0 auto;">Master the fundamental application cycle, from core resource configuration to terminal fiscal reporting.</p>
        </div>

        <!-- Ultimate Workflow Vertical Stepper -->
        <div class="wf-timeline" style="position: relative; padding-left: 60px;">
            
            <!-- Connecting Dash Line -->
            <div class="wf-line" style="position: absolute; top: 15px; bottom: 15px; left: 27px; border-left: 2px dashed #cbd5e1; z-index: 1;"></div>

            <!-- Step 1 -->
            <div class="wf-item" style="position: relative; margin-bottom: 45px; z-index: 2;">
                <div class="wf-circle" style="position: absolute; left: -47px; top: 0; width: 32px; height: 32px; border-radius: 50%; background: #186D55; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; box-shadow: 0 4px 10px rgba(24,109,85,0.3);">1</div>
                <div class="wf-card" style="background: #ffffff; border: 1.5px solid #eef2f6; border-radius: 20px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.01); transition: all 0.25s ease; cursor: default;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.04)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.01)';">
                    <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 12px;">
                        <div style="padding: 10px; background: rgba(24,109,85,0.1); border-radius: 12px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#186D55" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                        </div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 800; color: #1e293b; margin: 0;">Employee Acquisition</h3>
                            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #186D55; letter-spacing: 1px;">Phase One</span>
                        </div>
                    </div>
                    <p style="font-size: 13.5px; line-height: 1.6; color: #64748b; margin: 0;">Establish organizational roots by navigating to <strong>Add Employee</strong>. Secure base salary thresholds, assign dedicated positioning titles (IT, Finance, Sales), and spawn critical user account profiles required for internal authentication.</p>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="wf-item" style="position: relative; margin-bottom: 45px; z-index: 2;">
                <div class="wf-circle" style="position: absolute; left: -47px; top: 0; width: 32px; height: 32px; border-radius: 50%; background: #186D55; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; box-shadow: 0 4px 10px rgba(24,109,85,0.3);">2</div>
                <div class="wf-card" style="background: #ffffff; border: 1.5px solid #eef2f6; border-radius: 20px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.01); transition: all 0.25s ease; cursor: default;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.04)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.01)';">
                    <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 12px;">
                        <div style="padding: 10px; background: rgba(24,109,85,0.1); border-radius: 12px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#186D55" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        </div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 800; color: #1e293b; margin: 0;">Dynamic Expense Ledgering</h3>
                            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #186D55; letter-spacing: 1px;">Operational Capture</span>
                        </div>
                    </div>
                    <p style="font-size: 13.5px; line-height: 1.6; color: #64748b; margin: 0;">Daily utility management occurs via the <strong>Expenses</strong> panel. Record external disbursements (Hardware, Utilities, Rent), maintain digital transparency by attaching high-fidelity PDF invoices, and enforce mandatory audit trails globally.</p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="wf-item" style="position: relative; margin-bottom: 45px; z-index: 2;">
                <div class="wf-circle" style="position: absolute; left: -47px; top: 0; width: 32px; height: 32px; border-radius: 50%; background: #ff8c1a; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; box-shadow: 0 4px 10px rgba(255,140,26,0.3);">3</div>
                <div class="wf-card" style="background: #ffffff; border: 1.5px solid #eef2f6; border-radius: 20px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.01); transition: all 0.25s ease; cursor: default;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.04)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.01)';">
                    <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 12px;">
                        <div style="padding: 10px; background: rgba(255,140,26,0.1); border-radius: 12px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ff8c1a" stroke-width="2.5"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M12 11v6"/><path d="M12 7h.01"/></svg>
                        </div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 800; color: #1e293b; margin: 0;">Payroll Synchronization</h3>
                            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #ff8c1a; letter-spacing: 1px;">Monthly Closeout</span>
                        </div>
                    </div>
                    <p style="font-size: 13.5px; line-height: 1.6; color: #64748b; margin: 0;">Execute fiscal terminal states via <strong>Payroll Management</strong>. Select target employees, optionally append fluid Performance Bonuses or override structural deduction mechanisms, and process critical payout commands to freeze balance data permanently.</p>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="wf-item" style="position: relative; margin-bottom: 0; z-index: 2;">
                <div class="wf-circle" style="position: absolute; left: -47px; top: 0; width: 32px; height: 32px; border-radius: 50%; background: #186D55; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; box-shadow: 0 4px 10px rgba(24,109,85,0.3);">4</div>
                <div class="wf-card" style="background: #ffffff; border: 1.5px solid #eef2f6; border-radius: 20px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.01); transition: all 0.25s ease; cursor: default;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.04)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.01)';">
                    <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 12px;">
                        <div style="padding: 10px; background: rgba(24,109,85,0.1); border-radius: 12px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#186D55" stroke-width="2.5"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
                        </div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 800; color: #1e293b; margin: 0;">Integrated Intelligence Analysis</h3>
                            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #186D55; letter-spacing: 1px;">Terminal Oversight</span>
                        </div>
                    </div>
                    <p style="font-size: 13.5px; line-height: 1.6; color: #64748b; margin: 0;">Conclude cyclic protocols within <strong>Expense Reports</strong>. Generate visual aggregate visualizations for stakeholder oversight, compute cumulative Burn-Rates, and produce fully-audited chronological physical printouts for permanent safe archiving.</p>
                </div>
            </div>

        </div>

        <div class="help-footer" style="margin-top: 45px; border-top: 1px solid #f1f5f9; padding-top: 20px; display: flex; justify-content: space-between; align-items: center;">
             <div style="font-size: 12px; color: #94a3b8; display: flex; align-items: center; gap: 8px;">
                 <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                 Help Protocol V2.4
             </div>
             <a href="administrator_dashboard.php" style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: var(--brand-green); text-decoration: none; padding: 8px 20px; border-radius: 20px; background: rgba(24, 109, 85, 0.08);">
                 <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                 Return to Portal
             </a>

    </div>
</div>

<?php include_once "../includes/footer.php"; ?>
