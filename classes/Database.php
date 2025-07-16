<?php
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "spforward_chaining";
    private $connection;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset to UTF-8
            $this->connection->set_charset("utf8");
            
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql) {
        try {
            $result = $this->connection->query($sql);
            if (!$result) {
                throw new Exception("Query error: " . $this->connection->error);
            }
            return $result;
        } catch (Exception $e) {
            error_log("Database query error: " . $e->getMessage());
            return false;
        }
    }
    
    public function prepare($sql) {
        try {
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare error: " . $this->connection->error);
            }
            return $stmt;
        } catch (Exception $e) {
            error_log("Database prepare error: " . $e->getMessage());
            return false;
        }
    }
    
    public function escape_string($str) {
        return $this->connection->real_escape_string($str);
    }
    
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    public function __destruct() {
        $this->close();
    }
}

// Initialize database connection
$database = new Database();
?>