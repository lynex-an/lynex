<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        die("Please fill all required fields.");
    }
    
    $sql = "SELECT user_id, full_name, password_hash FROM Users WHERE email = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                
                header("location: medaqeq.php");
                exit;
            } else {
                die("Invalid password.");
            }
        } else {
            die("No account found with that email.");
        }
        $stmt->close();
    } else {
        die("Error preparing query: " . $conn->error);
    }
} else {
    header("location: login.php");
    exit;
}

$conn->close();
?>