<?php
session_start();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (isset($_POST['username']) && isset($_POST['password']) && !isset($_POST['firstname'])) {
        
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            die("Username and password are required");
        }
        
        try {
            $conn = new mysqli('localhost', 'root', '', 'parth');
            
            if ($conn->connect_error) {
                throw new Exception('Connection Failed: ' . $conn->connect_error);
            }
            
            $stmt = $conn->prepare("SELECT username, password, confirmpassword, firstname, lastname FROM quiz WHERE username = ?");
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
                    $_SESSION['username'] = $username;
                    $_SESSION['loggedin'] = true;
                    $_SESSION['firstname'] = $row['firstname'];
                    $_SESSION['lastname'] = $row['lastname'];
                    
                    header("Location: index.php");
                    exit();
                } else {
                    die("Invalid username or password");
                }
            } else {
                die("Invalid username or password");
            }
            
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    } 
    
    else if (isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['confirmpassword'])) {
        $firstname = $_POST['firstname'] ?? '';
        $lastname = $_POST['lastname'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmpassword = $_POST['confirmpassword'] ?? '';
        
        if (empty($firstname) || empty($lastname) || empty($username) || empty($password)) {
            die("All fields are required");
        }
        
        if ($password !== $confirmpassword) {
            die("Passwords do not match");
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $conn = new mysqli('localhost', 'root', '', 'parth');
            
            if($conn->connect_error){
                throw new Exception('Connection Failed: ' . $conn->connect_error);
            }
            
            
            $check_stmt = $conn->prepare("SELECT username FROM quiz WHERE username = ?");
            $check_stmt->bind_param("s", $username);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $check_stmt->close();
                $conn->close();
                die("Username already exists. Please choose another username.");
            }
            
            $check_stmt->close();
            
            
            $stmt = $conn->prepare("INSERT INTO quiz (firstname, lastname, username, password, confirmpassword) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $firstname, $lastname, $username, $hashed_password, $confirmpassword);
            
            if ($stmt->execute()) {
                $_SESSION['loggedin'] = true; 
                $_SESSION['username'] = $username;
                $_SESSION['firstname'] = $firstname;
                $_SESSION['lastname'] = $lastname;
                
                header("Location: index.php");
                exit();
            } else {
                throw new Exception("Error inserting data: " . $stmt->error);
            }
            
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    } 
    
    else if (isset($_POST['action']) && $_POST['action'] === 'logout') {
        
        session_unset();
        session_destroy();
        
        
        header("Location: index.php");
        exit();
    } else {
        die("Invalid form submission");
    }
} else {
    http_response_code(405);
    echo "Method Not Allowed";
    exit();
}
?>