<?php
namespace SiParte\Quiz\Controllers;

use SiParte\Quiz\Models\User;
use SiParte\Quiz\Database\Connection;

class QuizController {

    private $db;

    public function __construct() {
        $this->db = Connection::getInstance();
    }

    public function demoQuiz() {
        // Creo un utente demo
        $user = new User();
        $user->risposte_quiz = [
            "climate" => "mare",
            "activities" => ["relax","cultura"],
            "budget" => 1000
        ];
        $user->destinazione_assegnata = null;
        $user->tipo_viaggio = null;
        $user->budget_finale = $user->risposte_quiz['budget'];
        $user->scelte_extra = [];
        $user->email = "demo@example.com";
        $user->save();

        // Matching semplice della destinazione
        $dest = $this->matchDestination($user->risposte_quiz);

        // Salvo la destinazione e tipo viaggio nell'utente
        $user->destinazione_assegnata = $dest['nome_destinazione'];
        $user->tipo_viaggio = "Relax & Cultura"; // demo
        $user->save();

        return "Utente ID: {$user->id}<br>Destinazione suggerita: {$dest['nome_destinazione']} 
        (Categoria: {$dest['categoria_viaggio']}, Budget base: {$dest['fascia_budget_base']})";
    }

    private function matchDestination($answers) {
        $stmt = $this->db->query("SELECT * FROM destinations");
        $destinations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Matching molto semplice: scegli la prima destinazione che corrisponde al clima
        foreach ($destinations as $dest) {
            if ($dest['categoria_viaggio'] == $answers['climate']) {
                return $dest;
            }
        }

        // Se nessuna corrispondenza, ritorna la prima destinazione
        return $destinations[0];
    }
}
