<?php
// inclusione manuale dei file PHP
require __DIR__ . '/../src/Database/Connection.php';
require __DIR__ . '/../src/Models/User.php';
require __DIR__ . '/../src/Controllers/QuizController.php';

// usa i namespace
use SiParte\Quiz\Controllers\QuizController;

// demo quiz
$quiz = new QuizController();
echo $quiz->demoQuiz();
