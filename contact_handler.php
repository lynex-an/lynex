<?php
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact_submit'])) {
    
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $message_text = trim($_POST['message_text']);

    if (empty($full_name) || empty($email) || empty($message_text)) {
        die("Please fill all required fields.");
    }
    
    $sql = "INSERT INTO Messages (full_name, email, message_text) VALUES (?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sss", $full_name, $email, $message_text);
        
        if ($stmt->execute()) {
            header("location: contact.php?status=success");
            exit;
        } else {
            die("Error sending message: " . $stmt->error);
        }
        $stmt->close();
    } else {
        die("Error preparing query: " . $conn->error);
    }
} else {
    header("location: contact.php");
    exit;
}

$conn->close();
?>