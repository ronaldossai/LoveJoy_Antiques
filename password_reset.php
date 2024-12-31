<?php
session_start();
require_once 'db_connection.php';

// Validate the reset token
$token = $_GET['token'] ?? '';
if (empty($token)) {
    die("Invalid reset token.");
}

// Check token validity
$query = "SELECT user_id, expires_at FROM password_resets WHERE token = ? AND expires_at > NOW()";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invalid or expired reset token.");
}

$row = $result->fetch_assoc();
$userId = $row['user_id'];
$_SESSION['reset_user_id'] = $userId;
$_SESSION['reset_token'] = $token;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" type="text/css" href="LoginFormCSS.css">
</head>
<body>
    <h1>Reset Your Password</h1>
    <?php
    if (isset($_SESSION['reset_error'])) {
        echo "<p style='color: red;'>" . htmlspecialchars($_SESSION['reset_error']) . "</p>";
        unset($_SESSION['reset_error']);
    }
    ?>
    <form action="password_reset_process.php" method="POST">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required>
        
        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        
        <input type="submit" value="Reset Password">
    </form>
</body>
</html>