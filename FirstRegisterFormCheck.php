<?php
$mysql_host = "localhost";
$mysql_database = "SocNet";
$mysql_user = "root";
$mysql_password = "";

// Verification function call
require_once 'send_verification_email.php';

// Connect to the database server
$connection = mysqli_connect($mysql_host, $mysql_user, $mysql_password, $mysql_database) or die("Could not connect to the database server");

function validateRecaptcha($recaptchaResponse) {
    $secretKey = '6Ld9s5IqAAAAAN5wo1HpvjKc4MFrMMZoN_b50HtU';
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secretKey . '&response=' . $recaptchaResponse);
    $responseData = json_decode($verifyResponse);
    return $responseData->success;
}

$forename = $surname = $username = $telephone = $email1 = $email2 = $password1 = $password2 = '';
$securityQuestion1 = $securityAnswer1 = $securityQuestion2 = $securityAnswer2 = '';
$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $forename = trim($_POST['txtForename'] ?? '');
    $surname = trim($_POST['txtSurname'] ?? '');
    $username = trim($_POST['txtUsername'] ?? '');
    $telephone = trim($_POST['txtTelephone'] ?? '');
    $email1 = trim($_POST['txtEmail1'] ?? '');
    $email2 = trim($_POST['txtEmail2'] ?? '');
    $password1 = $_POST['txtPassword1'] ?? '';
    $password2 = $_POST['txtPassword2'] ?? '';
    $securityQuestion1 = trim($_POST['txtSecurityQuestion1'] ?? '');
    $securityAnswer1 = trim($_POST['txtSecurityAnswer1'] ?? '');
    $securityQuestion2 = trim($_POST['txtSecurityQuestion2'] ?? '');
    $securityAnswer2 = trim($_POST['txtSecurityAnswer2'] ?? '');
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    // Validate reCAPTCHA
    if (empty($recaptchaResponse)) {
        $errorMessages[] = 'Please complete the CAPTCHA verification.';
    } elseif (!validateRecaptcha($recaptchaResponse)) {
        $errorMessages[] = 'CAPTCHA verification failed. Please try again.';
    }

    // Validate username uniqueness
    $stmt = $connection->prepare("SELECT * FROM systemuser WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errorMessages[] = 'Username already exists. Please choose another.';
    }

    // Validate email uniqueness
    $stmt = $connection->prepare("SELECT * FROM systemuser WHERE Email = ?");
    $stmt->bind_param("s", $email1);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errorMessages[] = 'Email address is already registered.';
    }

    // Existing validation for other fields...
    if (empty($forename)) $errorMessages[] = 'Forename is required.';
    if (empty($surname)) $errorMessages[] = 'Surname is required.';
    if (empty($username)) $errorMessages[] = 'Username is required.';
    if (empty($telephone)) $errorMessages[] = 'Telephone is required.';

    // Email validation
    if (empty($email1) || empty($email2)) {
        $errorMessages[] = 'Both email addresses are required.';
    } elseif ($email1 !== $email2) {
        $errorMessages[] = 'Emails do not match.';
    } elseif (!filter_var($email1, FILTER_VALIDATE_EMAIL)) {
        $errorMessages[] = 'The email address is not valid.';
    }

    // Password validation with stronger regex
    $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])(?=.{8,})/';
    if (empty($password1) || empty($password2)) {
        $errorMessages[] = 'Password is required.';
    } elseif ($password1 !== $password2) {
        $errorMessages[] = 'Passwords do not match.';
    } elseif (!preg_match($passwordRegex, $password1)) {
        $errorMessages[] = 'Password does not meet complexity requirements.';
    }

    // Security question validation
    if (empty($securityQuestion1) || empty($securityAnswer1)) {
        $errorMessages[] = 'First security question and answer are required.';
    }
    if (empty($securityQuestion2) || empty($securityAnswer2)) {
        $errorMessages[] = 'Second security question and answer are required.';
    }
    if ($securityQuestion1 === $securityQuestion2) {
        $errorMessages[] = 'Security questions must be different.';
    }

    // If no errors, register user
    if (empty($errorMessages)) {
        $connection->begin_transaction();

        try {
            // Hash the password
            $hashedPassword = password_hash($password1, PASSWORD_DEFAULT);

            // Generate verification token
            $verificationToken = generateVerificationToken();

            // Insert user details with verification token
            $stmt = $connection->prepare("INSERT INTO systemuser (Username, Password, Forename, Surname, Email, Telephone, verification_token, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
            $stmt->bind_param("sssssss", $username, $hashedPassword, $forename, $surname, $email1, $telephone, $verificationToken);

            if ($stmt->execute()) {
                $userId = $connection->insert_id;

                // Hash security answers
                $hashedAnswer1 = password_hash($securityAnswer1, PASSWORD_DEFAULT);
                $hashedAnswer2 = password_hash($securityAnswer2, PASSWORD_DEFAULT);

                // Insert security questions
                $securityStmt = $connection->prepare("INSERT INTO securityquestions (user_id, question, answer, question2, answer2, created_at, attempts_left, last_attempt, locked_until) VALUES (?, ?, ?, ?, ?, NOW(), 5, NULL, NULL)");
                $securityStmt->bind_param("issss", $userId, $securityQuestion1, $hashedAnswer1, $securityQuestion2, $hashedAnswer2);

                if ($securityStmt->execute()) {
                    // Send verification email
                    if (sendVerificationEmail($email1, $verificationToken)) {
                        $connection->commit();

                        session_start();
                        $_SESSION['email_verification_message'] = 'Registration successful! Please check your email to verify your account.';
                        header("Location: FirstRegisterForm.php");
                        exit();
                    } else {
                        $connection->rollback();
                        $errorMessages[] = 'Failed to send verification email. Please try again.';
                    }
                } else {
                    $connection->rollback();
                    $errorMessages[] = 'Error saving security questions.';
                }
            } else {
                $connection->rollback();
                $errorMessages[] = 'Registration failed: ' . $stmt->error;
            }
        } catch (Exception $e) {
            $connection->rollback();
            $errorMessages[] = 'Registration failed: ' . $e->getMessage();
        }
    }

    // Store errors in session
    session_start();
    $_SESSION['registration_errors'] = $errorMessages;
    $_SESSION['form_data'] = [
        'forename' => $forename,
        'surname' => $surname,
        'username' => $username,
        'telephone' => $telephone,
        'email1' => $email1,
        'email2' => $email2
    ];
    header("Location: FirstRegisterForm.php");
    exit();
}

$connection->close();
?>