<?php
session_start();

// 1. Role Protection - Only accessible to Admin (role_id == '1') or employees with add_expense permission
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$is_admin = (isset($_SESSION['role_id']) && $_SESSION['role_id'] == '1');
$has_expense_perm = (isset($_SESSION['permissions']) && in_array('add_expense', $_SESSION['permissions']));

if (!$is_admin && !$has_expense_perm) {
    header("Location: employee_dashboard.php");
    exit();
}

$current_page = "expenses";
$extra_css    = "expenses";
$title        = "Company Expense Manager";

require_once '../pages/database.php';
$db = new Database();
$conn = $db->getConnection();

require_once '../pages/Expense.php';
$expenseHandler = new Expense($conn);

// Create upload directory if not exists
$upload_dir = '../assets/uploads/receipts/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// ─── AUTOMATIC SEEDING ───

// Seed Categories
$cat_check = $conn->query("SELECT COUNT(*) as count FROM expense_categories");
$cat_count = $cat_check->fetch_assoc()['count'];
if ($cat_count == 0) {
    $categories = ['Electricity', 'Water', 'Rent', 'Office Supplies', 'Software Licenses', 'Hardware & Equipment', 'Meals & Entertainment'];
    foreach ($categories as $cat) {
        $stmt = $conn->prepare("INSERT INTO expense_categories (category_name, created_at) VALUES (?, NOW())");
        $stmt->bind_param("s", $cat);
        $stmt->execute();
        $stmt->close();
    }
}

// Retrieve Categories for reference map
$categories_res = $conn->query("SELECT id, category_name FROM expense_categories");
$cat_map = [];
while($row = $categories_res->fetch_assoc()) {
    $cat_map[$row['category_name']] = $row['id'];
}



// ─── HANDLE FORM SUBMISSIONS (CRUD) ───

$success_message = "";
$error_message = "";

// Edit Mode Retrieval
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_stmt = $conn->prepare("SELECT * FROM expenses WHERE id = ?");
    $edit_stmt->bind_param("i", $edit_id);
    $edit_stmt->execute();
    $edit_res = $edit_stmt->get_result();
    if ($edit_res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $edit_res->fetch_assoc();
        
        // Parse invoice number from description if in "[INV-XXX] Description" format
        if (preg_match('/^\[(.*?)\]\s*(.*)$/', $edit_data['description'], $matches)) {
            $edit_data['invoice_number'] = $matches[1];
            $edit_data['pure_description'] = $matches[2];
        } else {
            $edit_data['invoice_number'] = '';
            $edit_data['pure_description'] = $edit_data['description'];
        }
    }
    $edit_stmt->close();
}

// Delete Action
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    // Get file attachment first to delete it
    $file_stmt = $conn->prepare("SELECT attachment_path FROM expenses WHERE id = ?");
    $file_stmt->bind_param("i", $delete_id);
    $file_stmt->execute();
    $file_res = $file_stmt->get_result();
    if ($file_res->num_rows > 0) {
        $file_path = $file_res->fetch_assoc()['attachment_path'];
        if ($file_path && file_exists('../assets/uploads/receipts/' . $file_path)) {
            unlink('../assets/uploads/receipts/' . $file_path);
        }
    }
    $file_stmt->close();

    $del_stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $del_stmt->bind_param("i", $delete_id);
    if ($del_stmt->execute()) {
        $success_message = "Expense deleted successfully.";
    } else {
        $error_message = "Failed to delete expense.";
    }
    $del_stmt->close();
}

// Approve / Reject Actions (Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    if (isset($_POST['action']) && $_POST['action'] === 'approve' && isset($_POST['expense_id'])) {
        if ($expenseHandler->approveExpense(intval($_POST['expense_id']), $_SESSION['user_id'])) {
            $success_message = "Expense approved successfully.";
        } else {
            $error_message = "Failed to approve expense.";
        }
    }
    if (isset($_POST['action']) && $_POST['action'] === 'reject' && isset($_POST['expense_id']) && isset($_POST['reject_reason'])) {
        if ($expenseHandler->rejectExpense(intval($_POST['expense_id']), $_SESSION['user_id'], trim($_POST['reject_reason']))) {
            $success_message = "Expense rejected.";
        } else {
            $error_message = "Failed to reject expense.";
        }
    }
}

