<?php
namespace SiParte\Quiz\Controllers;

use SiParte\Quiz\Models\User;
use SiParte\Quiz\Database\Connection;

class QuizController {
    private $db;

    public function __construct() {
        $this->db = (new Connection())->getConnection();
    }

    public function demoQuiz() {
        // Creo un utente demo
        $user = new User();
        $user->name = "Mario Rossi";
        $user->email = "mario@example.com";
        $user->answers = [
            "climate" => "mare",
            "budget" => 600
        ];
        $user->save();

        // Leggo tutte le destinazioni
        $stmt = $this->db->query("SELECT * FROM destinations");
        $destinations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Matching semplice basato sul clima/attività
        $chosen = null;
        foreach ($destinations as $dest) {
            if ($dest['category'] == $user->answers['climate']) {
                $chosen = $dest;
                break;
            }
        }

        if (!$chosen) $chosen = $destinations[0];

        return "Utente ID: {$user->id}<br>Destinazione suggerita: {$chosen['name']} (Categoria: {$chosen['category']}, Budget base: {$chosen['base_budget']})";
    }
}
