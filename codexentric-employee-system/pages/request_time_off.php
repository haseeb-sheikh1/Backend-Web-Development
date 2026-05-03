<?php
    session_start();

    // Check if the session variable we set in login.php exists
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        // If not logged in, kick them back to the login UI
        header("Location: login.php");
        exit();
    }

    $user_name = "Hammad";
    $user_role = "Employee";
    $current_page = "request_time_off";
    $user_dept = "Software Engineering";
    $pto_balance = 14;

    // Load styles
    $extra_css = "time_off";
    $title = "Request Time Off - CodeXentric";
    include_once "../includes/header.php";
?>

<div class="dashboard-container">
        <!-- Page Header -->
        <header class="page-header" role="banner">
            <div class="header-content">
                <h1 class="page-title">Request Time Off</h1>
                <p class="page-subtitle">Submit a request for vacation, sick leave, or personal time.</p>
            </div>
            <div class="header-actions">
                <a href="employee_dashboard.php" class="action-button secondary" aria-label="Return to employee dashboard">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </header>

        <!-- PTO Overview -->
        <section class="pto-overview" aria-labelledby="pto-heading">
            <h2 id="pto-heading" class="sr-only">PTO Overview</h2>
            <div class="stats-grid">
                <article class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </div>
                        <h3 class="stat-title">Available PTO</h3>
                    </div>
                    <div class="stat-value"><?php echo $pto_balance; ?> Days</div>
                    <p class="stat-description">Days remaining</p>
                </article>
                <article class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon pending">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </div>
                        <h3 class="stat-title">Pending Requests</h3>
                    </div>
                    <div class="stat-value">2</div>
                    <p class="stat-description">Awaiting approval</p>
                </article>
                <article class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon approved">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 12l2 2 4-4"/>
                                <path d="M21 12c.552 0 1-.448 1-1V5c0-.552-.448-1-1-1H3c-.552 0-1 .448-1 1v6c0 .552.448 1 1 1"/>
                                <path d="M3 12v7c0 .552.448 1 1 1h16c.552 0 1-.448 1-1v-7"/>
                            </svg>
                        </div>
                        <h3 class="stat-title">Approved This Year</h3>
                    </div>
                    <div class="stat-value">6</div>
                    <p class="stat-description">Days approved</p>
                </article>
                <article class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon used">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 11H5a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h2.5"/>
                                <path d="M9 11V9a3 3 0 0 1 6 0v2"/>
                                <path d="M13 11h4a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-2.5"/>
                                <path d="M22 18h-7l-3 3-3-3H2"/>
                            </svg>
                        </div>
                        <h3 class="stat-title">Used This Year</h3>
                    </div>
                    <div class="stat-value">8</div>
                    <p class="stat-description">Days taken</p>
                </article>
            </div>
        </section>

        <!-- Request Form -->
        <section class="request-form-section" aria-labelledby="form-heading">
            <div class="section-header">
                <h2 id="form-heading" class="section-title">Submit New Request</h2>
            </div>
            <div class="form-container">
                <form id="timeOffForm" onsubmit="handleSubmit(event)" class="request-form">
                    <div class="form-grid">
                        <div class="input-group">
                            <label for="leave_type">Leave Type *</label>
                            <select id="leave_type" name="leave_type" required>
                                <option value="">Select a type</option>
                                <option value="Vacation">Vacation</option>
                                <option value="Sick Leave">Sick Leave</option>
                                <option value="Personal Leave">Personal Leave</option>
                                <option value="Bereavement">Bereavement</option>
                                <option value="Maternity/Paternity">Maternity/Paternity</option>
                                <option value="Unpaid Leave">Unpaid Leave</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="duration">Duration *</label>
                            <select id="duration" name="duration" required>
                                <option value="">Select duration</option>
                                <option value="Full Day">Full Day</option>
                                <option value="Half Day (Morning)">Half Day (Morning)</option>
                                <option value="Half Day (Afternoon)">Half Day (Afternoon)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="input-group">
                            <label for="start_date">Start Date *</label>
                            <input type="date" id="start_date" name="start_date" required>
                        </div>
                        <div class="input-group">
                            <label for="end_date">End Date *</label>
                            <input type="date" id="end_date" name="end_date" required>
                        </div>
                    </div>

                    <div class="input-group full-width">
                        <label for="reason">Reason / Comments</label>
                        <textarea id="reason" name="reason" rows="4" placeholder="Please provide any additional details..."></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="action-button secondary" onclick="resetForm()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
                                <path d="M21 3v5h-5"/>
                                <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
                                <path d="M8 16H3v5"/>
                            </svg>
                            Clear Form
                        </button>
                        <button type="submit" class="action-button primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Submit Request
                        </button>
                    </div>
                </form>

                <div id="successMessage" class="alert-message success" style="display: none;" role="alert" aria-live="polite">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M9 12l2 2 4-4"/>
                    </svg>
                    Your time off request has been submitted successfully!
                </div>
            </div>
        </section>

        <!-- Pending Requests -->
        <section class="requests-section" aria-labelledby="pending-heading">
            <div class="section-header">
                <h2 id="pending-heading" class="section-title">Pending Requests</h2>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Requested On</th>
                            <th>Leave Type</th>
                            <th>Duration</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="pendingTableBody">
                        <tr>
                            <td><strong>Apr 18, 2026</strong></td>
                            <td>Vacation</td>
                            <td>Full Day</td>
                            <td>May 5, 2026</td>
                            <td>May 8, 2026</td>
                            <td><span class="badge pending">Pending</span></td>
                            <td><a href="#" class="action-link" onclick="cancelRequest(event, 1)">Cancel</a></td>
                        </tr>
                        <tr>
                            <td><strong>Apr 10, 2026</strong></td>
                            <td>Sick Leave</td>
                            <td>Full Day</td>
                            <td>Apr 22, 2026</td>
                            <td>Apr 22, 2026</td>
                            <td><span class="badge pending">Pending</span></td>
                            <td><a href="#" class="action-link" onclick="cancelRequest(event, 2)">Cancel</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Approved Requests -->
        <section class="requests-section" aria-labelledby="approved-heading">
            <div class="section-header">
                <h2 id="approved-heading" class="section-title">Approved Requests</h2>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
            <thead>
                <tr>
                    <th>Requested On</th>
                    <th>Leave Type</th>
                    <th>Duration</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Approved On</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="approvedTableBody">
                <tr>
                    <td><strong>Mar 25, 2026</strong></td>
                    <td>Vacation</td>
                    <td>Full Day</td>
                    <td>Apr 3, 2026</td>
                    <td>Apr 5, 2026</td>
                    <td>Mar 27, 2026</td>
                    <td><span class="badge approved">Approved</span></td>
                </tr>
                <tr>
                    <td><strong>Mar 15, 2026</strong></td>
                    <td>Personal Leave</td>
                    <td>Half Day (Morning)</td>
                    <td>Mar 20, 2026</td>
                    <td>Mar 20, 2026</td>
                    <td>Mar 16, 2026</td>
                    <td><span class="badge approved">Approved</span></td>
                </tr>
                <tr>
                    <td><strong>Feb 28, 2026</strong></td>
                    <td>Sick Leave</td>
                    <td>Full Day</td>
                    <td>Mar 10, 2026</td>
                    <td>Mar 10, 2026</td>
                    <td>Mar 1, 2026</td>
                    <td><span class="badge approved">Approved</span></td>
                </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Rejected Requests -->
        <section class="requests-section" aria-labelledby="rejected-heading">
            <div class="section-header">
                <h2 id="rejected-heading" class="section-title">Rejected Requests</h2>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
            <thead>
                <tr>
                    <th>Requested On</th>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>Rejected On</th>
                    <th>Reason</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="rejectedTableBody">
                <tr>
                    <td><strong>Feb 10, 2026</strong></td>
                    <td>Vacation</td>
                    <td>Feb 20, 2026</td>
                    <td>Feb 12, 2026</td>
                    <td>Insufficient coverage during this period</td>
                    <td><span class="badge rejected">Rejected</span></td>
                </tr>
                </tbody>
            </table>
            </div>
        </section>
