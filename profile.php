<?php
session_start();


if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    
    header("Location: login.html");
    exit;
}


if (isset($_GET['logout'])) {
    
    $_SESSION = array();
    
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    
    session_destroy();
    
    
    header("Location: index.php");
    exit;
}


$username = $_SESSION['username'];


$servername = "localhost";
$dbusername = "root";
$password = "";
$dbname = "parth"; 


$conn = new mysqli($servername, $dbusername, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$topicsQuery = "SELECT DISTINCT quiz_topic FROM quizresults WHERE username = '$username' ORDER BY quiz_topic";
$topicsResult = $conn->query($topicsQuery);


$overallStatsQuery = "SELECT 
                        COUNT(*) as total_attempts,
                        SUM(highest_score) as total_score,
                        SUM(total_questions) as total_questions,
                        SUM(attempted_questions) as total_attempted,
                        SUM(wrong_answers) as total_wrong,
                        AVG(average_score) as overall_average
                      FROM quizresults 
                      WHERE username = '$username'";
$overallStats = $conn->query($overallStatsQuery)->fetch_assoc();


$topPerformancesQuery = "SELECT 
                           quiz_topic, 
                           quiz_level, 
                           highest_score, 
                           total_questions,
                           (highest_score/total_questions)*100 as percentage 
                         FROM quizresults 
                         WHERE username = '$username' 
                         ORDER BY percentage DESC, highest_score DESC 
                         LIMIT 3";
$topPerformances = $conn->query($topPerformancesQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRACKQUIZ - User Profile</title>
    <link rel="stylesheet" href="style.css">

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #3498db;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 40px;
            margin-right: 20px;
        }
        
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .stat-card h3 {
            margin: 0;
            font-size: 24px;
            color: #3498db;
        }
        
        .stat-card p {
            margin: 5px 0 0;
            color: #777;
        }
        
        .quiz-topic {
            margin-bottom: 30px;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .quiz-topic h3 {
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .level-details {
            margin: 15px 0;
            padding: 15px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .level-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .progress-container {
            margin-top: 10px;
        }
        
        .progress {
            height: 10px;
            margin-bottom: 15px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        
        .stats-item {
            text-align: center;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .stats-item p {
            margin: 0;
            font-size: 14px;
            color: #777;
        }
        
        .stats-item h4 {
            margin: 5px 0 0;
            font-size: 18px;
            color: #444;
        }
        
        .badge-level {
            padding: 5px 10px;
            border-radius: 20px;
            color: white;
            font-weight: normal;
        }
        
        .badge-level-1 {
            background-color: #28a745;
        }
        
        .badge-level-2 {
            background-color: #ffc107;
            color: #333;
        }
        
        .badge-level-3 {
            background-color: #dc3545;
        }
        
        .top-performances {
            margin-bottom: 30px;
        }
        
        .performance-card {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .trophy-icon {
            font-size: 30px;
            margin-right: 20px;
            color: gold;
        }
        
        .gold { color: gold; }
        .silver { color: silver; }
        .bronze { color: #cd7f32; }
        
        .no-results {
            text-align: center;
            padding: 40px 20px;
            color: #777;
        }
        
        .logout-btn {
            padding: 8px 20px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background-color: #c82333;
            color: white;
            text-decoration: none;
        }
        
        .back-btn {
            padding: 8px 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            text-decoration: none;
            margin-right: 10px;
        }
        
        .back-btn:hover {
            background-color: #5a6268;
            text-decoration: none;
            color: white;
        }
        
        .action-buttons {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
    </style>
</head>
<body class="proj">
    <header>
        <nav class="mainnav">
            <div class="nav_left">
                <a href="index.php"><img src="https://uploads.turbologo.com/uploads/design/preview_image/61269529/watermark_preview_image20240831-1-1exmw04.png" alt=""></a>
            </div>
            <div class="nav_mid">
                <a href="index.php#aboutus" class="nav_link">About Us</a>
                <a href="feedback.php" class="nav_link">Feedback</a>
                <a href="index.php#q" class="nav_link">Games</a>
                <a href="index.php#learn" class="nav_link">Learning</a>
            </div>
            <div class="nav_right">
                <p style="visibility: hidden;">----------------------</p>
                <a href="profile.php"><button id="profile" class="active"><i class="fa-regular fa-user"></i></button></a>
            </div>
        </nav>
    </header>

    <div class="profile-container">
        <div class="profile-header">
            <div style="display: flex; align-items: center;">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                </div>
                <div>
                    <h2><?php echo ucfirst($username); ?>'s Profile</h2>
                    <p>CRACKQUIZ Player</p>
                </div>
            </div>
            <div>
                <a href="?logout=true" class="logout-btn">Logout</a>
            </div>
        </div>

        <div class="profile-stats">
            <div class="stat-card">
                <h3><?php echo $overallStats['total_attempts'] ?? 0; ?></h3>
                <p>Quiz Topics Attempted</p>
            </div>
            <div class="stat-card">
                <h3><?php 
                    $avgScore = $overallStats['overall_average'] ?? 0;
                    echo number_format($avgScore, 1); 
                ?>%</h3>
                <p>Average Score</p>
            </div>
            <div class="stat-card">
                <h3><?php 
                    $correct = ($overallStats['total_attempted'] ?? 0) - ($overallStats['total_wrong'] ?? 0);
                    echo $correct; 
                ?></h3>
                <p>Overall Correct Answers</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $overallStats['total_wrong'] ?? 0; ?></h3>
                <p>Overall Wrong Answers</p>
            </div>
        </div>

        <div class="top-performances">
            <h3>Your Top Performances</h3>
            
            <?php
            if ($topPerformances && $topPerformances->num_rows > 0) {
                $icons = ['gold', 'silver', 'bronze'];
                $counter = 0;
                
                while ($performance = $topPerformances->fetch_assoc()) {
                    $iconClass = $icons[$counter] ?? '';
                    $percentage = number_format($performance['percentage'], 1);
                    
                    echo '<div class="performance-card">
                            <div class="trophy-icon ' . $iconClass . '"><i class="fas fa-trophy"></i></div>
                            <div style="flex-grow: 1;">
                                <h4>' . $performance['quiz_topic'] . ' - Level ' . $performance['quiz_level'] . '</h4>
                                <p>Score: ' . $performance['highest_score'] . '/' . $performance['total_questions'] . ' (' . $percentage . '%)</p>
                            </div>
                        </div>';
                    
                    $counter++;
                }
            } else {
                echo '<div class="no-results">No quiz attempts recorded yet. Try taking some quizzes!</div>';
            }
            ?>
        </div>

        <h2>Quiz Performance</h2>
        
        <?php
        if ($topicsResult && $topicsResult->num_rows > 0) {
            while ($topic = $topicsResult->fetch_assoc()) {
                $quizTopic = $topic['quiz_topic'];
                
                
                $levelQuery = "SELECT * FROM quizresults 
                               WHERE username = '$username' AND quiz_topic = '$quizTopic' 
                               ORDER BY quiz_level";
                $levelResults = $conn->query($levelQuery);
                
                echo '<div class="quiz-topic">
                        <h3>' . $quizTopic . '</h3>';
                
                if ($levelResults && $levelResults->num_rows > 0) {
                    while ($level = $levelResults->fetch_assoc()) {
                        $percentage = ($level['highest_score'] / $level['total_questions']) * 100;
                        $levelClass = "badge-level-" . $level['quiz_level'];
                        
                        echo '<div class="level-details">
                                <div class="level-header">
                                    <h4><span class="badge-level ' . $levelClass . '">Level ' . $level['quiz_level'] . '</span></h4>
                                    <div>Attempts: ' . $level['attempts'] . '</div>
                                </div>
                                
                                <div class="progress-container">
                                    <div class="d-flex justify-content-between">
                                        <span>Best Score: ' . $level['highest_score'] . '/' . $level['total_questions'] . '</span>
                                        <span>' . number_format($percentage, 1) . '%</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: ' . $percentage . '%;" 
                                             aria-valuenow="' . $percentage . '" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                
                                <div class="stats-grid">
                                    <div class="stats-item">
                                        <p>Correct</p>
                                        <h4>' . ($level['attempted_questions'] - $level['wrong_answers']) . '</h4>
                                    </div>
                                    <div class="stats-item">
                                        <p>Wrong</p>
                                        <h4>' . $level['wrong_answers'] . '</h4>
                                    </div>
                                    <div class="stats-item">
                                        <p>Attempted</p>
                                        <h4>' . $level['attempted_questions'] . '</h4>
                                    </div>
                                    <div class="stats-item">
                                        <p>Unattempted</p>
                                        <h4>' . $level['unattempted_questions'] . '</h4>
                                    </div>
                                    <div class="stats-item">
                                        <p>Average</p>
                                        <h4>' . number_format($level['average_score'], 1) . '%</h4>
                                    </div>
                                </div>
                            </div>';
                    }
                } else {
                    echo '<p>No level data available for this topic.</p>';
                }
                
                echo '</div>';
            }
        } else {
            echo '<div class="no-results">
                    <i class="fas fa-chart-line" style="font-size: 48px; color: #ddd; margin-bottom: 20px;"></i>
                    <h3>No Quiz Results Yet</h3>
                    <p>Complete some quizzes to see your performance statistics here.</p>
                    <a href="index.php#q" class="btn btn-primary mt-3">Try a Quiz Now</a>
                  </div>';
        }
        ?>
        
        <div class="action-buttons">
            <a href="index.php" class="back-btn">Back to Home</a>
            <a href="index.php#q" class="btn btn-primary">Take Another Quiz</a>
        </div>
    </div>

    <footer class="text-center py-4 mt-5" style="background-color: #f8f9fa;">
        <p>© 2025 CRACKQUIZ. All rights reserved.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            const currentPath = window.location.pathname;
            
            
            if (currentPath.includes('profile.php')) {
                document.getElementById('profile').classList.add('active');
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>