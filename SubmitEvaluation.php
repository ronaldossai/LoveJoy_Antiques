<?php
session_start();

// Database connection details
$host = 'localhost';
$dbname = 'SocNet';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

// Get form data
$comment = $_POST['comment'];
$rating = $_POST['rating'];
$contactPreference = $_POST['contact_preference'];

// File upload handling
$file_name = null;
$file_path = null;

if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    $allowedExtensions = array('jpg', 'jpeg', 'png', 'pdf');
    $fileExtension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

    if (in_array($fileExtension, $allowedExtensions)) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $file_name = uniqid() . "." . $fileExtension;
        $file_path = $uploadDir . $file_name;

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
            die("Error uploading file.");
        }
    } else {
        die("Invalid file type. Only JPG, JPEG, PNG, and PDF are allowed.");
    }
}

// Prepare and execute SQL statement
// Removed username column from the INSERT statement
$stmt = $pdo->prepare("INSERT INTO evaluation_db (comment, rating, file_name, uploaded_at, contact_preference)
                        VALUES (:comment, :rating, :file_name, NOW(), :contact_preference)");

$stmt->bindParam(':comment', $comment);
$stmt->bindParam(':rating', $rating);
$stmt->bindParam(':file_name', $file_name);
$stmt->bindParam(':contact_preference', $contactPreference);

try {
    $stmt->execute();
    header("Location: EvaluationPage.php?status=success");
    exit();
} catch(PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$pdo = null; // Close the database connection
?>