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

<div class="emp-list-page">
        <!-- Minimal Header -->

        <!-- Search Section -->
        <!-- Search Section (Directory Style) -->
        <section class="search-section" aria-labelledby="directory-heading">
            <div class="search-card">
                <div class="search-card-header">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand-green)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <h2 id="directory-heading">Employee Search</h2>
                </div>
                <div class="search-card-body">
                    <form method="GET" action="">
                        <div class="search-grid">
                            <div class="search-field">
                                <label for="keyword">Employee Name</label>
                                <div class="input-wrap">
                                    <input 
                                        type="text" 
                                        name="keyword" 
                                        id="keyword" 
                                        placeholder="Type for hints..."
                                        value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>"
                                    >
                                </div>
                            </div>
                            <div class="search-field">
                                <label for="status">Status</label>
                                <div class="input-wrap">
                                    <select name="status" id="status">
                                        <option value="">-- Select --</option>
                                        <option value="Active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="Onboarding" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Onboarding') ? 'selected' : ''; ?>>Onboarding</option>
                                    </select>
                                </div>
                            </div>
                            <div class="search-field">
                                <label for="department">Department</label>
                                <div class="input-wrap">
                                    <select name="department" id="department">
                                        <option value="">-- Select --</option>
                                        <option value="Software Engineering" <?php echo (isset($_GET['department']) && $_GET['department'] == 'Software Engineering') ? 'selected' : ''; ?>>Software Engineering</option>
                                        <option value="Design" <?php echo (isset($_GET['department']) && $_GET['department'] == 'Design') ? 'selected' : ''; ?>>Design</option>
                                        <option value="Quality Assurance" <?php echo (isset($_GET['department']) && $_GET['department'] == 'Quality Assurance') ? 'selected' : ''; ?>>Quality Assurance</option>
                                        <option value="Human Resources" <?php echo (isset($_GET['department']) && $_GET['department'] == 'Human Resources') ? 'selected' : ''; ?>>Human Resources</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="search-actions">
                            <a href="employees_list.php" class="btn-reset">Reset</a>
                            <button type="submit" class="btn-search">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>



        <!-- Employees Table -->
        <section class="employees-section" style="margin-top: 40px;">
            <div class="table-wrapper">
                <table class="employees-table" role="table" aria-label="Employees list">
                    <thead>
                        <tr>
                            <th scope="col">
                                <div class="th-content">
                                    Personnel
                                    <div class="sort-icon">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                                    </div>
                                </div>
                            </th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Department</th>
                            <th scope="col">Status</th>
                            <th scope="col" style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="employeesTableBody">
                        <?php foreach ($allEmployees as $emp): ?>
                            <tr class="employee-row">
                                <td>
                                    <div class="emp-main-cell">
                                        <div class="emp-name-text"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($emp['email']); ?></td>
                                <td><?php echo htmlspecialchars($emp['position_title'] ?? 'Staff'); ?></td>
                                <td><?php echo htmlspecialchars($emp['department'] ?? 'General'); ?></td>
                                <td>
                                    <?php 
                                        $raw_status = strtolower($emp['status'] ?? 'active');
                                        $status_class = str_replace('_', '-', $raw_status);
                                        $display_label = str_replace('_', ' ', $raw_status);
                                        
                                        if ($raw_status === 'onboarding') {
                                            $status_class = 'on-leave';
                                            $display_label = 'on leave';
                                        } elseif ($raw_status === 'cancelled' || $raw_status === 'terminated') {
                                            $status_class = 'deactivated';
                                            $display_label = 'deactivated';
                                        }
                                    ?>
                                    <div class="status-indicator <?php echo $status_class; ?>">
                                        <span class="status-dot"></span>
                                        <span class="status-text"><?php echo htmlspecialchars($display_label); ?></span>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-trigger-group">
                                        <a href="manage_employee.php?id=<?php echo $emp['user_id'] ?? ''; ?>" class="action-icon-btn" title="Edit">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                        </a>
                                        <a href="#" class="action-icon-btn delete" title="Delete">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                        </a>
                                    </div>
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
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                <?php else: ?>
                    <span class="pagination-btn pagination-prev disabled" aria-disabled="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M15 19l-7-7 7-7"/>
                        </svg>
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
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                <?php else: ?>
                    <span class="pagination-btn pagination-next disabled" aria-disabled="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
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

<style>
:root {
  --bg: #f8fafc;
  --card-bg: #ffffff;
  --border: #e2e8f0;
  --text-main: #334155;
  --text-muted: #64748b;
  --brand-orange: #ff7b1d;
  --brand-orange-hover: #e66a15;
  --brand-green: #186D55;
  --font-body: 'Nunito Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
}

