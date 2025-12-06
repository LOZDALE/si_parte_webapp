<?php
namespace SiParte\Quiz\Database;

use PDO;

class Connection {
    private $conn;

    public function __construct() {
        $this->conn = new PDO("mysql:host=localhost;dbname=si_parte", "root", "");
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getConnection() {
        return $this->conn;
    }
}
