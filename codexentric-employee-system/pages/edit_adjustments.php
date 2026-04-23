<?php
    session_start();

    if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

    $user_role = "admin"; 
    $current_page = "payroll";
    $title = "Edit Salary Adjustments - CodeXentric";
    $extra_css = "payroll"; 
    include_once "../includes/header.php"; 

    $adjustment = [
        "name"        => "Hammad Ali",
        "bank_info"   => "HBL - 123456789",
        "base_salary" => 85000,
        "allowances"  => 5000,
        "deductions"  => 4200,
        "reason"      => "Unpaid Leaves"
    ];

    // calculation for the Preview box
    $net_preview = $adjustment['base_salary'] + $adjustment['allowances'] - $adjustment['deductions'];
?>

<style>
    /* CSS Fix for overlapping input boxes */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px; /* Creates clean space between the two columns */
        margin-bottom: 20px;
    }
    
    .input-group {
        display: flex;
        flex-direction: column;
        width: 100%;
    }
    
    .input-group input, 
    .input-group select {
        width: 100%; 
        box-sizing: border-box; /* Ensures padding doesn't push the width out of bounds causing overlap */
        padding: 10px 12px;
    }

    /* Stack inputs on top of each other on smaller screens */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<main class="main-content" role="main">
    <div class="dashboard-container">
        <header class="page-header" role="banner">
            <div class="header-content">
                <h1 class="page-title">Edit Salary Adjustments</h1>
                <p class="page-subtitle">Adjust allowances, deductions, and salary components for <?php echo $adjustment['name']; ?></p>
            </div>
            <div class="header-actions">
                <a href="payroll.php" class="action-button secondary" aria-label="Return to payroll management">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Payroll
                </a>
            </div>
        </header>

        <section class="employee-info-card" aria-labelledby="employee-info-heading">
            <h2 id="employee-info-heading" class="sr-only">Employee Information</h2>
            <div class="info-summary">
                <div class="info-item">
                    <span class="info-label">Employee:</span>
                    <span class="info-value"><?php echo $adjustment['name']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Bank Info:</span>
                    <span class="info-value"><?php echo $adjustment['bank_info']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Base Salary:</span>
                    <span class="info-value">Rs <?php echo number_format($adjustment['base_salary']); ?></span>
                </div>
            </div>
        </section>

        <section class="adjustment-form-section" aria-labelledby="adjustment-form-heading">
            <h2 id="adjustment-form-heading" class="sr-only">Salary Adjustment Form</h2>
            <form action="process_adjustments.php" method="POST" class="adjustment-form">
                
                <div class="form-grid">
                    <div class="input-group">
                        <label for="allowances">Allowances (Bonuses/Benefits)</label>
                        <input type="number" id="allowances" name="allowances" value="<?php echo $adjustment['allowances']; ?>" placeholder="e.g. 5000" required>
                        <small class="input-help">This amount will be added to the base salary.</small>
                    </div>

                    <div class="input-group">
                        <label for="deductions">Deductions (Tax/Absents)</label>
                        <input type="number" id="deductions" name="deductions" value="<?php echo $adjustment['deductions']; ?>" placeholder="e.g. 4200" required>
                        <small class="input-help">This amount will be subtracted from the total.</small>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="input-group">
                        <label for="reason">Adjustment Reason</label>
                        <select id="reason" name="reason" required>
                            <?php 
                                $reasons = ["Performance Bonus", "Tax Correction", "Unpaid Leaves", "Other"];
                                foreach ($reasons as $r): 
                            ?>
                                <option value="<?php echo $r; ?>" <?php echo ($adjustment['reason'] == $r) ? 'selected' : ''; ?>>
                                    <?php echo $r; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="net_preview">Net Salary Preview</label>
                        <input type="text" id="net_preview" value="Rs <?php echo number_format($net_preview); ?>" disabled readonly>
                        <small class="input-help">Calculated automatically based on adjustments.</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="action-button primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Update Salary Breakdown
                    </button>
                </div>
            </form>
        </section>
    </div>
</main>

<?php include_once "../includes/footer.php"; ?>