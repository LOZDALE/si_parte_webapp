<?php
namespace SiParte\Quiz\Models;

use SiParte\Quiz\Database\Connection;

class User {
    public $id;
    public $name;
    public $email;
    public $answers;
    private $db;

    public function __construct() {
        $this->db = (new Connection())->getConnection();
    }

    public function save() {
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, answers) VALUES (:name, :email, :answers)"
        );
        $stmt->execute([
            ':name' => $this->name,
            ':email' => $this->email,
            ':answers' => json_encode($this->answers)
        ]);
        $this->id = $this->db->lastInsertId();
    }
}
