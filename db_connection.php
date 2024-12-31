<?php
// Database connection parameters
$mysql_host = "localhost"; // Hostname, usually localhost
$mysql_database = "SocNet"; // Your database name
$mysql_user = "root"; // Your database username (root is default in XAMPP)
$mysql_password = ""; // Your database password (empty by default in XAMPP)

// Create a connection to the MySQL database
$conn = new mysqli($mysql_host, $mysql_user, $mysql_password, $mysql_database);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>
