const API_BASE = (window.API_BASE) ? window.API_BASE : '/Public/api.php';

let quizData = [];
let destinations = [];

function getDefaultQuizData() {
    return [
        {
            id: 1,
            question: "Quale clima preferisci?",
            answers: [
                { text: "Caldo e soleggiato", scores: { beach: 3, mountain: 0, city: 1, ski: 0 } },
                { text: "Fresco e montuoso", scores: { beach: 0, mountain: 3, city: 0, ski: 1 } },
                { text: "Temperato", scores: { beach: 1, mountain: 1, city: 3, ski: 0 } },
                { text: "Freddo", scores: { beach: 0, mountain: 1, city: 0, ski: 3 } }
            ],
            type: "preference"
        },
        {
            id: 2,
            question: "Quale attività ti piace di più?",
            answers: [
                { text: "Spiaggia", scores: { beach: 3, mountain: 0, city: 0, ski: 0 } },
                { text: "Trekking", scores: { beach: 0, mountain: 3, city: 0, ski: 0 } },
                { text: "Cultura", scores: { beach: 0, mountain: 0, city: 3, ski: 0 } },
                { text: "Sci", scores: { beach: 0, mountain: 0, city: 0, ski: 3 } }
            ],
            type: "preference"
        },
        {
            id: 3,
            question: "Quale è il tuo budget (approssimativo)?",
            answers: [
                { text: "Fino a 500€", scores: { beach: 1, mountain: 1, city: 1, ski: 1 } },
                { text: "500-1000€", scores: { beach: 2, mountain: 2, city: 2, ski: 2 } },
                { text: "1000-2000€", scores: { beach: 3, mountain: 3, city: 3, ski: 3 } },
                { text: "Oltre 2000€", scores: { beach: 4, mountain: 4, city: 4, ski: 4 } }
            ],
            type: "preference"
        },
        {
            id: 4,
            question: "Preferisci una vacanza?",
            answers: [
                { text: "Breve (3-4 giorni)", scores: { beach: 1, mountain: 1, city: 3, ski: 1 } },
                { text: "Media (1 settimana)", scores: { beach: 2, mountain: 2, city: 2, ski: 2 } },
                { text: "Lunga (2 settimane)", scores: { beach: 3, mountain: 3, city: 1, ski: 3 } },
                { text: "Molto lunga (3+ settimane)", scores: { beach: 4, mountain: 4, city: 0, ski: 4 } }
            ],
            type: "preference"
        },
        {
            id: 5,
            question: "Con chi desideri partire?",
            answers: [
                { text: "Da solo", scores: { beach: 1, mountain: 2, city: 3, ski: 2 } },
                { text: "In coppia", scores: { beach: 3, mountain: 1, city: 2, ski: 1 } },
                { text: "Con la famiglia", scores: { beach: 2, mountain: 2, city: 1, ski: 1 } },
                { text: "Con amici", scores: { beach: 3, mountain: 3, city: 2, ski: 3 } }
            ],
            type: "preference"
        }
    ];
}

const getDefaultDestinations = () => [
    { name: "Spiaggia Paradiso", category: "beach", image: "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop" },
    { name: "Avventura in Montagna", category: "mountain", image: "https://images.unsplash.com/photo-1464822759844-d150f39b8d7d?w=800&h=600&fit=crop" },
    { name: "Esploratore Urbano", category: "city", image: "https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=800&h=600&fit=crop" },
    { name: "Stazione Sciistica", category: "ski", image: "https://images.unsplash.com/photo-1551632811-561732d1e306?w=800&h=600&fit=crop" }
];

let totalScores = {beach: 0, mountain: 0, city: 0, ski: 0};
let recommendedDestination = null;
let userAnswers = [];
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
        } else {
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
    totalScores = {beach: 0, mountain: 0, city: 0, ski: 0};
    userAnswers.forEach((answerIndex, questionIndex) => {
        if (answerIndex !== null) {
            const scores = quizData[questionIndex].answers[answerIndex].scores;
            totalScores.beach += scores.beach;
            totalScores.mountain += scores.mountain;
            totalScores.city += scores.city;
            totalScores.ski += scores.ski;
        }
    });

    // Trova la categoria con il punteggio più alto
    let maxScore = 0;
    let bestCategory = 'beach';
    for (const [category, score] of Object.entries(totalScores)) {
        if (score > maxScore) {
            maxScore = score;
            bestCategory = category;
        }
    }

    recommendedDestination = destinations.find(dest => dest.category === bestCategory);
}

async function showResults() {
    quizContainer.classList.add('hidden');
    resultsContainer.classList.remove('hidden');

    // Mostra la destinazione raccomandata
    if (recommendedDestination) {
        document.getElementById('destinationImage').src = recommendedDestination.image;
        document.getElementById('destinationName').textContent = recommendedDestination.name;
        document.getElementById('destinationDescription').textContent = `Basato sulle tue preferenze, la destinazione perfetta per te è ${recommendedDestination.name}!`;
    } else {
        document.getElementById('destinationName').textContent = 'Nessuna destinazione trovata';
        document.getElementById('destinationDescription').textContent = 'Si è verificato un errore.';
    }

    try {
        const payload = {
            answers: userAnswers,
            totalScores: totalScores,
            recommendedDestination: recommendedDestination
        };

        const res = await fetch(`${API_BASE}/quiz/submit`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (res.ok) {
            const data = await res.json();
            // Se il backend ha una raccomandazione diversa, usala
            if (data.recommended_destination) {
                displayDestinationRecommendation(data.recommended_destination);
            }
        }
    } catch (err) {
        console.warn('Impossibile inviare risultati al backend:', err);
    }

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
