<?php
// Impedisce a PHP di stampare errori HTML che corrompono il JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Header fondamentali
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestione preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $basePath = dirname(__DIR__);
    
    // Inclusione file necessari
    require_once $basePath . '/src/Database/Connection.php';
    require_once $basePath . '/src/Controllers/QuizController.php';

    $route = $_GET['route'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    $quizController = new \SiParte\Quiz\Controllers\QuizController();

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
    // Ogni errore viene restituito come JSON pulito
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}