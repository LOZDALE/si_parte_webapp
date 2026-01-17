<?php
// 1. Configurazione Errori - Fondamentale per evitare output non JSON
error_reporting(E_ALL);
ini_set('display_errors', 0); 
ini_set('log_errors', 1);

// 2. Header obbligatori
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 3. Caricamento dipendenze con gestione percorsi Railway
try {
    // __DIR__ è /app/Public
    $basePath = dirname(__DIR__); // Sale a /app/
    
    // Verifica e carica Connection
    $connPath = $basePath . '/src/Database/Connection.php';
    if (!file_exists($connPath)) throw new Exception("File non trovato: src/Database/Connection.php");
    require_once $connPath;

    // Carica Model e Controller
    require_once $basePath . '/src/Models/User.php';
    require_once $basePath . '/src/Controllers/QuizController.php';

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Errore caricamento server: ' . $e->getMessage()
    ]);
    exit;
}

use SiParte\Quiz\Controllers\QuizController;

// 4. Routing Intelligente
$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? '';

// Pulizia della route (rimuove prefissi fastidiosi)
if (empty($route)) {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    // Rimuove "/Si_Parte/Public/api.php" o "/api.php" per isolare la rotta
    $route = str_replace(['/Si_Parte/Public/api.php', '/Public/api.php', '/api.php'], '', $requestUri);
}
$route = trim($route, '/');

$quizController = new QuizController();

try {
    switch ($method) {
        case 'GET':
            if ($route === 'quiz/questions') {
                $quizController->getQuestions();
            } elseif ($route === 'quiz/destinations') {
                $quizController->getDestinations();
            } else {
                throw new Exception("Endpoint GET non trovato: $route");
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            if ($route === 'quiz/select-paese') {
                $quizController->selectPaese($input);
            } elseif ($route === 'quiz/submit') {
                $quizController->submitQuiz($input);
            } else {
                throw new Exception("Endpoint POST non trovato: $route");
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Metodo non consentito']);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'route' => $route
    ]);
}