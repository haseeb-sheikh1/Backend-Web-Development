<?php
class Employee
{
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
  public $status;
  public $password;
  public $user_id;
  public $errors = [];
  private $connection;

  public function __construct($db)
  {
    $this->connection = $db;
  }

  public function createEmployee()
  {
    if (isset($_POST['create_employee'])) {
      
      $this->errors = []; // Reset errors

      // Only strictly require Name, Email, and Password as per request
      if (empty($_POST['first_name'])) {
        $this->errors['first_name'] = "First name is required.";
      }
      if (empty($_POST['last_name'])) {
        $this->errors['last_name'] = "Last name is required.";
      }
      if (empty($_POST['email'])) {
        $this->errors['email'] = "Email is required.";
      } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $this->errors['email'] = "Invalid email format.";
      } else {
        // Check if email already exists
        $email = trim($_POST['email']);
        $stmt = $this->connection->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $this->errors['email'] = "Email already exists.";
        }
        $stmt->close();
      }
      
      if (empty($_POST['password'])) {
        $this->errors['password'] = "Password is required.";
      } elseif (strlen($_POST['password']) < 6) {
        $this->errors['password'] = "Password must be at least 6 characters.";
      }

      // Other fields can be optional to avoid blocking the user
      // but we still capture them below.
      if (isset($_POST['base_salary']) && !empty($_POST['base_salary']) && (!is_numeric($_POST['base_salary']) || $_POST['base_salary'] < 0)) {
        $this->errors['base_salary'] = "Base salary must be a positive number.";
      }

      if (!empty($this->errors)) {
          return false; // Return early if there are validation errors
      }

      $this->Fname = trim($_POST['first_name'] ?? '');
      $this->Lname = trim($_POST['last_name'] ?? '');
      $this->email = trim($_POST['email'] ?? '');
      $this->password = trim($_POST['password'] ?? '');
      $this->homeAddress = trim($_POST['home_address'] ?? '');
      $this->positionTitle = trim($_POST['position_title'] ?? '');
      $this->department = trim($_POST['department'] ?? '');
      $this->status = trim($_POST['status'] ?? 'Active');
      $this->date_of_joining = trim($_POST['date_of_joining'] ?? date('Y-m-d'));
      $this->baseSalary = trim($_POST['base_salary'] ?? 0);

      // empty string trying to insert into an INT/DECIMAL column
      $allowances_input = trim($_POST['allowances'] ?? '');
      $this->allowances = ($allowances_input === '') ? 0 : $allowances_input;

      $this->bankName = trim($_POST['bank_name'] ?? '');
      $this->bankAccountNumber = trim($_POST['bank_account_number'] ?? '');

