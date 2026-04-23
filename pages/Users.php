<?php

class Users {
  public $name;
  public $email;
  public $password;
  private $connection;
  public $role;
  public $errors = array();

  public function __construct($db) {
    $this->connection = $db;
  }

  public function logIn() {
    if (isset($_POST['login'])) {
        $this->email = $_POST['email'];
        $this->password = $_POST['password'];
        $this->role = $_POST['role']??'';

        // Server-side validation
        if (empty($this->email)) {
            $this->errors['email'] = "Email is required.";
        }
        if (empty($this->password)) {
            $this->errors['password'] = "Password is required.";
        }
        if (empty($this->role)) {
            $this->errors['role'] = "Role is required.";
        }

        if (count($this->errors) > 0) {
            $this->errors['general'] = "Fix the errors below.";
        } else {
            

            $query = "SELECT users.*, roles.role_name 
                      FROM users 
                      JOIN roles ON users.role_id = roles.role_id 
                      WHERE users.email = '$this->email' AND roles.role_name = '$this->role'
                      LIMIT 1";
            
            
            $result = $this->connection->query($query);
            
            
            if ($result && $result->num_rows > 0) {
                // Row found - get the user data
                $row = $result->fetch_assoc();
                
                
                if (password_verify($this->password, $row['password_hash'])) {
                    
                    
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    
                    $_SESSION['user_id']   = $row['user_id'];
                    $_SESSION['role_name']      = $row['role_name'];
                    $_SESSION['email']     = $row['email'];
                    $_SESSION['logged_in'] = true;
                    if($row['role_name'] === 'admin') {
                    header("Location: administrator_dashboard.php"); 
                    } else {
                        header("Location: employee_dashboard.php"); 
                    }
                    exit(); 
                } else {
                    $this->errors['general'] = "Invalidbjhv email or password.";  
                }
            } else {
                $this->errors['general'] = "Invalid email ddvor password.";
            }
        }
    }
  }
}
?>