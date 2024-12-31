<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));  // Generate CSRF token
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>LoveJoy</title>
    <link rel="stylesheet" type="text/css" href="LoginFormCSS.css">
    <style>
        body, form {
            color: black;
        }
        a {
            color: #007BFF;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>LoveJoy Antiques</h1>
    <form action="LoginCheck.php" method="POST">
        Username <input name="txtUsername" type="text" />
        Password <input name="txtPassword" type="password" />
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
        <input type="submit" value="Login">
        <br /><br />
        Not Registered Yet? Click <a href="FirstRegisterForm.php">HERE</a>
        <br /><br />
        Forgot Password? Click <a href="ForgotPassword.php">HERE</a>
    </form>
</body>
</html>
