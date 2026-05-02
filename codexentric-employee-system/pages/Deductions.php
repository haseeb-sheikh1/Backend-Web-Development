<?php

class Deduction {
    private $connection;

    public function __construct($dbConnection) {
        $this->connection = $dbConnection;
    }

    // Fetch all active deductions for the admin to select from
    public function getAllDeductions() {
        $query = "SELECT id, name FROM deductions ORDER BY name ASC";
        $stmt = $this->connection->prepare($query);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        return [];
    }
}

?>