/**
 * CONFIGURAZIONE URL API
 * Su Railway il sito è nella root, quindi non serve "/Si_Parte/"
 */
const API_BASE = '/Public/api.php';

let quizData = [];
let destinations = [];
let totalScores = { beach: 0, mountain: 0, city: 0, lago: 0 };
let userAnswers = [];
let currentQuestion = 0;
let selectedPaese = null;
let phase1Complete = false;

// Elementi DOM
const elements = {
    home: document.getElementById('home'),
    quizContainer: document.getElementById('quizContainer'),
    resultsContainer: document.getElementById('resultsContainer'),
    questionTitle: document.getElementById('questionTitle'),
    questionText: document.getElementById('questionText'),
    answersContainer: document.getElementById('answersContainer'),
    progressFill: document.getElementById('progressFill'),
    prevBtn: document.getElementById('prevBtn'),
    nextBtn: document.getElementById('nextBtn'),
    restartBtn: document.getElementById('restartBtn')
};

document.addEventListener('DOMContentLoaded', initializeApp);

async function initializeApp() {
    startBtn.addEventListener('click', startQuiz);
    elements.nextBtn.addEventListener('click', nextQuestion);
    elements.prevBtn.addEventListener('click', prevQuestion);
    elements.restartBtn.addEventListener('click', restartQuiz);

    try {
        await loadQuizData();
        await loadDestinations();
    } catch (err) {
        console.error('Errore inizializzazione:', err);
        // Fallback dati locali se il server non risponde
        quizData = getDefaultQuizData();
        destinations = getDefaultDestinations();
    }
}

// --- CARICAMENTO DATI ---

async function loadQuizData() {
    try {
        const res = await fetch(`${API_BASE}?route=quiz/questions`);
        const payload = await res.json();
        quizData = Array.isArray(payload) ? payload : (payload.data || getDefaultQuizData());
    } catch (err) {
        quizData = getDefaultQuizData();
    }
}

async function loadDestinations() {
    try {
        const res = await fetch(`${API_BASE}?route=quiz/destinations`);
        const payload = await res.json();
        destinations = Array.isArray(payload) ? payload : (payload.data || getDefaultDestinations());
    } catch (err) {
        destinations = getDefaultDestinations();
    }
}

// --- LOGICA QUIZ ---

function startQuiz() {
    currentQuestion = 0;
    phase1Complete = false;
    selectedPaese = null;
    userAnswers = new Array(quizData.length).fill(null);
    
    elements.home.style.display = 'none';
    elements.quizContainer.classList.remove('hidden');
    elements.resultsContainer.classList.add('hidden');
    loadQuestion();
}

function loadQuestion() {
    const question = quizData[currentQuestion];
    if (!question) return;

    elements.questionTitle.textContent = `Domanda ${currentQuestion + 1} di ${quizData.length}`;
    elements.questionText.textContent = question.question;
    elements.progressFill.style.width = `${((currentQuestion + 1) / quizData.length) * 100}%`;

    elements.answersContainer.innerHTML = '';
    question.answers.forEach((answer, index) => {
        const btn = document.createElement('div');
        btn.className = `answer-option ${userAnswers[currentQuestion] === index ? 'selected' : ''}`;
        btn.textContent = answer.text;
        btn.onclick = () => selectAnswer(index);
        elements.answersContainer.appendChild(btn);
    });

    elements.prevBtn.disabled = currentQuestion === 0;
    
    // Testo pulsante dinamico
    if (currentQuestion === 2 && !phase1Complete) {
        elements.nextBtn.textContent = 'Scopri il tuo paese';
    } else if (currentQuestion === quizData.length - 1) {
        elements.nextBtn.textContent = 'Vedi la città ideale';
    } else {
        elements.nextBtn.textContent = 'Prossima';
    }
}

function selectAnswer(index) {
    userAnswers[currentQuestion] = index;
    const options = document.querySelectorAll('.answer-option');
    options.forEach((opt, i) => opt.classList.toggle('selected', i === index));
}

