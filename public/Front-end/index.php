<?php
// 1. Abilitiamo i messaggi di errore per capire se la connessione fallisce
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Percorso dinamico basato sul tuo albero:
// Esci da Front-end (../) ed entra in src/Database/Connection.php
$db_path = __DIR__ . '/../src/Database/Connection.php';

if (file_exists($db_path)) {
    include_once($db_path);
} else {
    // Se vedi questo messaggio sul sito, significa che il percorso include è ancora errato
    echo "";
}
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Si Parte - Homepage</title>
    <link rel="stylesheet" href="Style.css" />
  </head>
  <body>
    <header>
      <nav class="navbar">
        <div class="container">
          <div class="logo">
            <img src="img/Logo.png" alt="Logo" />
          </div>
          <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="#quiz">Quiz</a></li>
            <li><a href="About_us.html">About</a></li>
            <li><a href="Contact.html">Contact</a></li>
          </ul>
        </div>
      </nav>
    </header>

    <main>
      <section id="home" class="hero">
        <div class="container">
          <img src="img/SiParteBianco.png" alt="Si Parte" />
        </div>
      </section>

      <section class="info">
        <div class="container">
          <h1>Il tuo viaggio inizia da qui</h1>
          <p>SiParte! è la web app che ti aiuta a scegliere il tuo prossimo viaggio in modo semplice e personalizzato.</p>
          <p>Rispondi a poche domande, scopri la destinazione più adatta a te e vivi una simulazione interattiva del tuo viaggio, dalle attività al budget.</p>
          <p>Che tu stia cercando relax, avventura o cultura, SiParte! ti accompagna passo dopo passo nella pianificazione della tua esperienza ideale.</p>
          
          <?php 
          // Controllo visivo della connessione (solo per debug)
          if (isset($conn) && !$conn->connect_error) {
              echo "<p style='color: #2ecc71; font-weight: bold;'>✔ Sistema di ricerca pronto</p>";
          } elseif (isset($conn->connect_error)) {
              echo "<p style='color: #e74c3c;'>✘ Errore database: " . $conn->connect_error . "</p>";
          }
          ?>
        </div>
      </section>

      <section id="quiz" class="quiz-section">
        <div class="container">
          <button id="startBtn" class="btn btn-primary">Iniziamo</button>
          <div id="quizContainer" class="quiz-container hidden">
            <div class="quiz-header">
              <h2 id="questionTitle">Domanda 1</h2>
              <div class="progress-bar">
                <div id="progressFill" class="progress-fill"></div>
              </div>
            </div>

            <div class="quiz-content">
              <p id="questionText" class="question-text"></p>
              <div id="answersContainer" class="answers-container"></div>
            </div>

            <div class="quiz-footer">
              <button id="prevBtn" class="btn btn-secondary">Domanda precedente</button>
              <button id="nextBtn" class="btn btn-primary">Domanda successiva</button>
            </div>
          </div>

          <div id="resultsContainer" class="results-container hidden">
            <h2>La tua destinazione ideale</h2>
            <div id="destinationDisplay">
              <img id="destinationImage" src="" alt="Destinazione" style="width: 100%; max-width: 600px; height: auto; border-radius: 10px; margin-bottom: 1rem;" />
              <h3 id="destinationName"></h3>
              <p id="destinationDescription"></p>
            </div>
            <button id="restartBtn" class="btn btn-primary">Cambiato Idea?</button>
          </div>
        </div>
      </section>

      <section class="info">
        <div class="container">
          <p>Scegli la tua meta e organizza il tuo budget in pochi click con la nostra intelligenza artificiale.</p>
        </div>
      </section>
    </main>

    <footer>
      <div class="container">
        <p>&copy; 2026 Si Parte! All rights reserved.</p>
      </div>
    </footer>

    <script src="Script.js"></script>
  </body>
</html>