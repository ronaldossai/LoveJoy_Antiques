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

// Query to fetch evaluations (removed username join)
$query = "SELECT * FROM evaluation_db ORDER BY uploaded_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Evaluations</title>
    <link rel="stylesheet" href="adminstylesCSS.css">
</head>
<body>
    <h2>List of Evaluations</h2>

    <table border="1">
        <thead>
            <tr>
                <th>Comment</th>
                <th>Rating</th>
                <th>File</th>
                <th>Uploaded At</th>
                <th>Contact Preference</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Check if any records exist
            if ($result->num_rows > 0) {
                // Output data of each row
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['comment']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['rating']) . "</td>";
                    echo "<td>";
                    // Check if a file is uploaded and link it
                    if (!empty($row['file_name'])) {
                        echo "<a href='uploads/" . htmlspecialchars($row['file_name']) . "' target='_blank'>Download</a>";
                    } else {
                        echo "No file uploaded";
                    }
                    echo "</td>";
                    echo "<td>" . htmlspecialchars($row['uploaded_at']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['contact_preference']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No evaluations found</td></tr>";
            }

            // Close connection
            $conn->close();
            ?>
        </tbody>
    </table>

    <!-- Logout Button -->
    <div style="text-align: right; margin-top: 20px;">
        <form action="logout.php" method="POST" style="display: inline;">
            <button type="submit" style="padding: 10px 20px; background-color: red; color: white; border: none; cursor: pointer;">
                Logout
            </button>
        </form>
    </div>
</body>
</html>