</div>

<?php include_once "../includes/footer.php"; ?>

<script>
    function handleSubmit(event) {
        event.preventDefault();
        
        const form = document.getElementById('timeOffForm');
        const successMsg = document.getElementById('successMessage');
        
        // Get form values for display
        const leaveType = form.querySelector('select[name="leave_type"]').value;
        const startDate = form.querySelector('input[name="start_date"]').value;
        const endDate = form.querySelector('input[name="end_date"]').value;
        const duration = form.querySelector('select[name="duration"]').value;
        
        // Validate that end date is not before start date
        if (new Date(endDate) < new Date(startDate)) {
            alert('End date cannot be before start date');
            return;
        }
        
        // Show success message
        successMsg.style.display = 'block';
        
        // Add new row to pending table
        const pendingTable = document.getElementById('pendingTableBody');
        const newRow = document.createElement('tr');
        const today = new Date().toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
        
        newRow.innerHTML = `
            <td><strong>${today}</strong></td>
            <td>${leaveType}</td>
            <td>${duration}</td>
            <td>${formatDate(startDate)}</td>
            <td>${formatDate(endDate)}</td>
            <td><span class="badge pending">Pending</span></td>
            <td><a href="#" class="action-link" onclick="cancelRequest(event, 0)">Cancel</a></td>
        `;
        
        pendingTable.insertBefore(newRow, pendingTable.firstChild);
        
        // Reset form
        form.reset();
        
        // Hide success message after 3 seconds
        setTimeout(() => {
            successMsg.style.display = 'none';
        }, 3000);
        
        // Scroll to success message
        successMsg.scrollIntoView({ behavior: 'smooth' });
    }
    
    function resetForm() {
        document.getElementById('timeOffForm').reset();
    }
    
    function cancelRequest(event, requestId) {
        event.preventDefault();
        if (confirm('Are you sure you want to cancel this request?')) {
            event.target.closest('tr').remove();
            alert('Request cancelled successfully');
        }
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }
</script>
