<?php
// Disabilita l'output di errori HTML prima di impostare gli header JSON
error_reporting(E_ALL);
ini_set('display_errors', 0); // Non mostrare errori come HTML
ini_set('log_errors', 1); // Logga gli errori invece

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestisci preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Gestore di errori personalizzato per assicurarsi che tutto sia JSON
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Gestore di eccezioni globale
set_exception_handler(function($exception) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Errore del server',
        'message' => $exception->getMessage(),
        'file' => basename($exception->getFile()),
        'line' => $exception->getLine()
    ]);
    error_log("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . ":" . $exception->getLine());
    exit;
});

// Carica le dipendenze
try {
    // Composer autoload (opzionale, se esiste)
    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require $autoloadPath;
    }
    
    // Carica i file necessari manualmente
    require __DIR__ . '/../src/Database/Connection.php';
    require __DIR__ . '/../src/Models/User.php';
    require __DIR__ . '/../src/Controllers/QuizController.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Errore nel caricamento delle dipendenze',
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
    exit;
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Errore fatale nel caricamento',
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
    exit;
}

use SiParte\Quiz\Controllers\QuizController;

// Ottieni metodo e path
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/Public/api.php';
$path = parse_url($requestUri, PHP_URL_PATH);

// Estrai il path dopo api.php usando diversi metodi
// Priorità 1: Query string (più compatibile con XAMPP/Apache)
if (isset($_GET['route']) && !empty($_GET['route'])) {
    $path = trim($_GET['route'], '/');
} else {
    // Priorità 2: PATH_INFO (funziona se il server lo supporta)
    $pathInfo = $_SERVER['PATH_INFO'] ?? '';
    
    if (!empty($pathInfo)) {
        $path = trim($pathInfo, '/');
    } else {
        // Priorità 3: Estrai dal REQUEST_URI
        // Rimuovi eventuale query string dal path
        $pathWithoutQuery = $path;
        
        // Trova api.php nel path e prendi tutto ciò che viene dopo
        $apiPhpPos = strpos($pathWithoutQuery, 'api.php');
        if ($apiPhpPos !== false) {
            // Prendi tutto dopo "api.php"
            $afterApiPhp = substr($pathWithoutQuery, $apiPhpPos + strlen('api.php'));
            // Rimuovi eventuale slash iniziale e finale
            $path = trim($afterApiPhp, '/');
        } else {
            // Fallback: prova a rimuovere vari percorsi base
            $pathWithoutQuery = str_replace(['/Si_Parte/Public/api.php', '/Public/api.php', 'Public/api.php', '/api.php', 'api.php'], '', $pathWithoutQuery);
            $path = trim($pathWithoutQuery, '/');
        }
    }
}

// Instanzia il controller
$quizController = new QuizController();

// Normalizza il path (rimuovi trailing slash e spazi)
$path = trim($path, '/');

// Log per debug (rimuovi in produzione)
error_log("API Request - Method: $method, Path: '$path', URI: $requestUri, PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'null') . ", SCRIPT_NAME: $scriptName");

// Router semplice
try {
    switch ($method) {
        case 'GET':
            if ($path === 'quiz/questions') {
                $quizController->getQuestions();
            } elseif ($path === 'quiz/destinations') {
                $quizController->getDestinations();
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint non trovato', 'path' => $path, 'request_uri' => $requestUri]);
                exit;
            }
            break;

        case 'POST':
            if ($path === 'quiz/select-paese') {
                $input = json_decode(file_get_contents('php://input'), true);
                if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
                    http_response_code(400);
                    echo json_encode([
                        'error' => 'JSON non valido',
                        'json_error' => json_last_error_msg()
                    ]);
                    exit;
                }
                $quizController->selectPaese($input ?? []);
            } elseif ($path === 'quiz/submit') {
                $input = json_decode(file_get_contents('php://input'), true);
                if ($input === null && json_last_error() !== JSON_ERROR_NONE) {
                    http_response_code(400);
                    echo json_encode([
                        'error' => 'JSON non valido',
                        'json_error' => json_last_error_msg()
                    ]);
                    exit;
                }
                $quizController->submitQuiz($input ?? []);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint non trovato', 'path' => $path, 'request_uri' => $requestUri]);
                exit;
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Metodo non consentito', 'method' => $method]);
            exit;
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Errore database',
        'message' => $e->getMessage(),
        'hint' => 'Verifica che il database "si_parte" esista e che la tabella "paesi" sia stata creata. Esegui lo script SQL/add_paesi_citta.sql'
    ]);
    error_log("API PDO Error: " . $e->getMessage());
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Errore del server',
        'message' => $e->getMessage(),
        'type' => get_class($e),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
    error_log("API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    exit;
}

