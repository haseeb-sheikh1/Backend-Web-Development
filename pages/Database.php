<?php
class Database{
    private $hostname = 'localhost';
    private $database = 'ems_db';
    private $username = 'root';
    private $password = '';


     public $connection = null;

     public function getConnection() {
        $this->connection = new mysqli($this->hostname, $this->username, $this->password, $this->database);
        if ($this->connection->connect_error){
               echo $this->connection->connect_error;
               die();
        }
            
        return $this->connection;
     }
}

?>