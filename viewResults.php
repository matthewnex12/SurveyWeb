<?php
session_start();

// Include config file
require_once "surveyDB.php";

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Fetch surveys taken by the user
$user_id = $_SESSION['id'];
$surveys_taken = [];
if ($stmt = $mysqli->prepare("SELECT DISTINCT s.id, s.title FROM Surveys s JOIN Questions q ON s.id = q.survey_id JOIN Responses r ON q.id = r.question_id WHERE r.user_id = ?")) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $surveys_taken[] = $row;
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View My Results</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>View My Results</h1>
        <div class="scrollable-container">
            <?php foreach ($surveys_taken as $survey): ?>
            <div class="survey-item">
                <span><?php echo htmlspecialchars($survey['title']); ?></span>
                <a href="viewSurveyUser.php?survey_id=<?php echo $survey['id']; ?>"><button type="button">View</button></a>
            </div>
            <?php endforeach; ?>
        </div>
        <button onclick="window.location.href='userHome.php'">Back</button>
    </div>
</body>
</html>