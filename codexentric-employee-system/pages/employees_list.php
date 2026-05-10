<?php
    session_start();

    if (!isset($_SESSION['email'])) {
        header("Location: login.php");
        exit();
    }
    if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != '1') {
        header("Location: employee_dashboard.php");
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
                <div class="search-card-header" style="justify-content: space-between; width: 100%;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand-green)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <h2 id="directory-heading">Employees</h2>
                    </div>
                    <button type="button" class="collapse-btn-circle" style="background: #f1f5f9; border: none; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #475569;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
                    </button>
                </div>
                <div class="search-card-body">
                    <form method="GET" action="">
                        <div class="search-grid" style="grid-template-columns: repeat(3, 1fr);">
                            <div class="search-field">
                                <label for="keyword">Keyword</label>
                                <div class="input-wrap">
                                    <input 
                                        type="text" 
                                        name="keyword" 
                                        id="keyword" 
                                        placeholder="Name, Position, Status"
                                        value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>"
                                    >
                                </div>
                            </div>
                            <div class="search-field">
                                <label for="department">Department</label>
                                <div class="input-wrap">
                                    <select name="department" id="department">
                                        <option value="">-- Select --</option>
                                        <option value="Software Engineering" <?php echo (isset($_GET['department']) && $_GET['department'] == 'Software Engineering') ? 'selected' : ''; ?>>Software Engineering</option>
                                        <option value="Design" <?php echo (isset($_GET['department']) && $_GET['department'] == 'Design') ? 'selected' : ''; ?>>Design</option>
                                    </select>
                                </div>
                            </div>
                            <div class="search-field">
                                <label for="status">Status</label>
                                <div class="input-wrap">
                                    <select name="status" id="status">
                                        <option value="">-- Select --</option>
                                        <option value="Active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="De-activated" <?php echo (isset($_GET['status']) && $_GET['status'] == 'De-activated') ? 'selected' : ''; ?>>De-activated</option>
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



        <!-- Actions pill-row and table -->
        <div class="table-header-action-bar">
            <a href="add_employee.php" class="btn-add-pill">+ Add</a>
            <span class="records-count">(<?php echo $total_employees; ?>) Records Found</span>
        </div>

        <!-- Employees Table -->
        <section class="employees-section" style="margin-top: 10px;">
            <div class="table-wrapper">
                <table class="employees-table" role="table" aria-label="Employees list">
                    <thead>
                        <tr>
                            <th scope="col" class="check-col">
                                <label class="custom-check-container">
                                    <input type="checkbox">
                                    <span class="checkmark"></span>
                                </label>
                            </th>
                            <th scope="col">
                                <div class="th-content">
                                    Employee Name
                                    <svg class="header-sort-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m7 15 5 5 5-5M7 9l5-5 5 5"/></svg>
                                </div>
                            </th>
                            <th scope="col">
                                <div class="th-content">
                                    Position
                                    <svg class="header-sort-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m7 15 5 5 5-5M7 9l5-5 5 5"/></svg>
                                </div>
                            </th>
                            <th scope="col">
                                <div class="th-content">
                                    Base Salary
                                    <svg class="header-sort-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m7 15 5 5 5-5M7 9l5-5 5 5"/></svg>
                                </div>
                            </th>
                            <th scope="col">
                                <div class="th-content">
                                    Status
                                    <svg class="header-sort-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m7 15 5 5 5-5M7 9l5-5 5 5"/></svg>
                                </div>
                            </th>
                            <th scope="col" style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="employeesTableBody">
                        <?php foreach ($allEmployees as $emp): ?>
                            <tr class="employee-row">
                                <td class="check-col">
                                    <label class="custom-check-container">
                                        <input type="checkbox">
                                        <span class="checkmark"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="emp-main-cell">
                                        <div class="emp-name-text"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($emp['position_title'] ?? 'Staff'); ?></td>
                                <td>Rs <?php echo number_format((float)($emp['base_salary_rs'] ?? 0)); ?></td>
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
                                        <button type="button" class="action-icon-btn delete" title="Delete">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                        </button>
                                        <a href="manage_employee.php?id=<?php echo $emp['user_id'] ?? ''; ?>" class="action-icon-btn edit" title="Edit">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
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
  --bg: #f4f6f9;
  --card-bg: #ffffff;
  --border: rgba(0, 0, 0, 0.05);
  --text-main: #334155;
  --text-muted: #64748b;
  --brand-orange: #ff7b1d;
  --brand-green: #186D55;
  --font-body: 'Nunito Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
}

