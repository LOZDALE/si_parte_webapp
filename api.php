<?php
// api.php - Posizione: / (ROOT)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Autoload di Composer (indispensabile per far funzionare il namespace SiParte\Quiz)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Fallback manuale se composer install non è ancora stato eseguito
    require_once __DIR__ . '/src/Database/Connection.php';
    require_once __DIR__ . '/src/Controllers/QuizController.php';
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

try {
    $route = $_GET['route'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Inizializzazione Controller usando il namespace del tuo composer.json
    $quizController = new \SiParte\Quiz\Controllers\QuizController();

    if ($method === 'GET') {
        if ($route === 'quiz/questions') {
            $quizController->getQuestions();
        } elseif ($route === 'quiz/destinations') {
            $quizController->getDestinations();
        }
    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        if ($route === 'quiz/select-paese') {
            $quizController->selectPaese($input);
        } elseif ($route === 'quiz/submit') {
            $quizController->submitQuiz($input);
        }
    } else {
        throw new Exception("Rotta non trovata: " . $route);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}