<?php
class Payroll {
    private $db;

    public function __construct($dbConnection){
        $this->db = $dbConnection;
    }

    public function getEmployeeIdByUserId($user_id) {
        $stmt = $this->db->prepare("SELECT employee_id FROM employees WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            return $row['employee_id'];
        }
        return false;
    }

    public function isSalaryProcessed($employee_id, $payroll_month) {
        $stmt = $this->db->prepare("SELECT id FROM payroll WHERE employee_id = ? AND payroll_month = ?");
        $stmt->bind_param("is", $employee_id, $payroll_month);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->num_rows > 0;
    }

    public function getSavedSalaryBreakdown($employee_id, $payroll_month) {
        $stmt = $this->db->prepare("SELECT id, net_payable_rs FROM payroll WHERE employee_id = ? AND payroll_month = ?");
        $stmt->bind_param("is", $employee_id, $payroll_month);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $payroll_id = $row['id'];
            $breakdown = [
                'net_payable' => $row['net_payable_rs'],
                'bonuses' => [],
                'allowances' => [],
                'deductions' => []
            ];

            $b_stmt = $this->db->prepare("SELECT b.name, ba.amount FROM bonus_allowance ba JOIN bonus b ON ba.bonus_id = b.id WHERE ba.payroll_id = ?");
            $b_stmt->bind_param("i", $payroll_id);
            $b_stmt->execute();
            $b_res = $b_stmt->get_result();
            while ($b_row = $b_res->fetch_assoc()) { $breakdown['bonuses'][] = $b_row; }

            $a_stmt = $this->db->prepare("SELECT a.name, pa.amount_rs as amount FROM payroll_allowances pa JOIN allowances a ON pa.allowance_id = a.id WHERE pa.payroll_id = ?");
            $a_stmt->bind_param("i", $payroll_id);
            $a_stmt->execute();
            $a_res = $a_stmt->get_result();
            while ($a_row = $a_res->fetch_assoc()) { $breakdown['allowances'][] = $a_row; }

            $d_stmt = $this->db->prepare("SELECT d.name, pd.amount_rs as amount FROM payroll_deductions pd JOIN deductions d ON pd.deduction_id = d.id WHERE pd.payroll_id = ?");
            $d_stmt->bind_param("i", $payroll_id);
            $d_stmt->execute();
            $d_res = $d_stmt->get_result();
            while ($d_row = $d_res->fetch_assoc()) { $breakdown['deductions'][] = $d_row; }

            return $breakdown;
        }
        return false;
    }

    public function getMonthlyPayrollStats($month) {
        $stats = [
            'gross_total' => 0,
            'total_deductions' => 0,
            'net_payable' => 0,
            'processed_count' => 0
        ];
        
        $stmt = $this->db->prepare("SELECT SUM(base_salary_rs) as base_total, SUM(net_payable_rs) as net_total, COUNT(id) as count FROM payroll WHERE payroll_month = ?");
        $stmt->bind_param("s", $month);
        $stmt->execute();
        $res = $stmt->get_result();
        $base_total = 0;
        if ($row = $res->fetch_assoc()) {
            $stats['net_payable'] = (float)$row['net_total'];
            $stats['processed_count'] = (int)$row['count'];
            $base_total = (float)$row['base_total'];
        }

        if ($stats['processed_count'] > 0) {
            $stmtD = $this->db->prepare("SELECT SUM(pd.amount_rs) as t FROM payroll_deductions pd JOIN payroll p ON pd.payroll_id = p.id WHERE p.payroll_month = ?");
            $stmtD->bind_param("s", $month);
            $stmtD->execute();
            if ($rowD = $stmtD->get_result()->fetch_assoc()) $stats['total_deductions'] = (float)$rowD['t'];

            $total_bonuses = 0;
            $stmtB = $this->db->prepare("SELECT SUM(ba.amount) as t FROM bonus_allowance ba JOIN payroll p ON ba.payroll_id = p.id WHERE p.payroll_month = ?");
            $stmtB->bind_param("s", $month);
            $stmtB->execute();
            if ($rowB = $stmtB->get_result()->fetch_assoc()) $total_bonuses += (float)$rowB['t'];

            $stmtA = $this->db->prepare("SELECT SUM(pa.amount_rs) as t FROM payroll_allowances pa JOIN payroll p ON pa.payroll_id = p.id WHERE p.payroll_month = ?");
            $stmtA->bind_param("s", $month);
            $stmtA->execute();
            if ($rowA = $stmtA->get_result()->fetch_assoc()) $total_bonuses += (float)$rowA['t'];

            $stats['gross_total'] = $base_total + $total_bonuses;
        }

        return $stats;
    }

    public function processSalary($user_id, $payroll_month, $base_salary, $b_names, $b_amounts, $a_names, $a_amounts, $d_names, $d_amounts) {
        $employee_id = $this->getEmployeeIdByUserId($user_id);
        if (!$employee_id) return "Employee not found.";

        if ($this->isSalaryProcessed($employee_id, $payroll_month)) {
            return "Salary already processed for this month.";
        }

        // Calculate totals
        $total_bonuses = 0;
        foreach ($b_amounts as $amt) {
            $total_bonuses += (float)$amt;
        }

        $total_allowances = 0;
        foreach ($a_amounts as $amt) {
            $total_allowances += (float)$amt;
        }

        // Add fixed allowance from DB
        $stmtFixed = $this->db->prepare("SELECT allowances_rs FROM employees WHERE employee_id = ?");
        $stmtFixed->bind_param("i", $employee_id);
        $stmtFixed->execute();
        $resFixed = $stmtFixed->get_result();
        if ($rowFixed = $resFixed->fetch_assoc()) {
            $total_allowances += (float)$rowFixed['allowances_rs'];
        }

        $total_deductions = 0;
        foreach ($d_amounts as $amt) {
            $total_deductions += (float)$amt;
        }

        $net_payable = $base_salary + $total_bonuses + $total_allowances - $total_deductions;
        $status = 'PROCESSED';

        $this->db->begin_transaction();

        try {
            // Insert Main Record
            $stmt = $this->db->prepare("INSERT INTO payroll (employee_id, payroll_month, base_salary_rs, net_payable_rs, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isdds", $employee_id, $payroll_month, $base_salary, $net_payable, $status);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert payroll record.");
            }
            
            $payroll_id = $this->db->insert_id;

            // Insert Bonuses
            if (!empty($b_names) && !empty($b_amounts)) {
                $stmtBonus = $this->db->prepare("INSERT INTO bonus_allowance (payroll_id, bonus_id, amount) VALUES (?, ?, ?)");
                foreach ($b_names as $index => $b_id) {
                    $amt = (float)($b_amounts[$index] ?? 0);
                    if ($b_id && $amt > 0) {
                        $stmtBonus->bind_param("iid", $payroll_id, $b_id, $amt);
                        $stmtBonus->execute();
                    }
                }
            }

            // Insert Allowances
            if (!empty($a_names) && !empty($a_amounts)) {
                $stmtAllow = $this->db->prepare("INSERT INTO payroll_allowances (payroll_id, allowance_id, amount_rs) VALUES (?, ?, ?)");
                foreach ($a_names as $index => $a_id) {
                    $amt = (float)($a_amounts[$index] ?? 0);
                    if ($a_id && $amt > 0) {
                        $stmtAllow->bind_param("iid", $payroll_id, $a_id, $amt);
                        $stmtAllow->execute();
                    }
                }
            }

            // Insert Deductions
            if (!empty($d_names) && !empty($d_amounts)) {
                $stmtDeduct = $this->db->prepare("INSERT INTO payroll_deductions (payroll_id, deduction_id, amount_rs) VALUES (?, ?, ?)");
                foreach ($d_names as $index => $d_id) {
                    $amt = (float)($d_amounts[$index] ?? 0);
                    if ($d_id && $amt > 0) {
                        $stmtDeduct->bind_param("iid", $payroll_id, $d_id, $amt);
                        $stmtDeduct->execute();
                    }
                }
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            return "Error processing salary: " . $e->getMessage();
        }
    }
}
?>