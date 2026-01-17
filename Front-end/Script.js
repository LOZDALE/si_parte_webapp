// Path dell'API - aggiusta questo in base alla struttura del tuo server
// Se il progetto è in http://localhost/Si_Parte/, usa '/Si_Parte/Public/api.php'
// Se il progetto è nella root di htdocs, usa '/Public/api.php'
// funziona db
const API_BASE = (window.API_BASE) ? window.API_BASE : '/Si_Parte/Public/api.php';

let quizData = [];
let destinations = [];

function getDefaultQuizData() {
    return [
        {
            id: 1,
            question: "Quale clima preferisci?",
            answers: [
                { text: "Caldo e soleggiato", scores: { beach: 3, mountain: 0, city: 1, lago: 0 } },
                { text: "Fresco e montuoso", scores: { beach: 0, mountain: 3, city: 0, lago: 0 } },
                { text: "Temperato", scores: { beach: 1, mountain: 1, city: 3, lago: 0 } },
                { text: "Freddo", scores: { beach: 0, mountain: 1, city: 0, lago: 0 } }
            ],
            type: "preference"
        },
        {
            id: 2,
            question: "Quale attività ti piace di più?",
            answers: [
                { text: "Spiaggia", scores: { beach: 3, mountain: 0, city: 0, lago: 0 } },
                { text: "Trekking", scores: { beach: 0, mountain: 3, city: 0, lago: 0 } },
                { text: "Cultura", scores: { beach: 0, mountain: 0, city: 3, lago: 0 } },
                { text: "Sci", scores: { beach: 0, mountain: 0, city: 0, lago: 0 } }
            ],
            type: "preference"
        },
        {
            id: 3,
            question: "Quale è il tuo budget (approssimativo)?",
            answers: [
                { text: "Fino a 500€", scores: { beach: 1, mountain: 1, city: 1, lago: 0 } },
                { text: "500-1000€", scores: { beach: 2, mountain: 2, city: 2, lago: 0 } },
                { text: "1000-2000€", scores: { beach: 3, mountain: 3, city: 3, lago: 0 } },
                { text: "Oltre 2000€", scores: { beach: 4, mountain: 4, city: 4, lago: 0 } }
            ],
            type: "preference"
        },
        {
            id: 4,
            question: "Preferisci una vacanza?",
            answers: [
                { text: "Breve (3-4 giorni)", scores: { beach: 1, mountain: 1, city: 3, lago: 0 } },
                { text: "Media (1 settimana)", scores: { beach: 2, mountain: 2, city: 2, lago: 0 } },
                { text: "Lunga (2 settimane)", scores: { beach: 3, mountain: 3, city: 1, lago: 0 } },
                { text: "Molto lunga (3+ settimane)", scores: { beach: 4, mountain: 4, city: 0, lago: 0 } }
            ],
            type: "preference"
        },
        {
            id: 5,
            question: "Con chi desideri partire?",
            answers: [
                { text: "Da solo", scores: { beach: 1, mountain: 2, city: 3, lago: 0 } },
                { text: "In coppia", scores: { beach: 3, mountain: 1, city: 2, lago: 0 } },
                { text: "Con la famiglia", scores: { beach: 2, mountain: 2, city: 1, lago: 0 } },
                { text: "Con amici", scores: { beach: 3, mountain: 3, city: 2, lago: 0 } }
            ],
            type: "preference"
        }
    ];
}

const getDefaultDestinations = () => [
    { name: "Spiaggia Paradiso", category: "beach" },
    { name: "Avventura in Montagna", category: "mountain" },
    { name: "Esploratore Urbano", category: "city" },
    { name: "Lago Sereno", category: "lago" }
]; 

let totalScores = {beach: 0, mountain: 0, city: 0, lago: 0};
let recommendedDestination = null;
let userAnswers = [];
let quizStarted = false;
let currentQuestion = 0;
let selectedPaese = null; // Paese selezionato nella fase 1
let phase1Complete = false; // Flag per indicare se la fase 1 è completata