async function nextQuestion() {
    if (userAnswers[currentQuestion] === null) {
        alert("Seleziona una risposta prima di continuare!");
        return;
    }

    // FINE FASE 1
    if (currentQuestion === 2 && !phase1Complete) {
        await handleSelectPaese();
        return;
    }

    if (currentQuestion < quizData.length - 1) {
        currentQuestion++;
        loadQuestion();
    } else {
        await handleFinalSubmit();
    }
}

// --- INTEGRAZIONE BACKEND ---

async function handleSelectPaese() {
    try {
        const payload = { answers: userAnswers.slice(0, 3) };
        const res = await fetch(`${API_BASE}?route=quiz/select-paese`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (data.success && data.paese_selezionato) {
            selectedPaese = data.paese_selezionato;
            phase1Complete = true;
            
            // Carica domande fase 2 se il server le ha
            try { await loadCountryQuestions(selectedPaese.id); } catch(e) {}
            
            showPaeseResult();
        } else {
            alert("Errore nel trovare un paese adatto. Riprova!");
        }
    } catch (err) {
        console.error("Errore API Paese:", err);
        alert("Connessione al server fallita. Controlla la tua rete.");
    }
}

async function handleFinalSubmit() {
    calculateScore();
    
    try {
        const payload = {
            answers: userAnswers,
            totalScores: totalScores,
            paese_id: selectedPaese?.id
        };

        const res = await fetch(`${API_BASE}?route=quiz/submit`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (data.recommended_destination) {
            displayResults(data.recommended_destination, data.paese);
        }
    } catch (err) {
        console.error("Errore Submit:", err);
        displayResults(recommendedDestination, selectedPaese); // Fallback locale
    }
}

// --- UTILITY ---

function calculateScore() {
    totalScores = { beach: 0, mountain: 0, city: 0, lago: 0 };
    userAnswers.forEach((ansIdx, qIdx) => {
        if (ansIdx !== null && quizData[qIdx]?.answers[ansIdx]?.scores) {
            const s = quizData[qIdx].answers[ansIdx].scores;
            totalScores.beach += s.beach || 0;
            totalScores.mountain += s.mountain || 0;
            totalScores.city += s.city || 0;
            totalScores.lago += s.lago || 0;
        }
    });
}

function showPaeseResult() {
    elements.quizContainer.classList.add('hidden');
    let container = document.getElementById('paeseContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'paeseContainer';
        container.className = 'paese-container';
        document.querySelector('#quiz .container').appendChild(container);
    }
    
    container.innerHTML = `
        <div class="paese-result card text-center p-4">
            <h2>🌍 Destinazione: ${selectedPaese.nome}</h2>
            <img src="${selectedPaese.immagine || ''}" style="max-width:300px; margin: 20px auto; border-radius: 10px;">
            <p>${selectedPaese.descrizione || ''}</p>
            <button id="continueBtn" class="btn btn-primary mt-3">Personalizza il viaggio (Fase 2)</button>
        </div>
    `;
    container.classList.remove('hidden');
    
    document.getElementById('continueBtn').onclick = () => {
        container.classList.add('hidden');
        elements.quizContainer.classList.remove('hidden');
        currentQuestion = 3;
        loadQuestion();
    };
}

function displayResults(dest, paese) {
    elements.quizContainer.classList.add('hidden');
    elements.resultsContainer.classList.remove('hidden');
    
    document.getElementById('destinationName').textContent = `${dest.name}, ${paese?.nome || ''}`;
    document.getElementById('destinationDescription').textContent = dest.description;
    const img = document.getElementById('destinationImage');
    if (img) {
        img.src = dest.immagine || dest.image || '';
        img.style.display = img.src ? 'block' : 'none';
    }
}

function restartQuiz() {
    location.reload(); // Il modo più pulito per resettare tutto
}