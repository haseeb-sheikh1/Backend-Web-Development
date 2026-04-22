<?php

$host = 'localhost'; // Usually localhost
$db   = 'ems_db';    
$user = 'root';      // Your database username (default for XAMPP/WAMP is 'root')
$pass = '';          // Your database password (default is usually empty)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    // Set PDO to throw exceptions on errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>