const startBtn = document.getElementById('startBtn');
const quizContainer = document.getElementById('quizContainer');
const resultsContainer = document.getElementById('resultsContainer');
const questionTitle = document.getElementById('questionTitle');
const questionText = document.getElementById('questionText');
const answersContainer = document.getElementById('answersContainer');
const progressFill = document.getElementById('progressFill');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const restartBtn = document.getElementById('restartBtn');
const scoreText = document.getElementById('scoreText');

document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
});
initializeApp();
startBtn.addEventListener('click', startQuiz);
nextBtn.addEventListener('click', nextQuestion);
prevBtn.addEventListener('click', prevQuestion);
restartBtn.addEventListener('click', restartQuiz);

async function initializeApp() {
    try {
        await loadQuizData();
        await loadDestinations();
    } catch (err) {
        console.error('Initialization error:', err);
        console.warn('Errore durante il caricamento iniziale. Uso dati locali.');
        quizData = getDefaultQuizData();
        destinations = getDefaultDestinations();
    }
}

async function loadQuizData() {
    try {
        // Usa query string invece di path info per compatibilità
        const res = await fetch(`${API_BASE}?route=quiz/questions`);
        if (!res.ok) throw new Error('Quiz fetch non ok');
        const payload = await res.json();
        if (Array.isArray(payload)) {
            quizData = payload;
        } else if (payload.data && Array.isArray(payload.data)) {
            quizData = payload.data;
        } else {
            quizData = getDefaultQuizData();
        }
    } catch (err) {
        console.warn('Non è stato possibile caricare il quiz dal backend, fallback ai dati locali', err);
        quizData = getDefaultQuizData();
    }
}

async function loadDestinations() {
    try {
        // Usa query string invece di path info per compatibilità
        const res = await fetch(`${API_BASE}?route=quiz/destinations`);
        if (!res.ok) throw new Error('Destinations fetch non ok');
        const payload = await res.json();
        if (Array.isArray(payload) && payload.length > 0) {
            destinations = payload;
            console.log('Destinazioni caricate dal backend:', destinations);
        } else if (payload.data && Array.isArray(payload.data)) {
            destinations = payload.data;
        } else {
            console.warn('Formato destinazioni non valido, uso default');
            destinations = getDefaultDestinations();
        }
    } catch (err) {
        console.warn('Non è stato possibile caricare destinazioni dal backend, uso default', err);
        destinations = getDefaultDestinations();
    }
}

function startQuiz() {
    quizStarted = true;
    currentQuestion = 0;
    userAnswers = new Array(quizData.length).fill(null);

    document.getElementById('home').style.display = 'none';
    quizContainer.classList.remove('hidden');
    resultsContainer.classList.add('hidden');


    loadQuestion();
}

