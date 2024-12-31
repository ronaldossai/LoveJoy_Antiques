<?php
session_start();
require_once 'db_connection.php';

// Validate session and token
$formToken = $_POST['token'] ?? '';
$sessionToken = $_SESSION['reset_token'] ?? '';

// Verify token matches
if (empty($formToken) || empty($sessionToken) || $formToken !== $sessionToken) {
    die("Invalid or expired reset token.");
}

// Validate token in database
$query = "SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $formToken);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invalid or expired reset token.");
}

$userId = $_SESSION['reset_user_id'];
$token = $sessionToken;

// Validate password input
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

$errorMessages = [];

// Password validation
if (empty($newPassword)) {
    $errorMessages[] = "New password is required.";
}

if ($newPassword !== $confirmPassword) {
    $errorMessages[] = "Passwords do not match.";
}

// Password strength check
$strongRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#\$%\^&\*])(?=.{8,})/';
if (!preg_match($strongRegex, $newPassword)) {
    $errorMessages[] = "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.";
}

if (!empty($errorMessages)) {
    $_SESSION['reset_error'] = implode("<br>", $errorMessages);
    header("Location: password_reset.php?token=$token");
    exit();
}

// Begin database transaction
$conn->begin_transaction();

try {
    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update user password
    $stmt = $conn->prepare("UPDATE SystemUser SET Password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $userId);
    if (!$stmt->execute()) {
        throw new Exception("Failed to update password.");
    }

    // Delete used reset token
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        throw new Exception("Failed to remove reset token.");
    }

    // Commit transaction
    $conn->commit();

    // Redirect to login with success message
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['password_reset_success'] = "Password successfully reset. Please log in.";
    header("Location: LoginForm.php");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    $_SESSION['reset_error'] = "An error occurred. Please try again.";
    header("Location: password_reset.php?token=$token");
    exit();
}

