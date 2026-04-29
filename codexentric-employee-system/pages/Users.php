<?php
class Users {
  public $Fname;
  public $Lname;
  public $email;
  public $password;
  public $profile_image;
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
            $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
            
            // Preparing the statement
            $stmt = $this->connection->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("s", $this->email);
                
                // Executing the statement
                $stmt->execute();
                
                // Getting the result set
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    // Row found - get the user data
                    $row = $result->fetch_assoc();
                    
                    if (password_verify($this->password, $row['password_hash'])) {
                        
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }
                        
                        $_SESSION['user_id']    = $row['user_id'];
                        $_SESSION['first_name'] = $row['first_name'];
                        $_SESSION['last_name']  = $row['last_name'];
                        $_SESSION['role_id']    = $row['role_id'];
                        $_SESSION['email']      = $row['email'];
                        $_SESSION['logged_in']  = true;

                        if ($_SESSION['role_id'] == '1') {
                            header("Location:administrator_dashboard.php"); 
                        } else {
                            header("Location: employee_dashboard.php"); 
                        }
                        exit(); 
                    } else {
                        $this->errors['general'] = "Invalid email or password.";  
                    }
                } else {
                    $this->errors['general'] = "Invalid email or password.";
                }

                // Closing the statement
                $stmt->close();
            } else {
                // Failsafe in case the database connection or query preparation fails
                $this->errors['general'] = "A database error occurred. Please try again.";
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
        $checkQuery = "SELECT user_id FROM users WHERE email = ? LIMIT 1";
        $stmtCheck = $this->connection->prepare($checkQuery);
        $stmtCheck->bind_param("s", $this->email);
        $stmtCheck->execute();
        $checkResult = $stmtCheck->get_result();

        if ($checkResult->num_rows > 0) {
            $this->errors['email'] = "An account with this email already exists.";
            $stmtCheck->close();
            return;
        }
        $stmtCheck->close();

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
        $insertUserQuery  = "INSERT INTO users (first_name, last_name, email, password_hash, role_id) VALUES (?, ?, ?, ?, ?)";
        $stmtUser = $this->connection->prepare($insertUserQuery);
        $stmtUser->bind_param("ssssi", $this->Fname, $this->Lname, $this->email, $hashedPassword, $roleId);
        $insertUserResult = $stmtUser->execute();

        if (!$insertUserResult) {
            $this->errors['general'] = "Registration failed: " . $this->connection->error;
            $stmtUser->close();
            return;
        }
        
        // to get the new user_id
        $newUserId = $this->connection->insert_id;
        $stmtUser->close();

        // Insert into employees table
        $insertEmpQuery  = "INSERT INTO employees (user_id) VALUES (?)";
        $stmtEmp = $this->connection->prepare($insertEmpQuery);
        $stmtEmp->bind_param("i", $newUserId);
        $insertEmpResult = $stmtEmp->execute();
        
        if (!$insertEmpResult) {
            $this->errors['general'] = "Employee insert failed: " . $this->connection->error;
            // Rollback user insertion that happened previously if both the queries don't run
            $stmtDelete = $this->connection->prepare("DELETE FROM users WHERE user_id = ?");
            $stmtDelete->bind_param("i", $newUserId);
            $stmtDelete->execute();
            $stmtDelete->close();
            
            $stmtEmp->close();
            return;
        }
        $stmtEmp->close();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id']   = $newUserId;
        $_SESSION['role_name'] = 'employee';
        $_SESSION['role_id']   = $roleId;
        $_SESSION['email']     = $this->email;
        $_SESSION['first_name'] = $this->Fname;
        $_SESSION['logged_in'] = true;

        header("Location: employee_dashboard.php");
        exit();
    }
}

public function uploadUserImage($file_array){
    //destination
    $destination = "../assets/";
    $file_extension = pathinfo($file_array['name'], PATHINFO_EXTENSION);
    $new_file_name = uniqid() . "." . $file_extension;
    $target_file = $destination . $new_file_name;
    // size check
    if ($file_array['size']>50000) {
        return "file too large";
    }
    //uploading to server
    if (move_uploaded_file($file_array['tmp_name'], $target_file)) {
        //update database with new file name
        $updateQuery = "UPDATE users SET profile_image = ? WHERE email = ?";
        $stmt = $this->connection->prepare($updateQuery);
        $stmt->bind_param("ss", $new_file_name, $this->email);
        if ($stmt->execute()) {
            $_SESSION['profile_image'] = $new_file_name; 
            return "file uploaded successfully";
        } else {
            return "database update failed: " . $this->connection->error;
        }
    } else {
        return "file upload failed";
    }


}
}
?>