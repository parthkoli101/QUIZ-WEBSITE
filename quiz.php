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
    $quizLevel = $_GET['quiz_level'] ?? 2; 
    
    if (empty($quizType)) {
        die("Quiz type not specified");
    }
    
    if ($quizLevel == 2 || $quizLevel == 3) {
        
        $checkPrevLevelStmt = null;
        $requiredScore = 0;
        $previousLevel = 0;
        
        if ($quizLevel == 2) {
            $checkPrevLevelStmt = $conn->prepare("SELECT highest_score FROM quizresults WHERE username = ? AND quiz_topic = ? AND quiz_level = 1");
            $previousLevel = 1;
            $requiredScore = 4;
        } else if ($quizLevel == 3) {
            $checkPrevLevelStmt = $conn->prepare("SELECT highest_score FROM quizresults WHERE username = ? AND quiz_topic = ? AND quiz_level = 2");
            $previousLevel = 2;
            $requiredScore = 8;
        }
        
        $checkPrevLevelStmt->bind_param("ss", $username, $quizType);
        $checkPrevLevelStmt->execute();
        $prevLevelResult = $checkPrevLevelStmt->get_result();
        
        $canAttemptHigherLevel = false;
        if ($prevLevelResult->num_rows > 0) {
            $prevLevelRow = $prevLevelResult->fetch_assoc();
            if ($prevLevelRow['highest_score'] >= $requiredScore) {
                $canAttemptHigherLevel = true;
            }
        }
        
        if (!$canAttemptHigherLevel) {
            echo '<div class="quizstart">';
            echo '<h1 id="heading">ACCESS DENIED</h1>';
            echo '</div>';
            echo '<div class="que">';
            echo '<h2>You need to score ' . $requiredScore . ' or more in Level ' . $previousLevel . ' of ' . htmlspecialchars($quizType) . ' quiz to attempt this level.</h2>';
            echo '</div>';
            echo '<div class="qz1">';
            echo '<div class="qbtn">';
            echo '<a href="index.php"><button class="qbtnexit">BACK TO HOME</button></a>';
            echo '</div>';
            echo '</div>';
            exit();
        }
    }
    
    $maxQuestions = 15; 
    if ($quizLevel == 1) {
        $maxQuestions = 10;
    } elseif ($quizLevel == 2) {
        $maxQuestions = 15;
    } elseif ($quizLevel == 3) {
        $maxQuestions = 20;
    } elseif ($quizLevel === NULL || $quizLevel === "NULL") {
        $maxQuestions = 20; 
        $quizLevel = NULL;
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
        echo '<div class="quizstart">';
        $levelDisplay = ($quizLevel === NULL) ? "PRACTICE" : "LEVEL $quizLevel";
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
        
        echo '<form id="results-form" method="post" action="save_results.php" style="display:none;">';
        echo '<input type="hidden" name="quiz_topic" value="' . htmlspecialchars($quizType) . '">';
        echo '<input type="hidden" name="quiz_level" value="' . ($quizLevel === NULL ? "NULL" : $quizLevel) . '">';
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
            let timeLeft = ' . $timerDuration . ';
            let timerEnabled = ' . ($timerDuration > 0 ? 'true' : 'false') . ';
            let username = "' . $username . '";
            let showPrevButton = ' . (($quizLevel == 1 || $quizLevel === NULL) ? 'true' : 'false') . ';
            
            // DOM elements
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
            const resultsForm = document.getElementById("results-form");
            const formScore = document.getElementById("form-score");
            const formAttempted = document.getElementById("form-attempted");
            const formUnattempted = document.getElementById("form-unattempted");
            const formWrong = document.getElementById("form-wrong");
            const formTotal = document.getElementById("form-total");
            
            // Timer elements might not exist if timer is disabled
            const timerText = timerEnabled ? document.getElementById("timer-text") : null;
            const timerBar = timerEnabled ? document.getElementById("timer-bar") : null;
            
            // Initialize the quiz
            function initQuiz() {
                if (questions.length > 0) {
                    showQuestion(currentQuestionIndex);
                    
                    // Disable previous button on first question
                    if (showPrevButton) {
                        prevButton.disabled = true;
                        prevButton.style.opacity = "0.5";
                        prevButton.style.cursor = "not-allowed";
                    }
                } else {
                    questionContainer.innerHTML = "<p>No questions available</p>";
                }
            }
            
            // Start the timer for current question (if timer is enabled)
            function startTimer() {
                // Skip if timer is not enabled for this level
                if (!timerEnabled) return;
                
                // Clear any existing timer
                if (timer) {
                    clearInterval(timer);
                }
                
                // Reset timer display
                timeLeft = ' . $timerDuration . ';
                timerText.textContent = timeLeft;
                timerBar.style.width = "100%";
                timerBar.style.backgroundColor = "#4CAF50"; // Reset color
                
                // Start the countdown
                timer = setInterval(function() {
                    timeLeft--;
                    timerText.textContent = timeLeft;
                    
                    // Update timer bar width
                    const percentage = (timeLeft / ' . $timerDuration . ') * 100;
                    timerBar.style.width = percentage + "%";
                    
                    // Change color as time decreases
                    if (timeLeft <= Math.floor(' . $timerDuration . ' / 3)) {
                        timerBar.style.backgroundColor = "#FF5252";
                    } else if (timeLeft <= Math.floor(' . $timerDuration . ' * 2/3)) {
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
                
                // Update previous button state
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
                        
                        // For level 1, only show blue selection without correct/incorrect indication
                        if (!' . ($quizLevel == 1 ? 'true' : 'false') . ' && key === question.correct_option) {
                            button.classList.add("correct");
                        } else if (!' . ($quizLevel == 1 ? 'true' : 'false') . ' && key !== question.correct_option) {
                            button.classList.add("incorrect");
                        }
                    }
                    
                    answerContainer.appendChild(button);
                    
                    // Add line break after each button
                    const br = document.createElement("br");
                    answerContainer.appendChild(br);
                });
                
                // Start the timer for this question (if enabled)
                if (timerEnabled) {
                    startTimer();
                }
            }
            
            // Handle answer selection
            function selectAnswer(button, correctOption) {
                // Remove selected class from all buttons
                const buttons = document.querySelectorAll(".ansbtn");
                buttons.forEach(btn => {
                    btn.classList.remove("selected", "correct", "incorrect");
                });
                
                // Add selected class to clicked button
                button.classList.add("selected");
                selectedOption = button.dataset.option;
                
                // Only show correct/incorrect colors for non-level 1 quizzes
                if (!' . ($quizLevel == 1 ? 'true' : 'false') . ') {
                    if (selectedOption === correctOption) {
                        button.classList.add("correct");
                    } else {
                        button.classList.add("incorrect");
                    }
                }
                
                // Record the answer
                userAnswers[currentQuestionIndex] = selectedOption;
            }
            
            // Handle next button click
            function nextQuestion() {
                // Clear the timer if it exists
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
            
            // Handle previous button click
            function prevQuestion() {
                // Clear the timer if it exists
                if (timer) {
                    clearInterval(timer);
                }
                
                // Move to previous question if not already at first question
                if (currentQuestionIndex > 0) {
                    currentQuestionIndex--;
                    showQuestion(currentQuestionIndex);
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
                
                fetch("quiz1.php", {  // Submit to this page
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
            if (showPrevButton) {
                prevButton.addEventListener("click", prevQuestion);
            }
            
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
                background-color: #4285f4;
                color: white;
                border-color: #2965c1;
            }
            
            .ansbtn.incorrect {
                background-color: #4285f4;
                color: white;
                border-color: #2965c1;
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
            
            #prevbtn {
                background-color: #9e9e9e;
                color: white;
                border: none;
            }
            
            #prevbtn:hover {
                background-color: #757575;
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
        $levelDisplay = ($quizLevel === NULL) ? "practice" : "level $quizLevel";
        echo '<div class="que">';
        echo '<h2>No questions available for ' . htmlspecialchars($quizType) . ' ' . $levelDisplay . ' quiz.</h2>';
        echo '</div>';
        echo '<div class="qz1">';
        echo '<div class="qbtn">';
        echo '<a href="index.php"><button class="qbtnexit">BACK TO HOME</button></a>';
        echo '</div>';
        echo '</div>';
    }
} else {
    echo '<div class="quizstart">';
    echo '<h1 id="heading">INVALID REQUEST</h1>';
    echo '</div>';
    echo '<div class="que">';
    echo '<h2>Please select a quiz from the main page.</h2>';
    echo '</div>';
    echo '<div class="qz1">';
    echo '<div class="qbtn">';
    echo '<a href="index.php"><button class="qbtnexit">BACK TO HOME</button></a>';
    echo '</div>';
    echo '</div>';
}

$conn->close();
?>