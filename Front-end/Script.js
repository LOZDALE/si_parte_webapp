const API_BASE = 'Public/api.php'; // Rimosso lo slash iniziale per sicurezza

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

// Funzione prevQuestion definita PRIMA dell'uso
const prevQuestion = () => {
    if (currentQuestion > 0) {
        currentQuestion--;
        loadQuestion();
    }
};

async function loadInitialData() {
    try {
        const res = await fetch(`${API_BASE}?route=quiz/questions`);
        const text = await res.text();
        try {
            quizData = JSON.parse(text);
            console.log("Dati caricati:", quizData);
        } catch (e) {
            console.error("Il server ha risposto con HTML invece di JSON. Controlla il percorso Public/api.php");
        }
    } catch (err) {
        console.error("Errore fetch:", err);
    }
}

async function handleFinalSubmit() {
    try {
        const res = await fetch(`${API_BASE}?route=quiz/submit`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ answers: userAnswers, paese_id: selectedPaese?.id })
        });
        const data = await res.json();
        
        // FIX: Definiamo la variabile locale per evitare ReferenceError
        const recDest = data.recommended_destination || { name: "Destinazione", description: "Dettagli non disponibili" };
        
        elements.quizContainer.classList.add('hidden');
        elements.resultsContainer.classList.remove('hidden');
        document.getElementById('destinationName').textContent = recDest.name;
        document.getElementById('destinationDescription').textContent = recDest.description;
    } catch (err) {
        console.error("Errore Submit:", err);
    }
}

// ... (tutte le altre funzioni: startQuiz, loadQuestion, nextQuestion, handleSelectPaese devono restare come prima)

document.addEventListener('DOMContentLoaded', () => {
    if (elements.startBtn) elements.startBtn.addEventListener('click', startQuiz);
    if (elements.nextBtn) elements.nextBtn.addEventListener('click', nextQuestion);
    if (elements.prevBtn) elements.prevBtn.addEventListener('click', prevQuestion);
    loadInitialData();
});