.emp-list-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px 24px 24px;
  font-family: var(--font-body);
}

/* ── Search Card ── */
.search-card {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.02);
  overflow: visible; /* Permit floating dropdown lists */
  position: relative;
  z-index: 100;
}

.search-card-header {
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  background: #ffffff;
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
}

#directory-heading {
  font-size: 15px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.search-card-body {
  padding: 24px;
}

.search-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr); /* 3 columns precisely like reference image */
  gap: 20px;
}

.search-field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.search-field label {
  font-size: 13px;
  font-weight: 600;
  color: #64748b;
  display: block;
}

.search-field input,
.search-field select {
  width: 100%;
  height: 44px;
  padding: 0 16px;
  background: #fcfdfd;
  border: 1.5px solid #e2e8f0;
  border-radius: 22px;
  font-size: 13.5px;
  font-weight: 500;
  color: #1e293b;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  box-sizing: border-box;
}

.search-field input:hover,
.search-field select:hover {
  border-color: #cbd5e1;
  background: #ffffff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}

.search-field select {
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 16px center;
  background-size: 14px;
  padding-right: 40px;
  cursor: pointer;
  accent-color: var(--brand-green);
}

.search-field select option {
  background-color: #ffffff;
  color: #1e293b;
}

.search-field select option:checked {
  background-color: var(--brand-green) !important;
  color: #ffffff !important;
  box-shadow: 0 0 10px 100px var(--brand-green) inset; /* Comprehensive browser fill support */
}

.search-field input::placeholder {
  color: #cbd5e1;
}

.search-field input:focus,
.search-field select:focus {
  border-color: var(--brand-green);
  background: #ffffff;
  box-shadow: 0 0 0 4px rgba(24, 109, 85, 0.12), 0 4px 12px rgba(24, 109, 85, 0.05);
  outline: none;
}

.search-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.btn-reset {
  background: #ffffff;
  color: var(--brand-green);
  border: 1px solid var(--brand-green);
  border-radius: 20px;
  padding: 10px 28px;
  font-size: 13.5px;
  font-weight: 700;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  transition: all 0.2s;
  cursor: pointer;
}

.btn-reset:hover {
  background: rgba(24, 109, 85, 0.05);
  transform: translateY(-1px);
}

.btn-search {
  background: var(--brand-green);
  color: #ffffff;
  border: none;
  border-radius: 20px;
  padding: 10px 28px;
  font-size: 13.5px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-search:hover {
  background: #11523f;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(24, 109, 85, 0.15);
}

/* ── Actions Row (Add Pill & Records Count) ── */
.table-header-action-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 32px;
  margin-bottom: 16px;
  padding: 0 4px;
}

.btn-add-pill {
  background: var(--brand-green);
  color: #ffffff;
  text-decoration: none;
  border-radius: 20px;
  padding: 8px 24px;
  font-size: 13.5px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(24, 109, 85, 0.15);
}

.btn-add-pill:hover {
  background: #11523f;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(24, 109, 85, 0.25);
}

.records-count {
  font-size: 14px;
  font-weight: 600;
  color: #64748b;
}

/* ── Custom Checkbox ── */
.custom-check-container {
  display: block;
  position: relative;
  width: 18px;
  height: 18px;
  cursor: pointer;
  user-select: none;
}

.custom-check-container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0; width: 0;
}

.checkmark {
  position: absolute;
  top: 0; left: 0;
  height: 18px; width: 18px;
  background-color: #ffffff;
  border: 2px solid #cbd5e1;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.custom-check-container:hover input ~ .checkmark {
  border-color: var(--brand-green);
}

