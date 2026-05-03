<?php
    session_start();

    if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
    $user_role = "admin";
    $current_page = "manage_employees";
    $extra_css = "employees_list";
    $title = "Select Employee - CodeXentric";
    include_once "../includes/header.php";
    require_once "../pages/database.php";
    require_once "../pages/Employee.php";
    $db = new Database();
    $employeeObj = new Employee($db->getConnection());
    $allEmployees = $employeeObj->getBasicEmployeeDetails();
$specificEmployee = null; 
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $employeeId = $_GET['id'];
    $specificEmployee = $employeeObj->getEmployeeDetailsById($employeeId);   
}
// searching employees based on keyword, status, and department
// capturing the data
$keyword = trim($_GET['keyword'] ?? '');
$status = $_GET['status'] ?? '';
$department = $_GET['department'] ?? '';
// pagination
$limit = 3;
// getting the pagination..page we are currently on and calculating the offset for sql query
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = (int)(($page - 1) * $limit);
// total number of employees based on search criteria and fetching the employees for current page
$total_employees = $employeeObj->getTotalEmployeesCount($keyword, $department, $status);
// fetching the employees for current page
$employees = $employeeObj->searchEmployee($keyword, $department, $status, $limit, $offset);
// Use filtered employees if search is active, otherwise use all employees
$allEmployees = (!empty($keyword) || !empty($department) || !empty($status)) ? $employees : $employeeObj->getBasicEmployeeDetails();
//calculating total pages for pagination
$totalPages = ceil($total_employees / $limit);
 
?>

