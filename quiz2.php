<?php
session_start(); 


if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

$conn = new mysqli('localhost', 'root', '', 'parth'); 

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $quizType = $_GET['quiz_type'] ?? ''; 
    $quizLevel = 3; 
    
    if (empty($quizType)) {
        die("Quiz type not specified");
    }
    
    $checkColumnStmt = $conn->prepare("SHOW COLUMNS FROM quizresults LIKE 'quiz_level'");
    $checkColumnStmt->execute();
    $columnResult = $checkColumnStmt->get_result();
    
    if ($columnResult->num_rows == 0) {
        
        echo '<div class="quizstart">';
        echo '<h1 id="heading">DATABASE ERROR</h1>';
        echo '</div>';
        echo '<div class="que">';
        echo '<h2>The quiz_level column is missing from the database. Please contact the administrator.</h2>';
        echo '</div>';
        echo '<div class="qz1">';
        echo '<div class="qbtn">';
        echo '<a href="index.php"><button class="qbtnexit">BACK TO HOME</button></a>';
        echo '</div>';
        echo '</div>';
        exit();
    }
    
    $checkLevel2Stmt = $conn->prepare("SELECT highest_score FROM quizresults WHERE username = ? AND quiz_topic = ? AND quiz_level = 2");
    $checkLevel2Stmt->bind_param("ss", $username, $quizType);
    $checkLevel2Stmt->execute();
    $level2Result = $checkLevel2Stmt->get_result();
    
    $canAttemptLevel3 = false;
    if ($level2Result->num_rows > 0) {
        $level2Row = $level2Result->fetch_assoc();
        if ($level2Row['highest_score'] >= 8) {
            $canAttemptLevel3 = true;
        }
    }
    
    if (!$canAttemptLevel3) {
        echo '<div class="quizstart">';
        echo '<h1 id="heading">ACCESS DENIED</h1>';
        echo '</div>';
        echo '<div class="que">';
        echo '<h2>You need to score 8 or more in Level 2 of ' . htmlspecialchars($quizType) . ' quiz to attempt this level.</h2>';
        echo '</div>';
        echo '<div class="qz1">';
        echo '<div class="qbtn">';
        echo '<a href="index.php"><button class="qbtnexit">BACK TO HOME</button></a>';
        echo '</div>';
        echo '</div>';
        exit();
    }
    
    
    $stmt = $conn->prepare("SELECT * FROM quiz_questions WHERE quiz_type = ? AND quiz_level = ? ORDER BY RAND() LIMIT 20");
    $stmt->bind_param("si", $quizType, $quizLevel);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo '<div class="quizstart">';
        echo '<h1 id="heading">'.strtoupper($quizType).' QUIZ - LEVEL 3</h1>';
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
        
        
        echo '<div id="question-container">';
        echo '<div class="que">';
        echo '<h2 id="question-text"></h2>';
        echo '</div>';
        echo '<div class="qz1" id="qqz1">';
        
        echo '<div class="timer-container">';
        echo '<div id="timer-bar"></div>';
        echo '<div id="timer-text">10</div>'; 
        echo '</div>';
        echo '<div class="abtn" id="abtn">';
        
        echo '</div>';
        echo '<div class="qbtn">';
        echo '<a href="index.php"><button class="qbtnexit">QUIT</button></a>';
        echo '<button id="nextbtn">NEXT</button>';
        
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
        
        
        echo '<form id="results-form" method="post" action="save_results.php" style="display:none;">';
        echo '<input type="hidden" name="quiz_topic" value="' . htmlspecialchars($quizType) . '">';
        echo '<input type="hidden" name="quiz_level" value="' . $quizLevel . '">';
        echo '<input type="hidden" name="score" id="form-score" value="">';
        echo '<input type="hidden" name="attempted_questions" id="form-attempted" value="">';
        echo '<input type="hidden" name="unattempted_questions" id="form-unattempted" value="">';
        echo '<input type="hidden" name="wrong_answers" id="form-wrong" value="">';
        echo '<input type="hidden" name="total_questions" id="form-total" value="' . $totalQuestions . '">';
        echo '</form>';
        
        
        echo '<script>
            // Quiz questions from PHP
            const questions = ' . $questionsJson . ';
            let currentQuestionIndex = 0;
            let score = 0;
            let selectedOption = null;
            let userAnswers = new Array(questions.length).fill(null); // Track user answers
            let timer = null;
            let timeLeft = 10; // Changed to 10 seconds
            let username = "' . $username . '";
            
            // DOM elements
            const questionContainer = document.getElementById("question-container");
            const resultsContainer = document.getElementById("results-container");
            const questionText = document.getElementById("question-text");
            const answerContainer = document.getElementById("abtn");
            const nextButton = document.getElementById("nextbtn");
            const currentQuestionSpan = document.getElementById("current-question");
            const totalQuestionsSpan = document.getElementById("total-questions");
            const finalScoreElement = document.getElementById("final-score");
            const correctCountElement = document.getElementById("correct-count");
            const wrongCountElement = document.getElementById("wrong-count");
            const attemptedCountElement = document.getElementById("attempted-count");
            const unattemptedCountElement = document.getElementById("unattempted-count");
            const questionsReviewElement = document.getElementById("questions-review");
            const timerText = document.getElementById("timer-text");
            const timerBar = document.getElementById("timer-bar");
            const resultsForm = document.getElementById("results-form");
            const formScore = document.getElementById("form-score");
            const formAttempted = document.getElementById("form-attempted");
            const formUnattempted = document.getElementById("form-unattempted");
            const formWrong = document.getElementById("form-wrong");
            const formTotal = document.getElementById("form-total");
            
            // Initialize the quiz
            function initQuiz() {
                if (questions.length > 0) {
                    showQuestion(currentQuestionIndex);
                } else {
                    questionContainer.innerHTML = "<p>No questions available</p>";
                }
            }
            
            // Start the timer for current question
            function startTimer() {
                // Clear any existing timer
                if (timer) {
                    clearInterval(timer);
                }
                
                // Reset timer display
                timeLeft = 10; // Changed to 10 seconds
                timerText.textContent = timeLeft;
                timerBar.style.width = "100%";
                timerBar.style.backgroundColor = "#4CAF50"; // Reset color
                
                // Start the countdown
                timer = setInterval(function() {
                    timeLeft--;
                    timerText.textContent = timeLeft;
                    
                    // Update timer bar width
                    const percentage = (timeLeft / 10) * 100; // Changed for 10 seconds
                    timerBar.style.width = percentage + "%";
                    
                    // Change color as time decreases
                    if (timeLeft <= 3) { // Adjusted for 10-second timer
                        timerBar.style.backgroundColor = "#FF5252";
                    } else if (timeLeft <= 6) { // Adjusted for 10-second timer
                        timerBar.style.backgroundColor = "#FFD740";
                    }
                    
                    // Auto-move to next question when time runs out
                    if (timeLeft <= 0) {
                        clearInterval(timer);
                        nextQuestion();
                    }
                }, 1000);
            }
            
            // Display a question
            function showQuestion(index) {
                // Reset selected option if no previous answer for this question
                if (userAnswers[index] === null) {
                    selectedOption = null;
                } else {
                    // Restore previously selected answer
                    selectedOption = userAnswers[index];
                }
                
                // Update question number
                currentQuestionSpan.textContent = index + 1;
                
                // Get current question
                const question = questions[index];
                questionText.textContent = question.question;
                
                // Clear previous answers
                answerContainer.innerHTML = "";
                
                // Add answer buttons
                Object.entries(question.options).forEach(([key, text]) => {
                    const button = document.createElement("button");
                    button.className = "ansbtn";
                    button.textContent = text;
                    button.dataset.option = key;
                    button.onclick = function() { selectAnswer(this, question.correct_option); };
                    
                    // If this option was previously selected
                    if (selectedOption === key) {
                        button.classList.add("selected");
                        
                        // Check if correct
                        if (key === question.correct_option) {
                            button.classList.add("correct");
                        } else {
                            button.classList.add("incorrect");
                        }
                    }
                    
                    answerContainer.appendChild(button);
                    
                    // Add line break after each button
                    const br = document.createElement("br");
                    answerContainer.appendChild(br);
                });
                
                // Start the timer for this question
                startTimer();
            }
            
            // Handle answer selection
            function selectAnswer(button, correctOption) {
                // Remove selected class from all buttons
                const buttons = document.querySelectorAll(".ansbtn");
                buttons.forEach(btn => btn.classList.remove("selected", "correct", "incorrect"));
                
                // Add selected class to clicked button
                button.classList.add("selected");
                selectedOption = button.dataset.option;
                
                // Record the answer
                userAnswers[currentQuestionIndex] = selectedOption;
            }
            
            // Handle next button click
            function nextQuestion() {
                // Clear the timer
                if (timer) {
                    clearInterval(timer);
                }
                
                // Move to next question or show results
                currentQuestionIndex++;
                if (currentQuestionIndex < questions.length) {
                    showQuestion(currentQuestionIndex);
                } else {
                    showResults();
                }
            }
            
            // Calculate quiz statistics
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
            
            // Submit results to server directly
            function submitResults(stats) {
                // Fill in the form with quiz statistics
                formScore.value = stats.score;
                formAttempted.value = stats.attemptedCount;
                formUnattempted.value = stats.unattemptedCount;
                formWrong.value = stats.wrongCount;
                
                // Use AJAX to submit the form data without page reload
                const formData = new FormData(resultsForm);
                
                fetch("quiz1.php", {  // Directly submit to this page instead of save_results.php
                    method: "POST",
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        console.error("Error saving quiz results");
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                });
            }
            
            // Show quiz results
            function showResults() {
                // Stop the timer if it\'s still running
                if (timer) {
                    clearInterval(timer);
                }
                
                questionContainer.style.display = "none";
                resultsContainer.style.display = "block";
                
                // Calculate stats
                const stats = calculateStats();
                
                // Update results display
                finalScoreElement.textContent = `Your Score: ${stats.score} out of ${questions.length}`;
                correctCountElement.textContent = stats.correctCount;
                wrongCountElement.textContent = stats.wrongCount;
                attemptedCountElement.textContent = stats.attemptedCount;
                unattemptedCountElement.textContent = stats.unattemptedCount;
                
                // Generate question review
                questionsReviewElement.innerHTML = "<h3>Question Review</h3>";
                
                for (let i = 0; i < questions.length; i++) {
                    const question = questions[i];
                    const userAnswer = userAnswers[i];
                    
                    const reviewItem = document.createElement("div");
                    reviewItem.className = "review-item";
                    
                    // Determine status class
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
                    
                    // Question text
                    reviewItem.innerHTML = `
                        <p><strong>Question ${i+1}:</strong> ${question.question}</p>
                        <p class="status ${statusClass}">${statusText}</p>
                    `;
                    
                    // Show the correct answer and user\'s answer if attempted
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
                
                // Submit results to server
                submitResults(stats);
            }
            
            // Add event listeners
            nextButton.addEventListener("click", nextQuestion);
            
            // Start the quiz
            initQuiz();
        </script>';
        
        // CSS Styles
        echo '<style>
            /* General Styles */
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f5f5f5;
            }
            
            /* Quiz Header */
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
            
            /* Question Styles */
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
            
            /* Quiz Container */
            .qz1 {
                background-color: white;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 15px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            
            /* Timer Styles */
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
            
            /* Answer Buttons */
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
                border-radius: 6px;
                cursor: pointer;
                font-size: 16px;
                text-align: left;
                transition: all 0.3s ease;
            }
            
            .ansbtn:hover {
                background-color: #e0e0e0;
            }
            
            .ansbtn.selected {
                background-color: #4285f4;
                color: white;
                border-color: #2965c1;
            }
            
            .ansbtn.correct {
                background-color: #4caf50;
                color: white;
                border-color: #388e3c;
            }
            
            .ansbtn.incorrect {
                background-color: #f44336;
                color: white;
                border-color: #d32f2f;
            }
            
            /* Navigation Buttons */
            .qbtn {
                display: flex;
                justify-content: space-between;
                margin-bottom: 15px;
            }
            
            .qbtn button {
                padding: 10px 20px;
                font-size: 16px;
                border-radius: 6px;
                cursor: pointer;
                font-weight: bold;
                transition: all 0.3s ease;
            }
            
            #nextbtn, .restart-btn {
                background-color: #4285f4;
                color: white;
                border: none;
            }
            
            #nextbtn:hover, .restart-btn:hover {
                background-color: #2965c1;
            }
            
            .qbtnexit {
                background-color: #f44336;
                color: white;
                border: none;
            }
            
            .qbtnexit:hover {
                background-color: #d32f2f;
            }
            
            /* Question Counter */
            #qno {
                text-align: center;
                font-weight: bold;
            }
            
            /* Results Container */
            .quiz-results {
                background-color: white;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 15px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            
            .result-details {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                margin: 20px 0;
                padding: 15px;
                background-color: #f9f9f9;
                border-radius: 8px;
            }
            
            .result-details p {
                margin: 5px 0;
                font-size: 16px;
            }
            
            #final-score {
                font-size: 24px;
                color: #4285f4;
                text-align: center;
            }
            
            /* Question Review */
            #questions-review {
                margin-top: 20px;
            }
            
            #questions-review h3 {
                margin-bottom: 15px;
                padding-bottom: 5px;
                border-bottom: 2px solid #4285f4;
            }
            
            .review-item {
                margin-bottom: 15px;
                padding: 15px;
                border-radius: 8px;
                background-color: #f9f9f9;
                border-left: 5px solid #ccc;
            }
            
            .review-item p {
                margin: 5px 0;
            }
            
            .review-item.correct {
                border-left-color: #4caf50;
            }
            
            .review-item.incorrect {
                border-left-color: #f44336;
            }
            
            .review-item.unattempted {
                border-left-color: #ff9800;
            }
            
            .status {
                font-weight: bold;
            }
            
            .status.correct {
                color: #4caf50;
            }
            
            .status.incorrect {
                color: #f44336;
            }
            
            .status.unattempted {
                color: #ff9800;
            }
        </style>';
        
    } else {
        echo '<div class="quizstart">';
        echo '<h1 id="heading">NO QUESTIONS FOUND</h1>';
        echo '</div>';
        echo '<div class="que">';
        echo '<h2>No level 3 questions available for this quiz type.</h2>';
        echo '</div>';
        echo '<div class="qz1">';
        echo '<div class="qbtn">';
        echo '<a href="index.php"><button class="qbtnexit">BACK TO HOME</button></a>';
        echo '</div>';
        echo '</div>';
    }
    
    $stmt->close();
    
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (!isset($_POST['quiz_topic']) || !isset($_POST['score'])) {
        die("Missing required fields");
    }
    
    $quizTopic = $_POST['quiz_topic'];
    $quizLevel = $_POST['quiz_level'] ?? 3; 
    $score = $_POST['score'];
    $attemptedQuestions = $_POST['attempted_questions'];
    $unattemptedQuestions = $_POST['unattempted_questions'];
    $wrongAnswers = $_POST['wrong_answers'];
    $totalQuestions = $_POST['total_questions'] ?? 20; 
    
    
    $checkStmt = $conn->prepare("SELECT * FROM quizresults WHERE username = ? AND quiz_topic = ? AND quiz_level = ?");
    $checkStmt->bind_param("ssi", $username, $quizTopic, $quizLevel);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        
        $row = $checkResult->fetch_assoc();
        $attempts = $row['attempts'] + 1;
        
        
        if ($score > $row['highest_score']) {
            
            $totalScoreSum = ($row['average_score'] * $row['attempts'] / 100 * $totalQuestions) + $score;
            $newAverageScore = round(($totalScoreSum / ($row['attempts'] + 1) / $totalQuestions) * 100, 2);
            
            $updateStmt = $conn->prepare("UPDATE quizresults SET 
                attempts = ?, 
                highest_score = ?, 
                total_questions = ?,
                attempted_questions = ?, 
                unattempted_questions = ?, 
                wrong_answers = ?, 
                average_score = ? 
                WHERE username = ? AND quiz_topic = ? AND quiz_level = ?");
                
            $updateStmt->bind_param("iiiiidsssi", 
                $attempts, 
                $score,
                $totalQuestions,
                $attemptedQuestions, 
                $unattemptedQuestions, 
                $wrongAnswers, 
                $newAverageScore,
                $username, 
                $quizTopic, 
                $quizLevel);
            
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            
            $totalScoreSum = ($row['average_score'] * $row['attempts'] / 100 * $totalQuestions) + $score;
            $newAverageScore = round(($totalScoreSum / ($row['attempts'] + 1) / $totalQuestions) * 100, 2);
            
            $updateStmt = $conn->prepare("UPDATE quizresults SET 
                attempts = ?, 
                total_questions = ?,
                average_score = ? 
                WHERE username = ? AND quiz_topic = ? AND quiz_level = ?");
                
            $updateStmt->bind_param("iidssi", 
                $attempts,
                $totalQuestions,
                $newAverageScore,
                $username, 
                $quizTopic, 
                $quizLevel);
            
            $updateStmt->execute();
            $updateStmt->close();
        }
    } else {
        
        $insertStmt = $conn->prepare("INSERT INTO quizresults 
            (username, quiz_topic, quiz_level, attempts, highest_score, total_questions, attempted_questions, unattempted_questions, wrong_answers, average_score) 
            VALUES (?, ?, ?, 1, ?, ?, ?, ?, ?, ?)");
            $averageScore = round(($score / $totalQuestions) * 100, 2);
        
            $insertStmt->bind_param("ssiiiiiid", 
                $username,
                $quizTopic,
                $quizLevel,
                $score,
                $totalQuestions,
                $attemptedQuestions,
                $unattemptedQuestions,
                $wrongAnswers,
                $averageScore);
        
        $insertStmt->execute();
        $insertStmt->close();
    }
    
    $checkStmt->close();
    
    
    echo json_encode(["success" => true]);
    exit;
}

$conn->close();
?>