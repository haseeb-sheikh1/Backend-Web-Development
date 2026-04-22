<?php
session_start();
require "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    // 1. Original safe query (no employee table joins to break the admin login)
    $sql = "SELECT u.user_id, u.password_hash, r.role_name
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            WHERE u.email = :email";
  
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($pass, $user['password_hash'])) {
        
        // 2. Set base session variables
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['user_id'];
        
        $db_role = strtolower($user['role_name']);
        
        // 3. ADMIN LOGIC
        if ($db_role === 'administrator' || $db_role === 'admin') {
            $_SESSION['user_role'] = 'admin';
            $_SESSION['user_name'] = 'Admin'; // Hardcoded as requested
            $_SESSION['user_initials'] = 'AD';
            
            header("Location: administrator_dashboard.php");
            exit();
        } 
        // 4. EMPLOYEE LOGIC
        else {
            $_SESSION['user_role'] = 'employee';
            // You can hardcode this as 'Employee' or run a second query here to fetch 
            // their specific name from the employee table using their user_id
            $_SESSION['user_name'] = 'Employee'; 
            $_SESSION['user_initials'] = 'EM';
            
            header("Location: employee_dashboard.php");
            exit();
        }
        
    } else {
        echo "Invalid credentials. <a href='login.php'>Try again</a>";
    }
}
?>