// Create/Update Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_expense'])) {
    $category_id = intval($_POST['category_id']);
    $amount = floatval($_POST['amount']);
    $bill_date = $_POST['bill_date'];
    
    if ($is_admin) {
        $status = 'approved';
        $approved_by = $_SESSION['user_id'];
        $approved_at = date('Y-m-d H:i:s');
    } else {
        $status = 'pending';
        $approved_by = null;
        $approved_at = null;
    }

    $invoice_number = trim($_POST['invoice_number']);
    $notes = trim($_POST['description']);
    $user_id = $_SESSION['user_id']; 

    // Handle Dynamic Category Creation
    if ($category_id === -1 && !empty($_POST['new_category_name'])) {
        $new_cat_name = trim($_POST['new_category_name']);
        
        // Check if category already exists to avoid duplicates
        $check_stmt = $conn->prepare("SELECT id FROM expense_categories WHERE category_name = ?");
        $check_stmt->bind_param("s", $new_cat_name);
        $check_stmt->execute();
        $check_res = $check_stmt->get_result();
        
        if ($check_res->num_rows > 0) {
            $category_id = $check_res->fetch_assoc()['id'];
        } else {
            $ins_cat_stmt = $conn->prepare("INSERT INTO expense_categories (category_name, created_at) VALUES (?, NOW())");
            $ins_cat_stmt->bind_param("s", $new_cat_name);
            $ins_cat_stmt->execute();
            $category_id = $conn->insert_id;
            $ins_cat_stmt->close();
        }
        $check_stmt->close();
    }

    // ── Backend Validation ──
    if (empty($_POST['category_id'])) {
        $error_message = "Expense Category is required.";
    } elseif (!isset($_POST['amount']) || $_POST['amount'] === '') {
        $error_message = "Amount is required.";
    } elseif (floatval($_POST['amount']) < 0) {
        $error_message = "Amount cannot be negative.";
    } elseif (empty($_POST['bill_date'])) {
        $error_message = "Billing Date is required.";
    }

    if (empty($error_message)) {
        $description = !empty($invoice_number) ? "[$invoice_number] $notes" : $notes;

        // File Upload handling
        $attachment_name = null;
        if ($edit_mode) {
            $attachment_name = $edit_data['attachment_path'];
        }

        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['attachment']['tmp_name'];
                $file_orig_name = $_FILES['attachment']['name'];
                $file_ext = strtolower(pathinfo($file_orig_name, PATHINFO_EXTENSION));
                
                $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png'];
                if (!in_array($file_ext, $allowed_exts)) {
                    $error_message = "Invalid file type. Only PDFs and Images (JPG, PNG) are allowed.";
                } elseif ($_FILES['attachment']['size'] > 5000000) {
                    $error_message = "Attachment is too large. Max limit is 5MB.";
                } else {
                    // Success - Move file
                    if ($edit_mode && $attachment_name && file_exists($upload_dir . $attachment_name)) {
                        unlink($upload_dir . $attachment_name);
                    }
                    $attachment_name = uniqid('receipt_', true) . '.' . $file_ext;
                    move_uploaded_file($file_tmp, $upload_dir . $attachment_name);
                }
            } else {
                // Catch error levels like UPLOAD_ERR_INI_SIZE (1), etc.
                $error_code = $_FILES['attachment']['error'];
                if ($error_code === 1 || $error_code === 2) {
                    $error_message = "The file is too large for the server's configuration.";
                } else {
                    $error_message = "An error occurred during file upload (Error Code $error_code).";
                }
            }
        }
    }

    if (empty($error_message)) {
        if ($edit_mode) {
            // Update
            $stmt = $conn->prepare("UPDATE expenses SET category_id = ?, amount = ?, description = ?, bill_date = ?, attachment_path = ? WHERE id = ?");
            $stmt->bind_param("idsssi", $category_id, $amount, $description, $bill_date, $attachment_name, $edit_data['id']);
            if ($stmt->execute()) {
                $_SESSION['success_msg'] = "Expense updated successfully.";
                header("Location: expenses.php");
                exit();
            } else {
                $error_message = "Failed to update expense: " . $conn->error;
            }
            $stmt->close();
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO expenses (category_id, user_id, amount, description, bill_date, status, attachment_path, approved_by, approved_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("iidssssis", $category_id, $user_id, $amount, $description, $bill_date, $status, $attachment_name, $approved_by, $approved_at);
            if ($stmt->execute()) {
                $success_message = "New expense recorded successfully!";
            } else {
                $error_message = "Failed to record expense: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// ─── CALCULATE SUMMARY METRICS ───

$current_month = date('Y-m');

// 1. Total Monthly Spend (Sum of all approved expenses for current month)
$sum_month_stmt = $conn->prepare("SELECT SUM(amount) as total FROM expenses WHERE DATE_FORMAT(bill_date, '%Y-%m') = ? AND status = 'approved'");
$sum_month_stmt->bind_param("s", $current_month);
$sum_month_stmt->execute();
$monthly_spend = floatval($sum_month_stmt->get_result()->fetch_assoc()['total']);
$sum_month_stmt->close();

// 2. Pending Approvals (Sum of all pending bills)
$pending_stmt = $conn->query("SELECT SUM(amount) as total FROM expenses WHERE status = 'pending'");
$pending_payments = floatval($pending_stmt->fetch_assoc()['total']);

// 3. Highest Expense Category (Sum of amount grouped by category, showing the top one)
$highest_stmt = $conn->query("SELECT ec.category_name, SUM(e.amount) as total 
                              FROM expenses e 
                              JOIN expense_categories ec ON e.category_id = ec.id 
                              WHERE e.status = 'approved'
                              GROUP BY e.category_id 
                              ORDER BY total DESC LIMIT 1");
$highest_expense_cat = "N/A";
$highest_expense_amt = 0;
if ($highest_stmt && $highest_stmt->num_rows > 0) {
    $row = $highest_stmt->fetch_assoc();
    $highest_expense_cat = $row['category_name'];
    $highest_expense_amt = floatval($row['total']);
}

// 4. Burn Rate (Payroll + General Expenses for the month)
// Let's get total payroll base salary fallback or active payroll sum
$payroll_stmt = $conn->query("SELECT SUM(net_payable_rs) as total FROM payroll WHERE DATE_FORMAT(processed_at, '%Y-%m') = '$current_month'");
$payroll_amt = 0;
if ($payroll_stmt && $payroll_stmt->num_rows > 0) {
    $payroll_amt = floatval($payroll_stmt->fetch_assoc()['total']);
}
if ($payroll_amt == 0) {
    // Fallback to sum of base salaries from employees
    $emp_salary_stmt = $conn->query("SELECT SUM(base_salary_rs) as total FROM employees WHERE status = 'Active'");
    $payroll_amt = floatval($emp_salary_stmt->fetch_assoc()['total']);
}
$burn_rate = $payroll_amt + $monthly_spend;

// ─── RETRIEVE AND FILTER THE MANAGEMENT TABLE ───

// Filters
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$month_filter = isset($_GET['month']) ? trim($_GET['month']) : '';

// Build SQL
$where_clauses = [];
$params = [];
$types = '';

if (!$is_admin) {
    $where_clauses[] = "e.user_id = ?";
    $params[] = $_SESSION['user_id'];
    $types .= 'i';
}

if (!empty($search_query)) {
    $where_clauses[] = "(e.description LIKE ?)";
    $search_param = '%' . $search_query . '%';
    $params[] = $search_param;
    $types .= 's';
}

if ($category_filter > 0) {
    $where_clauses[] = "e.category_id = ?";
    $params[] = $category_filter;
    $types .= 'i';
}

if (!empty($month_filter)) {
    $where_clauses[] = "DATE_FORMAT(e.bill_date, '%Y-%m') = ?";
    $params[] = $month_filter;
    $types .= 's';
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Pagination
$limit = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Count query for total pages
$count_query = "SELECT COUNT(*) as total FROM expenses e $where_sql";
$count_stmt = $conn->prepare($count_query);
if ($count_stmt === false) {
    // Table may not exist yet or SQL error — default to 0
    $total_rows = 0;
    $total_pages = 0;
    $expenses_res = null;
} else {
    if (count($params) > 0) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $total_rows = $count_stmt->get_result()->fetch_assoc()['total'];
    $total_pages = ceil($total_rows / $limit);
    $count_stmt->close();

    // Fetch Data
    $query = "SELECT e.*, ec.category_name 
              FROM expenses e 
              JOIN expense_categories ec ON e.category_id = ec.id 
              $where_sql 
              ORDER BY e.bill_date DESC 
              LIMIT ? OFFSET ?";
    $fetch_stmt = $conn->prepare($query);

    if ($fetch_stmt === false) {
        $expenses_res = null;
    } else {
        $bind_params = array_merge($params, [$limit, $offset]);
        $bind_types = $types . 'ii';
        $fetch_stmt->bind_param($bind_types, ...$bind_params);
        $fetch_stmt->execute();
        $expenses_res = $fetch_stmt->get_result();
    }
}

// ─── CHARTS DATA AGGREGATION ───

// 1. Expense Distribution Chart Data (Categories + Salaries)
$dist_stmt = $conn->query("SELECT ec.category_name, SUM(e.amount) as total 
                           FROM expenses e 
                           JOIN expense_categories ec ON e.category_id = ec.id 
                           WHERE e.status = 'approved'
                           GROUP BY e.category_id");
$dist_labels = [];
$dist_values = [];
$total_non_payroll = 0;
while($row = $dist_stmt->fetch_assoc()) {
    $dist_labels[] = $row['category_name'];
    $dist_values[] = floatval($row['total']);
    $total_non_payroll += floatval($row['total']);
}
// Add Salaries to Distribution Chart
if ($payroll_amt > 0) {
    $dist_labels[] = 'Salaries (Payroll)';
    $dist_values[] = $payroll_amt;
}

// 2. 6-Month Trend Data
$trend_months = [];
for ($i = 5; $i >= 0; $i--) {
    $trend_months[] = date('Y-m', strtotime("-$i months"));
}
$trend_labels = [];
$trend_values = [];
foreach ($trend_months as $m) {
    $month_name = date('M Y', strtotime($m . "-01"));
    $trend_labels[] = $month_name;
    
    $month_sum_stmt = $conn->prepare("SELECT SUM(amount) as total FROM expenses WHERE DATE_FORMAT(bill_date, '%Y-%m') = ? AND status = 'approved'");
    $month_sum_stmt->bind_param("s", $m);
    $month_sum_stmt->execute();
    $tot = floatval($month_sum_stmt->get_result()->fetch_assoc()['total']);
    $trend_values[] = $tot;
    $month_sum_stmt->close();
}

include_once "../includes/header.php";
include_once "../includes/sidebar.php";
?>

<style>
.expenses-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px 24px 24px;
}
.expense-grid-form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px 28px;
}
.form-group-span-all {
  grid-column: span 2;
}
.form-footer-actions {
  grid-column: span 2;
  margin-top: 12px;
  padding-top: 24px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

@media (max-width: 768px) {
  .expenses-container {
    padding: 0 12px 12px 12px !important;
  }
  .widget-header {
    padding: 16px 18px !important;
  }
  .widget-body {
    padding: 20px 16px !important;
  }
  .expense-grid-form {
    grid-template-columns: 1fr !important;
    gap: 18px !important;
  }
  .form-group-span-all {
    grid-column: span 1 !important;
  }
  .form-footer-actions {
    grid-column: span 1 !important;
    flex-direction: column-reverse !important;
    gap: 12px !important;
  }
  .form-footer-actions a,
  .form-footer-actions button {
    width: 100% !important;
    height: 44px !important; /* Bigger touch targets */
  }
}
</style>

<div class="expenses-container">

  <!-- Toast Trigger Logic -->
  <?php if (!empty($success_message)): ?>
    <script>document.addEventListener('DOMContentLoaded', () => window.showToast && window.showToast(<?php echo json_encode($success_message); ?>, 'success'));</script>
  <?php endif; ?>
  <?php if (!empty($error_message)): ?>
    <script>document.addEventListener('DOMContentLoaded', () => window.showToast && window.showToast(<?php echo json_encode($error_message); ?>, 'error'));</script>
  <?php endif; ?>
  <?php if (isset($_SESSION['success_msg'])): ?>
    <script>document.addEventListener('DOMContentLoaded', () => window.showToast && window.showToast(<?php echo json_encode($_SESSION['success_msg']); ?>, 'success'));</script>
    <?php unset($_SESSION['success_msg']); ?>
  <?php endif; ?>

  <?php if ($is_admin): ?>
  <!-- Pending Approvals Queue -->
  <div style="width: 100%; margin: 0 0 20px 0;">
    <div class="widget-card" style="border-radius: 20px; border: 1px solid #eef2f6; box-shadow: 0 6px 25px rgba(0,0,0,0.03);">
      <div class="widget-header" style="padding: 22px 24px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; text-transform: none; letter-spacing: 0;">
        <span class="widget-header-title" style="font-size: 16px; font-weight: 700; color: #334155;">Pending Approvals</span>
        <span style="background: #f37b1d; color: #fff; padding: 2px 10px; border-radius: 20px; font-size: 12px; font-weight: 700;">
          <?php echo $expenseHandler->getPendingCount(); ?> Pending
        </span>
      </div>
      <div class="widget-body" style="padding: 0;">
        <?php
        $pending_res = $conn->query("SELECT e.*, u.first_name, u.last_name, ec.category_name
                                     FROM expenses e
                                     JOIN users u ON e.user_id = u.user_id
                                     JOIN expense_categories ec ON e.category_id = ec.id
                                     WHERE e.status = 'pending' ORDER BY e.created_at ASC");
        if ($pending_res && $pending_res->num_rows > 0):
        ?>
        <div style="display:flex;flex-direction:column;">
          <?php while($row = $pending_res->fetch_assoc()): ?>
          <div style="display:flex;align-items:center;gap:20px;background:#fff;border-bottom:1px solid #f1f5f9;padding:20px 24px;transition:all 0.2s ease;" onmouseover="this.style.backgroundColor='#fafbfc'" onmouseout="this.style.backgroundColor='#fff'">

            <!-- Icon -->
            <div style="width:42px;height:42px;background:#edf7f3;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <svg viewBox="0 0 24 24" style="width:20px;height:20px;fill:none;stroke:#186D55;stroke-width:1.75;stroke-linecap:round;stroke-linejoin:round;">
                <path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                <circle cx="12" cy="12" r="2"/>
              </svg>
            </div>

            <!-- Name + subtitle -->
            <div style="flex:1;min-width:0;">
              <div style="font-size:14px;font-weight:700;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
              </div>
              <div style="font-size:12px;color:#64748b;margin-top:3px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                <span><?php echo htmlspecialchars($row['category_name']); ?></span>
                <span style="width:3px;height:3px;background:#cbd5e1;border-radius:50%;display:inline-block;"></span>
                <span><?php echo htmlspecialchars($row['bill_date']); ?></span>
                <?php if (!empty($row['description'])): ?>
                  <span style="width:3px;height:3px;background:#cbd5e1;border-radius:50%;display:inline-block;"></span>
                  <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px;"><?php echo htmlspecialchars($row['description']); ?></span>
                <?php endif; ?>
              </div>
            </div>

            <!-- Amount pill -->
            <div style="flex-shrink:0;background:#f8fafc;border:1px solid #e2e8f0;border-radius:30px;padding:6px 16px;font-size:13px;font-weight:800;color:#334155;white-space:nowrap;">
              Rs <?php echo number_format($row['amount'], 2); ?>
            </div>

            <?php if (!empty($row['attachment_path'])): ?>
            <a href="javascript:void(0)" onclick="openReceiptModal('<?php echo htmlspecialchars($row['attachment_path']); ?>')" title="View Receipt"
               style="flex-shrink:0;width:34px;height:34px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#64748b;text-decoration:none;transition:0.2s;"
               onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
              <svg viewBox="0 0 24 24" style="width:15px;height:15px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
              </svg>
            </a>
            <?php endif; ?>

            <!-- Action buttons -->
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
              <form action="expenses.php" method="POST" style="margin:0;">
                <input type="hidden" name="expense_id" value="<?php echo $row['id']; ?>">
                <input type="hidden" name="action" value="approve">
                <button type="submit" style="height:34px;padding:0 18px;background:var(--brand-green);color:#fff;border:none;border-radius:30px;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                  <svg viewBox="0 0 24 24" style="width:13px;height:13px;fill:none;stroke:currentColor;stroke-width:2.5;stroke-linecap:round;"><polyline points="20 6 9 17 4 12"/></svg>
                  Approve
                </button>
              </form>
              <form action="expenses.php" method="POST" style="margin:0;display:flex;align-items:center;gap:6px;">
                <input type="hidden" name="expense_id" value="<?php echo $row['id']; ?>">
                <input type="hidden" name="action" value="reject">
                <input type="text" name="reject_reason" placeholder="Reason..."
                       id="reason-<?php echo $row['id']; ?>"
                       style="display:none;height:34px;padding:0 14px;border:1px solid #e2e8f0;border-radius:30px;font-size:12px;color:#1e293b;width:140px;outline:none;"
                       onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='#e2e8f0'">
                <button type="button" id="reject-btn-<?php echo $row['id']; ?>"
                  onclick="toggleReject(<?php echo $row['id']; ?>)"
                  style="height:34px;padding:0 18px;background:#fff;color:#ef4444;border:1px solid #ef4444;border-radius:30px;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;"
                  onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'">
                  <svg viewBox="0 0 24 24" style="width:13px;height:13px;fill:none;stroke:currentColor;stroke-width:2.5;stroke-linecap:round;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  Reject
                </button>
                <button type="submit" id="confirm-reject-<?php echo $row['id']; ?>"
                  style="display:none;height:34px;padding:0 14px;background:#ef4444;color:#fff;border:none;border-radius:30px;font-size:12px;font-weight:700;cursor:pointer;">
                  Confirm
                </button>
              </form>
            </div>

          </div>
          <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div style="padding:32px;text-align:center;">
          <div style="width:48px;height:48px;background:#f1f5f9;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
            <svg viewBox="0 0 24 24" style="width:22px;height:22px;fill:none;stroke:#94a3b8;stroke-width:1.75;stroke-linecap:round;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
          <div style="font-size:14px;font-weight:700;color:#334155;">All caught up!</div>
          <div style="font-size:13px;color:#94a3b8;margin-top:4px;">No pending expenses to approve.</div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Full Width Form Wrapper -->
  <div style="width: 100%; margin: 0 0 20px 0;">
    <div class="widget-card" style="border-radius: 20px; border: 1px solid #eef2f6; box-shadow: 0 6px 25px rgba(0,0,0,0.03);">
      <div class="widget-header" style="padding: 22px 24px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; text-transform: none; letter-spacing: 0;">
        <span class="widget-header-title" style="font-size: 16px; font-weight: 700; color: #334155;">
          <?php echo $edit_mode ? 'Edit Bill Details' : 'Record Expenses'; ?>
        </span>
        <div style="display: flex; align-items: center; gap: 12px;">
          <?php if ($edit_mode): ?>
            <a href="expenses.php" style="font-size: 12px; color: var(--brand-green); font-weight:800; text-decoration:none; text-transform: uppercase; letter-spacing: 0.5px; margin-right: 8px;">Cancel Edit</a>
          <?php endif; ?>
          <!-- Interactive chevron toggle capsule seen in reference screenshot -->
          <div style="width: 26px; height: 26px; border-radius: 50%; background: #eff2f6; display: flex; align-items: center; justify-content: center; color: #475569; cursor: pointer;">
            <svg viewBox="0 0 24 24" style="width: 13px; height: 13px; stroke: currentColor; stroke-width: 3; fill: none;"><path d="M18 15l-6-6-6 6"/></svg>
          </div>
        </div>
      </div>
      <div class="widget-body" style="padding: 28px;">
        <form action="expenses.php<?php echo $edit_mode ? '?edit=' . $edit_data['id'] : ''; ?>" method="POST" enctype="multipart/form-data" class="expense-grid-form">
          
          <!-- Category -->
          <div class="form-group" style="margin: 0;">
            <label>Expense Category <span style="color: var(--danger);">*</span></label>
            <select name="category_id" id="category_select" class="form-select" style="height: 44px; font-weight: 600;" onchange="toggleNewCategoryField()" required>
              <option value="" disabled selected>Select Category</option>
              <?php
              $all_cats = $conn->query("SELECT * FROM expense_categories ORDER BY category_name ASC");
              while ($c = $all_cats->fetch_assoc()) {
                  $selected = ($edit_mode && $edit_data['category_id'] == $c['id']) ? 'selected' : '';
                  echo "<option value='{$c['id']}' $selected>" . htmlspecialchars($c['category_name']) . "</option>";
              }
              ?>
              <option value="-1" style="background: #f1f5f9; font-weight: 700;">+ Add New Category...</option>
            </select>
            <!-- Dynamic Category Input (Hidden by default) -->
            <div id="new_category_wrapper" style="display: none; margin-top: 10px;">
                <input type="text" name="new_category_name" id="new_category_name" class="form-input" style="border-color: var(--brand-green); height: 40px;" placeholder="Enter new category name...">
            </div>
          </div>

          <!-- Amount -->
          <div class="form-group" style="margin: 0;">
            <label>Amount (Rs) <span style="color: var(--danger);">*</span></label>
            <div class="input-container">
              <span class="currency-prefix" style="height: 44px;">Rs</span>
              <input type="number" step="0.01" name="amount" class="form-input form-input-prefix" style="height: 44px; font-weight: 700; font-size: 15px;" placeholder="0.00" value="<?php echo $edit_mode ? htmlspecialchars($edit_data['amount']) : ''; ?>" required>
            </div>
          </div>

          <!-- Date -->
          <div class="form-group" style="margin: 0;">
            <label>Billing Date <span style="color: var(--danger);">*</span></label>
            <input type="date" name="bill_date" class="form-input" style="height: 44px;" value="<?php echo $edit_mode ? htmlspecialchars($edit_data['bill_date']) : date('Y-m-d'); ?>" required>
          </div>

          <!-- Removed Status (now handled automatically based on role) -->

          <!-- Reference -->
          <div class="form-group" style="margin: 0;">
            <label>Reference / Invoice #</label>
            <input type="text" name="invoice_number" class="form-input" style="height: 44px;" placeholder="e.g. INV-2026-103" value="<?php echo $edit_mode ? htmlspecialchars($edit_data['invoice_number']) : ''; ?>">
          </div>

          <!-- Receipt -->
          <div class="form-group" style="margin: 0;">
            <label>Attach Receipt (PDF / Image)</label>
            <div class="upload-zone" style="height: 44px; padding: 0 20px; display: flex; align-items: center; justify-content: flex-start; gap: 10px; border-style: dashed; border-width: 1.5px;">
              <input type="file" name="attachment" class="upload-file-input" accept=".pdf,image/*" style="z-index: 10;" onchange="updateFileName(this)">
              <div class="upload-zone-icon" style="margin: 0; display: flex; align-items: center;">
                <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; stroke: var(--text-muted); stroke-width: 2.2; fill: none;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
              </div>
              <div class="upload-zone-text" id="file-upload-text" style="font-size: 13.5px; color: #64748b; font-weight: 600;">
                <?php echo ($edit_mode && !empty($edit_data['attachment_path'])) ? "File: " . htmlspecialchars($edit_data['attachment_path']) : "Click to upload receipt..."; ?>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div class="form-group form-group-span-all" style="margin: 0;">
            <label>Description / Notes</label>
            <textarea name="description" class="form-textarea" style="height: 80px; padding: 12px 16px;" placeholder="Provide details about the expense..."><?php echo $edit_mode ? htmlspecialchars($edit_data['pure_description']) : ''; ?></textarea>
          </div>

          <!-- Footer Separator & Action Layout modeled exactly from reference image -->
          <div class="form-footer-actions">
            <!-- Secondary Outline Pill -->
            <a href="expenses.php" style="display: flex; align-items: center; justify-content: center; height: 38px; padding: 0 32px; border-radius: 25px; border: 1.5px solid var(--brand-green); color: var(--brand-green); font-weight: 700; font-size: 13px; text-decoration: none; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">Reset</a>
            
            <!-- Primary Filled Pill -->
            <button type="submit" name="submit_expense" style="display: flex; align-items: center; justify-content: center; gap: 8px; height: 38px; padding: 0 36px; border-radius: 25px; background: var(--brand-green); color: #ffffff; border: none; font-weight: 700; font-size: 13px; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 10px rgba(24, 109, 85, 0.15);">
              <svg viewBox="0 0 24 24" style="width: 15px; height: 15px; stroke: currentColor; stroke-width: 2.5; fill: none;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
              <?php echo $edit_mode ? 'Update Record' : 'Save Expense'; ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

</div> <!-- End Container -->

<!-- PDF / Receipt Preview Modal -->
<div class="modal-overlay" id="receiptModal" onclick="closeReceiptModal()">
  <div class="modal-content" onclick="event.stopPropagation()">
    <div class="modal-header">
      <span class="modal-title" id="receiptModalTitle">Receipt Preview</span>
      <button class="modal-close-btn" onclick="closeReceiptModal()">
        <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body" id="receiptModalBody">
      <!-- Embedded receipt goes here -->
    </div>
  </div>
</div>

<script>
  // Update file name in upload zone
  function updateFileName(input) {
      const display = document.getElementById('file-upload-text');
      if (input.files && input.files.length > 0) {
          display.textContent = "Selected: " + input.files[0].name;
      }
  }

  // Modal Open/Close
  function openReceiptModal(path) {
      const modal = document.getElementById('receiptModal');
      const body = document.getElementById('receiptModalBody');
      const title = document.getElementById('receiptModalTitle');
      
      const fileUrl = "../assets/uploads/receipts/" + path;
      const fileExt = path.split('.').pop().toLowerCase();
      
      title.textContent = "Receipt Preview (" + path + ")";
      
      if (fileExt === 'pdf') {
          body.innerHTML = `<iframe src="${fileUrl}"></iframe>`;
      } else {
          body.innerHTML = `<div style="display:flex; align-items:center; justify-content:center; height:100%; overflow:auto; background:#1e293b;"><img src="${fileUrl}" style="max-width:100%; max-height:100%; object-fit:contain;"></div>`;
      }
      
      modal.classList.add('show');
  }

  function closeReceiptModal() {
      document.getElementById('receiptModal').classList.remove('show');
      document.getElementById('receiptModalBody').innerHTML = '';
  }

  // Dynamic Category Toggle
  function toggleNewCategoryField() {
      const select = document.getElementById('category_select');
      const wrapper = document.getElementById('new_category_wrapper');
      const input = document.getElementById('new_category_name');
      
      if (select.value === "-1") {
          wrapper.style.display = 'block';
          input.focus();
          input.required = true;
      } else {
          wrapper.style.display = 'none';
          input.required = false;
      }
  }

  function toggleReject(id) {
      const reasonInput = document.getElementById('reason-' + id);
      const rejectBtn   = document.getElementById('reject-btn-' + id);
      const confirmBtn  = document.getElementById('confirm-reject-' + id);
      const isVisible   = reasonInput.style.display !== 'none';

      if (isVisible) {
          // Collapse back
          reasonInput.style.display = 'none';
          confirmBtn.style.display  = 'none';
          rejectBtn.innerHTML = '<svg viewBox="0 0 24 24" style="width:13px;height:13px;fill:none;stroke:currentColor;stroke-width:2.5;stroke-linecap:round;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Reject';
      } else {
          // Expand — show reason input and confirm button
          reasonInput.style.display = 'inline-block';
          confirmBtn.style.display  = 'inline-block';
          reasonInput.required = true;
          reasonInput.focus();
          rejectBtn.innerHTML = '✕';
      }
  }
</script>


<?php include_once "../includes/footer.php"; ?>
