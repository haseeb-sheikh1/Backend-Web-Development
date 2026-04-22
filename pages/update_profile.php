<?php 
session_start();
    $user_role = "admin"; 
    $current_page = "manage_employees"; 
    $title = "Update Employee Profile";
    $extra_css = "add_employee"; 
    include_once "../includes/header.php"; 

    $employee = [
        "id"       => "EMP-102",
        "name"     => "Hammad Ali",
        "role"     => "Senior Backend Developer",
        "status"   => "Active",
        "email"    => "hammad@gmail.com",
        "phone"    => "03XXXXXXXXX",
        "cnic"     => "XXXXX-XXXXXXX-X",
        "salary"   => "85,000",
        "bank"     => "HBL Pakistan",
        "account"  => "PK00HBL123456789",
        "tax_id"   => "NTN-882299-1"
    ];
?>

<style>
    /* ── BASE & CONTAINER ── */
    .main-content {
        padding: 30px;
        background-color: #f8f9fa; /* Light grey background */
        min-height: 100vh;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .dashboard-container {
        max-width: 900px;
        margin: 0 auto;
    }

    /* ── HEADER ── */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: #202124;
        margin: 0 0 5px 0;
    }
    .page-subtitle {
        font-size: 14px;
        color: #5f6368;
        margin: 0;
    }

    /* ── FORM SECTION ── */
    .form-section {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        overflow: hidden;
    }
    .section-header {
        padding: 20px 25px;
        border-bottom: 1px solid #e0e0e0;
        background-color: #fafafa;
    }
    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #202124;
        margin: 0;
    }
    .form-container {
        padding: 25px;
    }

    /* ── FORM ELEMENTS ── */
    .form-fieldset {
        border: none;
        padding: 0;
        margin: 0 0 30px 0;
    }
    .fieldset-legend {
        font-size: 15px;
        font-weight: 600;
        color: #1a73e8; /* Theme blue */
        margin-bottom: 15px;
        padding-bottom: 5px;
        border-bottom: 2px solid #e8eaed;
        width: 100%;
    }
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 15px;
    }
    .input-group {
        display: flex;
        flex-direction: column;
    }
    .input-group label {
        font-size: 13px;
        font-weight: 500;
        color: #3c4043;
        margin-bottom: 6px;
    }
    .input-group input, 
    .input-group select {
        padding: 10px 12px;
        font-size: 14px;
        border: 1px solid #dadce0;
        border-radius: 6px;
        outline: none;
        transition: border-color 0.2s;
        background-color: #fff;
    }
    .input-group input:focus, 
    .input-group select:focus {
        border-color: #1a73e8;
        box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2);
    }
    
    /* Read-only styling for the ID field */
    .input-group input[readonly] {
        background-color: #f1f3f4;
        color: #5f6368;
        cursor: not-allowed;
        border-color: #e8eaed;
    }
    .input-group input[readonly]:focus {
        border-color: #e8eaed;
        box-shadow: none;
    }

    /* ── BUTTONS & ACTIONS ── */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
    }
    .action-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 6px;
        text-decoration: none;
        cursor: pointer;
        transition: background-color 0.2s, color 0.2s;
        border: none;
    }
    .action-button.primary {
        background-color: #1a73e8;
        color: #ffffff;
    }
    .action-button.primary:hover {
        background-color: #1557b0;
    }
    .action-button.secondary {
        background-color: #f1f3f4;
        color: #3c4043;
        border: 1px solid #dadce0;
    }
    .action-button.secondary:hover {
        background-color: #e8eaed;
    }

    /* ── RESPONSIVE DESIGN ── */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
    }
</style>

<main class="main-content" role="main">
    <div class="dashboard-container">
        <header class="page-header" role="banner">
            <div>
                <h1 class="page-title">Update Profile: <?php echo htmlspecialchars($employee['name']); ?></h1>
                <p class="page-subtitle"><?php echo htmlspecialchars($employee['role']); ?> | ID: <?php echo htmlspecialchars($employee['id']); ?></p>
            </div>
            <a href="manage_employee.php" class="action-button secondary" aria-label="Return to employee profile">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Profile
            </a>
        </header>

        <section class="form-section" aria-labelledby="form-heading">
            <div class="section-header">
                <h2 id="form-heading" class="section-title">Edit Employee Information</h2>
            </div>
            <div class="form-container">
                <form action="process_update_employee.php" method="POST" class="request-form">
                    
                    <fieldset class="form-fieldset">
                        <legend class="fieldset-legend">Personal Information</legend>
                        <div class="form-grid">
                            <div class="input-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($employee['name']); ?>" required>
                            </div>
                            <div class="input-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="input-group">
                                <label for="phone">Phone Number</label>
                                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($employee['phone']); ?>" required>
                            </div>
                            <div class="input-group">
                                <label for="cnic">CNIC Number</label>
                                <input type="text" id="cnic" name="cnic" value="<?php echo htmlspecialchars($employee['cnic']); ?>" required>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="form-fieldset">
                        <legend class="fieldset-legend">Job & Financial Details</legend>
                        
                        <div class="form-grid">
                            <div class="input-group">
                                <label for="employee_id">Employee ID</label>
                                <input type="text" id="employee_id" name="employee_id" value="<?php echo htmlspecialchars($employee['id']); ?>" readonly>
                            </div>
                            <div class="input-group">
                                <label for="status">Employment Status</label>
                                <select id="status" name="status">
                                    <option value="Active" <?php echo ($employee['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="On Leave" <?php echo ($employee['status'] == 'On Leave') ? 'selected' : ''; ?>>On Leave</option>
                                    <option value="Terminated" <?php echo ($employee['status'] == 'Terminated') ? 'selected' : ''; ?>>Terminated</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="input-group">
                                <label for="role">Role / Designation</label>
                                <select id="role" name="role">
                                    <option value="Senior Backend Developer" <?php echo ($employee['role'] == 'Senior Backend Developer') ? 'selected' : ''; ?>>Senior Backend Developer</option>
                                    <option value="Backend Developer" <?php echo ($employee['role'] == 'Backend Developer') ? 'selected' : ''; ?>>Backend Developer</option>
                                    <option value="Frontend Developer" <?php echo ($employee['role'] == 'Frontend Developer') ? 'selected' : ''; ?>>Frontend Developer</option>
                                    <option value="UI/UX Designer" <?php echo ($employee['role'] == 'UI/UX Designer') ? 'selected' : ''; ?>>UI/UX Designer</option>
                                </select>
                            </div>
                            <div class="input-group">
                                <label for="salary">Base Salary (PKR)</label>
                                <input type="text" id="salary" name="salary" value="<?php echo htmlspecialchars($employee['salary']); ?>">
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="input-group">
                                <label for="bank">Bank Name</label>
                                <input type="text" id="bank" name="bank" value="<?php echo htmlspecialchars($employee['bank']); ?>">
                            </div>
                            <div class="input-group">
                                <label for="account">Account Number (IBAN)</label>
                                <input type="text" id="account" name="account" value="<?php echo htmlspecialchars($employee['account']); ?>">
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="input-group" style="grid-column: 1 / 2;">
                                <label for="tax_id">Tax ID (NTN)</label>
                                <input type="text" id="tax_id" name="tax_id" value="<?php echo htmlspecialchars($employee['tax_id']); ?>">
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-actions">
                        <a href="manage_employee.php" class="action-button secondary">
                            Cancel
                        </a>
                        <button type="submit" class="action-button primary">Update Employee Details</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</main>

<?php include_once "../includes/footer.php"; ?>