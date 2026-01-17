<?php
namespace SiParte\Quiz\Database;

use PDO;
use PDOException;

class Connection {
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {
            try {
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
                    // Configurazione per localhost (XAMPP)
                    $host = getenv('MYSQLHOST') ?: 'localhost';
                    $port = getenv('MYSQLPORT') ?: '3306';
                    $db   = getenv('MYSQLDATABASE') ?: 'si_parte';
                    $user = getenv('MYSQLUSER') ?: 'root';
                    $pass = getenv('MYSQLPASSWORD') ?: '';
                }

                $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
                
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]);
                
            } catch (PDOException $e) {
                // Questo errore verrà catturato dal try-catch in api.php
                throw new \Exception("Connessione DB fallita: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}