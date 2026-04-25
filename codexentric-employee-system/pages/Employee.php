<?php 
class Employee {
  public $homeAddress;
  public $positionTitle;
  public $department;
  public $employeeType;
  public $baseSalary;
  public $allowances;
  public $bankName;
  public $bankAccountNumber;
  public $Fname;
  public $Lname;
  public $email;
  public $password;
  public $errors = [];
  private $connection;

  public function __construct($db) {
    $this->connection = $db;
  }
  
  public function createEmployee(){
    if (isset($_POST['create_employee'])) {
      
      // form data
      $this->Fname = trim($_POST['first_name'] ?? '');
      $this->Lname = trim($_POST['last_name'] ?? '');
      $this->email = trim($_POST['email'] ?? '');
      $this->password = trim($_POST['password'] ?? '');
      $this->homeAddress = trim($_POST['home_address'] ?? '');
      $this->positionTitle = trim($_POST['position_title'] ?? '');
      $this->department = trim($_POST['department'] ?? '');
      $this->employeeType = trim($_POST['employee_type'] ?? '');
      $this->baseSalary = trim($_POST['base_salary'] ?? 0);
      
      // empty string trying to insert into an INT/DECIMAL column
      $allowances_input = trim($_POST['allowances'] ?? '');
      $this->allowances = ($allowances_input === '') ? 0 : $allowances_input;
      
      $this->bankName = trim($_POST['bank_name'] ?? '');
      $this->bankAccountNumber = trim($_POST['bank_account_number'] ?? '');
      
      $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT); 
      
      $role_id = 2; 
      
      // inserting into users table using prepared statement
      $stmtUser = $this->connection->prepare("INSERT INTO users (first_name, last_name, email, password_hash, role_id) VALUES (?, ?, ?, ?, ?)");
      
      if (!$stmtUser) {
          $this->errors['general'] = "Error: " . $this->connection->error;
          return false;
      }
      
      $stmtUser->bind_param("ssssi", $this->Fname, $this->Lname, $this->email, $hashedPassword, $role_id);
      
      if (!$stmtUser->execute()) {
            $this->errors['general'] = "Error creating user: " . $stmtUser->error;
            return false;
      }
      
      $newuserId = $stmtUser->insert_id;
      $stmtUser->close();

      // Inserting into Employees Table using Prepared Statements
      $stmtEmp = $this->connection->prepare("INSERT INTO employees (user_id, home_address, position_title, department, employment_type, base_salary_rs, allowances_rs, bank_name, bank_account_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
      
      if (!$stmtEmp) {
          $this->errors['general'] = "Prepare failed for employee: " . $this->connection->error;
          // Rollback the user creation if employee preparation fails
          $this->connection->query("DELETE FROM users WHERE user_id = '$newuserId'");
          return false;
      }

     
      $stmtEmp->bind_param("isssssdss", $newuserId, $this->homeAddress, $this->positionTitle, $this->department, $this->employeeType, $this->baseSalary, $this->allowances, $this->bankName, $this->bankAccountNumber);

      if (!$stmtEmp->execute()) {
        $this->errors['general'] = "Error creating employee record: " . $stmtEmp->error;
        // Rollback the user creation
        $this->connection->query("DELETE FROM users WHERE user_id = '$newuserId'");
        $stmtEmp->close();
        return false;
      }
      
      $stmtEmp->close();
      
      //success message
      $this->errors['success'] = "Employee created successfully!";
      return true;
    }
    return false;
  }
}
?>