<?php
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "123321";
    private $dbname = "lumenpoledb";
    
    public function connect() {
        $conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);
        
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to UTF8
        $conn->set_charset("utf8mb4");
        
        return $conn;
    }
}
?>