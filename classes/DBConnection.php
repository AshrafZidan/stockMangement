<?php
if (!defined('DB_SERVER')) {
    require_once("../initialize.php");
}

class DBConnection {
    private $host = DB_SERVER;
    private $username = DB_USERNAME;
    private $password = DB_PASSWORD;
    private $database = DB_NAME;
    private $charset = "utf8mb4";

    public $conn;

    public function __construct() {
        if (!isset($this->conn)) {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);

            if ($this->conn->connect_error) {
                error_log("Connection failed: " . $this->conn->connect_error);
                exit('Cannot connect to database server');
            }
        }

        // if (!$this->conn->set_charset($this->charset)) {
        //     error_log("Error loading charset {$this->charset}: " . $this->conn->error);
        //     exit('Database charset setup failed. Please check the logs for details.');
        // }
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
