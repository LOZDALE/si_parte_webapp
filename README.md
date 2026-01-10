Requisiti:

- PHP 7.4 o superiore
- MySQL/MariaDB (XAMPP)
- Composer (per autoloading)

Setup:

1. Clona il repository
2. Installa le dipendenze con Composer (se necessario): `composer install`
3. Crea il database 'si_parte' e importa SQL/si_parte.sql
4. Configura la connessione al database in `src/Database/Connection.php` (default: localhost, root, password vuota)
5. Apri il frontend: `Front-end/Homepage.html` oppure configura il server web per servire la cartella `Public/`

API Backend:

L'API REST è disponibile in `Public/api.php` e supporta i seguenti endpoint:

- `GET /Public/api.php/quiz/questions` - Restituisce le domande del quiz
- `GET /Public/api.php/quiz/destinations` - Restituisce tutte le destinazioni disponibili
- `POST /Public/api.php/quiz/submit` - Invia le risposte del quiz e riceve la destinazione raccomandata

Il frontend (`Front-end/Script.js`) comunica automaticamente con l'API. Assicurati che il path `API_BASE` sia configurato correttamente per il tuo ambiente.

Struttura Backend:

- `src/Controllers/QuizController.php` - Gestisce la logica del quiz e il matching delle destinazioni
- `src/Models/User.php` - Modello per la gestione degli utenti nel database
- `src/Database/Connection.php` - Singleton per la connessione al database
- `Public/api.php` - Router API REST che gestisce le richieste HTTP

Il backend supporta:
- Matching intelligente delle destinazioni basato sulle risposte del quiz
- Calcolo automatico del budget consigliato
- Salvataggio delle risposte e delle destinazioni assegnate
- Mapping automatico tra categorie frontend (beach, mountain, city, ski) e database (mare, montagna, città, cultura)
