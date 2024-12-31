<?php
session_start();

// Include necessary files
require_once 'vendor/autoload.php';
require_once 'db_connection.php';
require_once 'MultiFactorAuth.php';

$userId = $_SESSION['login_user_id'];
$userEmail = $_SESSION['login_email'];

// Initialize MultiFactorAuth
$mfaAuth = new MultiFactorAuth($conn);

// Initialize $mfaCodeSent with a default value
$mfaCodeSent = false;

// Generate OTP only on initial page load
if (!isset($_SESSION['otp_generated'])) {
    $mfaCodeSent = $mfaAuth->generateTOTP($userId, $userEmail);
    $_SESSION['otp_generated'] = true;
}

// Handle form submission for code verification
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $providedCode = trim($_POST['mfa_code']);

    if ($mfaAuth->verifyMFACode($userId, $providedCode)) {
        // Verify user role and redirect appropriately
        $stmt = $conn->prepare("SELECT user_type FROM systemuser WHERE ID = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        //Set the username in the session
        $_SESSION['Username'] = $user['Username'];

        // Role-based redirection
        if ($user['user_type'] === 'admin') {
            header('Location: ListOfEvaluations.php');
        } else {
            header('Location: EvaluationPage.php');
        }
        exit();
    } else {
        $error_message = "Invalid code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Multi-Factor Authentication</title>
    <link rel="stylesheet" type="text/css" href="LoginFormCSS.css">
    <style>
        body, form {
            color: black;
        }
        .error-message {
            color: red;
            margin-bottom: 10px;
        }
        .success-message {
            color: green;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Multi-Factor Authentication</h1>

    <?php if (!empty($error_message)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <?php if ($mfaCodeSent): ?>
        <div class="success-message">A 6-digit verification code has been sent to your email.</div>
    <?php else: ?>
        <div class="error-message">Failed to send verification code. Please contact support.</div>
    <?php endif; ?>

    <form action="mfa_verification.php" method="POST">
        <label for="mfa_code">Enter 6-digit Code:</label>
        <input type="text" name="mfa_code" required maxlength="6" pattern="\d{6}" title="6-digit code" />
        <input type="submit" value="Verify" />
    </form>

    <p>Didn't receive the code? <a href="mfa_verification.php">Resend Code</a></p>
    <p>Not the correct user? <a href="loginform.php">Go to Login</a></p>
</body>
</html>