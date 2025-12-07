// ===== API CONFIG =====
// Default API entrypoint (when you run PHP server from project root use: php -S localhost:8000 -t . )
const API_BASE = (window.API_BASE) ? window.API_BASE : '/Public/api.php';

// ===== QUIZ DATA ===== 
// The app will try to fetch questions from the backend and fall back to this local data.
let quizData = [];
let destinations = [];

function getDefaultQuizData() {
    return [
        {
            id: 1,
            question: "Quale clima preferisci?",
            answers: ["Caldo e soleggiato", "Fresco e montuoso", "Temperato", "Freddo"],
            correct: 0,
            type: "preference"
        },
        {
            id: 2,
            question: "Quale attività ti piace di più?",
            answers: ["Spiaggia", "Trekking", "Cultura", "Sci"],
            correct: 1,
            type: "preference"
        },
        {
            id: 3,
            question: "Quale è il tuo budget (approssimativo)?",
            answers: ["Fino a 500€", "500-1000€", "1000-2000€", "Oltre 2000€"],
            correct: 2,
            type: "preference"
        },
        {
            id: 4,
            question: "Preferisci una vacanza?",
            answers: ["Breve (3-4 giorni)", "Media (1 settimana)", "Lunga (2 settimane)", "Molto lunga (3+ settimane)"],
            correct: 1,
            type: "preference"
        },
        {
            id: 5,
            question: "Con chi desideri partire?",
            answers: ["Da solo", "In coppia", "Con la famiglia", "Con amici"],
            correct: 3,
            type: "preference"
        }
    ];
}

// ===== STATE MANAGEMENT =====
let currentQuestion = 0;
let userAnswers = [];
let score = 0;
let quizStarted = false;

// ===== DOM ELEMENTS =====
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

// ===== EVENT LISTENERS =====
document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
    startBtn.addEventListener('click', startQuiz);
    nextBtn.addEventListener('click', nextQuestion);
    prevBtn.addEventListener('click', prevQuestion);
    restartBtn.addEventListener('click', restartQuiz);
});

async function initializeApp() {
    try {
        await loadQuizData();
        await loadDestinations();
    } catch (err) {
        console.error('Initialization error:', err);
        showError('Errore durante il caricamento iniziale. Uso dati locali.');
        quizData = getDefaultQuizData();
    }
}

async function loadQuizData() {
    try {
        const res = await fetch(`${API_BASE}/quiz/questions`);
        if (!res.ok) throw new Error('Quiz fetch non ok');
        const payload = await res.json();
        // Support both { success,message,data } and direct array
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
        const res = await fetch(`${API_BASE}/quiz/destinations`);
        if (!res.ok) throw new Error('Destinations fetch non ok');
        const payload = await res.json();
        if (Array.isArray(payload)) {
            destinations = payload;
        } else if (payload.data && Array.isArray(payload.data)) {
            destinations = payload.data;
        }
    } catch (err) {
        console.warn('Non è stato possibile caricare destinazioni dal backend', err);
        destinations = [];
    }
}

// ===== QUIZ FUNCTIONS =====
function startQuiz() {
    quizStarted = true;
    currentQuestion = 0;
    userAnswers = new Array(quizData.length).fill(null);
    score = 0;

    // Hide hero section and show quiz
    startBtn.closest('.hero').style.display = 'none';
    quizContainer.classList.remove('hidden');
    resultsContainer.classList.add('hidden');

    // Load first question
    loadQuestion();
}