function loadQuestion() {
    const question = quizData[currentQuestion];

    questionTitle.textContent = `Question ${currentQuestion + 1} of ${quizData.length}`;

    questionText.textContent = question.question;

    const progressPercentage = ((currentQuestion + 1) / quizData.length) * 100;
    progressFill.style.width = progressPercentage + '%';

    answersContainer.innerHTML = '';

    question.answers.forEach((answer, index) => {
        const answerDiv = document.createElement('div');
        answerDiv.className = 'answer-option';
        if (userAnswers[currentQuestion] === index) {
            answerDiv.classList.add('selected');
        }
        answerDiv.textContent = answer.text;

        answerDiv.addEventListener('click', () => selectAnswer(index));
        answersContainer.appendChild(answerDiv);
    });

    prevBtn.disabled = currentQuestion === 0;
    
    // Gestisci il testo del pulsante in base alla fase
    if (currentQuestion === 2 && !phase1Complete) {
        nextBtn.textContent = 'Scopri il tuo paese';
    } else if (currentQuestion === quizData.length - 1) {
        nextBtn.textContent = 'Vedi la destinazione';
    } else {
        nextBtn.textContent = 'Prossima domanda';
    }

    quizContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function selectAnswer(index) {
    userAnswers[currentQuestion] = index;

    const answerOptions = document.querySelectorAll('.answer-option');
    answerOptions.forEach((option, i) => {
        if (i === index) {
            option.classList.add('selected');
        } else {
            option.classList.remove('selected');
        }
    });
}

async function nextQuestion() {
    // Se siamo alla fine della fase 1 (domanda 3, indice 2), seleziona il paese
    if (currentQuestion === 2 && !phase1Complete) {
        // Verifica che la domanda sia risposta
        if (userAnswers[2] === null) {
            alert('Per favore rispondi a questa domanda prima di continuare');
            return;
        }
        
        // Seleziona il paese
        await selectPaese();
        return;
    }
    
    // Se abbiamo appena mostrato il paese e l'utente ha cliccato "Continua"
    if (phase1Complete && currentQuestion === 2) {
        currentQuestion = 3; // Passa alla domanda 4 (fase 2)
        loadQuestion();
        return;
    }
    
    // Comportamento normale per le altre domande
    if (currentQuestion < quizData.length - 1) {
        currentQuestion++;
        loadQuestion();
    } else {
        // Fine del quiz - mostra i risultati
        calculateScore();
        await showResults();
    }
}

function prevQuestion() {
    // Se siamo alla domanda 4 (indice 3) e la fase 1 è completa, torna alla visualizzazione del paese
    if (currentQuestion === 3 && phase1Complete) {
        showPaeseResult();
        return;
    }
    
    if (currentQuestion > 0) {
        currentQuestion--;
        loadQuestion();
    }
}

/**
 * Seleziona il paese basato sulle prime 3 risposte (Fase 1)
 */
async function selectPaese() {
    try {
        // Prepara i dati delle prime 3 risposte
        const phase1Answers = userAnswers.slice(0, 3);
        
        // Verifica che tutte le risposte siano presenti
        if (phase1Answers.some(answer => answer === null)) {
            alert('Per favore rispondi a tutte le domande prima di continuare');
            return;
        }

        const payload = {
            answers: phase1Answers
        };

        // Costruisci l'URL dell'API con query string per compatibilità
        const apiUrl = `${API_BASE}?route=quiz/select-paese`;
        console.log('Chiamata API:', apiUrl);
        console.log('Payload:', payload);

        const res = await fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (res.ok) {
            const data = await res.json();
            if (data.success && data.paese_selezionato) {
                selectedPaese = data.paese_selezionato;
                // Carica le domande specifiche per il paese selezionato (fase 2)
                try {
                    await loadCountryQuestions(selectedPaese.id);
                } catch (err) {
                    console.warn('Impossibile caricare domande specifiche per paese, uso fallback', err);
                }
                phase1Complete = true;
                showPaeseResult();
            } else {
                console.error('Errore nella selezione del paese:', data);
                let errorMsg = 'Errore nella selezione del paese. ';
                if (data.error) {
                    errorMsg += data.error;
                }
                if (data.hint) {
                    errorMsg += '\n\nSuggerimento: ' + data.hint;
                }
                alert(errorMsg);
            }
        } else {
            const errorData = await res.json().catch(() => ({ error: 'Errore sconosciuto' }));
            console.error('Errore dal backend:', errorData);
            let errorMsg = 'Errore nel caricamento del paese selezionato.';
            if (errorData.error) {
                errorMsg = errorData.error;
            }
            if (errorData.message) {
                errorMsg += '\n' + errorData.message;
            }
            if (errorData.hint) {
                errorMsg += '\n\n' + errorData.hint;
            }
            alert(errorMsg);
        }
    } catch (err) {
        console.error('Errore nella selezione del paese:', err);
        let errorMsg = 'Errore nella connessione al server.';
        if (err.message) {
            errorMsg += '\n' + err.message;
        }
        errorMsg += '\n\nVerifica che:\n- Il server sia avviato\n- La tabella "paesi" esista nel database\n- Lo script SQL/add_paesi_citta.sql sia stato eseguito';
        alert(errorMsg);
    }
}

/**
 * Carica le domande della fase 2 specifiche per un `paeseId` e sostituisce le domande locali.
 */
async function loadCountryQuestions(paeseId) {
    try {
        const res = await fetch(`${API_BASE}?route=quiz/questions&paese_id=${paeseId}`);
        if (!res.ok) throw new Error('Country questions fetch non ok');
        const payload = await res.json();
            if (Array.isArray(payload) && payload.length > 0) {
                // Sostituisci le domande da indice 3 in poi con le domande ricevute
                quizData.splice(3, Math.max(0, quizData.length - 3), ...payload);
                // Aggiorna userAnswers per riflettere la nuova lunghezza del quiz
                const newLength = quizData.length;
                const newAnswers = new Array(newLength).fill(null);
                // copia le risposte già fornite per le domande esistenti
                for (let i = 0; i < Math.min(userAnswers.length, newLength); i++) {
                    newAnswers[i] = userAnswers[i];
                }
                userAnswers = newAnswers;
                console.log('Domande fase2 caricate per paese:', paeseId, payload);
            } else {
            console.warn('Formato domande paese non valido, uso domande locali');
        }
    } catch (err) {
        console.warn('Errore caricamento domande paese:', err);
        throw err;
    }
}

/**
 * Mostra il risultato del paese selezionato (Fase 1 completata)
 */
function showPaeseResult() {
    // Nascondi il container delle domande e mostra un messaggio con il paese
    quizContainer.classList.add('hidden');
    
    // Crea o aggiorna il container per mostrare il paese
    let paeseContainer = document.getElementById('paeseContainer');
    if (!paeseContainer) {
        paeseContainer = document.createElement('div');
        paeseContainer.id = 'paeseContainer';
        paeseContainer.className = 'paese-container';
        paeseContainer.innerHTML = `
                <div class="paese-result">
                    <h2>🎉 Paese Selezionato!</h2>
                    <div id="paeseInfo"></div>
                    <button id="continueBtn" class="btn btn-primary">Continua con le domande sulla città</button>
                </div>
            `;
        document.querySelector('#quiz .container').appendChild(paeseContainer);
        
        // Aggiungi listener al pulsante continua
        document.getElementById('continueBtn').addEventListener('click', () => {
            paeseContainer.classList.add('hidden');
            quizContainer.classList.remove('hidden');
            currentQuestion = 3; // Passa alla domanda 4 (fase 2)
            loadQuestion();
        });
    }
    
    // Mostra le informazioni del paese
    const paeseInfo = document.getElementById('paeseInfo');
    // Usa l'immagine fornita dal backend se disponibile
    const paeseImage = selectedPaese && selectedPaese.immagine ? selectedPaese.immagine : '';
    paeseInfo.innerHTML = `
        ${paeseImage ? `<img src="${paeseImage}" alt="${selectedPaese.nome}" class="paese-image" style="max-width:100%;height:auto;border-radius:8px;margin-bottom:10px;">` : ''}
        <h3>${selectedPaese.nome}</h3>
        <p>${selectedPaese.descrizione || 'Paese perfetto per le tue preferenze!'}</p>
        <p><strong>Categorie suggerite:</strong> ${(selectedPaese.categorie_suggerite || []).join(', ')}</p>
    `;
    
    paeseContainer.classList.remove('hidden');
    paeseContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function calculateScore() {
    totalScores = {beach: 0, mountain: 0, city: 0, lago: 0};
    userAnswers.forEach((answerIndex, questionIndex) => {
        if (answerIndex !== null && quizData[questionIndex] && quizData[questionIndex].answers[answerIndex]) {
            const scores = quizData[questionIndex].answers[answerIndex].scores;
            if (scores) {
                totalScores.beach += scores.beach || 0;
                totalScores.mountain += scores.mountain || 0;
                totalScores.city += scores.city || 0;
                totalScores.lago += scores.lago || 0;
            }
        }
    });

    // Trova la categoria con il punteggio più alto
    let maxScore = 0;
    let bestCategory = 'city'; // default
    for (const [category, score] of Object.entries(totalScores)) {
        if (score > maxScore) {
            maxScore = score;
            bestCategory = category;
        }
    }

    // Trova la destinazione corrispondente
    recommendedDestination = destinations.find(dest => dest.category === bestCategory);
    
    // Se non trova corrispondenza, usa la prima disponibile
    if (!recommendedDestination && destinations.length > 0) {
        recommendedDestination = destinations[0];
    }
    
    console.log('Categoria migliore:', bestCategory, 'Punteggi:', totalScores);
}

async function showResults() {
    quizContainer.classList.add('hidden');
    resultsContainer.classList.remove('hidden');
    
    // Nascondi il container del paese se visibile
    const paeseContainer = document.getElementById('paeseContainer');
    if (paeseContainer) {
        paeseContainer.classList.add('hidden');
    }

    // Mostra la destinazione raccomandata locale (temporanea)
    document.getElementById('destinationName').textContent = 'Calcolo in corso...';
    document.getElementById('destinationDescription').textContent = 'Stiamo elaborando le tue risposte per trovare la città perfetta...';
    
    // Resetta e nascondi l'immagine prima del caricamento
    const destinationImage = document.getElementById('destinationImage');
    if (destinationImage) {
        destinationImage.src = '';
        destinationImage.alt = 'Caricamento...';
        destinationImage.style.display = 'none';
    }

    try {
        // Verifica che il paese sia selezionato
        if (!selectedPaese || !selectedPaese.id) {
            console.error('showResults: Paese non selezionato. selectedPaese:', selectedPaese);
            alert('Errore: paese non selezionato. Ricomincia il quiz.');
            restartQuiz();
            return;
        }

        console.log('showResults: Inizio elaborazione risultati');
        console.log('showResults: Paese selezionato:', selectedPaese);
        console.log('showResults: Risposte utente:', userAnswers);
        console.log('showResults: Punteggi totali:', totalScores);

        const payload = {
            answers: userAnswers,
            totalScores: totalScores,
            paese_id: selectedPaese.id,
            totalScores_phase1: totalScores, // Per ora usa gli stessi punteggi
            best_category_phase1: findBestCategory(totalScores)
        };

        console.log('showResults: Payload inviato al backend:', payload);

        const res = await fetch(`${API_BASE}?route=quiz/submit`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        console.log('showResults: Risposta HTTP ricevuta. Status:', res.status, res.statusText);

        if (res.ok) {
            const data = await res.json();
            console.log('showResults: Dati ricevuti dal backend:', data);
            
            // Verifica che la risposta contenga i dati necessari
            if (!data || !data.recommended_destination) {
                console.error('showResults: Risposta backend non valida. Dati ricevuti:', data);
                
                // Fallback: mostra una destinazione di default senza immagine
                displayDestinationRecommendation({
                    name: 'Destinazione Consigliata',
                    description: 'Siamo spiacenti, non siamo riusciti a trovare una destinazione specifica. Ricomincia il quiz per ottenere una raccomandazione personalizzata.',
                    base_budget: 1000,
                    budget_finale: 1000,
                    tipo_viaggio: 'Generale'
                }, selectedPaese ? { nome: selectedPaese.nome, descrizione: '' } : null);
                
                alert('Attenzione: non è stato possibile recuperare una destinazione specifica. Viene mostrata una destinazione di default.');
                return;
            }
            
            // Usa la destinazione raccomandata dal backend
            console.log('showResults: Destinazione raccomandata:', data.recommended_destination);
            console.log('showResults: Paese:', data.paese);
            
            displayDestinationRecommendation(data.recommended_destination, data.paese);
        } else {
            // Gestione errori HTTP
            let errorData;
            try {
                errorData = await res.json();
                console.error('showResults: Errore dal backend (JSON):', errorData);
            } catch (jsonError) {
                errorData = { 
                    error: `Errore HTTP ${res.status}: ${res.statusText}`,
                    hint: 'Il server non è riuscito a elaborare la richiesta'
                };
                console.error('showResults: Errore nel parsing JSON della risposta di errore:', jsonError);
            }
            
            // Mostra messaggio di errore dettagliato
            let errorMessage = 'Errore nell\'elaborazione dei risultati.';
            if (errorData.error) {
                errorMessage = errorData.error;
            }
            if (errorData.hint) {
                errorMessage += '\n\n' + errorData.hint;
            }
            if (errorData.message) {
                errorMessage += '\n\nDettagli: ' + errorData.message;
            }
            
            console.error('showResults: Messaggio errore completo:', errorMessage);
            alert(errorMessage);
            
            // Mostra una destinazione di default in caso di errore (senza immagine)
            displayDestinationRecommendation({
                name: 'Destinazione Temporanea',
                description: 'Si è verificato un errore nel recupero della destinazione. Per favore riprova più tardi o ricomincia il quiz.',
                base_budget: 1000,
                budget_finale: 1000,
                tipo_viaggio: 'Generale'
            }, selectedPaese ? { nome: selectedPaese.nome, descrizione: '' } : null);
        }
    } catch (err) {
        console.error('showResults: Eccezione durante l\'elaborazione:', err);
        console.error('showResults: Stack trace:', err.stack);
        
        let errorMessage = 'Errore nella connessione al server.';
        if (err.message) {
            errorMessage += '\n\nDettagli: ' + err.message;
        }
        errorMessage += '\n\nVerifica che:\n- Il server sia avviato\n- La connessione internet sia attiva\n- Prova a ricaricare la pagina';
        
        alert(errorMessage);
        
        // Fallback: mostra una destinazione di default anche in caso di errore di rete (senza immagine)
        displayDestinationRecommendation({
            name: 'Destinazione Temporanea',
            description: 'Si è verificato un errore di connessione. Per favore ricarica la pagina e riprova.',
            base_budget: 1000,
            budget_finale: 1000,
            tipo_viaggio: 'Generale'
        }, selectedPaese ? { nome: selectedPaese.nome, descrizione: '' } : null);
    }

    resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/**
 * Trova la categoria con il punteggio più alto
 */
function findBestCategory(scores) {
    let maxScore = 0;
    let bestCategory = 'city';
    for (const [category, score] of Object.entries(scores)) {
        if (score > maxScore) {
            maxScore = score;
            bestCategory = category;
        }
    }
    return bestCategory;
}

function displayDestinationRecommendation(dest, paese = null) {
    console.log('displayDestinationRecommendation: Destinazione ricevuta:', dest);
    console.log('displayDestinationRecommendation: Paese ricevuto:', paese);
    
    try {
        const destinationImage = document.getElementById('destinationImage');
        const destinationName = document.getElementById('destinationName');
        const destinationDescription = document.getElementById('destinationDescription');
        
        // Verifica che gli elementi esistano
        if (!destinationImage || !destinationName || !destinationDescription) {
            console.error('displayDestinationRecommendation: Elementi DOM non trovati');
            alert('Errore: elementi della pagina non trovati. Ricarica la pagina.');
            return;
        }
        
        // Aggiorna l'immagine della destinazione:
        // preferisci in ordine: dest.image, dest.immagine, then paese.immagine
        const candidateImages = [];
        if (dest) {
            if (dest.image) candidateImages.push(dest.image);
            if (dest.immagine) candidateImages.push(dest.immagine);
        }
        if (paese && paese.immagine) candidateImages.push(paese.immagine);
        const chosenImage = candidateImages.find(src => !!src) || null;
        if (chosenImage) {
            destinationImage.style.display = '';
            destinationImage.src = chosenImage;
            destinationImage.alt = dest?.name || paese?.nome || 'Destinazione consigliata';
            console.log('displayDestinationRecommendation: Immagine impostata:', destinationImage.src);
            destinationImage.onerror = function() {
                console.error('displayDestinationRecommendation: Errore nel caricamento immagine:', this.src);
                this.onerror = null;
                this.style.display = 'none';
            };
        } else {
            // Nascondi elemento immagine se non ci sono sorgenti valide
            destinationImage.style.display = 'none';
            destinationImage.src = '';
            destinationImage.alt = '';
            console.log('displayDestinationRecommendation: Nessuna immagine da mostrare, elemento nascosto');
        }
        
        // Mostra nome città e paese
        let nameText = 'Destinazione consigliata';
        if (dest && dest.name) {
            if (paese && (dest.paese || paese.nome)) {
                nameText = `${dest.name}, ${dest.paese || paese.nome}`;
            } else {
                nameText = dest.name;
            }
        } else if (paese && paese.nome) {
            nameText = `Destinazione in ${paese.nome}`;
        }
        
        destinationName.textContent = nameText;
        console.log('displayDestinationRecommendation: Nome impostato:', nameText);
        
        // Costruisci la descrizione
        let description = '';
        
        // Aggiungi descrizione del paese se disponibile
        if (paese && paese.descrizione) {
            description = paese.descrizione;
            if (!description.endsWith('.') && !description.endsWith('!')) {
                description += '.';
            }
            description += ' ';
        }
        
        // Aggiungi descrizione della destinazione
        if (dest && dest.description) {
            description += dest.description;
        } else if (dest && dest.name) {
            description += `Perfetto per te! ${dest.name} è una destinazione ideale basata sulle tue preferenze.`;
        } else {
            description += 'Perfetto per te! Questa destinazione è ideale basata sulle tue preferenze.';
        }
        
        // Aggiungi informazioni aggiuntive se disponibili
        const additionalInfo = [];
        
        if (dest && dest.tipo_viaggio) {
            additionalInfo.push(`Tipo di viaggio: ${dest.tipo_viaggio}`);
        }
        
        if (dest && dest.base_budget) {
            additionalInfo.push(`Budget consigliato: €${dest.base_budget.toFixed(2)}`);
        }
        
        if (dest && dest.budget_finale && dest.budget_finale !== dest.base_budget) {
            additionalInfo.push(`Budget finale: €${dest.budget_finale.toFixed(2)}`);
        }
        
        if (additionalInfo.length > 0) {
            description += '\n\n' + additionalInfo.join(' | ');
        }
        
        destinationDescription.textContent = description || 'Perfetto per te!';
        console.log('displayDestinationRecommendation: Descrizione impostata:', description);
        
        console.log('displayDestinationRecommendation: Visualizzazione completata con successo');
    } catch (err) {
        console.error('displayDestinationRecommendation: Errore durante la visualizzazione:', err);
        console.error('displayDestinationRecommendation: Stack trace:', err.stack);
        alert('Errore nella visualizzazione dei risultati. Per favore ricarica la pagina.');
    }
}

function restartQuiz() {
    currentQuestion = 0;
    userAnswers = [];
    quizStarted = false;
    selectedPaese = null;
    phase1Complete = false;

    document.getElementById('home').style.display = 'block';
    quizContainer.classList.add('hidden');
    resultsContainer.classList.add('hidden');
    
    // Nascondi il container del paese se presente
    const paeseContainer = document.getElementById('paeseContainer');
    if (paeseContainer) {
        paeseContainer.classList.add('hidden');
    }

    document.body.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}

function handleFormSubmit(e) {
    if (e) {
        e.preventDefault();
    }
}

function logProgress() {
    console.log('Current Question:', currentQuestion);
    console.log('User Answers:', userAnswers);
    console.log('Score:', score);
}