<main class="main-content" role="main">
    <div class="emp-list-page">
        <!-- Page Header -->
        <header class="dash-welcome" role="banner">
            <div class="dash-welcome-text">
                <h1>Select Employee</h1>
                <p>Search or select an employee to view and manage their details.</p>
            </div>
            <div class="dash-welcome-actions">
                <a href="administrator_dashboard.php" class="btn-wh" aria-label="Return to admin dashboard">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </header>

        <!-- Search Section -->
        <section class="search-section" aria-labelledby="search-heading">
            <h2 id="search-heading" class="sr-only">Search Employees</h2>
            <form method="GET" action="">
                <div class="search-container">
                    <div class="search-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input 
                            type="text" 
                            name="keyword"
                            id="searchInput" 
                            class="search-input" 
                            placeholder="Search by name, email, role, or department..."
                            aria-label="Search employees"
                            value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>"
                        >
                        <button type="submit" class="search-button" aria-label="Search">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M5 12h14M12 5v14"/>
                            </svg>
                        </button>
                    </div>

                    <div class="filter-group">
                        <select id="statusFilter" name="status" class="filter-select" aria-label="Filter by status" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="Active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Onboarding" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Onboarding') ? 'selected' : ''; ?>>Onboarding</option>
                        </select>

                        <select id="deptFilter" name="department" class="filter-select" aria-label="Filter by department" onchange="this.form.submit()">
                            <option value="">All Departments</option>
                            <option value="Software Engineering" <?php echo (isset($_GET['department']) && $_GET['department'] == 'Software Engineering') ? 'selected' : ''; ?>>Software Engineering</option>
                            <option value="Design" <?php echo (isset($_GET['department']) && $_GET['department'] == 'Design') ? 'selected' : ''; ?>>Design</option>
                            <option value="Quality Assurance" <?php echo (isset($_GET['department']) && $_GET['department'] == 'Quality Assurance') ? 'selected' : ''; ?>>Quality Assurance</option>
                            <option value="Human Resources" <?php echo (isset($_GET['department']) && $_GET['department'] == 'Human Resources') ? 'selected' : ''; ?>>Human Resources</option>
                        </select>
                    </div>
                </div>
            </form>
        </section>

        <!-- Results Count -->
        <div class="results-info" id="resultsInfo">
            Showing <span id="resultCount"><?php echo count($allEmployees); ?></span> employee(s)
        </div>

        <!-- Employees Table -->
        <section class="employees-section" aria-labelledby="employees-heading">
            <h2 id="employees-heading" class="sr-only">Employees List</h2>
            <div class="table-wrapper">
                <table class="employees-table" role="table" aria-label="Employees list">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Department</th>
                            <th scope="col">Status</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody id="employeesTableBody">
                        <?php foreach ($allEmployees as $emp): ?>
                            <tr class="employee-row" 
                               data-id="<?php echo htmlspecialchars($emp['user_id'] ?? ''); ?>" 
                                data-name="<?php echo htmlspecialchars(strtolower($emp['first_name'] ?? '')); ?>" 
                                 data-email="<?php echo htmlspecialchars(strtolower($emp['email'] ?? '')); ?>" 
                               data-role="<?php echo htmlspecialchars(strtolower($emp['position_title'] ?? '')); ?>" 
                              data-dept="<?php echo htmlspecialchars(strtolower($emp['department'] ?? '')); ?>" 
                                data-status="<?php echo htmlspecialchars($emp['status'] ?? ''); ?>">
                                <td>
                                    <div class="employee-info">
                                        <div class="employee-avatar">
                                            <?php echo substr($emp['first_name'], 0, 1); ?>
                                        </div>
                                        <div class="employee-details">
                                            <div class="employee-name"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></div>
                                            <div class="employee-joined">Joined: <?php echo htmlspecialchars($emp['date_of_joining']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($emp['email']); ?></td>
                                <td><span class="role-badge"><?php echo htmlspecialchars($emp['position_title']); ?></span></td>
                                <td><?php echo htmlspecialchars($emp['department']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower(str_replace('/', '_', $emp['status'])); ?>">
                                        <?php echo htmlspecialchars($emp['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="manage_employee.php?id=<?php echo $emp['user_id']; ?>" class="action-link" aria-label="View details for <?php echo htmlspecialchars($emp['first_name']); ?>">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div id="noResults" class="no-results" style="display: none;" role="alert">
                No employees found matching your search criteria.
            </div>
        </section>

        <!-- Pagination Section -->
        <?php if ($totalPages > 1): ?>
        <section class="pagination-section" aria-label="Pagination">
            <nav class="pagination" role="navigation">
                <!-- Previous Button -->
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo ($page - 1); ?>&keyword=<?php echo urlencode($keyword); ?>&status=<?php echo urlencode($status); ?>&department=<?php echo urlencode($department); ?>" 
                       class="pagination-btn pagination-prev" aria-label="Previous page">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M15 19l-7-7 7-7"/>
                        </svg>
                        Previous
                    </a>
                <?php else: ?>
                    <span class="pagination-btn pagination-prev disabled" aria-disabled="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M15 19l-7-7 7-7"/>
                        </svg>
                        Previous
                    </span>
                <?php endif; ?>

                <!-- Page Numbers -->
                <div class="pagination-pages">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($page == $i): ?>
                            <span class="pagination-page active" aria-current="page">
                                <?php echo $i; ?>
                            </span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&keyword=<?php echo urlencode($keyword); ?>&status=<?php echo urlencode($status); ?>&department=<?php echo urlencode($department); ?>" 
                               class="pagination-page" aria-label="Page <?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>

                <!-- Next Button -->
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo ($page + 1); ?>&keyword=<?php echo urlencode($keyword); ?>&status=<?php echo urlencode($status); ?>&department=<?php echo urlencode($department); ?>" 
                       class="pagination-btn pagination-next" aria-label="Next page">
                        Next
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                <?php else: ?>
                    <span class="pagination-btn pagination-next disabled" aria-disabled="true">
                        Next
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                <?php endif; ?>
            </nav>
            <div class="pagination-info">
                Page <strong><?php echo $page; ?></strong> of <strong><?php echo $totalPages; ?></strong>
            </div>
        </section>
        <?php endif; ?>
    </div>
</main>

<?php include_once "../includes/footer.php"; ?>



<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

:root {
  --blue:        #1E6FD9;
  --blue-dark:   #1559B5;
  --blue-light:  #EBF2FC;
  --blue-xlight: #F0F6FF;
  --green:       #059669;
  --green-bg:    #D1FAE5;
  --amber:       #D97706;
  --amber-bg:    #FEF3C7;
  --red:         #DC2626;
  --red-bg:      #FEE2E2;
  --border:      #E2E8F0;
  --surface:     #F8FAFC;
  --card:        #ffffff;
  --text-h:      #0F172A;
  --text-b:      #374151;
  --text-m:      #64748B;
  --text-s:      #94A3B8;
  --radius:      12px;
  --radius-sm:   8px;
  --shadow-xs:   0 1px 3px rgba(15,23,42,0.05);
  --shadow-sm:   0 1px 6px rgba(15,23,42,0.07);
  --shadow-md:   0 4px 20px rgba(15,23,42,0.09);
  --shadow-blue: 0 6px 24px rgba(21,89,181,0.16);
}

.emp-list-page { display: flex; flex-direction: column; width: 100%; box-sizing: border-box; }

/* Welcome Banner */
.dash-welcome {
  background: linear-gradient(135deg, #0f1c2e 0%, #1252cc 60%, #1a6eff 100%);
  border-radius: var(--radius);
  padding: 32px 36px;
  display: flex; align-items: center; justify-content: space-between;
  position: relative; overflow: hidden;
  box-shadow: 0 6px 24px rgba(26, 110, 255, 0.15);
  margin-bottom: 24px;
}
@media (max-width: 760px) {
  .dash-welcome { flex-direction: column; align-items: flex-start; gap: 20px; padding: 24px; }
}
.dash-welcome::before {
  content: ''; position: absolute;
  width: 300px; height: 300px; border-radius: 50%;
  background: rgba(255,255,255,0.04);
  top: -100px; right: -60px; pointer-events: none;
}
.dash-welcome::after {
  content: ''; position: absolute;
  width: 160px; height: 160px; border-radius: 50%;
  background: rgba(255,255,255,0.03);
  bottom: -50px; right: 200px; pointer-events: none;
}
.dash-welcome-text { position: relative; z-index: 1; }
.dash-welcome-text h1 { font-family: 'Inter', sans-serif; font-size: 26px; font-weight: 800; color: #fff; margin: 0 0 6px 0; letter-spacing: -0.3px; }
.dash-welcome-text p { font-size: 14.5px; color: rgba(255,255,255,0.8); margin: 0; }

.dash-welcome-actions { position: relative; z-index: 1; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.btn-wh {
  background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.3);
  border-radius: 7px; padding: 8px 16px; font-size: 13px; font-weight: 600; font-family: 'Inter', sans-serif;
  text-decoration: none; display: inline-flex; align-items: center; gap: 6px; backdrop-filter: blur(6px);
  transition: all 0.18s; cursor: pointer;
}
.btn-wh:hover { background: rgba(255,255,255,0.25); transform: translateY(-1px); }

/* ── Search Section ── */
.search-section {
    margin-bottom: 28px;
}

.search-container {
    background: var(--card);
    border-radius: var(--radius);
    padding: 22px;
    box-shadow: var(--shadow-xs);
    border: 1px solid var(--border);
    display: flex;
    align-items: flex-end;
    gap: 16px;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    min-width: 280px;
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    background: white;
    transition: all 0.15s;
}

.search-box:focus-within {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(30, 111, 217, 0.1);
    background: var(--blue-xlight);
}

.search-box svg {
    color: var(--text-m);
    flex-shrink: 0;
}

.search-input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-size: 13px;
    color: var(--text-h);
    font-family: inherit;
}

.search-input::placeholder {
    color: var(--text-s);
}

.search-button {
    background: transparent;
    border: none;
    color: var(--text-m);
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.15s;
}

.search-button:hover {
    color: var(--blue);
}

.search-button:active {
    color: var(--blue-dark);
}

.filter-group {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-select {
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    font-size: 13px;
    background: white;
    color: var(--text-h);
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
    min-width: 180px;
}

.filter-select:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(30, 111, 217, 0.1);
}

.filter-select:hover {
    border-color: var(--blue-light);
}

/* ── Pagination Section ── */
.pagination-section {
    margin-top: 28px;
    padding: 20px 0;
    border-top: 1.5px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.pagination {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.pagination-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 14px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    background: white;
    color: var(--text-h);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s;
}

.pagination-btn:hover:not(.disabled) {
    border-color: var(--blue);
    background: var(--blue-xlight);
    color: var(--blue-dark);
}

.pagination-btn:active:not(.disabled) {
    background: var(--blue-light);
    border-color: var(--blue-dark);
}

.pagination-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    color: var(--text-m);
}

.pagination-pages {
    display: flex;
    gap: 4px;
    align-items: center;
}

.pagination-page {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    background: white;
    color: var(--text-h);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s;
}

.pagination-page:hover {
    border-color: var(--blue);
    background: var(--blue-xlight);
    color: var(--blue);
}

.pagination-page.active {
    background: linear-gradient(125deg, var(--blue-dark) 0%, var(--blue) 100%);
    border-color: var(--blue-dark);
    color: white;
    font-weight: 600;
    cursor: default;
    box-shadow: 0 2px 8px rgba(21, 89, 181, 0.2);
}

.pagination-info {
    font-size: 13px;
    color: var(--text-m);
    font-weight: 500;
}

/* ── Results Info ── */
.results-info {
    font-size: 12px;
    color: var(--text-m);
    margin-bottom: 16px;
    padding: 0 2px;
}

/* ── Employees Table ── */
.employees-section {
    margin-bottom: 28px;
}

.table-wrapper {
    background: var(--card);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow-xs);
    border: 1px solid var(--border);
}

.employees-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    background: transparent;
}

.employees-table thead {
    background: var(--surface);
}

.employees-table th {
    padding: 14px 16px;
    font-size: 11px;
    color: var(--text-b);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1.5px solid var(--border);
    text-align: left;
}

.employees-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    color: var(--text-h);
    vertical-align: middle;
}

.employees-table tbody tr {
    transition: background-color 0.15s;
}

.employees-table tbody tr:hover {
    background-color: var(--surface);
}

.employees-table tr:last-child td {
    border-bottom: none;
}

/* ── Employee Info Cell ── */
.employee-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.employee-avatar {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: linear-gradient(135deg, var(--blue), var(--blue-light));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    flex-shrink: 0;
}

.employee-details {
    min-width: 0;
}

.employee-name {
    font-weight: 600;
    color: var(--text-h);
    font-size: 13px;
    margin-bottom: 2px;
}

.employee-joined {
    font-size: 11px;
    color: var(--text-m);
    font-weight: 500;
}

/* ── Role Badge ── */
.role-badge {
    display: inline-block;
    padding: 4px 8px;
    background: var(--blue-xlight);
    color: var(--blue);
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

/* ── Status Badges ── */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    border: 1.5px solid transparent;
}

.status-badge.active {
    background: var(--green-bg);
    color: var(--green);
    border-color: var(--green);
}

.status-badge.onboarding {
    background: var(--amber-bg);
    color: var(--amber);
    border-color: var(--amber);
}

/* ── Action Link ── */
.action-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    color: var(--blue);
    background: var(--blue-xlight);
    border: 1px solid var(--blue-light);
    transition: all 0.15s;
    cursor: pointer;
}

.action-link:hover {
    background: var(--blue-light);
    color: var(--blue-dark);
    border-color: var(--blue);
    transform: translateY(-1px);
}

/* ── No Results ── */
.no-results {
    padding: 40px 20px;
    text-align: center;
    color: var(--text-m);
    font-size: 14px;
}

/* ── Screen Reader Only ── */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* ── Responsive Design ── */

    .page-header {
        padding: 20px 24px;
        flex-direction: column;
        text-align: center;
    }

    .page-title {
        font-size: 18px;
    }

    .header-actions {
        justify-content: center;
        width: 100%;
    }

    .search-container {
        flex-direction: column;
        align-items: stretch;
    }

    .search-box {
        min-width: unset;
    }

    .filter-group {
        justify-content: stretch;
    }

    .filter-select {
        min-width: unset;
        flex: 1;
    }

    .employees-table {
        font-size: 12px;
    }

    .employees-table th,
    .employees-table td {
        padding: 10px 12px;
    }

    .employee-avatar {
        width: 36px;
        height: 36px;
        font-size: 12px;
    }
}

@media (max-width: 520px) {
    .dashboard-container {
        padding: 16px 12px;
    }

    .page-header {
        padding: 16px 20px;
        gap: 12px;
    }

    .page-title {
        font-size: 16px;
        margin-bottom: 4px;
    }

    .page-subtitle {
        font-size: 12px;
    }

    .search-container {
        padding: 16px;
    }

    .search-box {
        font-size: 13px;
    }

    .filter-select {
        min-width: unset;
        flex: 1;
        font-size: 13px;
    }

    .employees-table {
        font-size: 11px;
    }

    .employees-table th,
    .employees-table td {
        padding: 10px 8px;
    }

    .employee-info {
        gap: 8px;
    }

    .employee-avatar {
        width: 32px;
        height: 32px;
        font-size: 11px;
    }

    .action-link {
        padding: 5px 10px;
        font-size: 11px;
    }
}
</style>
