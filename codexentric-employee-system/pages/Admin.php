<?php
  class Admin{
    public $Fname;
    public $Lname;
    public $email;
    public $password;
    public $errors = [];
    private $connection;

    public function __construct($db) {
      $this->connection = $db;
    }
        

  }


?>