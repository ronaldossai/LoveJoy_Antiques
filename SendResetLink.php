<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'vendor/autoload.php';  // Composer autoload for PHPMailer
require_once 'db_connection.php';

function generateResetToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function sendPasswordResetEmail($email, $resetLink) {
    $mail = new PHPMailer(true);

    try {
        // Gmail SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ronjjossai@gmail.com';  // Replace with your email
        $mail->Password = 'yfujbsviigjphpgq';     // Use App Password, not regular password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email content
        $mail->setFrom('ronjjossai@gmail.com', 'Password Reset');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = "
            <p>You have requested a password reset. Click the link below to reset your password:</p>
            <p><a href='$resetLink'>Reset Password</a></p>
            <p>This link will expire in 1 hour.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email send failed: {$mail->ErrorInfo}");
        return false;
    }
}

function createPasswordResetToken($userId, $conn) {
    $token = generateResetToken();
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $token, $expiresAt);
    
    return $stmt->execute() ? $token : false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $_SESSION['reset_error'] = "Invalid email address.";
        header("Location: ForgotPassword.php");
        exit();
    }

    // Find user by email
    $stmt = $conn->prepare("SELECT id FROM systemUser WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Generic message to prevent email enumeration
        $_SESSION['reset_message'] = "If an account exists with this email, a reset link will be sent.";
        header("Location: ForgotPassword.php");
        exit();
    }

    $user = $result->fetch_assoc();
    $userId = $user['id'];

    // Generate reset token
    $resetToken = createPasswordResetToken($userId, $conn);
    
    if (!$resetToken) {
        $_SESSION['reset_error'] = "Could not generate reset token.";
        header("Location: ForgotPassword.php");
        exit();
    }

    // Construct reset link
    $resetLink = "http://localhost/php_program/password_reset.php?token=" . $resetToken;

    // Send reset email
    if (sendPasswordResetEmail($email, $resetLink)) {
        $_SESSION['reset_message'] = "Password reset link sent to your email.";
    } else {
        $_SESSION['reset_error'] = "Could not send reset email.";
    }

    header("Location: ForgotPassword.php");
    exit();
}