.dashboard-container {
  max-width: 1140px;
  margin: 0 auto;
  padding: 0 24px 24px 24px;
  font-family: var(--font-body);
}

/* ── Minimal Header ── */
.dash-header {
  display: flex;
  justify-content: space-between;
  align-items: center; /* Matches admin dashboard centering */
  margin-bottom: 24px;
}

#search-heading {
  font-size: 22px;
  font-weight: 700;
  color: var(--text-main);
  margin: 0;
  letter-spacing: -0.5px;
}

.dash-subtitle {
  font-size: 14px;
  color: var(--text-muted);
  margin: 5px 0 0 0;
}

.btn-minimal {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 8px;
  color: var(--text-main);
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.2s;
}

.btn-minimal:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

/* ── Directory Search Card ── */
.search-card {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.03);
  overflow: hidden;
}


#directory-heading {
  font-size: 14px;
  font-weight: 700;
  color: #334155;
  margin: 0;
}

.search-card-header {
  padding: 16px 20px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  gap: 10px;
  background: #fcfcfd;
}

.search-card-body {
  padding: 16px 20px;
}

.search-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 0;
}

.search-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.search-field label {
  font-size: 13px;
  font-weight: 700;
  color: #475569;
  margin-bottom: 8px;
  display: block;
}

.input-wrap {
  position: relative;
}

.search-field input,
.search-field select {
  width: 100%;
  height: 38px;
  padding: 0 12px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 14px;
  color: #1e293b;
  transition: all 0.2s;
}

.search-field input::placeholder {
  color: #94a3b8;
}

.search-field input:focus,
.search-field select:focus {
  border-color: var(--brand-green);
  box-shadow: 0 0 0 3px rgba(24, 109, 85, 0.1);
  outline: none;
}

.search-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 16px;
  margin-top: 16px;
  border-top: 1px solid #f1f5f9;
}

.btn-search {
  background: var(--brand-green);
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 8px 20px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-search:hover {
  background: #125542;
  transform: translateY(-1px);
}

.btn-reset {
  background: #fff;
  color: #64748b;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 8px 20px;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  transition: all 0.2s;
}

.btn-reset:hover {
  background: rgba(124, 179, 66, 0.05);
}

/* ── Minimal Table ── */
.table-wrapper {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}

.employees-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 10px;
  margin-top: -10px;
}

.employees-table thead tr {
  background: transparent;
}

.employees-table th {
  padding: 12px 20px;
  text-align: left;
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  border-bottom: none;
}

.th-content {
  display: flex;
  align-items: center;
  gap: 8px;
}

.sort-icon {
  display: flex;
  flex-direction: column;
  opacity: 0.4;
}

