<!-- This will open the form that will send a reset link for users who have forgotten their password -->
<!DOCTYPE html>
<html>
    <title>Forgot Password</title>
<head>
    <link rel="stylesheet" type="text/css" href="LoginFormCSS.css">
</head>
<body>
    <h1>Forgot Password</h1>
    
    <!-- Display session messages -->
    <?php
    session_start();
    if (isset($_SESSION['reset_message'])) {
        echo "<p style='color: green; text-align: center;'>" . htmlspecialchars($_SESSION['reset_message']) . "</p>";
        unset($_SESSION['reset_message']);
    }

    if (isset($_SESSION['reset_error'])) {
        echo "<p style='color: red; text-align: center;'>" . htmlspecialchars($_SESSION['reset_error']) . "</p>";
        unset($_SESSION['reset_error']);
    }
    ?>

    <form action="SendResetLink.php" method="POST">
        Enter your email address:
        <input name="email" type="email" required />
        <input type="submit" value="Send Reset Link">
        
        <div style='margin-top:20px;'>
            <p>Already have an account? <a href='LoginForm.php'>Login now</a></p>
        </div>
    </form>
</body>
</html>
