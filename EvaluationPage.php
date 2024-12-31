<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Form</title>
    <link rel="stylesheet" href="EvaluationPageCSS.css">
</head>
<body>
    <!-- Logout Button -->
    <div style="text-align: right; margin-bottom: 20px;">
        <form action="logout.php" method="POST" style="display: inline;">
            <button type="submit" style="padding: 10px 20px; background-color: red; color: white; border: none; cursor: pointer;">
                Logout
            </button>
        </form>
    </div>

    <h2>Submit Your Evaluation</h2>

    <?php
    // Check if a success message is passed via the query string (after form submission)
    if (isset($_GET['status']) && $_GET['status'] === 'success') {
        echo "<p>Evaluation submitted successfully! Submit another below.</p>";
    }
    ?>

    <form action="SubmitEvaluation.php" method="POST" enctype="multipart/form-data">
        <label for="comment">Your Comment:</label><br>
        <textarea id="comment" name="comment" rows="4" cols="50" required></textarea><br><br>

        <label for="rating">Rating (1 to 5):</label><br>
        <select id="rating" name="rating" required>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
        </select><br><br>

        <label for="contact_preference">Contact Preference (Email or Telephone):</label><br>
        <select id="contact_preference" name="contact_preference" required>
            <option value="email">Email</option>
            <option value="telephone">Telephone</option>
        </select><br><br>

        <label for="file">Upload File (Optional):</label><br>
        <input type="file" id="file" name="file" accept=".jpg,.jpeg,.png,.pdf"><br><br>

        <input type="submit" value="Submit Evaluation">
    </form>
</body>
</html>