.employees-table td {
  padding: 14px 20px;
  color: #64748b;
  background: #ffffff;
  border-top: 1px solid #e2e8f0;
  border-bottom: 1px solid #e2e8f0;
  vertical-align: middle;
  font-size: 13px;
  transition: all 0.2s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.employees-table td:first-child {
  border-left: 1px solid #e2e8f0;
  border-top-left-radius: 50px;
  border-bottom-left-radius: 50px;
  padding-left: 30px;
}

.employees-table td:last-child {
  border-right: 1px solid #e2e8f0;
  border-top-right-radius: 50px;
  border-bottom-right-radius: 50px;
  padding-right: 30px;
}

.employees-table tbody tr:hover td {
  background: #fcfcfd;
}

.check-col {
  width: 50px;
}

.custom-check {
  width: 18px;
  height: 18px;
  border-radius: 4px;
  border: 2px solid #cbd5e1;
  background: #fff;
  cursor: pointer;
}

.employees-table tbody tr:hover {
  background: #fcfcfd;
}

.employees-table tr:last-child td {
  border-bottom: none;
}

/* ── Employee Cell ── */
.emp-main-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.emp-avatar-circle {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.emp-name-text {
  font-size: 14px;
  font-weight: 700;
  color: #1e293b;
}

/* ── Status Indicators (Dot Style) ── */
.status-indicator {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 500;
  color: #64748b; /* Neutral text color */
  text-transform: lowercase;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-indicator.active .status-dot { background: var(--brand-green); }
.status-indicator.on-leave .status-dot { background: #ff7b1d; } /* Vibrant Brand Orange */
.status-indicator.deactivated .status-dot { background: #ef4444; }

/* ── Action Eye Icon ── */
.action-trigger-group {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.action-icon-btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  text-decoration: none;
  transition: all 0.2s;
}

.action-icon-btn:hover {
  background: var(--brand-green);
  color: #fff;
}

.action-icon-btn.delete:hover {
  background: #ef4444;
}

/* ── Pagination (Screenshot Style) ── */
.pagination-section {
  margin-top: 30px;
  display: flex;
  justify-content: flex-end; /* Align to right */
  align-items: center;
  gap: 16px;
  padding: 0 4px;
}

.pagination {
  display: flex;
  align-items: center;
  gap: 16px;
}

.pagination-pages {
  display: flex;
  align-items: center;
  gap: 8px;
}

.pagination-btn {
  color: #cbd5e1;
  text-decoration: none;
  display: flex;
  align-items: center;
  transition: color 0.2s;
}

.pagination-btn:hover:not(.disabled) {
  color: #6366f1;
}

.pagination-page {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
  color: #475569;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.2s;
}

.pagination-page.active {
  border-color: #6366f1;
  color: #6366f1;
  background: #fff;
}

.pagination-info {
  font-size: 13px;
  color: #94a3b8;
  font-weight: 500;
}

@media (max-width: 768px) {
  /* Hide the desktop table layout */
  .table-wrapper {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
  }
  
  .employees-table,
  .employees-table thead,
  .employees-table tbody,
  .employees-table th,
  .employees-table td,
  .employees-table tr {
    display: block !important;
    width: 100% !important;
    box-sizing: border-box !important;
  }
  
  .employees-table thead {
    display: none !important;
  }
  
  .employees-table tbody {
    display: flex !important;
    flex-direction: column !important;
    gap: 16px !important;
  }
  
  /* Each row becomes a premium card */
  .employees-table tr {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    padding: 18px 20px !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02) !important;
    position: relative !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 12px !important;
    margin-bottom: 0 !important;
  }
  
  /* Disable hover cells backgrounds */
  .employees-table tbody tr:hover td {
    background: transparent !important;
  }
  
  /* Reset all individual cell styling */
  .employees-table td {
    border: none !important;
    border-radius: 0 !important;
    padding: 0 !important;
    text-align: left !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 4px !important;
    box-shadow: none !important;
    background: none !important;
  }
  
  /* First Cell (Employee Name) */
  .employees-table td:nth-child(1) {
    border-bottom: 1px solid #f1f5f9 !important;
    padding-bottom: 12px !important;
    margin-bottom: 4px !important;
  }
  
  /* Dynamic labels matching screenshot exactly */
  .employees-table td:nth-child(1)::before {
    content: "Employee Name" !important;
  }
  
  .employees-table td:nth-child(2)::before {
    content: "Username" !important;
  }
  
  .employees-table td:nth-child(3)::before {
    content: "User Role" !important;
  }
  
  .employees-table td:nth-child(5)::before {
    content: "Status" !important;
  }
  
  /* Hidden columns (like Department) on mobile for a clean look */
  .employees-table td:nth-child(4) {
    display: none !important;
  }
  
  /* Common label styles */
  .employees-table td:nth-child(1)::before,
  .employees-table td:nth-child(2)::before,
  .employees-table td:nth-child(3)::before,
  .employees-table td:nth-child(5)::before {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #94a3b8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    display: block !important;
    margin-bottom: 2px !important;
  }
  
  /* Value styles */
  .employees-table .emp-name-text {
    font-size: 16px !important;
    font-weight: 700 !important;
    color: #1e293b !important;
  }
  
  .employees-table td:nth-child(2),
  .employees-table td:nth-child(3) {
    font-size: 14px !important;
    color: #475569 !important;
    font-weight: 600 !important;
  }
  
  /* Action column floating on top right exactly like screenshot */
  .employees-table td:last-child {
    position: absolute !important;
    top: 18px !important;
    right: 20px !important;
    width: auto !important;
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  
  .employees-table .action-trigger-group {
    display: flex !important;
    gap: 8px !important;
    align-items: center !important;
  }
  
  /* Match screenshot action icons */
  .employees-table .action-icon-btn {
    width: 38px !important;
    height: 38px !important;
    border-radius: 50% !important;
    background: #f1f5f9 !important;
    color: #64748b !important;
    border: 1px solid #e2e8f0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s !important;
  }
  
  .employees-table .action-icon-btn:hover {
    background: #e2e8f0 !important;
    color: #1e293b !important;
    transform: scale(1.05) !important;
  }
  
  .employees-table .action-icon-btn.delete:hover {
    background: #fef2f2 !important;
    color: #ef4444 !important;
    border-color: #fee2e2 !important;
  }
}
</style>
<?php include_once "../includes/footer.php"; ?>
