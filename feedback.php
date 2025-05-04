<?php
session_start();


if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    
    header("Location: login.html");
    exit();
}


function connectDB() {
    $conn = new mysqli('localhost', 'root', '', 'parth');
    if ($conn->connect_error) {
        throw new Exception('Connection Failed: ' . $conn->connect_error);
    }
    return $conn;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION['username']; 
    $password = $_POST['password'] ?? '';
    $quizRating = $_POST['quizRating'] ?? '';
    $feedback = $_POST['feedback'] ?? '';
    $improvements = $_POST['improvements'] ?? '';
    
    
    if (empty($password) || empty($quizRating) || empty($feedback) || empty($improvements)) {
        $error_message = "All fields are required. Please fill in all the information.";
    } else {
        try {
            $conn = connectDB();
            
            
            $stmt = $conn->prepare("SELECT password, confirmpassword FROM quiz WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                
                
                $password_match = password_verify($password, $row['password']);
                
                if (!$password_match && $password === $row['confirmpassword']) {
                    $password_match = true;
                }
                
                if ($password_match) {
                    
                    $table_info = $conn->query("DESCRIBE feedback");
                    $columns = [];
                    while ($column = $table_info->fetch_assoc()) {
                        $columns[] = $column['Field'];
                    }
                    
                
                    $query_fields = [];
                    $query_placeholders = [];
                    $query_types = "";
                    $query_params = [];
                    
                
                    $query_fields[] = 'username';
                    $query_placeholders[] = '?';
                    $query_types .= 's';
                    $query_params[] = $username;
                    
                    if (in_array('quizRating', $columns)) {
                        $query_fields[] = 'quizRating';
                        $query_placeholders[] = '?';
                        $query_types .= 'i';
                        $query_params[] = $quizRating;
                    }
                    
                    if (in_array('feedback', $columns)) {
                        $query_fields[] = 'feedback';
                        $query_placeholders[] = '?';
                        $query_types .= 's';
                        $query_params[] = $feedback;
                    }
                    
                    if (in_array('improvements', $columns)) {
                        $query_fields[] = 'improvements';
                        $query_placeholders[] = '?';
                        $query_types .= 's';
                        $query_params[] = $improvements;
                    }
                    
                    
                    $sql = "INSERT INTO feedback (" . implode(', ', $query_fields) . ") VALUES (" . implode(', ', $query_placeholders) . ")";
                    $feedback_stmt = $conn->prepare($sql);
                    
                    
                    $bind_params = array($query_types);
                    foreach ($query_params as $key => $value) {
                        $bind_params[] = &$query_params[$key];
                    }
                    
                    call_user_func_array(array($feedback_stmt, 'bind_param'), $bind_params);
                    
                    if ($feedback_stmt->execute()) {
                        $success_message = "Thank you for your feedback!";
                    } else {
                        throw new Exception("Error saving feedback: " . $feedback_stmt->error);
                    }
                    
                    $feedback_stmt->close();
                } else {
                    $error_message = "Incorrect password. Please try again.";
                }
            } else {
                $error_message = "User not found. Please try again.";
            }
            
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .feedback-container {
            max-width: 800px;
            margin: 50px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #007bff;
            font-weight: bold;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        .rating-section {
            margin: 20px 0;
        }
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            font-size: 30px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
            padding: 0 5px;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #f8d32a;
        }
        .btn-submit {
            background-color: #007bff;
            border: none;
            padding: 10px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            background-color: #0056b3;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .alert {
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .user-info {
            background-color: #e9f5ff;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="feedback-container">
        <div class="header">
            <h1>Quiz Feedback</h1>
            <p class="text-muted">Your opinion matters to us!</p>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="user-info">
            <h5>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h5>
            <p>Please share your thoughts about our quiz.</p>
        </div>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" novalidate>
            <div class="mb-3">
                <label for="password" class="form-label">Enter your password</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <small class="text-muted">For security verification</small>
            </div>
            
            <div class="mb-4 rating-section">
                <label class="form-label">Rate the Quiz</label>
                <div class="star-rating">
                    <input type="radio" id="star5" name="quizRating" value="5" required>
                    <label for="star5">★</label>
                    <input type="radio" id="star4" name="quizRating" value="4">
                    <label for="star4">★</label>
                    <input type="radio" id="star3" name="quizRating" value="3">
                    <label for="star3">★</label>
                    <input type="radio" id="star2" name="quizRating" value="2">
                    <label for="star2">★</label>
                    <input type="radio" id="star1" name="quizRating" value="1">
                    <label for="star1">★</label>
                </div>
                <div class="invalid-feedback">Please select a rating</div>
            </div>
            
            <div class="mb-3">
                <label for="feedback" class="form-label">Your Feedback</label>
                <textarea class="form-control" id="feedback" name="feedback" rows="4" required placeholder="Tell us what you think about our quiz..."></textarea>
                <div class="invalid-feedback">Please provide your feedback</div>
            </div>
            
            <div class="mb-4">
                <label for="improvements" class="form-label">Suggested Improvements</label>
                <textarea class="form-control" id="improvements" name="improvements" rows="3" required placeholder="How can we make this quiz better?"></textarea>
                <div class="invalid-feedback">Please suggest some improvements</div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-submit">Submit Feedback</button>
            </div>
        </form>
        
        <div class="mt-4 text-center">
            <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
            <form method="POST" action="connect.php" style="display: inline;">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="btn btn-outline-danger ms-2">Logout</button>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    
    (function() {
        'use strict';
        
        var forms = document.querySelectorAll('form');
        
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    var inputs = form.querySelectorAll('input, textarea, select');
                    inputs.forEach(function(input) {
                        if (input.hasAttribute('required') && !input.value.trim()) {
                            input.classList.add('is-invalid');
                        } else {
                            input.classList.remove('is-invalid');
                        }
                    });
                    
                    
                    var radioGroups = {};
                    form.querySelectorAll('input[type="radio"]').forEach(function(radio) {
                        if (radio.hasAttribute('required')) {
                            radioGroups[radio.name] = radioGroups[radio.name] || false;
                            radioGroups[radio.name] = radioGroups[radio.name] || radio.checked;
                        }
                    });
                    
                    
                    for (var groupName in radioGroups) {
                        var radioGroup = form.querySelectorAll('input[name="' + groupName + '"]');
                        if (!radioGroups[groupName]) {
                            
                            radioGroup[0].closest('.rating-section').classList.add('was-validated');
                        }
                    }
                }
                
                form.classList.add('was-validated');
            }, false);
            
            
            form.querySelectorAll('input, textarea, select').forEach(function(input) {
                input.addEventListener('focus', function() {
                    this.classList.remove('is-invalid');
                });
            });
        });
    })();
    </script>
</body>
</html>