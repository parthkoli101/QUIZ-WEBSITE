<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

$conn = new mysqli('localhost', 'root', 'Parth@23102025', 'crackquiz');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $quizTopic = $_POST['quiz_topic'] ?? '';
    $quizLevel = $_POST['quiz_level'] ?? '';
    $score = (int)($_POST['score'] ?? 0);
    $totalQuestions = (int)($_POST['total_questions'] ?? 0);
    $attemptedQuestions = (int)($_POST['attempted_questions'] ?? 0);
    $unattemptedQuestions = (int)($_POST['unattempted_questions'] ?? 0);
    $wrongAnswers = (int)($_POST['wrong_answers'] ?? 0);

    $quizLevelForDb = ($quizLevel === "NULL") ? NULL : (int)$quizLevel;

    if ($quizLevelForDb === NULL) {
        $checkStmt = $conn->prepare("SELECT * FROM quizresults WHERE username = ? AND quiz_topic = ? AND quiz_level IS NULL");
        $checkStmt->bind_param("ss", $username, $quizTopic);
    } else {
        $checkStmt = $conn->prepare("SELECT * FROM quizresults WHERE username = ? AND quiz_topic = ? AND quiz_level = ?");
        $checkStmt->bind_param("ssi", $username, $quizTopic, $quizLevelForDb);
    }

    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $existingRecord = $checkResult->fetch_assoc();
        $newAttempts = $existingRecord['attempts'] + 1;
        $newHighestScore = max($existingRecord['highest_score'], $score);
        $newAverageScore = (($existingRecord['average_score'] * $existingRecord['attempts']) + $score) / $newAttempts;

        if ($quizLevelForDb === NULL) {
            $updateStmt = $conn->prepare("UPDATE quizresults SET attempts = ?, highest_score = ?, average_score = ? WHERE username = ? AND quiz_topic = ? AND quiz_level IS NULL");
            $updateStmt->bind_param("iidss", $newAttempts, $newHighestScore, $newAverageScore, $username, $quizTopic);
        } else {
            $updateStmt = $conn->prepare("UPDATE quizresults SET attempts = ?, highest_score = ?, average_score = ? WHERE username = ? AND quiz_topic = ? AND quiz_level = ?");
            $updateStmt->bind_param("iidssi", $newAttempts, $newHighestScore, $newAverageScore, $username, $quizTopic, $quizLevelForDb);
        }

        if ($updateStmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Results updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update results']);
        }
        $updateStmt->close();
    } else {
        $insertStmt = $conn->prepare("INSERT INTO quizresults (username, quiz_topic, quiz_level, attempts, highest_score, total_questions, attempted_questions, unattempted_questions, wrong_answers, average_score) VALUES (?, ?, ?, 1, ?, ?, ?, ?, ?, ?)");
        $insertStmt->bind_param("ssiiiiiid", $username, $quizTopic, $quizLevelForDb, $score, $totalQuestions, $attemptedQuestions, $unattemptedQuestions, $wrongAnswers, $score);

        if ($insertStmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Results saved successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save results']);
        }
        $insertStmt->close();
    }

    $checkStmt->close();
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $quizType = $_GET['quiz_type'] ?? '';
    $quizLevel = $_GET['quiz_level'] ?? 1;

    if (empty($quizType)) {
        die("Quiz type not specified");
    }

    if ($quizLevel === "NULL" || $quizLevel === null) {
        $quizLevel = NULL;
    } else {
        $quizLevel = (int)$quizLevel;
    }

    if ($quizLevel === 2 || $quizLevel === 3) {
        $requiredScore = ($quizLevel === 2) ? 4 : 8;
        $previousLevel = ($quizLevel === 2) ? 1 : 2;

        $checkPrevLevelStmt = $conn->prepare("SELECT highest_score FROM quizresults WHERE username = ? AND quiz_topic = ? AND quiz_level = ?");
        $checkPrevLevelStmt->bind_param("ssi", $username, $quizType, $previousLevel);
        $checkPrevLevelStmt->execute();
        $prevLevelResult = $checkPrevLevelStmt->get_result();

        $canAttemptHigherLevel = false;
        if ($prevLevelResult->num_rows > 0) {
            $prevLevelRow = $prevLevelResult->fetch_assoc();
            if ($prevLevelRow['highest_score'] >= $requiredScore) {
                $canAttemptHigherLevel = true;
            }
        }

        $checkPrevLevelStmt->close();

        if (!$canAttemptHigherLevel) {
            echo '<!DOCTYPE html>';
            echo '<html><head><title>Access Denied</title>';
            echo '<style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background-color: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { color: #dc3545; margin-bottom: 20px; }
                h2 { color: #333; margin-bottom: 30px; }
                .btn { background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; }
                .btn:hover { background-color: #0056b3; }
            </style></head><body>';
            echo '<div class="container">';
            echo '<h1>ACCESS DENIED</h1>';
            echo '<h2>You need to score ' . $requiredScore . ' or more in Level ' . $previousLevel . ' of ' . htmlspecialchars($quizType) . ' quiz to attempt this level.</h2>';
            echo '<a href="index.php" class="btn">BACK TO HOME</a>';
            echo '</div></body></html>';
            exit();
        }
    }

    $maxQuestions = 10;
    if ($quizLevel === 2) {
        $maxQuestions = 15;
    } elseif ($quizLevel === 3) {
        $maxQuestions = 20;
    } elseif ($quizLevel === NULL) {
        $maxQuestions = 20;
    }

    if ($quizLevel === NULL) {
        $stmt = $conn->prepare("SELECT * FROM quiz_questions WHERE quiz_type = ? AND quiz_level IS NULL ORDER BY RAND() LIMIT ?");
        $stmt->bind_param("si", $quizType, $maxQuestions);
    } else {
        $stmt = $conn->prepare("SELECT * FROM quiz_questions WHERE quiz_type = ? AND quiz_level = ? ORDER BY RAND() LIMIT ?");
        $stmt->bind_param("sii", $quizType, $quizLevel, $maxQuestions);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<!DOCTYPE html>';
        echo '<html lang="en">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $levelDisplay = ($quizLevel === NULL) ? "PRACTICE" : "LEVEL $quizLevel";
        echo '<title>'.strtoupper($quizType).' QUIZ - '.$levelDisplay.'</title>';
        echo '</head>';
        echo '<body>';

        echo '<div class="quizstart">';
        echo '<h1 id="heading">'.strtoupper($quizType).' QUIZ - '.$levelDisplay.'</h1>';
        echo '</div>';

        $totalQuestions = $result->num_rows;
        $questions = [];

        while ($row = $result->fetch_assoc()) {
            $options = [
                'A' => $row['option_a'],
                'B' => $row['option_b'],
                'C' => $row['option_c'],
                'D' => $row['option_d']
            ];

            $correctOption = strtoupper($row['correct_option']);

            if (!isset($options[$correctOption])) {
                $foundKey = null;
                foreach ($options as $key => $value) {
                    if (strtoupper($value) === strtoupper($correctOption)) {
                        $foundKey = $key;
                        break;
                    }
                }
                $correctOption = $foundKey ?? 'A';
            }

            $correctAnswer = $options[$correctOption];

            $optionKeys = array_keys($options);
            shuffle($optionKeys);

            $shuffledOptions = [];
            $newCorrectOption = null;

            foreach ($optionKeys as $key) {
                $shuffledOptions[$key] = $options[$key];

                if ($options[$key] === $correctAnswer) {
                    $newCorrectOption = $key;
                }
            }

            if ($newCorrectOption === null) {
                $newCorrectOption = $optionKeys[0];
            }

            $questions[] = [
                'id' => $row['id'],
                'question' => $row['question'],
                'options' => $shuffledOptions,
                'correct_option' => $newCorrectOption,
                'correct_answer' => $correctAnswer
            ];
        }

        $questionsJson = json_encode($questions);

        $timerDuration = 0;
        if ($quizLevel == 2) {
            $timerDuration = 15;
        } elseif ($quizLevel == 3) {
            $timerDuration = 10;
        }

        echo '<div id="question-container">';
        echo '<div class="que">';
        echo '<h2 id="question-text"></h2>';
        echo '</div>';
        echo '<div class="qz1" id="qqz1">';

        if ($timerDuration > 0) {
            echo '<div class="timer-container">';
            echo '<div id="timer-bar"></div>';
            echo '<div id="timer-text">' . $timerDuration . '</div>';
            echo '</div>';
        }

        echo '<div class="abtn" id="abtn">';
        echo '</div>';
        echo '<div class="qbtn">';

        if ($quizLevel == 1 || $quizLevel === NULL) {
            echo '<a href="index.php"><button class="qbtnexit">QUIT</button></a>';
            echo '<button id="prevbtn">PREVIOUS</button>';
            echo '<button id="nextbtn">NEXT</button>';
        } else {
            echo '<a href="index.php"><button class="qbtnexit">QUIT</button></a>';
            echo '<button id="nextbtn">NEXT</button>';
        }

        echo '</div>';
        echo '<div id="qno">';
        echo '<p><span id="current-question">1</span> of <span id="total-questions">' . $totalQuestions . '</span> questions</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        echo '<div id="results-container" style="display:none;">';
        echo '<div class="quizstart">';
        echo '<h1 id="heading">QUIZ COMPLETE</h1>';
        echo '</div>';
        echo '<div class="quiz-results">';
        echo '<h2 id="final-score"></h2>';
        echo '<div class="result-details">';
        echo '<p>Correct Answers: <span id="correct-count">0</span></p>';
        echo '<p>Wrong Answers: <span id="wrong-count">0</span></p>';
        echo '<p>Attempted Questions: <span id="attempted-count">0</span></p>';
        echo '<p>Unattempted Questions: <span id="unattempted-count">0</span></p>';
        echo '</div>';
        echo '<div id="questions-review"></div>';
        echo '</div>';
        echo '<div class="qz1">';
        echo '<div class="qbtn">';
        echo '<a href="index.php"><button class="qbtnexit">BACK TO HOME</button></a>';
        echo '<button class="restart-btn" onclick="location.reload()">RESTART QUIZ</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        echo '<script>
            const questions = ' . $questionsJson . ';
            let currentQuestionIndex = 0;
            let score = 0;
            let selectedOption = null;
            let userAnswers = new Array(questions.length).fill(null);
            let timer = null;
            let timeLeft = ' . $timerDuration . ';
            let timerEnabled = ' . ($timerDuration > 0 ? 'true' : 'false') . ';
            let username = "' . $username . '";
            let showPrevButton = ' . (($quizLevel == 1 || $quizLevel === NULL) ? 'true' : 'false') . ';
            let quizType = "' . htmlspecialchars($quizType) . '";
            let quizLevel = "' . ($quizLevel === NULL ? "NULL" : $quizLevel) . '";

            const questionContainer = document.getElementById("question-container");
            const resultsContainer = document.getElementById("results-container");
            const questionText = document.getElementById("question-text");
            const answerContainer = document.getElementById("abtn");
            const nextButton = document.getElementById("nextbtn");
            const prevButton = showPrevButton ? document.getElementById("prevbtn") : null;
            const currentQuestionSpan = document.getElementById("current-question");
            const totalQuestionsSpan = document.getElementById("total-questions");
            const finalScoreElement = document.getElementById("final-score");
            const correctCountElement = document.getElementById("correct-count");
            const wrongCountElement = document.getElementById("wrong-count");
            const attemptedCountElement = document.getElementById("attempted-count");
            const unattemptedCountElement = document.getElementById("unattempted-count");
            const questionsReviewElement = document.getElementById("questions-review");
            const timerText = timerEnabled ? document.getElementById("timer-text") : null;
            const timerBar = timerEnabled ? document.getElementById("timer-bar") : null;

            function initQuiz() {
                if (questions.length > 0) {
                    showQuestion(currentQuestionIndex);

                    if (showPrevButton) {
                        prevButton.disabled = true;
                        prevButton.style.opacity = "0.5";
                        prevButton.style.cursor = "not-allowed";
                    }
                } else {
                    questionContainer.innerHTML = "<p>No questions available</p>";
                }
            }

            function startTimer() {
                if (!timerEnabled) return;

                if (timer) {
                    clearInterval(timer);
                }

                timeLeft = ' . $timerDuration . ';
                timerText.textContent = timeLeft;
                timerBar.style.width = "100%";
                timerBar.style.backgroundColor = "#4CAF50";

                timer = setInterval(function() {
                    timeLeft--;
                    timerText.textContent = timeLeft;

                    const percentage = (timeLeft / ' . $timerDuration . ') * 100;
                    timerBar.style.width = percentage + "%";

                    if (timeLeft <= Math.floor(' . $timerDuration . ' / 3)) {
                        timerBar.style.backgroundColor = "#FF5252";
                    } else if (timeLeft <= Math.floor(' . $timerDuration . ' * 2/3)) {
                        timerBar.style.backgroundColor = "#FFD740";
                    }

                    if (timeLeft <= 0) {
                        clearInterval(timer);
                        nextQuestion();
                    }
                }, 1000);
            }

            function showQuestion(index) {
                if (userAnswers[index] === null) {
                    selectedOption = null;
                } else {
                    selectedOption = userAnswers[index];
                }

                currentQuestionSpan.textContent = index + 1;

                if (showPrevButton) {
                    if (index === 0) {
                        prevButton.disabled = true;
                        prevButton.style.opacity = "0.5";
                        prevButton.style.cursor = "not-allowed";
                    } else {
                        prevButton.disabled = false;
                        prevButton.style.opacity = "1";
                        prevButton.style.cursor = "pointer";
                    }
                }

                const question = questions[index];
                questionText.textContent = question.question;

                answerContainer.innerHTML = "";

                Object.entries(question.options).forEach(([key, text]) => {
                    const button = document.createElement("button");
                    button.className = "ansbtn";
                    button.textContent = text;
                    button.dataset.option = key;
                    button.onclick = function() { selectAnswer(this, question.correct_option); };

                    if (selectedOption === key) {
                        button.classList.add("selected");
                    }

                    answerContainer.appendChild(button);
                    const br = document.createElement("br");
                    answerContainer.appendChild(br);
                });

                if (timerEnabled) {
                    startTimer();
                }
            }

            function selectAnswer(button, correctOption) {
                const buttons = document.querySelectorAll(".ansbtn");
                buttons.forEach(btn => {
                    btn.classList.remove("selected");
                });

                button.classList.add("selected");
                selectedOption = button.dataset.option;
                userAnswers[currentQuestionIndex] = selectedOption;
            }

            function nextQuestion() {
                if (timer) {
                    clearInterval(timer);
                }

                currentQuestionIndex++;
                if (currentQuestionIndex < questions.length) {
                    showQuestion(currentQuestionIndex);
                } else {
                    showResults();
                }
            }

            function prevQuestion() {
                if (timer) {
                    clearInterval(timer);
                }

                if (currentQuestionIndex > 0) {
                    currentQuestionIndex--;
                    showQuestion(currentQuestionIndex);
                }
            }

            function calculateStats() {
                let correctCount = 0;
                let wrongCount = 0;
                let attemptedCount = 0;
                let unattemptedCount = 0;

                for (let i = 0; i < questions.length; i++) {
                    if (userAnswers[i] === null) {
                        unattemptedCount++;
                    } else {
                        attemptedCount++;
                        if (userAnswers[i] === questions[i].correct_option) {
                            correctCount++;
                        } else {
                            wrongCount++;
                        }
                    }
                }

                return {
                    correctCount,
                    wrongCount,
                    attemptedCount,
                    unattemptedCount,
                    score: correctCount
                };
            }

            function submitResults(stats) {
                const formData = new FormData();
                formData.append("quiz_topic", quizType);
                formData.append("quiz_level", quizLevel);
                formData.append("score", stats.score);
                formData.append("total_questions", questions.length);
                formData.append("attempted_questions", stats.attemptedCount);
                formData.append("unattempted_questions", stats.unattemptedCount);
                formData.append("wrong_answers", stats.wrongCount);

                fetch("quiz.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        console.log("Quiz results saved successfully");
                    } else {
                        console.error("Error saving quiz results:", data.message);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                });
            }

            function showResults() {
                if (timer) {
                    clearInterval(timer);
                }

                questionContainer.style.display = "none";
                resultsContainer.style.display = "block";

                const stats = calculateStats();

                finalScoreElement.textContent = `Your Score: ${stats.score} out of ${questions.length}`;
                correctCountElement.textContent = stats.correctCount;
                wrongCountElement.textContent = stats.wrongCount;
                attemptedCountElement.textContent = stats.attemptedCount;
                unattemptedCountElement.textContent = stats.unattemptedCount;

                questionsReviewElement.innerHTML = "<h3>Question Review</h3>";

                for (let i = 0; i < questions.length; i++) {
                    const question = questions[i];
                    const userAnswer = userAnswers[i];

                    const reviewItem = document.createElement("div");
                    reviewItem.className = "review-item";

                    let statusClass = "";
                    let statusText = "";

                    if (userAnswer === null) {
                        statusClass = "unattempted";
                        statusText = "Unattempted";
                    } else if (userAnswer === question.correct_option) {
                        statusClass = "correct";
                        statusText = "Correct";
                    } else {
                        statusClass = "incorrect";
                        statusText = "Incorrect";
                    }

                    reviewItem.classList.add(statusClass);

                    reviewItem.innerHTML = `
                        <p><strong>Question ${i+1}:</strong> ${question.question}</p>
                        <p class="status ${statusClass}">${statusText}</p>
                    `;

                    if (userAnswer !== null) {
                        const userSelectedText = question.options[userAnswer];
                        reviewItem.innerHTML += `
                            <p>Your Answer: ${userSelectedText}</p>
                            <p>Correct Answer: ${question.correct_answer}</p>
                        `;
                    } else {
                        reviewItem.innerHTML += `
                            <p>Correct Answer: ${question.correct_answer}</p>
                        `;
                    }

                    questionsReviewElement.appendChild(reviewItem);
                }

                submitResults(stats);
            }

            nextButton.addEventListener("click", nextQuestion);
            if (showPrevButton) {
                prevButton.addEventListener("click", prevQuestion);
            }

            initQuiz();
        </script>';

        echo '<style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f5f5f5;
            }
            .quizstart {
                background-color: #4a4a4a;
                color: white;
                padding: 15px;
                border-radius: 8px 8px 0 0;
                margin-bottom: 15px;
                text-align: center;
            }
            .quizstart h1 {
                margin: 0;
                font-size: 24px;
            }
            .que {
                background-color: white;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 15px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            #question-text {
                font-size: 18px;
                margin: 0;
            }
            .qz1 {
                background-color: white;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 15px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            .timer-container {
                margin-bottom: 15px;
                text-align: center;
                position: relative;
                height: 30px;
                background-color: #f0f0f0;
                border-radius: 15px;
                overflow: hidden;
            }
            #timer-bar {
                position: absolute;
                top: 0;
                left: 0;
                height: 100%;
                width: 100%;
                background-color: #4CAF50;
                transition: width 1s linear, background-color 0.5s;
                z-index: 1;
            }
            #timer-text {
                position: relative;
                z-index: 2;
                font-weight: bold;
                font-size: 18px;
                line-height: 30px;
                color: #000;
            }
            .abtn {
                margin-bottom: 20px;
            }
            .ansbtn {
                display: inline-block;
                width: 100%;
                padding: 12px 15px;
                margin-bottom: 10px;
                background-color: #f0f0f0;
                border: 1px solid #ddd;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                text-align: left;
                transition: all 0.3s;
            }
            .ansbtn:hover {
                background-color: #e0e0e0;
                border-color: #ccc;
            }
            .ansbtn.selected {
                background-color: #007bff;
                color: white;
                border-color: #0056b3;
            }
            .qbtn {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
            }
            .qbtnexit, #nextbtn, #prevbtn, .restart-btn {
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                font-weight: bold;
                transition: all 0.3s;
            }
            .qbtnexit {
                background-color: #dc3545;
                color: white;
            }
            .qbtnexit:hover {
                background-color: #c82333;
            }
            #nextbtn, .restart-btn {
                background-color: #28a745;
                color: white;
            }
            #nextbtn:hover, .restart-btn:hover {
                background-color: #218838;
            }
            #prevbtn {
                background-color: #6c757d;
                color: white;
            }
            #prevbtn:hover {
                background-color: #5a6268;
            }
            #prevbtn:disabled {
                background-color: #6c757d;
                opacity: 0.5;
                cursor: not-allowed;
            }
            #qno {
                text-align: center;
                font-size: 14px;
                color: #666;
            }
            .quiz-results {
                background-color: white;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 15px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            #final-score {
                text-align: center;
                color: #28a745;
                margin-bottom: 20px;
                font-size: 24px;
            }
            .result-details {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
                margin-bottom: 20px;
                padding: 15px;
                background-color: #f8f9fa;
                border-radius: 5px;
            }
            .result-details p {
                margin: 5px 0;
                font-weight: bold;
            }
            #questions-review h3 {
                color: #333;
                border-bottom: 2px solid #007bff;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }
            .review-item {
                margin-bottom: 15px;
                padding: 15px;
                border-radius: 5px;
                border-left: 4px solid #ddd;
            }
            .review-item.correct {
                background-color: #d4edda;
                border-left-color: #28a745;
            }
            .review-item.incorrect {
                background-color: #f8d7da;
                border-left-color: #dc3545;
            }
            .review-item.unattempted {
                background-color: #fff3cd;
                border-left-color: #ffc107;
            }
            .review-item p {
                margin: 8px 0;
            }
            .review-item .status {
                font-weight: bold;
                text-transform: uppercase;
                font-size: 12px;
            }
            .review-item .status.correct {
                color: #28a745;
            }
            .review-item .status.incorrect {
                color: #dc3545;
            }
            .review-item .status.unattempted {
                color: #ffc107;
            }
            @media (max-width: 600px) {
                body {
                    padding: 10px;
                }
                .qbtn {
                    flex-direction: column;
                    gap: 10px;
                }
                .qbtnexit, #nextbtn, #prevbtn, .restart-btn {
                    width: 100%;
                }
                .result-details {
                    grid-template-columns: 1fr;
                }
                .quizstart h1 {
                    font-size: 20px;
                }
                #question-text {
                    font-size: 16px;
                }
            }
        </style>';

        echo '</body>';
        echo '</html>';
    } else {
        echo '<!DOCTYPE html>';
        echo '<html><head><title>No Questions</title>';
        echo '<style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background-color: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #dc3545; margin-bottom: 20px; }
            .btn { background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; }
            .btn:hover { background-color: #0056b3; }
        </style></head><body>';
        echo '<div class="container">';
        echo '<h1>NO QUESTIONS AVAILABLE</h1>';
        echo '<p>There are no questions available for this quiz type and level.</p>';
        echo '<a href="index.php" class="btn">BACK TO HOME</a>';
        echo '</div></body></html>';
    }

    $stmt->close();
}

$conn->close();
?>
