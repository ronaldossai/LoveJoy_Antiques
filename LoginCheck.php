<?php
// Server and DB connection parameters
$servername = "localhost";
$rootuser = "root";
$db = "SocNet";
$rootPassword = "";

// Create connection
$conn = new mysqli($servername, $rootuser, $rootPassword, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session for CSRF token validation
session_start();

$error_message = ""; // Initialize error message
$max_attempts = 5; // Maximum number of failed attempts
$lockout_time = 15 * 60; // Lockout time in seconds (15 minutes)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = "CSRF token validation failed. Please try again.";
    } else {
        // Get form values
        $username = trim($_POST['txtUsername']);
        $password = $_POST['txtPassword'];

        // Check if the user exists in the LoginAttempts table
        $attemptQuery = "SELECT * FROM LoginAttempts WHERE username = ?";
        $stmt = $conn->prepare($attemptQuery);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $attemptResult = $stmt->get_result();

        if ($attemptResult->num_rows > 0) {
            $attemptRow = $attemptResult->fetch_assoc();
            // Check if the account is locked
            if ($attemptRow['lockout_time'] && time() < strtotime($attemptRow['lockout_time'])) {
                $error_message = "Account is temporarily locked. Please try again later.";
            }
        } else {
            // If no record found, initialize login attempt record
            $insertAttemptQuery = "INSERT INTO LoginAttempts (username) VALUES (?)";
            $stmt = $conn->prepare($insertAttemptQuery);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $attemptRow = [
                'username' => $username,
                'attempts' => 0,
                'lockout_time' => null
            ];
        }

        // Query to check user credentials
        $userQuery = "SELECT * FROM SystemUser WHERE Username = ?";
        $stmt = $conn->prepare($userQuery);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $userResult = $stmt->get_result();

        if ($userResult->num_rows > 0) {
            $userRow = $userResult->fetch_assoc();

            // Check if email is verified
            if (!$userRow['is_verified']) {
                $error_message = "Please verify your email before logging in.";
            } elseif (password_verify($password, $userRow['Password'])) {
                // Reset failed attempts and lockout time upon successful login
                $stmt = $conn->prepare("UPDATE LoginAttempts SET attempts = 0, lockout_time = NULL WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();

                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                // Store user details for multi-factor authentication
                $_SESSION['login_user_id'] = $userRow['ID'];
                $_SESSION['login_email'] = $userRow['Email'];

                // Redirect to Multi-Factor Authentication
                header('Location: mfa_verification.php');
                exit();
            } else {
                // Increment failed attempts on incorrect password
                $attempts = $attemptRow['attempts'] + 1;

                if ($attempts >= $max_attempts) {
                    // Lock the account if too many failed attempts
                    $lockout_timestamp = date('Y-m-d H:i:s', time() + $lockout_time);
                    $stmt = $conn->prepare("UPDATE LoginAttempts SET attempts = ?, lockout_time = ? WHERE username = ?");
                    $stmt->bind_param("iss", $attempts, $lockout_timestamp, $username);
                    $stmt->execute();

                    $error_message = "Too many failed attempts. Account locked for 15 minutes.";
                } else {
                    // Update failed attempts without locking the account
                    $stmt = $conn->prepare("UPDATE LoginAttempts SET attempts = ? WHERE username = ?");
                    $stmt->bind_param("is", $attempts, $username);
                    $stmt->execute();

                    $error_message = "Wrong password. Attempt " . $attempts . " of " . $max_attempts;
                }
            }
        } else {
            $error_message = "This user was not found in our database.";
        }

        // Close the statement
        $stmt->close();
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LoveJoy Antiques - Login</title>
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
        .error-message {
            color: red;
            margin-bottom: 10px;
        }
    </style>
    <script>
        // Function to display error message
        function showError(message) {
            if (message) {
                var errorDiv = document.getElementById('error-container');
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
            }
        }

        // Safely pass the error message
        document.addEventListener('DOMContentLoaded', function() {
            var errorMessage = <?php echo json_encode($error_message); ?>;
            showError(errorMessage);
        });
    </script>
</head>
<body>
    <h1>LoveJoy Antiques</h1>
    <div id="error-container" class="error-message" style="display: none;"></div>
    <form action='LoginCheck.php' method='POST'>
        Username <input name='txtUsername' type='text' required />
        Password <input name='txtPassword' type='password' required />
        <input type='hidden' name='csrf_token' value='<?php echo $_SESSION['csrf_token']; ?>' />
        <input type='submit' value='Login'>
        <br /><br />Not Registered Yet? Click <a href='FirstRegisterForm.php'>HERE</a>
        <br /><br />Forgot Password? Click <a href='ForgotPassword.php'>HERE</a>
    </form>
</body>
</html>
