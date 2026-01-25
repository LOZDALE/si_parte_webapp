Requisiti:

- PHP 8.2 o superiore (backend)
- MySQL/MariaDB (XAMPP)
- Composer (per autoloading backend)
- Un server statico per il frontend (es. `python -m http.server`)

Docker (sviluppo locale):

1. Assicurati che MySQL sia attivo in locale (XAMPP).
2. Se usi credenziali diverse, modifica i valori in `docker-compose.yml`.
3. Avvia i servizi: `docker compose up --build`
4. Backend su `http://127.0.0.1:8000`, frontend su `http://127.0.0.1:5173`

Struttura:

- `backend/` - API PHP + DB
- `frontend/` - HTML/CSS/JS statici

Setup backend:

1. `cd backend`
2. Installa le dipendenze: `composer install`
3. Crea il database `si_parte` e importa uno dei dump in `backend/database/`
4. Configura la connessione al database in `backend/src/Database/Connection.php`
5. Avvia il server: `php -S 127.0.0.1:8000 -t public`

API backend:

L'API REST è disponibile in `backend/public/api.php` e supporta i seguenti endpoint:

- `GET /api.php?route=quiz/questions`
- `GET /api.php?route=quiz/destinations`
- `POST /api.php?route=quiz/submit`

Setup frontend:

1. Configura l'endpoint API modificando `API_BASE` in `frontend/assets/js/app.js`
2. Avvia un server statico nella cartella `frontend/` (es. `python -m http.server 5173`)
3. Apri `http://127.0.0.1:5173/index.html`

Note:

- Se il backend è su un dominio diverso, serve una URL assoluta in `API_BASE` (es. `https://tuo-backend/api.php`).

Deploy separati:

- Backend: punta il servizio alla cartella `backend/` (su Railway c'è già `backend/railway.json`).
- Frontend: pubblica la cartella `frontend/` su un host statico (Netlify, Vercel, GitHub Pages, ecc.).
