<?php
class Allowance {
private $connection;
// creating connection
public function __construct($dbConnection) {
$this->connection = $dbConnection;
}
public function getAllAllowances() {
$query = "SELECT id, name FROM allowances ORDER BY name ASC";
$stmt = $this->connection->prepare($query);


if ($stmt->execute()) {
// Get the result set from the executed statement
$result = $stmt->get_result();

// to fetch all rows as an associative array
return $result->fetch_all(MYSQLI_ASSOC);
}

// Return an empty array if the execution fails, to prevent errors elsewhere
return [];
}

}
?>