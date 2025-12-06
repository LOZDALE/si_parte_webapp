<?php
require __DIR__ . '/../vendor/autoload.php';

use SiParte\Quiz\Controllers\QuizController;

$quiz = new QuizController();
echo $quiz->demoQuiz();
