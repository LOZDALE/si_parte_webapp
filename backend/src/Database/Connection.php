<?php
namespace SiParte\Quiz\Database;

use mysqli;
use mysqli_stmt;
use Exception;

class LoggedMysqliStmt {
    private $stmt;
    private $sql;

    public function __construct(mysqli_stmt $stmt, string $sql) {
        $this->stmt = $stmt;
        $this->sql = $sql;
    }

    public function execute(...$args) {
        error_log('[DB] execute: ' . $this->sql);
        return $this->stmt->execute(...$args);
    }

    public function __call($name, $args) {
        return $this->stmt->$name(...$args);
    }

    public function __get($name) {
        return $this->stmt->$name;
    }

    public function __set($name, $value) {
        $this->stmt->$name = $value;
    }
}

class LoggedMysqli {
    private $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    private function normalizeSql(string $sql): string {
        return preg_replace('/\s+/', ' ', trim($sql));
    }

    public function query(string $sql, int $resultmode = MYSQLI_STORE_RESULT) {
        error_log('[DB] query: ' . $this->normalizeSql($sql));
        return $this->conn->query($sql, $resultmode);
    }

    public function prepare(string $sql) {
        $normalized = $this->normalizeSql($sql);
        error_log('[DB] prepare: ' . $normalized);
        $stmt = $this->conn->prepare($sql);
        return $stmt ? new LoggedMysqliStmt($stmt, $normalized) : $stmt;
    }

    public function __call($name, $args) {
        if (in_array($name, ['real_query', 'multi_query', 'execute_query'], true) && isset($args[0]) && is_string($args[0])) {
            error_log('[DB] ' . $name . ': ' . $this->normalizeSql($args[0]));
        }
        return $this->conn->$name(...$args);
    }

    public function __get($name) {
        return $this->conn->$name;
    }

    public function __set($name, $value) {
        $this->conn->$name = $value;
    }
}

class Connection {
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {
            // Railway fornisce MYSQL_URL. Se non esiste (locale), usiamo i parametri singoli.
            $url = getenv('MYSQL_URL');

            if ($url) {
                $dbparts = parse_url($url);
                $host = $dbparts['host'];
                $port = $dbparts['port'] ?? '3306';
                $user = $dbparts['user'];
                $pass = $dbparts['pass'];
                $db   = ltrim($dbparts['path'], '/');
            } else {
                // Configurazione per localhost
                $host = getenv('MYSQLHOST') ?: 'localhost';
                $port = getenv('MYSQLPORT') ?: '3306';
                $db   = getenv('MYSQLDATABASE') ?: 'si_parte';
                $user = getenv('MYSQLUSER') ?: 'root';
                $pass = getenv('MYSQLPASSWORD') ?: '';
            }

            error_log(sprintf('[DB] connecting host=%s port=%s db=%s user=%s', $host, $port, $db, $user));
            $conn = @new mysqli($host, $user, $pass, $db, (int) $port);
            if ($conn->connect_error) {
                error_log('[DB] connection failed: ' . $conn->connect_error);
                throw new Exception("Connessione DB fallita: " . $conn->connect_error);
            }

            $conn->set_charset('utf8mb4');
            error_log('[DB] connection ok');
            self::$instance = new LoggedMysqli($conn);
        } else {
            error_log('[DB] connection reuse');
        }

        return self::$instance;
    }
}