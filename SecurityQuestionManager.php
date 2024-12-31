<?php
class SecurityQuestionManager {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

   // Validate the secuirty question is in the database
    public function validateSecurityQuestions($userId, $questionId, $providedAnswer) {
        $stmt = $this->conn->prepare("SELECT answer FROM securityquestions
                                      WHERE user_id = ? AND id = ? AND attempts_left > 0");
        $stmt->bind_param("ii", $userId, $questionId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['valid' => false, 'message' => 'Invalid question or no attempts left'];
        }

        $row = $result->fetch_assoc();
        
        if (password_verify($providedAnswer, $row['answer'])) {
            return ['valid' => true, 'message' => 'Answer correct'];
        }

        $this->decrementAttempts($questionId);
        return ['valid' => false, 'message' => 'Incorrect answer'];
    }

    // Decrement the number of attempts left for a security question
    private function decrementAttempts($questionId) {
        $stmt = $this->conn->prepare("UPDATE securityquestions
                                      SET attempts_left = GREATEST(0, attempts_left - 1)
                                      WHERE id = ?");
        $stmt->bind_param("i", $questionId);
        $stmt->execute();
    }

    // Reset the number of attempts left for all security questions for a user
    public function resetQuestionAttempts($userId) {
        $stmt = $this->conn->prepare("UPDATE securityquestions
                                      SET attempts_left = 3
                                      WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
    }
}
?>