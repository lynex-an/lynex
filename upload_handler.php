<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$upload_dir = 'uploads/'; 
$analysis_status = 'Pending'; 

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['analyze_submit']) && isset($_FILES['document_file'])) {
    
    $file = $_FILES['document_file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("File upload error: " . $file['error']);
    }
    
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $unique_filename = uniqid("doc_") . '.' . $file_extension;
    $target_file = $upload_dir . $unique_filename;
    
    $output_file_name = uniqid("output_") . '.' . $file_extension;
    $output_file_path = $upload_dir . $output_file_name;
    
    copy($file['tmp_name'], $output_file_path); 

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        
        $sql = "INSERT INTO Analyses (user_id, input_file_path, output_file_path, status) VALUES (?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("isss", $user_id, $target_file, $output_file_path, $analysis_status);
            
            if ($stmt->execute()) {
                $new_analysis_id = $conn->insert_id;
                $stmt->close();
                
                header("location: medaqeq.php?analysis_id=" . $new_analysis_id);
                exit;
            } else {
                die("Error saving analysis record: " . $stmt->error);
            }
        }
    } else {
        die("Error moving uploaded file.");
    }
} else {
    header("location: medaqeq.php");
    exit;
}
$conn->close();
?>