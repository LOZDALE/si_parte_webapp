<?php
namespace SiParte\Quiz\Database;

use mysqli;
use Exception;

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

            $conn = @new mysqli($host, $user, $pass, $db, (int) $port);
            if ($conn->connect_error) {
                throw new Exception("Connessione DB fallita: " . $conn->connect_error);
            }

            $conn->set_charset('utf8mb4');
            self::$instance = $conn;
        }

        return self::$instance;
    }
}