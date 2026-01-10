<?php
namespace SiParte\Quiz\Controllers;

use SiParte\Quiz\Models\User;
use SiParte\Quiz\Database\Connection;

class QuizController {

    private $db;
    
    // Mapping tra categorie frontend e database
    private $categoryMapping = [
        'beach' => 'mare',
        'mountain' => 'montagna',
        'city' => 'città',
        'ski' => 'montagna', // sci mappa a montagna per ora
        'cultura' => 'cultura',
        // Categorie database aggiuntive che possono essere usate come fallback
        'cibo' => 'cibo',
        'divertimento' => 'divertimento'
    ];

    public function __construct() {
        $this->db = Connection::getInstance();
    }

    /**
     * Genera un URL a una mappa statica OpenStreetMap centrata su lat/lon con un marker
     */
    private function generateMapUrl($lat, $lon, $zoom = 5, $size = '800x600') {
        if (empty($lat) || empty($lon)) return null;
        $lat = urlencode($lat);
        $lon = urlencode($lon);
        $zoom = (int)$zoom;
        $size = urlencode($size);
        // Servizio pubblico OSM static map (nessuna API key richiesta)
        return "https://staticmap.openstreetmap.de/staticmap.php?center={$lat},{$lon}&zoom={$zoom}&size={$size}&markers={$lat},{$lon},red-pushpin";
    }

