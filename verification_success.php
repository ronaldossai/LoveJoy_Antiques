<?php
session_start();
$verificationSuccess = isset($_SESSION['verification_success']) ? $_SESSION['verification_success'] : false;
$verificationError = isset($_SESSION['verification_error']) ? $_SESSION['verification_error'] : '';

// Clear the session variables
unset($_SESSION['verification_success']);
unset($_SESSION['verification_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }
        .success-message {
            color: green;
            background-color: #eeffee;
            border: 1px solid green;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error-message {
            color: red;
            background-color: #ffeeee;
            border: 1px solid red;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .login-link {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <?php if ($verificationSuccess): ?>
        <div class="success-message">
            <h1>Registration Successful!</h1>
            <p>Your email has been verified. You can now log in to your account.</p>
            <a href="LoginForm.php" class="login-link">Go to Login</a>
        </div>
    <?php elseif ($verificationError): ?>
        <div class="error-message">
            <h1>Verification Error</h1>
            <p><?php echo htmlspecialchars($verificationError); ?></p>
            <a href="FirstRegisterForm.php" class="login-link">Back to Registration</a>
        </div>
    <?php else: ?>
        <div class="error-message">
            <h1>Invalid Access</h1>
            <p>You've reached this page incorrectly.</p>
            <a href="FirstRegisterForm.php" class="login-link">Back to Registration</a>
        </div>
    <?php endif; ?>
</body>
</html>