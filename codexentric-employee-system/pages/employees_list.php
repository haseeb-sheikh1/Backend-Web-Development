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
    $employeeObj = new Employee($db->getConnection());
$allEmployees = $employeeObj->getBasicEmployeeDetails();
$specificEmployee = null; 
if (isset($_GET['id']) && !empty($_GET['id'])) {

    $specificEmployee = $employeeObj->getEmployeeDetailsById($employeeId);
    
}
 
?>

<main class="main-content" role="main">
    <div class="dashboard-container">
        <!-- Page Header -->
        <header class="page-header" role="banner">
            <div class="header-content">
                <h1 class="page-title">Select Employee</h1>
                <p class="page-subtitle">Search or select an employee to view and manage their details.</p>
            </div>
            <div class="header-actions">
                <a href="administrator_dashboard.php" class="action-button secondary" aria-label="Return to admin dashboard">
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
            <div class="search-container">
                <div class="search-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="search-input" 
                        placeholder="Search by name, email, role, or department..."
                        aria-label="Search employees"
                    >
                </div>
                <div class="filter-group">
                    <select id="statusFilter" class="filter-select" aria-label="Filter by status">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Onboarding">Onboarding</option>
                    </select>
                    <select id="deptFilter" class="filter-select" aria-label="Filter by department">
                        <option value="">All Departments</option>
                        <option value="Software Engineering">Software Engineering</option>
                        <option value="Design">Design</option>
                        <option value="Quality Assurance">Quality Assurance</option>
                        <option value="Human Resources">Human Resources</option>
                    </select>
                </div>
            </div>
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
    </div>
</main>

<?php include_once "../includes/footer.php"; ?>

<script>
    // Get search and filter inputs
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const deptFilter = document.getElementById('deptFilter');
    const employeeRows = document.querySelectorAll('.employee-row');
    const noResults = document.getElementById('noResults');
    const resultCount = document.getElementById('resultCount');

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Filter employees
    const filterEmployees = debounce(function() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedStatus = statusFilter.value;
        const selectedDept = deptFilter.value;
        let visibleCount = 0;

        employeeRows.forEach(row => {
            const name = row.dataset.name;
            const email = row.dataset.email;
            const role = row.dataset.role;
            const dept = row.dataset.dept;
            const status = row.dataset.status;

            // Check search term
            const matchesSearch = !searchTerm || 
                                name.includes(searchTerm) || 
                                email.includes(searchTerm) || 
                                role.includes(searchTerm) ||
                                dept.toLowerCase().includes(searchTerm);

            // Check status filter
            const matchesStatus = !selectedStatus || status === selectedStatus;

            // Check department filter
            const matchesDept = !selectedDept || dept === selectedDept.toLowerCase();

            // Show/hide row
            const shouldShow = matchesSearch && matchesStatus && matchesDept;
            row.style.display = shouldShow ? '' : 'none';
            if (shouldShow) visibleCount++;
        });

        // Show/hide no results message
        noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        resultCount.textContent = visibleCount;
    }, 300);

    // Add event listeners
    searchInput.addEventListener('input', filterEmployees);
    statusFilter.addEventListener('change', filterEmployees);
    deptFilter.addEventListener('change', filterEmployees);

    // Keyboard shortcuts for better accessibility
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            searchInput.value = '';
            statusFilter.value = '';
            deptFilter.value = '';
            filterEmployees();
        }
    });
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&display=swap');

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

.main-content {
    flex: 1;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    min-height: calc(100vh - 56px);
    overflow-y: auto;
}

.dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 28px 32px;
    width: 100%;
}

/* ── Page Header ── */
.page-header {
    background: linear-gradient(125deg, #1248A0 0%, #1559B5 40%, #1E6FD9 75%, #2B87F0 100%);
    border-radius: var(--radius);
    padding: 28px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-blue);
    margin-bottom: 28px;
}

.page-header::before {
    content: '';
    position: absolute;
    width: 320px;
    height: 320px;
    border-radius: 50%;
    background: rgba(255,255,255,0.06);
    top: -120px;
    right: -80px;
    pointer-events: none;
}

.page-header::after {
    content: '';
    position: absolute;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
    bottom: -60px;
    right: 120px;
    pointer-events: none;
}

.header-content {
    position: relative;
    z-index: 1;
}

.page-title {
    font-family: 'Nunito', sans-serif;
    font-size: 22px;
    font-weight: 900;
    color: #fff;
    margin: 0 0 5px 0;
    line-height: 1.2;
}

.page-subtitle {
    font-size: 13.5px;
    color: rgba(255,255,255,0.78);
    margin: 0;
    font-weight: 400;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
    z-index: 1;
    flex-wrap: wrap;
}

.action-button.secondary {
    background: rgba(255,255,255,0.15);
    color: #fff;
    border: 1.5px solid rgba(255,255,255,0.3);
    border-radius: 7px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    backdrop-filter: blur(6px);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.18s;
}

.action-button.secondary:hover {
    background: rgba(255,255,255,0.25);
}

/* ── Search Section ── */
.search-section {
    margin-bottom: 28px;
}

.search-container {
    background: var(--card);
    border-radius: var(--radius);
    padding: 22px;
    box-shadow: var(--shadow-xs);
    border: 1.5px solid var(--border);
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
    border: 1.5px solid var(--border);
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

.filter-group {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-select {
    padding: 10px 12px;
    border: 1.5px solid var(--border);
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
    border: 1.5px solid var(--border);
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
@media (max-width: 900px) {
    .dashboard-container {
        padding: 20px 16px;
    }

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