    /**
     * Genera una mappa SVG del paese evidenziando il confine in verde e la città con un punto blu.
     * Usa Nominatim per recuperare il GeoJSON del paese e salva una copia SVG in Public/maps per caching.
     * Restituisce l'URL relativo alla mappa SVG (es: /Si_Parte/Public/maps/paese_{id}.svg) o null.
     */
    private function generateCountrySvgMap($paeseId, $countryName, $cityLat, $cityLon, $width = 800, $height = 600) {
        if (empty($countryName) || empty($cityLat) || empty($cityLon)) return null;

        $baseDir = realpath(__DIR__ . '/../../..');
        if ($baseDir === false) {
            $baseDir = __DIR__ . '/../../../';
        }
        $mapsDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Public' . DIRECTORY_SEPARATOR . 'maps';
        if (!is_dir($mapsDir)) {
            @mkdir($mapsDir, 0755, true);
        }

        $safeName = 'paese_' . intval($paeseId) . '.svg';
        $mapPath = $mapsDir . DIRECTORY_SEPARATOR . $safeName;
        $mapUrl = '/Si_Parte/Public/maps/' . $safeName;

        // Se la mappa è già presente, restituisci subito l'URL
        if (file_exists($mapPath)) {
            return $mapUrl;
        }

        // Richiama Nominatim per ottenere il poligono del paese (GeoJSON)
        $query = urlencode($countryName);
        $nominatim = "https://nominatim.openstreetmap.org/search.php?country={$query}&polygon_geojson=1&format=jsonv2";

        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: SiParteApp/1.0 (your@email.example)\r\n"
            ]
        ];
        $context = stream_context_create($opts);

        $raw = @file_get_contents($nominatim, false, $context);
        if ($raw === false) return null;

        $resp = json_decode($raw, true);
        if (!is_array($resp) || count($resp) === 0) return null;

        // Trova la prima entry che contiene 'geojson'
        $geo = null;
        foreach ($resp as $r) {
            if (!empty($r['geojson'])) { $geo = $r['geojson']; break; }
        }
        if (!$geo) return null;

        // Normalizza multipoligono/ poligono in array di poli
        $polygons = [];
        if ($geo['type'] === 'Polygon') {
            $polygons[] = $geo['coordinates'];
        } elseif ($geo['type'] === 'MultiPolygon') {
            $polygons = $geo['coordinates'];
        } else {
            return null;
        }

        // Calcola bbox (lat/lon)
        $minLat = 90; $maxLat = -90; $minLon = 180; $maxLon = -180;
        foreach ($polygons as $poly) {
            foreach ($poly as $ring) {
                foreach ($ring as $pt) {
                    $lon = $pt[0]; $lat = $pt[1];
                    if ($lat < $minLat) $minLat = $lat;
                    if ($lat > $maxLat) $maxLat = $lat;
                    if ($lon < $minLon) $minLon = $lon;
                    if ($lon > $maxLon) $maxLon = $lon;
                }
            }
        }

        // Margine del 5%
        $latPad = ($maxLat - $minLat) * 0.05;
        $lonPad = ($maxLon - $minLon) * 0.05;
        $minLat -= $latPad; $maxLat += $latPad; $minLon -= $lonPad; $maxLon += $lonPad;

        // Funzione di proiezione semplice equirettangolare per disegnare su SVG
        $project = function($lat, $lon) use ($minLat, $maxLat, $minLon, $maxLon, $width, $height) {
            $x = ($lon - $minLon) / ($maxLon - $minLon) * $width;
            $y = ($maxLat - $lat) / ($maxLat - $minLat) * $height;
            return [(float)$x, (float)$y];
        };

        // Crea path SVG per ogni poligono
        $paths = [];
        foreach ($polygons as $poly) {
            foreach ($poly as $ring) {
                $d = '';
                $first = true;
                foreach ($ring as $pt) {
                    $lon = $pt[0]; $lat = $pt[1];
                    list($x, $y) = $project($lat, $lon);
                    $d .= ($first ? 'M' : 'L') . round($x,2) . ' ' . round($y,2) . ' ';
                    $first = false;
                }
                $d .= 'Z';
                $paths[] = $d;
            }
        }

        // Proietta coordinate della città
        list($cityX, $cityY) = $project($cityLat, $cityLon);

        // Costruisci SVG
        $svg = '<?xml version="1.0" encoding="UTF-8"?>\n';
        $svg .= "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$width}\" height=\"{$height}\" viewBox=\"0 0 {$width} {$height}\">\n";
        $svg .= "<rect width=\"100%\" height=\"100%\" fill=\"#eef6fb\"/>\n";
        foreach ($paths as $p) {
            $svg .= "<path d=\"{$p}\" fill=\"#2ecc71\" fill-opacity=\"0.65\" stroke=\"#1b6f3a\" stroke-width=\"1\"/>\n";
        }
        // Punto città blu con alone bianco
        $svg .= "<circle cx=\"" . round($cityX,2) . "\" cy=\"" . round($cityY,2) . "\" r=\"8\" fill=\"#0b62d6\" stroke=\"#ffffff\" stroke-width=\"2\"/>\n";
        $svg .= "</svg>\n";

        // Salva su file
        $written = @file_put_contents($mapPath, $svg);
        if ($written === false) {
            error_log('generateCountrySvgMap: impossibile scrivere il file SVG su ' . $mapPath);
            return null;
        }
        // Log percorso per debug
        error_log('generateCountrySvgMap: SVG scritto correttamente su ' . $mapPath . ' (url: ' . $mapUrl . ')');
        return $mapUrl;
    }

    /**
     * Restituisce le domande del quiz in formato JSON
     * Prime 3 domande per determinare il paese, poi altre domande per la città
     */
    public function getQuestions() {
        // Se è stata richiesta una versione specifica per paese (fase 2), restituisci solo le domande della fase 2
        $paeseId = isset($_GET['paese_id']) ? (int)$_GET['paese_id'] : null;

        // Domande base fase 1
        $phase1 = [
            // FASE 1: Domande per determinare il PAESE (prime 3)
            [
                'id' => 1,
                'question' => 'Quale clima preferisci?',
                'phase' => 1,
                'answers' => [
                    ['text' => 'Caldo e soleggiato', 'scores' => ['beach' => 3, 'mountain' => 0, 'city' => 1, 'ski' => 0]],
                    ['text' => 'Fresco e montuoso', 'scores' => ['beach' => 0, 'mountain' => 3, 'city' => 0, 'ski' => 1]],
                    ['text' => 'Temperato', 'scores' => ['beach' => 1, 'mountain' => 1, 'city' => 3, 'ski' => 0]],
                    ['text' => 'Freddo', 'scores' => ['beach' => 0, 'mountain' => 1, 'city' => 0, 'ski' => 3]]
                ],
                'type' => 'preference'
            ],
            [
                'id' => 2,
                'question' => 'Quale attività ti piace di più?',
                'phase' => 1,
                'answers' => [
                    ['text' => 'Spiaggia', 'scores' => ['beach' => 3, 'mountain' => 0, 'city' => 0, 'ski' => 0]],
                    ['text' => 'Trekking', 'scores' => ['beach' => 0, 'mountain' => 3, 'city' => 0, 'ski' => 0]],
                    ['text' => 'Cultura e arte', 'scores' => ['beach' => 0, 'mountain' => 0, 'city' => 3, 'ski' => 0]],
                    ['text' => 'Sci', 'scores' => ['beach' => 0, 'mountain' => 0, 'city' => 0, 'ski' => 3]]
                ],
                'type' => 'preference'
            ],
            [
                'id' => 3,
                'question' => 'Quale è il tuo budget per persona (approssimativo)?',
                'phase' => 1,
                'answers' => [
                    ['text' => 'Fino a 500€', 'scores' => ['beach' => 1, 'mountain' => 1, 'city' => 1, 'ski' => 1]],
                    ['text' => '500-1000€', 'scores' => ['beach' => 2, 'mountain' => 2, 'city' => 2, 'ski' => 2]],
                    ['text' => '1000-2000€', 'scores' => ['beach' => 3, 'mountain' => 3, 'city' => 3, 'ski' => 3]],
                    ['text' => 'Oltre 2000€', 'scores' => ['beach' => 4, 'mountain' => 4, 'city' => 4, 'ski' => 4]]
                ],
                'type' => 'preference'
            ],
            // FASE 2: Domande specifiche per la CITTÀ (dopo la selezione del paese)
            [
                'id' => 4,
                'question' => 'Preferisci una destinazione?',
                'phase' => 2,
                'answers' => [
                    ['text' => 'Grande città metropolitana', 'scores' => ['beach' => 0, 'mountain' => 0, 'city' => 4, 'ski' => 0]],
                    ['text' => 'Città storica e artistica', 'scores' => ['beach' => 1, 'mountain' => 0, 'city' => 3, 'ski' => 0]],
                    ['text' => 'Località di mare', 'scores' => ['beach' => 4, 'mountain' => 0, 'city' => 1, 'ski' => 0]],
                    ['text' => 'Località montana', 'scores' => ['beach' => 0, 'mountain' => 4, 'city' => 0, 'ski' => 3]]
                ],
                'type' => 'preference'
            ],
            [
                'id' => 5,
                'question' => 'Quanto tempo vuoi dedicare al viaggio?',
                'phase' => 2,
                'answers' => [
                    ['text' => 'Weekend breve (2-3 giorni)', 'scores' => ['beach' => 1, 'mountain' => 1, 'city' => 4, 'ski' => 1]],
                    ['text' => 'Settimana (5-7 giorni)', 'scores' => ['beach' => 3, 'mountain' => 3, 'city' => 3, 'ski' => 3]],
                    ['text' => 'Due settimane (10-14 giorni)', 'scores' => ['beach' => 4, 'mountain' => 4, 'city' => 2, 'ski' => 4]],
                    ['text' => 'Più di due settimane', 'scores' => ['beach' => 4, 'mountain' => 4, 'city' => 1, 'ski' => 4]]
                ],
                'type' => 'preference'
            ],
            [
                'id' => 6,
                'question' => 'Cosa ti attrae di più?',
                'phase' => 2,
                'answers' => [
                    ['text' => 'Vita notturna e divertimento', 'scores' => ['beach' => 2, 'mountain' => 0, 'city' => 4, 'ski' => 1]],
                    ['text' => 'Relax e benessere', 'scores' => ['beach' => 4, 'mountain' => 2, 'city' => 1, 'ski' => 0]],
                    ['text' => 'Sport e attività all\'aria aperta', 'scores' => ['beach' => 2, 'mountain' => 4, 'city' => 0, 'ski' => 4]],
                    ['text' => 'Shopping e intrattenimento', 'scores' => ['beach' => 1, 'mountain' => 0, 'city' => 4, 'ski' => 0]]
                ],
                'type' => 'preference'
            ],
            [
                'id' => 7,
                'question' => 'Preferisci un\'atmosfera?',
                'phase' => 2,
                'answers' => [
                    ['text' => 'Tradizionale e autentica', 'scores' => ['beach' => 2, 'mountain' => 3, 'city' => 3, 'ski' => 2]],
                    ['text' => 'Moderno e cosmopolita', 'scores' => ['beach' => 1, 'mountain' => 0, 'city' => 4, 'ski' => 0]],
                    ['text' => 'Turistica ma accogliente', 'scores' => ['beach' => 4, 'mountain' => 2, 'city' => 2, 'ski' => 3]],
                    ['text' => 'Esclusiva e raffinata', 'scores' => ['beach' => 3, 'mountain' => 4, 'city' => 3, 'ski' => 4]]
                ],
                'type' => 'preference'
            ]
        ];

        // Domande generiche fase 2 (fallback)
        $phase2Generic = [
            [
                'id' => 4,
                'question' => 'Preferisci una destinazione?',
                'phase' => 2,
                'answers' => [
                    ['text' => 'Grande città metropolitana', 'scores' => ['beach' => 0, 'mountain' => 0, 'city' => 4, 'ski' => 0]],
                    ['text' => 'Città storica e artistica', 'scores' => ['beach' => 1, 'mountain' => 0, 'city' => 3, 'ski' => 0]],
                    ['text' => 'Località di mare', 'scores' => ['beach' => 4, 'mountain' => 0, 'city' => 1, 'ski' => 0]],
                    ['text' => 'Località montana', 'scores' => ['beach' => 0, 'mountain' => 4, 'city' => 0, 'ski' => 3]]
                ],
                'type' => 'preference'
            ],
            [
                'id' => 5,
                'question' => 'Quanto tempo vuoi dedicare al viaggio?',
                'phase' => 2,
                'answers' => [
                    ['text' => 'Weekend breve (2-3 giorni)', 'scores' => ['beach' => 1, 'mountain' => 1, 'city' => 4, 'ski' => 1]],
                    ['text' => 'Settimana (5-7 giorni)', 'scores' => ['beach' => 3, 'mountain' => 3, 'city' => 3, 'ski' => 3]],
                    ['text' => 'Due settimane (10-14 giorni)', 'scores' => ['beach' => 4, 'mountain' => 4, 'city' => 2, 'ski' => 4]],
                    ['text' => 'Più di due settimane', 'scores' => ['beach' => 4, 'mountain' => 4, 'city' => 1, 'ski' => 4]]
                ],
                'type' => 'preference'
            ],
            [
                'id' => 6,
                'question' => 'Cosa ti attrae di più?',
                'phase' => 2,
                'answers' => [
                    ['text' => 'Vita notturna e divertimento', 'scores' => ['beach' => 2, 'mountain' => 0, 'city' => 4, 'ski' => 1]],
                    ['text' => 'Relax e benessere', 'scores' => ['beach' => 4, 'mountain' => 2, 'city' => 1, 'ski' => 0]],
                    ['text' => 'Sport e attività all\'aria aperta', 'scores' => ['beach' => 2, 'mountain' => 4, 'city' => 0, 'ski' => 4]],
                    ['text' => 'Shopping e intrattenimento', 'scores' => ['beach' => 1, 'mountain' => 0, 'city' => 4, 'ski' => 0]]
                ],
                'type' => 'preference'
            ]
        ];

        // Se non è stata richiesta una versione paese-specifica, restituisci domanda complete (fase1 + fase2 generic)
        if (!$paeseId) {
            $questions = array_merge($phase1, $phase2Generic);
            echo json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        // Costruisci le domande fase2 specifiche per il paese chiedendo le principali città del paese
        try {
            $stmt = $this->db->prepare("SELECT nome, categoria_viaggio FROM citta WHERE id_paese = :id ORDER BY popolarita DESC LIMIT 4");
            $stmt->execute([':id' => $paeseId]);
            $cities = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // In caso di errore DB, ritorna le domande generiche
            echo json_encode($phase2Generic, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        // Mappa categoria DB -> frontend per assegnare punteggi
        $categoryMap = [
            'mare' => 'beach',
            'montagna' => 'mountain',
            'città' => 'city',
            'cultura' => 'city',
            'cibo' => 'city',
            'divertimento' => 'city'
        ];

        // Costruiamo le domande fase2 in modo che NON espongano i nomi delle città,
        // ma offrano scelte di tipo/categoria coerenti con le categorie disponibili nel paese.
        // Otteniamo le categorie uniche presenti nelle città del paese
        $availableDbCategories = array_unique(array_map(function($c) { return $c['categoria_viaggio'] ?? ''; }, $cities));

        // Mappa DB category -> frontend category + testo leggibile
        $dbToFrontend = [
            'mare' => ['key' => 'beach', 'text' => 'Località di mare'],
            'montagna' => ['key' => 'mountain', 'text' => 'Località di montagna'],
            'città' => ['key' => 'city', 'text' => 'Città e cultura'],
            'cultura' => ['key' => 'city', 'text' => 'Città e cultura'],
            'cibo' => ['key' => 'city', 'text' => 'Città e gastronomia'],
            'divertimento' => ['key' => 'city', 'text' => 'Vita notturna e divertimento'],
            'sci' => ['key' => 'mountain', 'text' => 'Località sciistiche']
        ];

        $categoryAnswers = [];
        foreach ($availableDbCategories as $dbCat) {
            if (empty($dbCat)) continue;
            $map = $dbToFrontend[$dbCat] ?? ['key' => 'city', 'text' => ucfirst($dbCat)];
            // assegna punteggi in base alla categoria frontend
            $scores = ['beach' => 0, 'mountain' => 0, 'city' => 0, 'ski' => 0];
            $scores[$map['key']] = 4;
            $categoryAnswers[] = ['text' => $map['text'], 'scores' => $scores];
        }

        // Se non sono state trovate categorie specifiche, usa le risposte generiche per categoria
        if (empty($categoryAnswers)) {
            echo json_encode($phase2Generic, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        // Limitiamo il numero di opzioni a 4 e aggiungiamo opzioni di fallback per varietà
        $categoryAnswers = array_slice($categoryAnswers, 0, 4);
        // Se meno di 3 opzioni, aggiungi opzioni generiche
        $genericCategoryOptions = [
            ['text' => 'Grande città metropolitana', 'scores' => ['beach'=>0,'mountain'=>0,'city'=>4,'ski'=>0]],
            ['text' => 'Località di mare', 'scores' => ['beach'=>4,'mountain'=>0,'city'=>1,'ski'=>0]],
            ['text' => 'Località montana', 'scores' => ['beach'=>0,'mountain'=>4,'city'=>0,'ski'=>3]]
        ];
        foreach ($genericCategoryOptions as $opt) {
            if (count($categoryAnswers) >= 4) break;
            // evita duplicati testuali
            $exists = false;
            foreach ($categoryAnswers as $ca) { if ($ca['text'] === $opt['text']) { $exists = true; break; } }
            if (!$exists) $categoryAnswers[] = $opt;
        }

        // Costruiamo le domande fase2: prima domanda determina la categoria preferita all'interno del paese
        $phase2 = [];
        $phase2[] = [
            'id' => 4,
            'question' => 'Quale tipo di destinazione preferisci in questo paese?',
            'phase' => 2,
            'answers' => $categoryAnswers,
            'type' => 'preference'
        ];

        // Aggiungi le restanti domande generiche della fase2 come contesto
        $phase2 = array_merge($phase2, $phase2Generic);

        echo json_encode($phase2, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Restituisce le destinazioni dal database mappate al formato frontend
     */
    public function getDestinations() {
        try {
            $stmt = $this->db->query("SELECT * FROM destinations");
            $destinations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Mappa le destinazioni al formato frontend
            $mappedDestinations = array_map(function($dest) {
                // Mappa categoria database -> frontend
                $categoryMap = [
                    'mare' => 'beach',
                    'montagna' => 'mountain',
                    'città' => 'city',
                    'cultura' => 'city'
                ];

                $frontendCategory = $categoryMap[$dest['categoria_viaggio']] ?? 'city';

                // Determina l'immagine in base alla destinazione specifica
                $destinationImages = [
                    'Roma' => 'https://images.unsplash.com/photo-1529260830199-42c24126f198?w=800&h=600&fit=crop',
                    'Barcellona' => 'https://images.unsplash.com/photo-1539037116277-4db20889f2d4?w=800&h=600&fit=crop',
                    'Alpi' => 'https://images.unsplash.com/photo-1464822759844-d150f39b8d7d?w=800&h=600&fit=crop',
                    'Parigi' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800&h=600&fit=crop'
                ];

                // Immagini fallback per categoria
                $categoryImages = [
                    'beach' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop',
                    'mountain' => 'https://images.unsplash.com/photo-1464822759844-d150f39b8d7d?w=800&h=600&fit=crop',
                    'city' => 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=800&h=600&fit=crop',
                    'ski' => 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=800&h=600&fit=crop'
                ];

                $image = $destinationImages[$dest['nome_destinazione']] ?? $categoryImages[$frontendCategory] ?? $categoryImages['city'];

                return [
                    'id' => (int)$dest['id'],
                    'name' => $dest['nome_destinazione'],
                    'category' => $frontendCategory,
                    'image' => $image,
                    'base_budget' => (float)$dest['fascia_budget_base'],
                    'description' => "Destinazione perfetta per un viaggio di tipo {$dest['categoria_viaggio']}",
                    'original_category' => $dest['categoria_viaggio']
                ];
            }, $destinations);

            echo json_encode($mappedDestinations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Errore nel recupero delle destinazioni',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Endpoint per la FASE 1: Determina il paese basato sulle prime 3 domande
     */
    public function selectPaese($data) {
        try {
            error_log("SelectPaese: Inizio selezione paese. Dati ricevuti: " . json_encode($data));
            
            if (!isset($data['answers']) || !is_array($data['answers']) || count($data['answers']) < 3) {
                error_log("SelectPaese: Risposte incomplete. Ricevute: " . count($data['answers'] ?? []));
                http_response_code(400);
                echo json_encode(['error' => 'Risposte incomplete per la fase 1', 'received' => count($data['answers'] ?? [])]);
                exit;
            }

            // Calcola i punteggi totali dalle prime 3 domande
            $totalScores = $this->calculateScoresFromAnswers($data['answers'], 0, 3);
            error_log("SelectPaese: Punteggi calcolati dalle prime 3 risposte: " . json_encode($totalScores));
            
            // Trova la categoria migliore
            $bestCategory = $this->findBestCategory($totalScores);
            error_log("SelectPaese: Categoria migliore trovata: $bestCategory");
            
            // Trova il paese corrispondente
            $paese = $this->matchPaese($bestCategory, $totalScores, $data);
            
            if (!$paese) {
                error_log("SelectPaese: Nessun paese trovato. bestCategory=$bestCategory, scores=" . json_encode($totalScores));
                http_response_code(404);
                echo json_encode([
                    'error' => 'Nessun paese trovato nel database per le tue preferenze',
                    'hint' => 'Verifica che la tabella "paesi" esista e sia popolata. Esegui lo script SQL/add_paesi_citta.sql',
                    'debug' => [
                        'bestCategory' => $bestCategory,
                        'totalScores' => $totalScores
                    ]
                ]);
                exit;
            }
            
            error_log("SelectPaese: Paese selezionato: {$paese['nome']} (ID: {$paese['id']})");

            // Gestisci il caso in cui categorie_suggerite sia una stringa JSON
            $categorie = $paese['categorie_suggerite'];
            if (is_string($categorie)) {
                $categorie = json_decode($categorie, true) ?? [];
            }

            // Determina immagine per il paese (usa campo `immagine_bandiera` se presente, altrimenti fallback)
            $countryImages = [
                'Italia' => 'https://images.unsplash.com/photo-1508747703725-7199031b6a75?w=800&h=600&fit=crop',
                'Spagna' => 'https://images.unsplash.com/photo-1508098682720-64385ff3a8d5?w=800&h=600&fit=crop',
                'Francia' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800&h=600&fit=crop',
                'Stati Uniti' => 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=800&h=600&fit=crop',
                'Grecia' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop',
                'Turchia' => 'https://images.unsplash.com/photo-1526481280698-5d02a0c77d4b?w=800&h=600&fit=crop',
                'Giappone' => 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=800&h=600&fit=crop',
            ];

            $image = null;
            if (!empty($paese['immagine_bandiera'])) {
                $image = $paese['immagine_bandiera'];
            } elseif (!empty($paese['nome']) && isset($countryImages[$paese['nome']])) {
                $image = $countryImages[$paese['nome']];
            }

            // Prova a recuperare le coordinate della città più popolare del paese per generare una mappa
            $mapImage = null;
            try {
                $stmtMap = $this->db->prepare("SELECT lat, lon FROM citta WHERE id_paese = :id AND lat IS NOT NULL AND lon IS NOT NULL ORDER BY popolarita DESC LIMIT 1");
                $stmtMap->execute([':id' => $paese['id']]);
                $coords = $stmtMap->fetch(\PDO::FETCH_ASSOC);
                if ($coords && !empty($coords['lat']) && !empty($coords['lon'])) {
                    // Calcola bbox dalle città del paese per fallback se Nominatim non restituisce poligono
                    try {
                        $stmtB = $this->db->prepare("SELECT MIN(lat) AS minLat, MAX(lat) AS maxLat, MIN(lon) AS minLon, MAX(lon) AS maxLon FROM citta WHERE id_paese = :id AND lat IS NOT NULL AND lon IS NOT NULL");
                        $stmtB->execute([':id' => $paese['id']]);
                        $bbox = $stmtB->fetch(\PDO::FETCH_ASSOC);
                    } catch (\Exception $e) {
                        $bbox = null;
                    }

                    $countryParam = urlencode($paese['nome']);
                    $mapImage = "/Si_Parte/Public/map.php?paese_id=" . intval($paese['id']) . "&country={$countryParam}&lat=" . urlencode($coords['lat']) . "&lon=" . urlencode($coords['lon']) . "&width=800&height=600";
                    if ($bbox && !empty($bbox['minLat']) && !empty($bbox['minLon']) && $bbox['maxLat'] != $bbox['minLat'] && $bbox['maxLon'] != $bbox['minLon']) {
                        $mapImage .= "&minLat=" . urlencode($bbox['minLat']) . "&maxLat=" . urlencode($bbox['maxLat']) . "&minLon=" . urlencode($bbox['minLon']) . "&maxLon=" . urlencode($bbox['maxLon']);
                    }
                }
            } catch (\Exception $e) {
                // Non blocking: in caso di errore DB non interrompiamo il flusso, lasciamo mapImage a null
                error_log('SelectPaese: impossibile recuperare coords per mappa: ' . $e->getMessage());
            }

            // Se non abbiamo coordinate di una singola città, prova a calcolare il centro del bbox del paese
            if (empty($mapImage)) {
                try {
                    $stmtBB = $this->db->prepare("SELECT MIN(lat) AS minLat, MAX(lat) AS maxLat, MIN(lon) AS minLon, MAX(lon) AS maxLon FROM citta WHERE id_paese = :id AND lat IS NOT NULL AND lon IS NOT NULL");
                    $stmtBB->execute([':id' => $paese['id']]);
                    $bb = $stmtBB->fetch(\PDO::FETCH_ASSOC);
                    if ($bb && isset($bb['minLat']) && isset($bb['maxLat']) && isset($bb['minLon']) && isset($bb['maxLon']) && $bb['maxLat'] != $bb['minLat'] && $bb['maxLon'] != $bb['minLon']) {
                        $centerLat = ($bb['minLat'] + $bb['maxLat']) / 2.0;
                        $centerLon = ($bb['minLon'] + $bb['maxLon']) / 2.0;
                        $countryParam = urlencode($paese['nome']);
                        $mapImage = "/Si_Parte/Public/map.php?paese_id=" . intval($paese['id']) . "&country={$countryParam}&lat=" . urlencode($centerLat) . "&lon=" . urlencode($centerLon) . "&width=800&height=600&minLat=" . urlencode($bb['minLat']) . "&maxLat=" . urlencode($bb['maxLat']) . "&minLon=" . urlencode($bb['minLon']) . "&maxLon=" . urlencode($bb['maxLon']);
                    }
                } catch (\Exception $e) {
                    // ignore
                }
            }

            $response = [
                'success' => true,
                'phase' => 1,
                'paese_selezionato' => [
                    'id' => (int)$paese['id'],
                    'nome' => $paese['nome'] ?? 'Paese non specificato',
                    'codice_iso' => $paese['codice_iso'] ?? null,
                    'descrizione' => $paese['descrizione'] ?? 'Paese selezionato in base alle tue preferenze',
                    'categorie_suggerite' => $categorie,
                    'immagine' => $image,
                    'map_image' => $mapImage
                ],
                'scores' => $totalScores,
                'best_category' => $bestCategory
            ];

            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;

        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Errore database nella selezione del paese',
                'message' => $e->getMessage(),
                'hint' => 'Verifica che la tabella "paesi" esista e sia popolata. Esegui lo script SQL/add_paesi_citta.sql'
            ]);
            error_log("SelectPaese PDO Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Errore nella selezione del paese',
                'message' => $e->getMessage(),
                'type' => get_class($e)
            ]);
            error_log("SelectPaese Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            exit;
        }
    }

    /**
     * Processa le risposte del quiz FASE 2 e restituisce la città raccomandata
     */
    public function submitQuiz($data) {
        try {
            error_log("SubmitQuiz: Inizio elaborazione quiz. Dati ricevuti: " . json_encode($data));
            
            // Valida i dati in input
            if (!isset($data['answers']) || !is_array($data['answers'])) {
                error_log("SubmitQuiz: Risposte mancanti o non valide");
                http_response_code(400);
                echo json_encode([
                    'error' => 'Risposte mancanti o non valide',
                    'hint' => 'Assicurati di aver completato tutte le domande del quiz'
                ]);
                exit;
            }

            error_log("SubmitQuiz: Numero risposte ricevute: " . count($data['answers']));

            // Valida che ci sia l'ID del paese selezionato (dalla fase 1)
            if (!isset($data['paese_id']) || empty($data['paese_id'])) {
                error_log("SubmitQuiz: Paese ID mancante. Dati ricevuti: " . json_encode(array_keys($data)));
                http_response_code(400);
                echo json_encode([
                    'error' => 'Paese non selezionato. Completa prima la fase 1',
                    'hint' => 'Assicurati di aver completato le prime 3 domande e selezionato un paese'
                ]);
                exit;
            }

            $idPaese = (int)$data['paese_id'];
            error_log("SubmitQuiz: Paese ID: $idPaese");

            // Calcola i punteggi totali dalle domande della fase 2 (domande 4-7, indici 3-6)
            $phase2Scores = [];
            if (isset($data['totalScores']) && is_array($data['totalScores'])) {
                $phase2Scores = $data['totalScores'];
                error_log("SubmitQuiz: Punteggi fase 2 da totalScores: " . json_encode($phase2Scores));
            } else {
                // Calcola dai punteggi delle domande fase 2
                $allScores = $this->calculateScoresFromAllAnswers($data['answers']);
                $phase2Scores = $allScores;
                error_log("SubmitQuiz: Punteggi fase 2 calcolati: " . json_encode($phase2Scores));
            }

            // Trova la categoria migliore per la fase 2
            $bestCategory = $this->findBestCategory($phase2Scores);
            error_log("SubmitQuiz: Categoria migliore fase 2: $bestCategory");

            // Recupera informazioni sul paese PRIMA di cercare la città
            try {
                $stmt = $this->db->prepare("SELECT * FROM paesi WHERE id = ?");
                $stmt->execute([$idPaese]);
                $paese = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if (!$paese) {
                    error_log("SubmitQuiz: Paese con ID $idPaese non trovato nel database");
                    http_response_code(404);
                    echo json_encode([
                        'error' => 'Paese non trovato nel database',
                        'hint' => 'Il paese selezionato nella fase 1 non esiste più. Ricomincia il quiz.',
                        'paese_id' => $idPaese
                    ]);
                    exit;
                }
                
                error_log("SubmitQuiz: Paese trovato: {$paese['nome']} (ID: {$paese['id']})");
            } catch (\PDOException $e) {
                error_log("SubmitQuiz PDO Error nel recupero paese: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                http_response_code(500);
                echo json_encode([
                    'error' => 'Errore database nel recupero del paese',
                    'message' => $e->getMessage(),
                    'hint' => 'Verifica che la tabella "paesi" esista e sia popolata. Esegui lo script SQL/add_paesi_citta.sql'
                ]);
                exit;
            }

            // Verifica le categorie effettivamente disponibili nel paese e adattare la bestCategory
            try {
                $stmt = $this->db->prepare("SELECT DISTINCT categoria_viaggio FROM citta WHERE id_paese = ?");
                $stmt->execute([$idPaese]);
                $dbCats = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
            } catch (\Exception $e) {
                $dbCats = [];
            }

            // Mappa categorie DB a frontend
            $dbToFront = [
                'mare' => 'beach',
                'montagna' => 'mountain',
                'città' => 'city',
                'cultura' => 'city',
                'cibo' => 'city',
                'divertimento' => 'city',
                'sci' => 'mountain'
            ];

            $availableFrontCats = [];
            if (!empty($dbCats)) {
                foreach ($dbCats as $dbc) {
                    if (isset($dbToFront[$dbc])) $availableFrontCats[$dbToFront[$dbc]] = true;
                }
                $availableFrontCats = array_keys($availableFrontCats);
            } else {
                // Se non ci sono città registrate, usa le categorie suggerite nel record del paese
                $categorieSuggerite = $paese['categorie_suggerite'] ?? null;
                if (is_string($categorieSuggerite)) {
                    $categorieSuggerite = json_decode($categorieSuggerite, true) ?? [];
                }
                if (is_array($categorieSuggerite) && !empty($categorieSuggerite)) {
                    foreach ($categorieSuggerite as $dbc) {
                        if (isset($dbToFront[$dbc])) $availableFrontCats[$dbToFront[$dbc]] = true;
                    }
                    $availableFrontCats = array_keys($availableFrontCats);
                }
            }

            if (!empty($availableFrontCats) && !in_array($bestCategory, $availableFrontCats)) {
                // Scegli tra le categorie disponibili quella con il punteggio più alto
                $bestAvailable = null;
                $bestScore = -INF;
                foreach ($availableFrontCats as $fc) {
                    $scoreVal = $phase2Scores[$fc] ?? 0;
                    if ($scoreVal > $bestScore) {
                        $bestScore = $scoreVal;
                        $bestAvailable = $fc;
                    }
                }
                if ($bestAvailable) {
                    error_log("SubmitQuiz: bestCategory '$bestCategory' non disponibile per paese $idPaese. Uso '$bestAvailable' invece.");
                    $bestCategory = $bestAvailable;
                }
            }

            // Trova la città nel paese selezionato (ora matchCitta ritorna sempre qualcosa)
            $citta = $this->matchCitta($idPaese, $bestCategory, $phase2Scores, $data);
            
            if (!$citta) {
                error_log("SubmitQuiz: Errore critico - matchCitta ha ritornato null nonostante i fallback");
                http_response_code(500);
                echo json_encode([
                    'error' => 'Errore nella selezione della città',
                    'hint' => 'Si è verificato un errore imprevisto. Riprova più tardi.',
                    'debug' => [
                        'paese_id' => $idPaese,
                        'bestCategory' => $bestCategory,
                        'scores' => $phase2Scores
                    ]
                ]);
                exit;
            }

            error_log("SubmitQuiz: Città selezionata: {$citta['nome']} (ID: {$citta['id']}, Categoria: {$citta['categoria_viaggio']})");

            // Estrai budget dalle risposte se presente (dalla fase 1, domanda 3)
            $budget = $this->extractBudget($data);
            error_log("SubmitQuiz: Budget estratto: " . ($budget ?: 'non specificato'));
            
            // Salva l'utente nel database
            try {
                $user = new User();
                $user->risposte_quiz = [
                    'answers_phase1' => array_slice($data['answers'], 0, 3),
                    'answers_phase2' => array_slice($data['answers'], 3),
                    'totalScores_phase1' => $data['totalScores_phase1'] ?? [],
                    'totalScores_phase2' => $phase2Scores,
                    'bestCategory_phase1' => $data['best_category_phase1'] ?? null,
                    'bestCategory_phase2' => $bestCategory
                ];
                $user->budget_finale = $budget ?: (float)$citta['fascia_budget_base'];
                $user->destinazione_assegnata = $citta['nome'];
                $user->paese_selezionato = $paese['nome'] ?? null;
                $user->tipo_viaggio = ucfirst($citta['categoria_viaggio']);
                $user->scelte_extra = [];
                $user->email = $data['email'] ?? null;
                $user->save();
                
                error_log("SubmitQuiz: Utente salvato con ID: " . ($user->id ?? 'null'));
            } catch (\Exception $e) {
                error_log("SubmitQuiz Error nel salvataggio utente: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                // Non bloccare il processo se il salvataggio fallisce, continua comunque
            }

            // Mappa categoria database -> frontend per la risposta
            // Nota: 'cultura', 'cibo' e 'divertimento' vengono mappati a 'city' 
            // perché non ci sono categorie frontend equivalenti specifiche
            $categoryMap = [
                'mare' => 'beach',
                'montagna' => 'mountain',
                'città' => 'city',
                'cultura' => 'city',  // cultura mappata a city (destinazioni urbane/culturali)
                'cibo' => 'city',     // cibo mappato a city (esperienze urbane)
                'divertimento' => 'city' // divertimento mappato a city (vita urbana)
            ];
            $frontendCategory = $categoryMap[$citta['categoria_viaggio']] ?? 'city';
            
            // Log per debug del mapping
            error_log("SubmitQuiz: Categoria database '{$citta['categoria_viaggio']}' mappata a frontend '$frontendCategory'");

            // Immagine: preferisci immagine specifica della città, poi immagine del paese, poi fallback per categoria
            $image = $citta['immagine'] ?? null;
            if (!$image) {
                // prova immagine bandiera/paese
                $image = $paese['immagine_bandiera'] ?? null;
            }
            if (!$image) {
                $categoryImages = [
                    'beach' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop',
                    'mountain' => 'https://images.unsplash.com/photo-1464822759844-d150f39b8d7d?w=800&h=600&fit=crop',
                    'city' => 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=800&h=600&fit=crop',
                    'ski' => 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=800&h=600&fit=crop'
                ];
                $image = $categoryImages[$frontendCategory] ?? $categoryImages['city'];
                error_log("SubmitQuiz: Usata immagine di default per categoria: $frontendCategory");
            } else {
                error_log("SubmitQuiz: Usata immagine specifica: $image");
            }

            // Genera URL mappa dinamica per la destinazione (evidenzia paese con bbox se disponibile)
            $mapImage = null;
            if (!empty($citta['lat']) && !empty($citta['lon'])) {
                $cityLat = $citta['lat'];
                $cityLon = $citta['lon'];
            } else {
                $cityLat = null;
                $cityLon = null;
            }
                try {
                    $stmtB = $this->db->prepare("SELECT MIN(lat) AS minLat, MAX(lat) AS maxLat, MIN(lon) AS minLon, MAX(lon) AS maxLon FROM citta WHERE id_paese = :id AND lat IS NOT NULL AND lon IS NOT NULL");
                    $stmtB->execute([':id' => $paese['id']]);
                    $bbox = $stmtB->fetch(\PDO::FETCH_ASSOC);
                } catch (\Exception $e) {
                    $bbox = null;
                }

                $countryParam = urlencode($paese['nome']);
                // Se lat/lon della città non sono presenti, usa il centro del bbox come posizione del marker
                if (empty($cityLat) && $bbox && isset($bbox['minLat']) && isset($bbox['maxLat'])) {
                    $cityLat = ($bbox['minLat'] + $bbox['maxLat']) / 2.0;
                }
                if (empty($cityLon) && $bbox && isset($bbox['minLon']) && isset($bbox['maxLon'])) {
                    $cityLon = ($bbox['minLon'] + $bbox['maxLon']) / 2.0;
                }

                $mapImage = "/Si_Parte/Public/map.php?paese_id=" . intval($paese['id']) . "&country={$countryParam}&lat=" . urlencode($cityLat) . "&lon=" . urlencode($cityLon) . "&width=800&height=600";
                if ($bbox && !empty($bbox['minLat']) && !empty($bbox['minLon']) && $bbox['maxLat'] != $bbox['minLat'] && $bbox['maxLon'] != $bbox['minLon']) {
                    $mapImage .= "&minLat=" . urlencode($bbox['minLat']) . "&maxLat=" . urlencode($bbox['maxLat']) . "&minLon=" . urlencode($bbox['minLon']) . "&maxLon=" . urlencode($bbox['maxLon']);
                }

            // Prepara la risposta nel formato atteso dal frontend
            $response = [
                'success' => true,
                'phase' => 2,
                'user_id' => $user->id ?? null,
                'paese' => [
                    'id' => (int)$paese['id'],
                    'nome' => $paese['nome'],
                    'descrizione' => $paese['descrizione'] ?? ''
                ],
                'recommended_destination' => [
                    'id' => (int)$citta['id'],
                    'name' => $citta['nome'],
                    'paese' => $paese['nome'],
                    'category' => $frontendCategory,
                    'image' => $image,
                    'map_image' => $mapImage,
                    'base_budget' => (float)$citta['fascia_budget_base'],
                    'description' => $citta['descrizione'] ?? "Perfetto per te! {$citta['nome']} ({$paese['nome']}) è ideale per un viaggio di tipo {$citta['categoria_viaggio']}.",
                    'budget_finale' => $user->budget_finale ?? (float)$citta['fascia_budget_base'],
                    'tipo_viaggio' => $user->tipo_viaggio ?? ucfirst($citta['categoria_viaggio'])
                ],
                'scores' => $phase2Scores,
                'best_category' => $bestCategory
            ];

            error_log("SubmitQuiz: Risposta preparata con successo. Città: {$citta['nome']}, Paese: {$paese['nome']}");
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;

        } catch (\PDOException $e) {
            error_log("SubmitQuiz PDO Exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode([
                'error' => 'Errore database nell\'elaborazione del quiz',
                'message' => $e->getMessage(),
                'hint' => 'Verifica che le tabelle "paesi" e "citta" esistano e siano popolate. Esegui lo script SQL/add_paesi_citta.sql'
            ]);
            exit;
        } catch (\Exception $e) {
            error_log("SubmitQuiz Exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode([
                'error' => 'Errore nell\'elaborazione del quiz',
                'message' => $e->getMessage(),
                'type' => get_class($e),
                'hint' => 'Controlla i log del server per maggiori dettagli'
            ]);
            exit;
        }
    }

    /**
     * Calcola i punteggi totali da tutte le risposte
     */
    private function calculateScoresFromAllAnswers($answers) {
        $totalScores = ['beach' => 0, 'mountain' => 0, 'city' => 0, 'ski' => 0];
        
        // Le prime 3 domande hanno questa struttura (per fase 1)
        $phase1Questions = $this->getQuestionsArray();
        
        // Domande fase 2 (indici 3-6)
        $phase2Questions = [
            [
                'answers' => [
                    ['scores' => ['beach' => 0, 'mountain' => 0, 'city' => 4, 'ski' => 0]],
                    ['scores' => ['beach' => 1, 'mountain' => 0, 'city' => 3, 'ski' => 0]],
                    ['scores' => ['beach' => 4, 'mountain' => 0, 'city' => 1, 'ski' => 0]],
                    ['scores' => ['beach' => 0, 'mountain' => 4, 'city' => 0, 'ski' => 3]]
                ]
            ],
            [
                'answers' => [
                    ['scores' => ['beach' => 1, 'mountain' => 1, 'city' => 4, 'ski' => 1]],
                    ['scores' => ['beach' => 3, 'mountain' => 3, 'city' => 3, 'ski' => 3]],
                    ['scores' => ['beach' => 4, 'mountain' => 4, 'city' => 2, 'ski' => 4]],
                    ['scores' => ['beach' => 4, 'mountain' => 4, 'city' => 1, 'ski' => 4]]
                ]
            ],
            [
                'answers' => [
                    ['scores' => ['beach' => 2, 'mountain' => 0, 'city' => 4, 'ski' => 1]],
                    ['scores' => ['beach' => 4, 'mountain' => 2, 'city' => 1, 'ski' => 0]],
                    ['scores' => ['beach' => 2, 'mountain' => 4, 'city' => 0, 'ski' => 4]],
                    ['scores' => ['beach' => 1, 'mountain' => 0, 'city' => 4, 'ski' => 0]]
                ]
            ],
            [
                'answers' => [
                    ['scores' => ['beach' => 2, 'mountain' => 3, 'city' => 3, 'ski' => 2]],
                    ['scores' => ['beach' => 1, 'mountain' => 0, 'city' => 4, 'ski' => 0]],
                    ['scores' => ['beach' => 4, 'mountain' => 2, 'city' => 2, 'ski' => 3]],
                    ['scores' => ['beach' => 3, 'mountain' => 4, 'city' => 3, 'ski' => 4]]
                ]
            ]
        ];
        
        // Combina le domande
        $allQuestions = array_merge($phase1Questions, $phase2Questions);
        
        // Calcola i punteggi
        for ($i = 0; $i < min(count($answers), count($allQuestions)); $i++) {
            if (isset($answers[$i]) && $answers[$i] !== null && isset($allQuestions[$i])) {
                $question = $allQuestions[$i];
                $answerIndex = $answers[$i];
                
                if (isset($question['answers'][$answerIndex]['scores'])) {
                    $scores = $question['answers'][$answerIndex]['scores'];
                    $totalScores['beach'] += $scores['beach'] ?? 0;
                    $totalScores['mountain'] += $scores['mountain'] ?? 0;
                    $totalScores['city'] += $scores['city'] ?? 0;
                    $totalScores['ski'] += $scores['ski'] ?? 0;
                }
            }
        }
        
        return $totalScores;
    }

    /**
     * Calcola i punteggi totali dalle risposte (deprecato, usa calculateScoresFromAllAnswers)
     */
    private function calculateScores($answers) {
        return $this->calculateScoresFromAllAnswers($answers);
    }

    /**
     * Trova la categoria con il punteggio più alto
     */
    private function findBestCategory($scores) {
        $maxScore = 0;
        $bestCategory = 'city'; // default

        foreach ($scores as $category => $score) {
            if ($score > $maxScore) {
                $maxScore = $score;
                $bestCategory = $category;
            }
        }

        return $bestCategory;
    }

    /**
     * Trova la destinazione migliore basata sulla categoria e i punteggi
     */
    private function matchDestination($bestCategory, $scores, $data = []) {
        try {
            // Mappa categoria frontend -> categoria database
            $dbCategory = $this->categoryMapping[$bestCategory] ?? 'città';

            // Query per trovare destinazioni che corrispondono
            $stmt = $this->db->prepare("SELECT * FROM destinations WHERE categoria_viaggio = ?");
            $stmt->execute([$dbCategory]);
            $matchingDests = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($matchingDests)) {
                // Se non ci sono corrispondenze, prendi tutte le destinazioni
                $stmt = $this->db->query("SELECT * FROM destinations");
                $matchingDests = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            if (empty($matchingDests)) {
                // Fallback: destinazione di default
                return [
                    'id' => 0,
                    'nome_destinazione' => 'Destinazione generica',
                    'categoria_viaggio' => 'città',
                    'fascia_budget_base' => 1000.00
                ];
            }

            // Se c'è un budget specificato, trova la destinazione più vicina al budget
            $budget = $this->extractBudget($data);
            if ($budget && !empty($matchingDests)) {
                $bestDest = null;
                $minDiff = PHP_FLOAT_MAX;

                foreach ($matchingDests as $dest) {
                    $diff = abs((float)$dest['fascia_budget_base'] - $budget);
                    if ($diff < $minDiff) {
                        $minDiff = $diff;
                        $bestDest = $dest;
                    }
                }

                return $bestDest ?: $matchingDests[0];
            }

            // Ritorna la prima destinazione che corrisponde
            return $matchingDests[0];
        } catch (\Exception $e) {
            error_log("matchDestination Error: " . $e->getMessage());
            // Ritorna una destinazione di default in caso di errore
            return [
                'id' => 0,
                'nome_destinazione' => 'Destinazione generica',
                'categoria_viaggio' => 'città',
                'fascia_budget_base' => 1000.00
            ];
        }
    }

    /**
     * Estrae il budget dalle risposte del quiz
     */
    private function extractBudget($data) {
        if (isset($data['budget'])) {
            return (float)$data['budget'];
        }
        
        // Cerca nelle risposte se c'è una domanda sul budget
        if (isset($data['answers']) && is_array($data['answers'])) {
            // La domanda 3 (indice 2) è quella sul budget
            // I valori sono: 0=500, 1=500-1000, 2=1000-2000, 3=2000+
            if (isset($data['answers'][2])) {
                $budgetIndex = $data['answers'][2];
                $budgetRanges = [500, 750, 1500, 2500];
                if (isset($budgetRanges[$budgetIndex])) {
                    return (float)$budgetRanges[$budgetIndex];
                }
            }
        }

        return null;
    }

    /**
     * Calcola i punteggi totali da un range specifico di risposte
     */
    private function calculateScoresFromAnswers($answers, $startIndex, $endIndex) {
        $totalScores = ['beach' => 0, 'mountain' => 0, 'city' => 0, 'ski' => 0];
        
        // Carica le domande (prime 3 per fase 1)
        $questions = $this->getQuestionsArray();
        
        for ($i = $startIndex; $i < min($endIndex, count($answers)); $i++) {
            if (isset($answers[$i]) && $answers[$i] !== null && isset($questions[$i])) {
                $question = $questions[$i];
                $answerIndex = $answers[$i];
                
                if (isset($question['answers'][$answerIndex]['scores'])) {
                    $scores = $question['answers'][$answerIndex]['scores'];
                    $totalScores['beach'] += $scores['beach'] ?? 0;
                    $totalScores['mountain'] += $scores['mountain'] ?? 0;
                    $totalScores['city'] += $scores['city'] ?? 0;
                    $totalScores['ski'] += $scores['ski'] ?? 0;
                }
            }
        }
        
        return $totalScores;
    }

    /**
     * Restituisce l'array delle domande (per uso interno)
     */
    private function getQuestionsArray() {
        return [
            [
                'answers' => [
                    ['scores' => ['beach' => 3, 'mountain' => 0, 'city' => 1, 'ski' => 0]],
                    ['scores' => ['beach' => 0, 'mountain' => 3, 'city' => 0, 'ski' => 1]],
                    ['scores' => ['beach' => 1, 'mountain' => 1, 'city' => 3, 'ski' => 0]],
                    ['scores' => ['beach' => 0, 'mountain' => 1, 'city' => 0, 'ski' => 3]]
                ]
            ],
            [
                'answers' => [
                    ['scores' => ['beach' => 3, 'mountain' => 0, 'city' => 0, 'ski' => 0]],
                    ['scores' => ['beach' => 0, 'mountain' => 3, 'city' => 0, 'ski' => 0]],
                    ['scores' => ['beach' => 0, 'mountain' => 0, 'city' => 3, 'ski' => 0]],
                    ['scores' => ['beach' => 0, 'mountain' => 0, 'city' => 0, 'ski' => 3]]
                ]
            ],
            [
                'answers' => [
                    ['scores' => ['beach' => 1, 'mountain' => 1, 'city' => 1, 'ski' => 1]],
                    ['scores' => ['beach' => 2, 'mountain' => 2, 'city' => 2, 'ski' => 2]],
                    ['scores' => ['beach' => 3, 'mountain' => 3, 'city' => 3, 'ski' => 3]],
                    ['scores' => ['beach' => 4, 'mountain' => 4, 'city' => 4, 'ski' => 4]]
                ]
            ]
        ];
    }

    /**
     * Trova il paese migliore basato sulla categoria e i punteggi
     */
    private function matchPaese($bestCategory, $scores, $data = []) {
        try {
            // Verifica se la tabella paesi esiste
            $stmt = $this->db->query("SHOW TABLES LIKE 'paesi'");
            $tableExists = $stmt->rowCount() > 0;
            
            if (!$tableExists) {
                error_log("matchPaese: Tabella 'paesi' non trovata. Assicurati di aver eseguito lo script SQL add_paesi_citta.sql");
                // Non ritornare un paese di default - ritorna null e lascia che selectPaese() gestisca l'errore
                return null;
            }

            // Mappa categoria frontend -> categorie database nel campo JSON
            // Includi anche categorie correlate che potrebbero essere presenti nel database
            $categoryToDb = [
                'beach' => ['mare', 'isole', 'costa'],
                'mountain' => ['montagna', 'montagne', 'alpi', 'natura'],
                'city' => ['città', 'cultura', 'storia', 'tradizione', 'shopping', 'divertimento', 'festa'],
                'ski' => ['montagna', 'sci', 'snowboard']
            ];
            
            $dbCategories = $categoryToDb[$bestCategory] ?? ['città'];
            if (!is_array($dbCategories)) {
                $dbCategories = [$dbCategories];
            }

            // Query per trovare paesi che hanno la categoria suggerita nel JSON
            $stmt = $this->db->query("SELECT * FROM paesi");
            $allPaesi = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            if (empty($allPaesi)) {
                error_log("matchPaese: Nessun paese trovato nel database. Popola la tabella paesi con lo script SQL.");
                // Non ritornare un paese di default - ritorna null e lascia che selectPaese() gestisca l'errore
                return null;
            }
            
            error_log("matchPaese: Trovati " . count($allPaesi) . " paesi totali nel database");
            error_log("matchPaese: Categoria frontend: $bestCategory -> categorie DB: " . json_encode($dbCategories));
            
            $matchingPaesi = [];
            $paeseScores = []; // Array per tenere traccia del "punteggio" di ogni paese
            
            foreach ($allPaesi as $paese) {
                $categoriePaese = json_decode($paese['categorie_suggerite'], true) ?? [];
                $score = 0;
                
                error_log("matchPaese: Analizzando paese {$paese['nome']} (ID: {$paese['id']}) con categorie: " . json_encode($categoriePaese));
                
                // Calcola un punteggio basato su quante categorie corrispondono
                // Usa confronto case-insensitive per essere più flessibile
                $categoriePaeseLower = array_map('strtolower', $categoriePaese);
                $primaryMatch = false;
                
                foreach ($dbCategories as $idx => $cat) {
                    $catLower = strtolower($cat);
                    if (in_array($catLower, $categoriePaeseLower)) {
                        $score += 1;
                        // Aggiungi un bonus maggiore se è la categoria principale (prima nella lista)
                        if ($idx === 0) {
                            $score += 2;
                            $primaryMatch = true;
                        }
                    }
                }
                
                // Bonus se il paese ha molte categorie che potrebbero essere correlate
                // Es: per "city", bonus se ha "cultura", "storia", "shopping", ecc.
                if ($bestCategory === 'city' && !$primaryMatch) {
                    $cityRelated = ['cultura', 'città', 'storia', 'tradizione', 'shopping', 'divertimento', 'festa', 'cibo'];
                    foreach ($cityRelated as $related) {
                        if (in_array(strtolower($related), $categoriePaeseLower)) {
                            $score += 0.5;
                        }
                    }
                }
                
                // Aggiungi una componente di variabilità basata sull'ID del paese e i punteggi
                // Questo aiuta a evitare che lo stesso paese venga sempre selezionato
                // Usa i punteggi delle risposte per creare un "bias" casuale ma deterministico
                if (!empty($scores)) {
                    $scoreSum = array_sum($scores);
                    $paeseHash = crc32($paese['nome'] . $paese['id']);
                    // Aggiungi un piccolo bonus random basato sull'hash del paese (0-0.3 punti)
                    // Questo crea variabilità senza alterare troppo i risultati
                    $randomBonus = (($paeseHash % 100) / 1000) * 0.3;
                    $score += $randomBonus;
                }
                
                error_log("matchPaese: Paese {$paese['nome']} - Score: $score");
                
                // Accetta paesi anche con score basso, li ordineremo dopo
                $matchingPaesi[] = $paese;
                $paeseScores[$paese['id']] = $score;
            }

            // Rimuovi paesi con score 0 solo se abbiamo altri paesi con score > 0
            $hasGoodMatches = false;
            foreach ($paeseScores as $score) {
                if ($score > 0) {
                    $hasGoodMatches = true;
                    break;
                }
            }
            
            if ($hasGoodMatches) {
                // Filtra solo paesi con score > 0
                $matchingPaesi = array_filter($matchingPaesi, function($paese) use ($paeseScores) {
                    return ($paeseScores[$paese['id']] ?? 0) > 0;
                });
                $matchingPaesi = array_values($matchingPaesi); // Re-indicizza l'array
            }
            
            error_log("matchPaese: Dopo filtraggio, trovati " . count($matchingPaesi) . " paesi con score > 0");

            if (empty($matchingPaesi)) {
                error_log("matchPaese: Nessun paese disponibile nel database");
                return null;
            }

            // Ordina i paesi per punteggio (decrescente) in modo stabile
            usort($matchingPaesi, function($a, $b) use ($paeseScores) {
                $scoreA = $paeseScores[$a['id']] ?? 0;
                $scoreB = $paeseScores[$b['id']] ?? 0;
                
                if ($scoreA !== $scoreB) {
                    return $scoreB <=> $scoreA; // Ordine decrescente (PHP 7+ spaceship operator)
                }
                
                // Se hanno lo stesso punteggio, mantieni ordine originale (stabile sort)
                return 0;
            });
            
            // Raggruppa i paesi per punteggio
            $groupedByScore = [];
            foreach ($matchingPaesi as $paese) {
                $score = $paeseScores[$paese['id']] ?? 0;
                if (!isset($groupedByScore[$score])) {
                    $groupedByScore[$score] = [];
                }
                $groupedByScore[$score][] = $paese;
            }
            
            // Ordina i gruppi per punteggio (decrescente)
            krsort($groupedByScore);
            
            // Strategia: prendi sempre i primi 2 gruppi di score (anche se il primo ha solo 1 paese)
            // Questo garantisce varietà anche quando un paese ha chiaramente il punteggio più alto
            $topScores = array_slice(array_keys($groupedByScore), 0, 2, true);
            
            $candidates = [];
            $firstGroupScore = null;
            
            foreach ($topScores as $idx => $score) {
                if ($idx === 0) {
                    $firstGroupScore = $score;
                }
                
                $paesiConStessoScore = $groupedByScore[$score];
                
                // Per il primo gruppo (score più alto): se ha più di 1 paese, prendi tutti, altrimenti solo quello
                // Per il secondo gruppo: aggiungi sempre per dare varietà
                if ($idx === 0) {
                    // Primo gruppo: randomizza e prendi tutti (o max 3 se sono troppi)
                    shuffle($paesiConStessoScore);
                    $maxFromFirst = min(count($paesiConStessoScore), count($paesiConStessoScore) > 1 ? count($paesiConStessoScore) : 1);
                    $candidates = array_merge($candidates, array_slice($paesiConStessoScore, 0, $maxFromFirst));
                } else {
                    // Secondo gruppo: randomizza e aggiungi fino a 2 per dare varietà
                    shuffle($paesiConStessoScore);
                    $candidates = array_merge($candidates, array_slice($paesiConStessoScore, 0, 2));
                }
                
                // Se abbiamo abbastanza candidati (minimo 3, meglio 4-5), fermiamoci
                if (count($candidates) >= 5) {
                    break;
                }
            }
            
            // Assicuriamoci di avere sempre almeno 2-3 candidati per la selezione finale
            // Se abbiamo solo 1 candidato (raro), aggiungi anche i migliori del secondo gruppo
            if (count($candidates) < 2 && count($topScores) > 1) {
                $secondGroupScore = array_keys($groupedByScore)[1] ?? null;
                if ($secondGroupScore !== null && isset($groupedByScore[$secondGroupScore])) {
                    $secondGroupPaesi = $groupedByScore[$secondGroupScore];
                    shuffle($secondGroupPaesi);
                    $candidates = array_merge($candidates, array_slice($secondGroupPaesi, 0, 2));
                }
            }
            
            // Se ancora non abbiamo abbastanza candidati, aggiungi paesi dal terzo gruppo se esiste
            if (count($candidates) < 3 && count($groupedByScore) > 2) {
                $thirdGroupScore = array_keys($groupedByScore)[2] ?? null;
                if ($thirdGroupScore !== null && isset($groupedByScore[$thirdGroupScore])) {
                    $thirdGroupPaesi = $groupedByScore[$thirdGroupScore];
                    shuffle($thirdGroupPaesi);
                    $needed = 3 - count($candidates);
                    $candidates = array_merge($candidates, array_slice($thirdGroupPaesi, 0, $needed));
                }
            }
            
            // Se non abbiamo candidati, usa tutti i paesi (non dovrebbe succedere, ma per sicurezza)
            if (empty($candidates)) {
                shuffle($matchingPaesi);
                $candidates = array_slice($matchingPaesi, 0, 5);
            }
            
            // Limita a massimo 5 candidati per la selezione finale, ma assicurati di averne almeno 2
            $minCandidates = min(2, count($matchingPaesi));
            $maxCandidates = min(5, count($matchingPaesi));
            $targetCount = max($minCandidates, min($maxCandidates, count($candidates)));
            $candidates = array_slice($candidates, 0, $targetCount);
            
            // Randomizza l'ordine finale più volte per maggiore casualità
            // Usa mt_rand() per un seeding migliore
            mt_srand(time() + crc32(json_encode($candidates)));
            for ($i = 0; $i < 5; $i++) {
                shuffle($candidates);
                // Aggiungi anche uno shuffle manuale per maggiore casualità
                $shuffled = [];
                $remaining = $candidates;
                while (!empty($remaining)) {
                    $randomIdx = mt_rand(0, count($remaining) - 1);
                    $shuffled[] = $remaining[$randomIdx];
                    array_splice($remaining, $randomIdx, 1);
                }
                $candidates = $shuffled;
            }
            
            $selectedPaese = $candidates[0];
            
            // Log dettagliato
            $logScores = [];
            $shownCount = 0;
            foreach ($groupedByScore as $score => $paesi) {
                foreach (array_slice($paesi, 0, 3) as $p) {
                    if ($shownCount < 5) {
                        $logScores[] = "{$p['nome']} (score: $score)";
                        $shownCount++;
                    }
                }
                if ($shownCount >= 5) break;
            }
            error_log("matchPaese: Top paesi per score: " . implode(", ", $logScores));
            error_log("matchPaese: Candidati selezionati: " . count($candidates));
            foreach ($candidates as $idx => $cand) {
                error_log("matchPaese: Candidato " . ($idx+1) . ": {$cand['nome']} (ID: {$cand['id']}, score: " . ($paeseScores[$cand['id']] ?? 0) . ")");
            }
            error_log("matchPaese: Paese selezionato FINALE: {$selectedPaese['nome']} (ID: {$selectedPaese['id']}, score: " . ($paeseScores[$selectedPaese['id']] ?? 0) . ")");
            
            return $selectedPaese;
            
        } catch (\PDOException $e) {
            error_log("matchPaese PDO Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            // Non ritornare un paese di default - ritorna null e lascia che selectPaese() gestisca l'errore
            return null;
        } catch (\Exception $e) {
            error_log("matchPaese Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Trova la città migliore nel paese selezionato basata sulla categoria e i punteggi della fase 2
     */
    private function matchCitta($idPaese, $bestCategory, $scores, $data = []) {
        try {
            // Verifica se la tabella citta esiste
            $stmt = $this->db->query("SHOW TABLES LIKE 'citta'");
            $tableExists = $stmt->rowCount() > 0;
            
            if (!$tableExists) {
                error_log("Tabella 'citta' non trovata. Assicurati di aver eseguito lo script SQL add_paesi_citta.sql");
                error_log("matchCitta: idPaese=$idPaese, bestCategory=$bestCategory");
                // Fallback: ritorna una città di default
                return $this->getDefaultCitta($idPaese, $bestCategory);
            }

            // Mappa categoria frontend -> categoria database
            $dbCategory = $this->categoryMapping[$bestCategory] ?? 'città';
            error_log("matchCitta: cercando città per idPaese=$idPaese, categoria=$dbCategory (bestCategory=$bestCategory)");

            // Query per trovare città nel paese selezionato che corrispondono alla categoria
            $stmt = $this->db->prepare("SELECT * FROM citta WHERE id_paese = ? AND categoria_viaggio = ? ORDER BY popolarita DESC");
            $stmt->execute([$idPaese, $dbCategory]);
            $matchingCitta = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            error_log("matchCitta: trovate " . count($matchingCitta) . " città con categoria $dbCategory per paese $idPaese");

            if (empty($matchingCitta)) {
                // Se non ci sono corrispondenze esatte, prova categorie correlate
                // Per city, prova anche 'cultura', 'cibo', 'divertimento'
                if ($dbCategory === 'città') {
                    $relatedCategories = ['cultura', 'cibo', 'divertimento'];
                    foreach ($relatedCategories as $relatedCat) {
                        $stmt = $this->db->prepare("SELECT * FROM citta WHERE id_paese = ? AND categoria_viaggio = ? ORDER BY popolarita DESC LIMIT 1");
                        $stmt->execute([$idPaese, $relatedCat]);
                        $relatedCitta = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                        if (!empty($relatedCitta)) {
                            $matchingCitta = $relatedCitta;
                            error_log("matchCitta: trovata città correlata con categoria $relatedCat");
                            break;
                        }
                    }
                }
                
                // Se ancora vuoto, prendi tutte le città del paese
                if (empty($matchingCitta)) {
                    $stmt = $this->db->prepare("SELECT * FROM citta WHERE id_paese = ? ORDER BY popolarita DESC");
                    $stmt->execute([$idPaese]);
                    $matchingCitta = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    error_log("matchCitta: nessuna città con categoria $dbCategory o correlate, trovate " . count($matchingCitta) . " città totali per paese $idPaese");
                }
            }

            if (empty($matchingCitta)) {
                error_log("matchCitta: nessuna città trovata per paese $idPaese. Usando città di default.");
                // Se non sono state trovate città filtrate, prova a prendere la città più popolare del paese
                $stmt = $this->db->prepare("SELECT * FROM citta WHERE id_paese = ? ORDER BY popolarita DESC LIMIT 1");
                $stmt->execute([$idPaese]);
                $topCity = $stmt->fetch(\PDO::FETCH_ASSOC);
                if (!empty($topCity)) {
                    error_log("matchCitta: nessuna città con categoria specifica, uso la città più popolare: {$topCity['nome']}");
                    return $topCity;
                }

                // Fallback: ritorna una città di default per il paese
                return $this->getDefaultCitta($idPaese, $bestCategory);
            }

            // Se c'è un budget specificato, trova la città più vicina al budget
            $budget = $this->extractBudget($data);
            if ($budget && !empty($matchingCitta)) {
                $bestCitta = null;
                $minDiff = PHP_FLOAT_MAX;

                foreach ($matchingCitta as $citta) {
                    $diff = abs((float)$citta['fascia_budget_base'] - $budget);
                    if ($diff < $minDiff) {
                        $minDiff = $diff;
                        $bestCitta = $citta;
                    }
                }

                $selected = $bestCitta ?: $matchingCitta[0];
                error_log("matchCitta: selezionata città '{$selected['nome']}' (id={$selected['id']})");
                return $selected;
            }

            // Ritorna la prima città più popolare
            $selected = $matchingCitta[0];
            error_log("matchCitta: selezionata città più popolare '{$selected['nome']}' (id={$selected['id']}, popolarità={$selected['popolarita']})");
            return $selected;
            
        } catch (\PDOException $e) {
            error_log("matchCitta PDO Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            error_log("matchCitta: idPaese=$idPaese, bestCategory=$bestCategory");
            // Fallback in caso di errore database
            return $this->getDefaultCitta($idPaese, $bestCategory);
        } catch (\Exception $e) {
            error_log("matchCitta Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            error_log("matchCitta: idPaese=$idPaese, bestCategory=$bestCategory");
            return $this->getDefaultCitta($idPaese, $bestCategory);
        }
    }

    /**
     * Ritorna una città di default quando non è possibile trovarne una nel database
     */
    private function getDefaultCitta($idPaese, $bestCategory) {
        // Mappa categoria frontend -> categoria database
        $dbCategory = $this->categoryMapping[$bestCategory] ?? 'città';
        
        // Immagini di default per categoria
        $categoryImages = [
            'beach' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop',
            'mountain' => 'https://images.unsplash.com/photo-1464822759844-d150f39b8d7d?w=800&h=600&fit=crop',
            'city' => 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=800&h=600&fit=crop',
            'ski' => 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=800&h=600&fit=crop'
        ];
        
        $image = $categoryImages[$bestCategory] ?? $categoryImages['city'];
        
        // Nomi di città di default in base alla categoria
        $defaultCityNames = [
            'beach' => 'Destinazione di Mare',
            'mountain' => 'Destinazione di Montagna',
            'city' => 'Destinazione Urbana',
            'ski' => 'Destinazione Sciistica'
        ];
        
        $cityName = $defaultCityNames[$bestCategory] ?? 'Destinazione Consigliata';
        
        return [
            'id' => 0,
            'nome' => $cityName,
            'id_paese' => $idPaese,
            'categoria_viaggio' => $dbCategory,
            'fascia_budget_base' => 1000.00,
            'descrizione' => "Perfetto per te! Questa è una destinazione consigliata basata sulle tue preferenze.",
            'immagine' => $image,
            'popolarita' => 5
        ];
    }

    /**
     * Metodo demo per compatibilità (deprecato)
     */
    public function demoQuiz() {
        $user = new User();
        $user->risposte_quiz = [
            "climate" => "mare",
            "activities" => ["relax","cultura"],
            "budget" => 1000
        ];
        $user->destinazione_assegnata = null;
        $user->tipo_viaggio = null;
        $user->budget_finale = 1000;
        $user->scelte_extra = [];
        $user->email = "demo@example.com";
        $user->save();

        $dest = $this->matchDestination('beach', ['beach' => 3], ['budget' => 1000]);
        
        if ($dest) {
            $user->destinazione_assegnata = $dest['nome_destinazione'];
            $user->tipo_viaggio = ucfirst($dest['categoria_viaggio']);
            $user->save();
        }

        return "Utente ID: {$user->id}<br>Destinazione suggerita: {$dest['nome_destinazione']} 
        (Categoria: {$dest['categoria_viaggio']}, Budget base: {$dest['fascia_budget_base']})";
    }
}
