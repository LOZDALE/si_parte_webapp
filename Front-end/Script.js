const API_BASE = (window.API_BASE) ? window.API_BASE : '/Public/api.php';

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

let currentQuestion = 0;
let userAnswers = [];
let score = 0;
let quizStarted = false;

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
        showError('Errore durante il caricamento iniziale. Uso dati locali.');
        quizData = getDefaultQuizData();
    }
}

async function loadQuizData() {
    try {
        const res = await fetch(`${API_BASE}/quiz/questions`);
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

function startQuiz() {
    quizStarted = true;
    currentQuestion = 0;
    userAnswers = new Array(quizData.length).fill(null);
    score = 0;

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
        answerDiv.textContent = answer;

        answerDiv.addEventListener('click', () => selectAnswer(index));
        answersContainer.appendChild(answerDiv);
    });

    prevBtn.disabled = currentQuestion === 0;
    nextBtn.textContent = currentQuestion === quizData.length - 1 ? 'Finish' : 'Next';

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

    resultsContainer.insertBefore(recommendation, scoreText.nextSibling);
}

function restartQuiz() {
    currentQuestion = 0;
    userAnswers = [];
    score = 0;
    quizStarted = false;

    document.getElementById('home').style.display = 'block';
    quizContainer.classList.add('hidden');
    resultsContainer.classList.add('hidden');

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
