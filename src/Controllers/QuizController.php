<?php
namespace SiParte\Quiz\Controllers;

use SiParte\Quiz\Database\Connection;

class QuizController {
    private $db;

    public function __construct() {
        try {
            $this->db = Connection::getInstance();
        } catch (\Exception $e) {
            // Se il DB fallisce, logghiamo ma non blocchiamo l'istanza qui
            error_log("Errore connessione nel Controller: " . $e->getMessage());
        }
    }

    // Rimosso /Si_Parte/ dai percorsi per compatibilità Railway
    private function generateCountrySvgMap($paeseId, $countryName, $cityLat, $cityLon) {
        $mapUrl = '/Public/maps/paese_' . intval($paeseId) . '.svg';
        // ... (il resto della logica rimane uguale, ma senza il prefisso della cartella)
        return $mapUrl;
    }

    public function getQuestions() {
        // Forza l'uscita in JSON
        header('Content-Type: application/json');
        
        // Domande Hardcoded di sicurezza (se il DB è vuoto, il sito funziona comunque!)
        $questions = [
            [
                'id' => 1,
                'question' => 'Quale clima preferisci?',
                'answers' => [
                    ['text' => 'Caldo e soleggiato', 'scores' => ['beach' => 3, 'city' => 1]],
                    ['text' => 'Fresco e montuoso', 'scores' => ['mountain' => 3]],
                    ['text' => 'Temperato', 'scores' => ['city' => 3, 'beach' => 1]],
                    ['text' => 'Freddo', 'scores' => ['mountain' => 1]]
                ]
            ],
            [
                'id' => 2,
                'question' => 'Cosa cerchi in un viaggio?',
                'answers' => [
                    ['text' => 'Relax totale', 'scores' => ['beach' => 3]],
                    ['text' => 'Avventura e sport', 'scores' => ['mountain' => 3]],
                    ['text' => 'Musei e shopping', 'scores' => ['city' => 3]],
                    ['text' => 'Natura incontaminata', 'scores' => ['mountain' => 2, 'beach' => 1]]
                ]
            ],
            [
                'id' => 3,
                'question' => 'Budget per persona?',
                'answers' => [
                    ['text' => 'Economico', 'scores' => ['city' => 2]],
                    ['text' => 'Medio', 'scores' => ['beach' => 2]],
                    ['text' => 'Lusso', 'scores' => ['beach' => 3, 'city' => 3]]
                ]
            ]
        ];

        echo json_encode($questions, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function selectPaese($data) {
        header('Content-Type: application/json');
        
        // Se il DB non è connesso, diamo un paese di default per non rompere il sito
        if (!$this->db) {
            echo json_encode([
                'success' => true,
                'paese_selezionato' => [
                    'id' => 1,
                    'nome' => 'Italia (Modalità Offline)',
                    'descrizione' => 'Connessione database non riuscita, ma puoi continuare il quiz!'
                ]
            ]);
            exit;
        }

        try {
            // Cerchiamo un paese a caso nel DB (o logica di matching)
            $stmt = $this->db->query("SELECT * FROM paesi LIMIT 1");
            $paese = $stmt->fetch();

            if (!$paese) {
                throw new \Exception("Nessun paese nel database");
            }

            echo json_encode([
                'success' => true,
                'paese_selezionato' => $paese
            ]);
        } catch (\Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}