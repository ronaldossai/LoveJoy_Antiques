<?php
require 'vendor/autoload.php'; // Include Composer's autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Function to generate a 64-character random verification token
function generateVerificationToken() {
    return bin2hex(random_bytes(32));
}

// Function to send a verification email
function sendVerificationEmail($email, $token) {
    $verificationLink = "http://localhost/php_program/verify_email.php?token=" . $token;

    // Initialize PHPMailer
    $mail = new PHPMailer(true);

    try {
        // SMTP server configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Local SMTP server
        $mail->SMTPAuth   = true;       // Authentication SMTP server
        $mail->Username   = 'ronjjossai@gmail.com'; // SMTP server username
        $mail->Password   = 'yfujbsviigjphpgq'; // SMTP server password
        $mail->Port       = 587;        // TLS connection
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS Encryption


        // Email sender and recipient configuration
        $mail->setFrom('ronjjossai@gmail.com', 'LoveJoy Antiques');
        $mail->addAddress($email); // Recipient's email address

        // Email subject and body content
        $mail->isHTML(true);
        $mail->Subject = "Verify Your Email for LoveJoy Antiques";
        $mail->Body = "
            <p>Thank you for registering!</p>
            <p>To complete your registration, please click the following link to verify your email:</p>
            <p><a href='$verificationLink'>$verificationLink</a></p>
            <p>If you did not register on our site, please ignore this email.</p>
        ";
        $mail->AltBody = "Thank you for registering! To complete your registration, open the following link in your browser: $verificationLink";

        // Send email
        $mail->send();
        echo "Verification email sent successfully!";
        return true;
    } catch (Exception $e) {
        // Handle errors
        echo "Verification email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the email from the user input
    $email = $_POST['txtEmail1'];
    
    // Generate the token and send the verification email
    $token = generateVerificationToken();
    sendVerificationEmail($email, $token);
}
?>