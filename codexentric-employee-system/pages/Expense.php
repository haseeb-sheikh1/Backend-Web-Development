<?php
class Expense {
    private $connection;

    public function __construct($db) {
        $this->connection = $db;
    }

    // Approve an expense
    public function approveExpense($expense_id, $admin_user_id) {
        $query = "UPDATE expenses SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        if ($stmt) {
            $stmt->bind_param("ii", $admin_user_id, $expense_id);
            if ($stmt->execute()) {
                $stmt->close();
                return true;
            }
            $stmt->close();
        }
        return false;
    }

    // Reject an expense with a reason
    public function rejectExpense($expense_id, $admin_user_id, $reason) {
        $query = "UPDATE expenses SET status = 'rejected', approved_by = ?, approved_at = NOW(), rejection_reason = ? WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        if ($stmt) {
            $stmt->bind_param("isi", $admin_user_id, $reason, $expense_id);
            if ($stmt->execute()) {
                $stmt->close();
                return true;
            }
            $stmt->close();
        }
        return false;
    }

    // Get count of pending expenses (for sidebar badge)
    public function getPendingCount() {
        $query = "SELECT COUNT(*) as total FROM expenses WHERE status = 'pending'";
        $result = $this->connection->query($query);
        if ($result && $row = $result->fetch_assoc()) {
            return (int)$row['total'];
        }
        return 0;
    }
}
?>
