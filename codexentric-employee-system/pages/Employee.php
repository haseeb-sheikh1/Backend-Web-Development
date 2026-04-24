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
  public $errors = [];
  private $connection;

   public function __construct($db) {
    $this->connection = $db;
  }
  
  public function createEmployee(){
    if (isset($_POST['create_employee'])) {
      $this->homeAddress = trim($_POST['home_address']);
      $this->positionTitle = trim($_POST['position_title']);
      $this->department = trim($_POST['department']);
      $this->employeeType = trim($_POST['employee_type']);
      $this->baseSalary = trim($_POST['base_salary']);
      $this->allowances = trim($_POST['allowances']);
      $this->bankName = trim($_POST['bank_name']);
      $this->bankAccountNumber = trim($_POST['bank_account_number']);
    
       $query = "INSERT INTO employees (home_address, position_title, department, employment_type, base_salary_rs, allowances_rs, bank_name, bank_account_number) 
       VALUES ('$this->homeAddress', '$this->positionTitle', '$this->department', '$this->employeeType', '$this->baseSalary', '$this->allowances', '$this->bankName', '$this->bankAccountNumber')";
      // Here you would typically add code to save this data to a database
      $result = $this->connection->query($query);

      if ($result) {
        // Employee created successfully
        return true;
      } else {
        // Handle database error
        $this->errors[] = "Database error: " . $this->connection->error;
        return false;
      }
      if (count($this->errors) > 0) {
        // Handle validation errors
        return false;
      } else {
        // Employee created successfully
        return true;
      }
    }
  }
}



