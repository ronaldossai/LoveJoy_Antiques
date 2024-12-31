<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MultiFactorAuth {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    /**
     * Generate a 6-digit Time-Based One-Time Password (TOTP)
     */
    public function generateTOTP($userId, $userEmail) {
        // Generate a 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Calculate expiration time (15 minutes from now)
        $expiration = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Remove any existing codes for this user first
        $deleteStmt = $this->conn->prepare("DELETE FROM user_mfa WHERE user_id = ?");
        $deleteStmt->bind_param("i", $userId);
        $deleteStmt->execute();

        // Insert new code
        $stmt = $this->conn->prepare("INSERT INTO user_mfa (user_id, code, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $code, $expiration);
        
        // If insertion fails, return false
        if (!$stmt->execute()) {
            return false;
        }

        // Send email with the code
        return $this->sendVerificationEmail($userEmail, $code);
    }

    /**
     * Verify the Multi-Factor Authentication code
     */
    public function verifyMFACode($userId, $providedCode) {
        // Prepare SQL to check code
        $stmt = $this->conn->prepare("SELECT code, expires_at FROM user_mfa
                                      WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Check if a valid code exists
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $storedCode = $row['code'];
            $expiresAt = $row['expires_at'];
    
            // Check if the provided code matches the stored code and is not expired
            if ($providedCode == $storedCode && $expiresAt > date('Y-m-d H:i:s')) {
                // Delete the used code
                $deleteStmt = $this->conn->prepare("DELETE FROM user_mfa WHERE user_id = ?");
                $deleteStmt->bind_param("i", $userId);
                $deleteStmt->execute();
    
                return true;
            }
        }
    
        return false;
    }

    /**
     * Send verification email with MFA code
     */
    private function sendVerificationEmail($email, $code) {
        // Configure PHPMailer for Gmail SMTP
        $mail = new PHPMailer(true);

        try {
            // SMTP configuration for Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'ronjjossai@gmail.com';
            $mail->Password   = 'yfujbsviigjphpgq';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Email content
            $mail->setFrom('ronjjossai@gmail.com', 'LoveJoy Antiques');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Your Verification Code';
            $mail->Body    = "
                <html>
                <body>
                    <h2>Your Verification Code</h2>
                    <p>Your 6-digit verification code is: <strong>{$code}</strong></p>
                    <p>This code will expire in 15 minutes.</p>
                </body>
                </html>
            ";

            // Send email
            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Cleanup expired MFA codes
     */
    public function cleanupExpiredCodes() {
        $stmt = $this->conn->prepare("DELETE FROM user_mfa WHERE expires_at < NOW()");
        $stmt->execute();
    }
}