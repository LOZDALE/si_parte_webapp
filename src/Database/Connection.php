<?php
namespace SiParte\Quiz\Database;

use PDO;
use PDOException;

class Connection {
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(
                    "mysql:host=localhost;dbname=si_parte;charset=utf8mb4",
                    "root",   // utente DB
                    ""        // password vuota
                );
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Non usare die() che stampa HTML, ma lancia un'eccezione
                throw new \Exception("Connessione database fallita: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
