<?php
// api.php - Posizione: / (ROOT)
ini_set('display_errors', 1); // Temporaneamente a 1 per vedere errori se il 404 sparisce
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

try {
    // 1. Gestione Autoload/Inclusioni
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    } else {
        // Fallback manuale basato sul tuo albero: /src/Database e /src/Controllers
        require_once __DIR__ . '/src/Database/Connection.php';
        require_once __DIR__ . '/src/Controllers/QuizController.php';
    }

    $route = $_GET['route'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    // 2. Controllo esistenza classe prima dell'istanza
    if (!class_exists('\SiParte\Quiz\Controllers\QuizController')) {
        throw new Exception("Errore: La classe QuizController non è stata caricata. Controlla i namespace.");
    }

    $quizController = new \SiParte\Quiz\Controllers\QuizController();

    // 3. Routing
    if ($method === 'GET') {
        if ($route === 'quiz/questions') {
            $quizController->getQuestions();
        } elseif ($route === 'quiz/destinations') {
            $quizController->getDestinations();
        } else {
            throw new Exception("Rotta GET non trovata: " . $route);
        }
    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        if ($route === 'quiz/select-paese') {
            $quizController->selectPaese($input);
        } elseif ($route === 'quiz/submit') {
            $quizController->submitQuiz($input);
        } else {
            throw new Exception("Rotta POST non trovata: " . $route);
        }
    } else {
        throw new Exception("Metodo non supportato: " . $method);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getFile() . " on line " . $e->getLine() // Ti aiuta a capire DOVE rompe
    ]);
}