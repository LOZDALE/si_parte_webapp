const API_BASE = 'api.php'; 

let quizData = [];
let userAnswers = [];
let currentQuestion = 0;
let selectedPaese = null;
let phase1Complete = false;

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

async function loadInitialData() {
    try {
        const res = await fetch(`${API_BASE}?route=quiz/questions`);
        if (!res.ok) throw new Error('Server 404 o 500');
        quizData = await res.json();
        console.log("Quiz caricato!");
    } catch (err) {
        console.error("Errore caricamento dati. Verifica che api.php sia nella root:", err);
    }
}

function startQuiz() {
    if (!quizData || quizData.length === 0) {
        alert("Errore: Dati non caricati. Riprova tra un momento.");
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
    if (currentQuestion === 2 && !phase1Complete) {
        elements.nextBtn.textContent = 'Scopri il Paese';
    } else {
        elements.nextBtn.textContent = currentQuestion === quizData.length - 1 ? 'Vedi Risultato' : 'Prossima';
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
        }
    } catch (err) { console.error(err); }
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
        <h2>Il tuo paese ideale: ${selectedPaese.nome}</h2>
        <p>${selectedPaese.descrizione || ''}</p>
        <button onclick="window.continueQuiz()" class="btn btn-primary">Continua</button>
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
            body: JSON.stringify({ answers: userAnswers, paese_id: selectedPaese?.id })
        });
        const data = await res.json();
        elements.quizContainer.classList.add('hidden');
        elements.resultsContainer.classList.remove('hidden');
        const dest = data.recommended_destination || { name: "Fine!", description: "Grazie per aver partecipato" };
        document.getElementById('destinationName').textContent = dest.name;
        document.getElementById('destinationDescription').textContent = dest.description;
    } catch (err) { console.error(err); }
}

document.addEventListener('DOMContentLoaded', () => {
    if (elements.startBtn) elements.startBtn.addEventListener('click', startQuiz);
    if (elements.nextBtn) elements.nextBtn.addEventListener('click', nextQuestion);
    if (elements.prevBtn) elements.prevBtn.addEventListener('click', prevQuestion);
    loadInitialData();
});