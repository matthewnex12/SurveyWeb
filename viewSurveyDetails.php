<?php
session_start();

// Include config file
require_once "surveyDB.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit;
}

// Check if survey_id is set in the query parameter
if (!isset($_GET['survey_id'])) {
    die("Survey ID not specified.");
}

// Get the survey ID from the query parameter
$survey_id = intval($_GET['survey_id']);

// Fetch survey details
function fetchSurveyDetails($mysqli, $survey_id) {
    $sql = "SELECT q.id AS question_id, q.text AS question_text, a.id AS answer_id, a.text AS answer_text,
            (SELECT COUNT(*) FROM Responses r WHERE r.answer_id = a.id) AS answer_count
            FROM Questions q
            JOIN Answers a ON q.id = a.question_id
            WHERE q.survey_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $survey_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $details = [];
    while ($row = $result->fetch_assoc()) {
        $details[] = $row;
    }
    $stmt->close();
    return $details;
}

$survey_details = fetchSurveyDetails($mysqli, $survey_id);

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
                        <?php echo htmlspecialchars($detail['answer_text']); ?> - <?php echo $detail['answer_count']; ?> selections
                    </div>
            <?php endforeach; ?>
                </div>
        </div>
        <button onclick="window.history.back()" class="btn">Back</button>
    </div>
</body>
</html>