.custom-check-container input:checked ~ .checkmark {
  background-color: var(--brand-green);
  border-color: var(--brand-green);
}

.checkmark:after {
  content: "";
  position: absolute;
  display: none;
}

.custom-check-container input:checked ~ .checkmark:after {
  display: block;
}

.custom-check-container .checkmark:after {
  left: 5px; top: 1.5px;
  width: 4px; height: 8px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

/* ── Table Layout Overhaul ── */
.table-wrapper {
  background: transparent;
  border: none;
  box-shadow: none;
  overflow: visible;
}

.employees-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 12px; /* Gap between separate rows exactly like screenshot */
}

.employees-table thead tr {
  background: transparent;
}

.employees-table th {
  padding: 12px 24px;
  text-align: left;
  font-size: 12.5px;
  font-weight: 700;
  color: #64748b;
  letter-spacing: 0.5px;
}

.th-content {
  display: flex;
  align-items: center;
  gap: 6px;
}

.header-sort-icon {
  width: 12px;
  height: 12px;
  opacity: 0.5;
}

.employees-table td {
  padding: 16px 24px;
  color: #475569;
  background: #ffffff;
  border-top: 1px solid rgba(0, 0, 0, 0.04);
  border-bottom: 1px solid rgba(0, 0, 0, 0.04);
  vertical-align: middle;
  font-size: 13.5px;
  font-weight: 600;
  transition: all 0.2s;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.015);
}

.employees-table td:first-child {
  border-left: 1px solid rgba(0, 0, 0, 0.04);
  border-top-left-radius: 25px; /* Highly rounded row left edge exactly like screenshot */
  border-bottom-left-radius: 25px;
  padding-left: 28px;
}

.employees-table td:last-child {
  border-right: 1px solid rgba(0, 0, 0, 0.04);
  border-top-right-radius: 25px; /* Highly rounded row right edge exactly like screenshot */
  border-bottom-right-radius: 25px;
  padding-right: 28px;
}

.employees-table tbody tr:hover td {
  background: #fafbfc;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.025);
}

.check-col {
  width: 40px;
}

/* ── Employee Cell ── */
.emp-main-cell {
  display: flex;
  align-items: center;
  gap: 12px;
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
  font-weight: 600;
  color: #475569;
  text-transform: capitalize;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-indicator.active .status-dot { background: var(--brand-green); }
.status-indicator.on-leave .status-dot { background: #ff7b1d; }
.status-indicator.deactivated .status-dot { background: #ef4444; }

/* ── Action Buttons ── */
.action-trigger-group {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.action-icon-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #475569;
  text-decoration: none;
  border: none;
  cursor: pointer;
  transition: all 0.25s ease;
}

.action-icon-btn.edit:hover {
  background: #eaf5f2;
  color: var(--brand-green);
  transform: scale(1.08);
}

.action-icon-btn.delete:hover {
  background: #fef2f2;
  color: #ef4444;
  transform: scale(1.08);
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
  
  /* First Cell (Checkbox) */
  .employees-table td:nth-child(1) {
    display: none !important;
  }
  
  /* Second Cell (Employee Name) */
  .employees-table td:nth-child(2) {
    border-bottom: 1px solid #f1f5f9 !important;
    padding-bottom: 12px !important;
    margin-bottom: 4px !important;
  }
  
  /* Dynamic labels matching screenshot exactly */
  .employees-table td:nth-child(2)::before {
    content: "Employee Name" !important;
  }
  
  .employees-table td:nth-child(3)::before {
    content: "Position" !important;
  }
  
  .employees-table td:nth-child(4)::before {
    content: "Base Salary" !important;
  }
  
  .employees-table td:nth-child(5)::before {
    content: "Status" !important;
  }
  
  /* Common label styles */
  .employees-table td:nth-child(2)::before,
  .employees-table td:nth-child(3)::before,
  .employees-table td:nth-child(4)::before,
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
  
  .employees-table td:nth-child(3),
  .employees-table td:nth-child(4),
  .employees-table td:nth-child(5) {
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
