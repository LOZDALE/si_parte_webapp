/**
 * CONFIGURAZIONE URL API
 */
const API_BASE = '/Public/api.php'; 

let quizData = [];
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
    restartBtn: document.getElementById('restartBtn'),
    startBtn: document.getElementById('startBtn')
};

document.addEventListener('DOMContentLoaded', initializeApp);

async function initializeApp() {
    if (elements.startBtn) elements.startBtn.addEventListener('click', startQuiz);
    if (elements.nextBtn) elements.nextBtn.addEventListener('click', nextQuestion);
    if (elements.prevBtn) elements.prevBtn.addEventListener('click', prevQuestion); // Ora definita sotto
    if (elements.restartBtn) elements.restartBtn.addEventListener('click', () => location.reload());

    await loadInitialData();
}

async function loadInitialData() {
    try {
        const res = await fetch(`${API_BASE}?route=quiz/questions`);
        const text = await res.text(); // Leggiamo come testo per debuggare se non è JSON
        try {
            quizData = JSON.parse(text);
            console.log("Quiz caricato correttamente");
        } catch (e) {
            console.error("Il server non ha risposto con JSON. Risposta ricevuta:", text);
        }
    } catch (err) {
        console.error("Errore caricamento dati:", err);
    }
}

function startQuiz() {
    if (!quizData || quizData.length === 0) {
        alert("Dati del quiz non disponibili. Controlla il database su Railway.");
        return;
    }
    currentQuestion = 0;
    phase1Complete = false;
    userAnswers = new Array(quizData.length).fill(null);
    
    elements.home.style.display = 'none';
    elements.quizContainer.classList.remove('hidden');
    loadQuestion();
}

function loadQuestion() {
    const question = quizData[currentQuestion];
    elements.questionTitle.textContent = `Domanda ${currentQuestion + 1} di ${quizData.length}`;
    elements.questionText.textContent = question.question;
    elements.progressFill.style.width = `${((currentQuestion + 1) / quizData.length) * 100}%`;

    elements.answersContainer.innerHTML = '';
    question.answers.forEach((answer, index) => {
        const btn = document.createElement('div');
        btn.className = `answer-option ${userAnswers[currentQuestion] === index ? 'selected' : ''}`;
        btn.textContent = answer.text;
        btn.onclick = () => {
            userAnswers[currentQuestion] = index;
            loadQuestion();
        };
        elements.answersContainer.appendChild(btn);
    });

    elements.prevBtn.disabled = currentQuestion === 0;
    
    // Gestione testo bottone
    if (currentQuestion === 2 && !phase1Complete) {
        elements.nextBtn.textContent = 'Scopri il Paese';
    } else if (currentQuestion === quizData.length - 1) {
        elements.nextBtn.textContent = 'Vedi Risultato';
    } else {
        elements.nextBtn.textContent = 'Prossima';
    }
}

function prevQuestion() {
    if (currentQuestion > 0) {
        currentQuestion--;
        loadQuestion();
    }
}

async function nextQuestion() {
    if (userAnswers[currentQuestion] === null) {
        alert("Seleziona una risposta!");
        return;
    }

    if (currentQuestion === 2 && !phase1Complete) {
        await handleSelectPaese();
    } else if (currentQuestion < quizData.length - 1) {
        currentQuestion++;
        loadQuestion();
    } else {
        await handleFinalSubmit();
    }
}

async function handleSelectPaese() {
    try {
        const res = await fetch(`${API_BASE}?route=quiz/select-paese`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ answers: userAnswers.slice(0, 3) })
        });
        const data = await res.json();
        if (data.success) {
            selectedPaese = data.paese_selezionato;
            phase1Complete = true;
            showPaeseResult();
        } else {
            alert("Errore nella selezione del paese: " + (data.error || "riprova"));
        }
    } catch (err) {
        console.error("Errore selezione paese:", err);
    }
}

function showPaeseResult() {
    elements.quizContainer.classList.add('hidden');
    let container = document.getElementById('paeseResult');
    if (!container) {
        container = document.createElement('div');
        container.id = 'paeseResult';
        container.className = 'card p-4 text-center my-4';
        elements.quizContainer.parentNode.insertBefore(container, elements.resultsContainer);
    }
    container.innerHTML = `
        <h2 class="mb-3">Il tuo paese ideale è: <span class="text-primary">${selectedPaese.nome}</span></h2>
        <p class="mb-4">${selectedPaese.descrizione || 'Un posto fantastico scelto per te!'}</p>
        <button onclick="window.continueQuiz()" class="btn btn-success">Ora personalizza la città!</button>
    `;
    container.classList.remove('hidden');
}

window.continueQuiz = () => {
    document.getElementById('paeseResult').classList.add('hidden');
    elements.quizContainer.classList.remove('hidden');
    currentQuestion++;
    loadQuestion();
};

async function handleFinalSubmit() {
    try {
        const res = await fetch(`${API_BASE}?route=quiz/submit`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                answers: userAnswers, 
                paese_id: selectedPaese ? selectedPaese.id : null 
            })
        });
        const data = await res.json();
        displayResults(data);
    } catch (err) {
        console.error("Errore Submit:", err);
    }
}

function displayResults(data) {
    elements.quizContainer.classList.add('hidden');
    elements.resultsContainer.classList.remove('hidden');
    
    // Usiamo i nomi corretti che arrivano dal backend
    const destination = data.recommended_destination || data.destination || {};
    
    document.getElementById('destinationName').textContent = destination.name || "Destinazione trovata!";
    document.getElementById('destinationDescription').textContent = destination.description || "Goditi il tuo viaggio!";
}