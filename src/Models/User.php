<?php
namespace SiParte\Quiz\Models;

use SiParte\Quiz\Database\Connection;

class User {
    public $id;
    public $risposte_quiz;
    public $destinazione_assegnata;
    public $tipo_viaggio;
    public $budget_finale;
    public $scelte_extra;
    public $email;

    private $db;

    public function __construct() {
        $this->db = Connection::getInstance();
    }

    public function save() {
        $stmt = $this->db->prepare(
            "INSERT INTO users (risposte_quiz, destinazione_assegnata, tipo_viaggio, budget_finale, scelte_extra, email)
             VALUES (:risposte_quiz, :destinazione_assegnata, :tipo_viaggio, :budget_finale, :scelte_extra, :email)"
        );
        $stmt->execute([
            ':risposte_quiz' => json_encode($this->risposte_quiz),
            ':destinazione_assegnata' => $this->destinazione_assegnata,
            ':tipo_viaggio' => $this->tipo_viaggio,
            ':budget_finale' => $this->budget_finale,
            ':scelte_extra' => json_encode($this->scelte_extra),
            ':email' => $this->email
        ]);
        $this->id = $this->db->lastInsertId();
    }
}
