<?php
session_start();

// Include config file
require_once "surveyDB.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit;
}

// Fetch user's answers for a specific survey
$user_id = $_GET['user_id'];
$survey_id = $_GET['survey_id'];
$answers = [];

if ($stmt = $mysqli->prepare("SELECT q.text AS question, a.text AS answer FROM Questions q JOIN Answers a ON q.id = a.question_id JOIN Responses r ON a.id = r.answer_id WHERE r.user_id = ? AND q.survey_id = ?")) {
    $stmt->bind_param("ii", $user_id, $survey_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $answers[] = $row;
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Answers</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Make sure this path is correct -->
    <style>
        .answer-item {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>View Answers</h1>
        <div class="scrollable-container">
            <?php foreach ($answers as $answer): ?>
            <div class="answer-item">
                <strong><?php echo htmlspecialchars($answer['question']); ?>:</strong> <?php echo htmlspecialchars($answer['answer']); ?>
            </div>
            <?php endforeach; ?>
        </div>
        <button onclick="window.history.back()" class="btn">Back</button>
    </div>
</body>
</html>