function loadQuestion() {
    const question = quizData[currentQuestion];

    // Update question title
    questionTitle.textContent = `Question ${currentQuestion + 1} of ${quizData.length}`;

    // Update question text
    questionText.textContent = question.question;

    // Update progress bar
    const progressPercentage = ((currentQuestion + 1) / quizData.length) * 100;
    progressFill.style.width = progressPercentage + '%';

    // Clear previous answers
    answersContainer.innerHTML = '';

    // Load answer options
    question.answers.forEach((answer, index) => {
        const answerDiv = document.createElement('div');
        answerDiv.className = 'answer-option';
        if (userAnswers[currentQuestion] === index) {
            answerDiv.classList.add('selected');
        }
        answerDiv.textContent = answer;

        answerDiv.addEventListener('click', () => selectAnswer(index));
        answersContainer.appendChild(answerDiv);
    });

    // Update button states
    prevBtn.disabled = currentQuestion === 0;
    nextBtn.textContent = currentQuestion === quizData.length - 1 ? 'Finish' : 'Next';

    // Scroll to top
    quizContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function selectAnswer(index) {
    userAnswers[currentQuestion] = index;

    // Update visual selection
    const answerOptions = document.querySelectorAll('.answer-option');
    answerOptions.forEach((option, i) => {
        if (i === index) {
            option.classList.add('selected');
        } else {
            option.classList.remove('selected');
        }
    });
}

function nextQuestion() {
    if (currentQuestion < quizData.length - 1) {
        currentQuestion++;
        loadQuestion();
    } else {
        calculateScore();
        showResults();
    }
}

function prevQuestion() {
    if (currentQuestion > 0) {
        currentQuestion--;
        loadQuestion();
    }
}

function calculateScore() {
    score = 0;
    userAnswers.forEach((answer, index) => {
        if (answer === quizData[index].correct) {
            score++;
        }
    });
}

async function showResults() {
    quizContainer.classList.add('hidden');
    resultsContainer.classList.remove('hidden');

    // Invia le risposte al backend se possibile
    try {
        const payload = {
            answers: userAnswers,
            score: score,
            totalQuestions: quizData.length
        };

        const res = await fetch(`${API_BASE}/quiz/submit`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (res.ok) {
            const data = await res.json();
            // backend potrebbe rispondere con { success, message, data: { recommended_destination }}
            let rec = null;
            if (data.recommended_destination) rec = data.recommended_destination;
            if (data.data && data.data.recommended_destination) rec = data.data.recommended_destination;
            if (data.data && data.data.recommended_destination === null && data.data) rec = data.data.recommended_destination;
            if (rec) displayDestinationRecommendation(rec);
        }
    } catch (err) {
        console.warn('Impossibile inviare risultati al backend:', err);
    }

    const percentage = ((score / quizData.length) * 100).toFixed(1);
    scoreText.textContent = `Hai risposto correttamente a ${score} domande su ${quizData.length} (${percentage}%)`;

    resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function displayDestinationRecommendation(dest) {
    // remove existing recommendation if present
    const existing = document.querySelector('.destination-recommendation');
    if (existing) existing.remove();

    const recommendation = document.createElement('div');
    recommendation.className = 'destination-recommendation';
    recommendation.innerHTML = `
        <h3>🎯 Destinazione Consigliata</h3>
        <p><strong>${dest.name}</strong></p>
        <p>Categoria: ${dest.category || 'N/A'}</p>
        <p>Budget base: €${dest.base_budget || 'N/A'}</p>
        <p>${dest.description || ''}</p>
    `;

    // insert recommendation at top of results container
    resultsContainer.insertBefore(recommendation, scoreText.nextSibling);
}

function restartQuiz() {
    // Reset state
    currentQuestion = 0;
    userAnswers = [];
    score = 0;
    quizStarted = false;

    // Show hero section again
    startBtn.closest('.hero').style.display = 'block';
    quizContainer.classList.add('hidden');
    resultsContainer.classList.add('hidden');

    // Scroll to top
    document.body.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ===== UTILITY FUNCTIONS =====
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}

// Prevent form submission if needed
function handleFormSubmit(e) {
    if (e) {
        e.preventDefault();
    }
}

// Log quiz progress (for debugging)
function logProgress() {
    console.log('Current Question:', currentQuestion);
    console.log('User Answers:', userAnswers);
    console.log('Score:', score);
}