      $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);

      $role_id = isset($_POST['role_id']) ? (int)$_POST['role_id'] : 2;

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
      $stmtEmp = $this->connection->prepare("INSERT INTO employees (user_id, home_address, position_title, department, date_of_joining, status, base_salary_rs, allowances_rs, bank_name, bank_account_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

      if (!$stmtEmp) {
        $this->errors['general'] = "Prepare failed for employee: " . $this->connection->error;
        // Rollback the user creation if employee preparation fails
        $this->connection->query("DELETE FROM users WHERE user_id = '$newuserId'");
        return false;
      }

      $stmtEmp->bind_param("isssssddss", $newuserId, $this->homeAddress, $this->positionTitle, $this->department, $this->date_of_joining, $this->status, $this->baseSalary, $this->allowances, $this->bankName, $this->bankAccountNumber);

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
  public function getBasicEmployeeDetails()
  {
    $query = "SELECT u.first_name, u.last_name, u.email, e.position_title, e.department, e.status, e.date_of_joining, u.user_id
          From users u
          JOIN employees e ON u.user_id = e.user_id";

    $result = $this->connection->query($query);
    $employeesList = [];
    while ($row = $result->fetch_assoc()) {
      $employeesList[] = $row;
    }
    return $employeesList;
  }

  public function getAllEmployeesPayrollDetails()
  {
    $query = "SELECT u.user_id, u.first_name, u.last_name, e.bank_name, e.bank_account_number, e.base_salary_rs, e.allowances_rs
          FROM users u
          JOIN employees e ON u.user_id = e.user_id";

    $result = $this->connection->query($query);
    $employeesList = [];
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $employeesList[] = $row;
      }
    }
    return $employeesList;
  }

  public function getSalaryComponents()
  {
    $components = [
      'bonuses' => [],
      'allowances' => [],
      'deductions' => []
    ];

    $res1 = $this->connection->query("SELECT id, name FROM bonus ORDER BY name");
    if ($res1) {
      while ($row = $res1->fetch_assoc()) {
        $components['bonuses'][] = $row;
      }
    }

    $res2 = $this->connection->query("SELECT id, name FROM allowances ORDER BY name");
    if ($res2) {
      while ($row = $res2->fetch_assoc()) {
        $components['allowances'][] = $row;
      }
    }

    $res3 = $this->connection->query("SELECT id, name FROM deductions ORDER BY name");
    if ($res3) {
      while ($row = $res3->fetch_assoc()) {
        $components['deductions'][] = $row;
      }
    }

    return $components;
  }

  public function getDashboardData()
  {
      $data = [
          'total_headcount' => 0,
          'monthly_payroll' => 0,
          'team_members' => []
      ];

      $query = "SELECT u.user_id, u.first_name, u.last_name, e.position_title, e.base_salary_rs, e.status
                FROM users u
                JOIN employees e ON u.user_id = e.user_id
                ORDER BY e.date_of_joining DESC";
      $result = $this->connection->query($query);
      if ($result) {
          while ($row = $result->fetch_assoc()) {
              $data['total_headcount']++;
              $data['monthly_payroll'] += (float)$row['base_salary_rs'];
              
              if (count($data['team_members']) < 5) {
                  $badge = 'badge-active';
                  $status = strtolower($row['status'] ?? 'active');
                  if ($status === 'onboarding' || $status === 'pending') {
                      $badge = 'badge-onboarding';
                  } else if ($status === 'inactive' || $status === 'terminated') {
                      $badge = 'badge-inactive';
                  }
                  
                  $data['team_members'][] = [
                      'user_id' => $row['user_id'],
                      'name' => trim($row['first_name'] . ' ' . $row['last_name']),
                      'role' => $row['position_title'] ?: 'Employee',
                      'salary' => 'Rs ' . number_format($row['base_salary_rs']),
                      'status' => ucfirst($row['status'] ?: 'Active'),
                      'badge' => $badge
                  ];
              }
          }
      }

      return $data;
  }

  public function getEmployeeDetailsById($user_id)
  {

    $query = "SELECT u.user_id, u.first_name, u.last_name, u.email, 
                         e.home_address,e.employee_id, e.position_title, e.department, 
                         e.employment_type, e.base_salary_rs, e.allowances_rs, 
                         e.bank_name, e.bank_account_number, e.status, e.date_of_joining
                  FROM users u
                  JOIN employees e ON u.user_id = e.user_id
                  WHERE u.user_id = ?";


    $result = $this->connection->prepare($query);


    if (!$result) {
      $this->errors['general'] = "Prepare failed: " . $this->connection->error;
      return false;
    }

    //  Binding ID  securely to the '?' placeholder
    $result->bind_param("i", $user_id);
    $result->execute();

    //  Fetching the actual data
    $resultData = $result->get_result();

    // if found exactly one employee
    if ($resultData->num_rows == 1) {

      return $resultData->fetch_assoc();
    } else {
      $this->errors['general'] = "Employee not found.";
      return false;
    }
  }


  public function updateEmployeeProfile($first_name, $last_name, $email, $position_title, $department, $home_address, $status, $base_salary_rs, $allowances, $employment_type, $bank_name, $bank_account_number, $user_id)
  {


    $query = "UPDATE users u
              JOIN employees e ON u.user_id = e.user_id
              SET u.first_name = ?,
                  u.last_name = ?,
                  u.email = ?, 
                  e.position_title = ?,
                  e.department = ?,
                  e.home_address = ?,
                  e.status = ?,
                  e.base_salary_rs = ?,
                  e.allowances_rs = ?,
                  e.employment_type = ?,
                  e.bank_name = ?,
                  e.bank_account_number = ?
              WHERE u.user_id = ?";

    $result = $this->connection->prepare($query);

    if (!$result) {
      $this->errors['general'] = "Prepare failed: " . $this->connection->error;
      return false;
    }

    $result->bind_param(
      "sssssssddsssi",
      $first_name,
      $last_name,
      $email,
      $position_title,
      $department,
      $home_address,
      $status,
      $base_salary_rs,
      $allowances,
      $employment_type,
      $bank_name,
      $bank_account_number,
      $user_id
    );

    if ($result->execute()) {

      $this->errors['general'] = "Update successful.";
      return true;
    } else {
      $this->errors['general'] = "Update failed: " . $result->error;
      return false;
    }
  }
  public function deleteEmployee($user_id)
  {
    $query = "DELETE FROM users WHERE user_id = ?";
    $stmt = $this->connection->prepare($query);
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
  }

  public function getTotalEmployeesCount($keyword = "", $department = "", $status = "") {
    $query = "SELECT COUNT(u.user_id) as total FROM users u
              JOIN employees e ON u.user_id = e.user_id
              WHERE 1=1";

    $types = "";
    $parameters = [];

    if (!empty($keyword)) {
        $query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR e.position_title LIKE ? OR e.department LIKE ?)";
        $types .= "sssss";
        $kw = "%$keyword%";
        array_push($parameters, $kw, $kw, $kw, $kw, $kw);
    }

    if (!empty($department)) {
        $query .= " AND e.department = ?";
        $types .= "s";
        $parameters[] = $department;
    }

    if (!empty($status)) {
        $query .= " AND e.status = ?";
        $types .= "s";
        $parameters[] = $status;
    }

    $stmt = $this->connection->prepare($query);
    
    if (!empty($parameters)) {
        $stmt->bind_param($types, ...$parameters);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

   public function searchEmployee($keyword = "", $department = "", $status = "", $limit = 3, $offset = 0) {
    $query = "SELECT u.user_id, u.first_name, u.last_name, u.email, e.position_title, e.department, e.status, e.date_of_joining 
              FROM users u
              JOIN employees e ON u.user_id = e.user_id
              WHERE 1=1";
    
    $types = "";
    $parameters = [];

    // Keyword Filter
    if (!empty($keyword)) {
        $query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR e.position_title LIKE ?)";
        $types .= "ssss";
        $kw = "%$keyword%";
        array_push($parameters, $kw, $kw, $kw, $kw);
    }

    //  Department Filter
    if (!empty($department)) {
        $query .= " AND e.department = ?";
        $types .= "s";
        $parameters[] = $department;
    }

    // Status Filter
    if (!empty($status)) {
        $query .= " AND e.status = ?";
        $types .= "s";
        $parameters[] = $status;
    }

    
    $query .= " LIMIT ? OFFSET ?";
    $types .= "ii";
    array_push($parameters, $limit, $offset);

    $stmt = $this->connection->prepare($query);
    $stmt->bind_param($types, ...$parameters);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

}
?>