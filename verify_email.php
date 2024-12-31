<?php
session_start();
$mysql_host = "localhost";
$mysql_database = "SocNet";
$mysql_user = "root";
$mysql_password = "";

$connection = mysqli_connect($mysql_host, $mysql_user, $mysql_password, $mysql_database);

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Verify token in database
    $stmt = $connection->prepare("SELECT * FROM SystemUser WHERE verification_token = ? AND is_verified = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update user as verified
        $updateStmt = $connection->prepare("UPDATE systemuser SET is_verified = 1, verification_token = NULL WHERE verification_token = ?");
        $updateStmt->bind_param("s", $token);
        
        if ($updateStmt->execute()) {
            // Store success message in session to be displayed on verification success page
            $_SESSION['verification_success'] = true;
            header("Location: verification_success.php");
            exit();
        } else {
            $_SESSION['verification_error'] = 'Error updating verification status.';
            header("Location: verification_success.php");
            exit();
        }
    } else {
        $_SESSION['verification_error'] = 'Invalid or expired verification token.';
        header("Location: verification_success.php");
        exit();
    }
}
?>