<?php
session_start();

// Retrieve error messages and form data from session
$errorMessages = isset($_SESSION['registration_errors']) ? $_SESSION['registration_errors'] : [];
$formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
$emailVerificationMessage = isset($_SESSION['email_verification_message']) ? $_SESSION['email_verification_message'] : '';

// Clear the session variables
unset($_SESSION['registration_errors']);
unset($_SESSION['form_data']);
unset($_SESSION['email_verification_message']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel='stylesheet' type='text/css' href='registerformCSS.css'>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        .error-messages {
            color: red;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid red;
            border-radius: 5px;
            background-color: #ffeeee;
        }
        .error-messages ul {
            margin: 0;
            padding-left: 20px;
        }
        .success-message {
            color: green;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid green;
            border-radius: 5px;
            background-color: #eeffee;
            text-align: center;
        }
        .recaptcha-container {
            display: flex;
            justify-content: center;
            margin: 15px 0;
        }
        .verification-message {
            color: blue;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid blue;
            border-radius: 5px;
            background-color: #e6f2ff;
            text-align: center;
        }
        
        /* Password Policy Box styling */
        #password-policy {
            margin-top: 5px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f8f8f8;
            font-size: 14px;
            width: 100%;
        }

    </style>
</head>
<body>
    <form action='FirstRegisterFormCheck.php' method='post'>
        <h1>Please register your details below:</h1>
        <pre>
Type in your Forename:
<input name='txtForename' type='text' value='<?php echo htmlspecialchars($formData['forename'] ?? ''); ?>' />

Type in your Surname:
<input name='txtSurname' type='text' value='<?php echo htmlspecialchars($formData['surname'] ?? ''); ?>' />

Type in your Username:
<input name='txtUsername' type='text' value='<?php echo htmlspecialchars($formData['username'] ?? ''); ?>' />

Type in your Telephone:
<input name='txtTelephone' type='text' value='<?php echo htmlspecialchars($formData['telephone'] ?? ''); ?>' />

Type in your Email Address:
<input name='txtEmail1' type='text' value='<?php echo htmlspecialchars($formData['email1'] ?? ''); ?>' />

Type in your Email Address again:
<input name='txtEmail2' type='text' value='<?php echo htmlspecialchars($formData['email2'] ?? ''); ?>' />

Type in your Password:
<input id='password' name='txtPassword1' type='password' onkeyup='checkPasswordStrength()'/>
<div id='password-strength'></div>

<!-- Password Policy Box -->
<div id="password-policy">
    <ul>
        <li>Minimum 8 characters</li>
        <li>At least one uppercase letter</li>
        <li>At least one lowercase letter</li>
        <li>At least one number</li>
        <li>At least one special character (e.g., !, @, #, $, etc.)</li>
    </ul>
</div>

Type in your Password again:
<input name='txtPassword2' type='password'/>

Security Question 1:
<select name='txtSecurityQuestion1'>
    <option value="">Select a security question</option>
    <option value="What was the name of your first pet?">What was the name of your first pet?</option>
    <option value="In what city were you born?">In what city were you born?</option>
    <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
    <option value="What was your favorite teacher's name?">What was your favorite teacher's name?</option>
</select>
<input name='txtSecurityAnswer1' type='text' placeholder='Answer to security question 1'/>

Security Question 2:
<select name='txtSecurityQuestion2'>
    <option value="">Select a security question</option>
    <option value="What was your childhood nickname?">What was your childhood nickname?</option>
    <option value="What was the make of your first car?">What was the make of your first car?</option>
    <option value="What is the name of your favorite sports team?">What is the name of your favorite sports team?</option>
    <option value="What was the first concert you attended?">What was the first concert you attended?</option>
</select>
<input name='txtSecurityAnswer2' type='text' placeholder='Answer to security question 2'/>
        </pre>

        <br/>
        <input type='checkbox' id='terms' name='terms'>
        <label for='terms'>I accept the <a href='terms-and-conditions.html' target='_blank'>Terms and Conditions</a></label>
        <br/><br/>

        <div class="recaptcha-container">
            <div class="g-recaptcha" data-sitekey="6Ld9s5IqAAAAAL7xrtECXvT4DvwlmnTWsPPyonSh"></div>
        </div>

        <?php
          if (!empty($emailVerificationMessage)) {
            echo "<div class='verification-message'>" . htmlspecialchars($emailVerificationMessage) . "</div>";
        }

        if (!empty($errorMessages)) {
            echo "<div class='error-messages'>";
            echo "<ul>";
            foreach ($errorMessages as $error) {
                echo "<li>" . htmlspecialchars($error) . "</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
        ?>

        <br/>
        <input type='submit' value='Register'>
    </form>

    <div style='margin-top:20px;'>
        <p>Already have an account? <a href='LoginForm.php'>Login now</a></p>
    </div>

    <script>
    function checkPasswordStrength() {
        var strength = document.getElementById('password-strength');
        var password = document.getElementById('password').value;

        if (password.length === 0) {
            strength.innerHTML = '';
            return;
        }

        var strongRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#\$%\^&\*])(?=.{8,})/;
        var mediumRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.{6,})/;

        if (strongRegex.test(password)) {
            strength.innerHTML = '<span style="color:green">Strong</span>';
        } else if (mediumRegex.test(password)) {
            strength.innerHTML = '<span style="color:orange">Medium</span>';
        } else {
            strength.innerHTML = '<span style="color:red">Weak</span>';
        }
    }

    document.querySelector('form').onsubmit = function(event) {
        if (!document.getElementById('terms').checked) {
            alert('You must accept the Terms and Conditions before registering.');
            event.preventDefault();
        }
    };
    </script>
</body>
</html>
