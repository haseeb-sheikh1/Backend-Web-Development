<?php 
class Attendance {
    private $connection;
    private $table = 'attendance';

    public function __construct($dbConnection) {
        $this->connection = $dbConnection;
    }

    // Fetching attendance records for a specific employee
    public function punchInTime($employeeId, $note = null) {
        $query = "INSERT INTO " . $this->table . " (employee_id, attendance_date, punch_in_time, punch_in_note) VALUES (?, NOW(), NOW(), ?)";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("is", $employeeId, $note);
        return $stmt->execute();
     
    }

    public function punchOutTime($employeeId, $note = null) {
        $query = "UPDATE " . $this->table . " SET punch_out_time = NOW(), punch_out_note = ? WHERE employee_id = ? AND attendance_date = CURDATE() AND punch_out_time IS NULL";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("si", $note, $employeeId);
        return $stmt->execute();
    }

    public function getAttendanceDetails($employeeId) {
      $query = "SELECT * FROM " . $this->table . " WHERE employee_id = ? ORDER BY attendance_date DESC";
      $stmt = $this->connection->prepare($query);
      $stmt->bind_param("i", $employeeId);
      $stmt->execute();
    }

  

}
 ?>
