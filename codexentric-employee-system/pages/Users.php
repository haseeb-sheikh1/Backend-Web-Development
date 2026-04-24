<?php
class Users {
  public $Fname;
  public $Lname;
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
       

        // Server-side validation
        if (empty($this->email)) {
            $this->errors['email'] = "Email is required.";
        }
        if (empty($this->password)) {
            $this->errors['password'] = "Password is required.";
        }
   

        if (count($this->errors) > 0) {
            $this->errors['general'] = "Fix the errors below.";
        } else {
            
            $query = "SELECT * FROM users WHERE email = '$this->email' LIMIT 1";
            
            $result = $this->connection->query($query);
            
            
            if ($result && $result->num_rows > 0) {
                // Row found - get the user data
                $row = $result->fetch_assoc();
                
                
                if (password_verify($this->password, $row['password_hash'])) {
                    
      
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    
                    $_SESSION['user_id']   = $row['user_id'];
                    $_SESSION['first_name'] = $row['first_name'];
                    $_SESSION['last_name']  = $row['last_name'];
                    $_SESSION['role_id']  = $row['role_id'];
                    $_SESSION['email']     = $row['email'];
                    $_SESSION['logged_in'] = true;
                    if($row['role_id'] === '1') {
                    header("Location: administrator_dashboard.php"); 
                    } else {
                        header("Location: employee_dashboard.php"); 
                    }
                    exit(); 
                } else {
                    $this->errors['general'] = "Invalidbjhv email or password.";  
                }
            } 
            else {
                $this->errors['general'] = "Invalid email ddvor password.";
            }
        }
    }
    
   }

 
public function registerUser() {
    if (isset($_POST['register'])) {

        // data from form
        $this->Fname     = trim($_POST['first_name']);
        $this->Lname     = trim($_POST['last_name']);
        $this->email     = trim($_POST['email']);
        $this->password  = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        // validation
        if (empty($this->Fname)) {
            $this->errors['first_name'] = "First name is required.";
        }
        if (empty($this->Lname)) {
            $this->errors['last_name'] = "Last name is required.";
        }
        if (empty($this->email)) {
            $this->errors['email'] = "Email is required.";
        } 
        if (empty($this->password)) {
            $this->errors['password'] = "Password is required.";
        } elseif (strlen($this->password) < 8) {
            $this->errors['password'] = "Password must be at least 8 characters.";
        }
        if (empty($confirmPassword)) {
            $this->errors['confirm_password'] = "Please confirm your password.";
        } elseif ($this->password !== $confirmPassword) {
            $this->errors['confirm_password'] = "Passwords do not match.";
        }

        // if error is found
        if (count($this->errors) > 0) return;

        // if email is already in the database
        $checkQuery  = "SELECT user_id FROM users WHERE email = '$this->email' LIMIT 1";
        $checkResult = $this->connection->query($checkQuery);

        if ($checkResult->num_rows > 0) {
            $this->errors['email'] = "An account with this email already exists.";
            return;
        }

        // getting employee role id
        $roleQuery  = "SELECT role_id FROM roles WHERE role_name = 'employee' LIMIT 1";
        $roleResult = $this->connection->query($roleQuery);
        $roleRow    = $roleResult->fetch_assoc();

        if (!$roleRow) {
            $this->errors['general'] = "Role setup error. Contact admin.";
            return;
        }
        $roleId = $roleRow['role_id'];

        // hashing password
        $hashedPassword = password_hash($this->password, PASSWORD_BCRYPT);

        // Insert into users table 
        $insertUserQuery  = "INSERT INTO users (first_name, last_name, email, password_hash, role_id) VALUES ('$this->Fname', '$this->Lname', '$this->email', '$hashedPassword', '$roleId')";
        $insertUserResult = $this->connection->query($insertUserQuery);

        if (!$insertUserResult) {
            $this->errors['general'] = "Registration failed: " . $this->connection->error;
            return;
        }
        // to get the new user_id
        $newUserId = $this->connection->insert_id;
        // Insert into employees ta
       $insertEmpQuery  = "INSERT INTO employees (user_id) VALUES ('$newUserId')";
        $insertEmpResult = $this->connection->query($insertEmpQuery);
        if (!$insertEmpResult) {
            $this->errors['general'] = "Employee insert failed: " . $this->connection->error;
            // Rollback user insertion that happened previously if both the queries don't run
            $this->connection->query("DELETE FROM users WHERE user_id = '$newUserId'");
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id']   = $newUserId;
        $_SESSION['role_name'] = 'employee';
        $_SESSION['email']     = $this->email;
        $_SESSION['logged_in'] = true;

        header("Location: employee_dashboard.php");
        exit();
    }
}
}
?>