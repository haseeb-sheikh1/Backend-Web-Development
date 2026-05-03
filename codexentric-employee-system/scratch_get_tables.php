<?php
require_once 'pages/database.php';
$db = new Database();
$conn = $db->getConnection();

$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_row()) {
    $table = $row[0];
    echo "TABLE: $table\n";
    $cols = $conn->query("DESCRIBE $table");
    while ($col = $cols->fetch_assoc()) {
        echo "  " . $col['Field'] . "\n";
    }
}
