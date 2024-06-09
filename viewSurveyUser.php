<?php
session_start();

// Include config file
require_once "surveyDB.php";

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Get the survey ID from the query parameter
$survey_id = $_GET['survey_id'];
$user_id = $_SESSION['id'];

// Fetch survey details and user's answers
$survey_details = [];
if ($stmt = $mysqli->prepare("SELECT q.id AS question_id, q.text AS question_text, a.id AS answer_id, a.text AS answer_text,
            (SELECT r.answer_id FROM Responses r WHERE r.question_id = q.id AND r.user_id = ?) AS user_answer
            FROM Questions q
            JOIN Answers a ON q.id = a.question_id
            WHERE q.survey_id = ?")) {
    $stmt->bind_param("ii", $user_id, $survey_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $survey_details[] = $row;
    }
    $stmt->close();
}

// Process form submission to update answers
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST['answers'] as $question_id => $answer_id) {
        if ($stmt = $mysqli->prepare("INSERT INTO Responses (user_id, question_id, answer_id) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE answer_id = VALUES(answer_id)")) {
            $stmt->bind_param("iii", $user_id, $question_id, $answer_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    header('Location: userHome.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Details</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Ensure this path is correct -->
    <style>
        .question-item {
            margin-bottom: 20px;
        }
        .answer-item {
            margin-left: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Survey Details</h1>
        <form method="post">
            <div class="scrollable-container">
                <?php
                $current_question_id = 0;
                foreach ($survey_details as $detail): 
                    if ($current_question_id != $detail['question_id']):
                        if ($current_question_id != 0):
                            echo "</div>";
                        endif;
                        $current_question_id = $detail['question_id'];
                ?>
                    <div class="question-item">
                        <strong><?php echo htmlspecialchars($detail['question_text']); ?></strong>
                <?php endif; ?>
                        <div class="answer-item">
                            <label>
                                <input type="radio" name="answers[<?php echo $detail['question_id']; ?>]" value="<?php echo $detail['answer_id']; ?>" <?php echo ($detail['user_answer'] == $detail['answer_id']) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($detail['answer_text']); ?>
                            </label>
                        </div>
                <?php endforeach; ?>
                    </div>
            </div>
            <div class="form-group">
                <input type="submit" value="Update Answers" class="btn">
            </div>
        </form>
        <button onclick="window.location.href='userHome.php'" class="btn">Back</button>
    </div>
</body>
</html>