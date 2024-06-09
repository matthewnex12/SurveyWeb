<?php
session_start();

// Include config file
require_once "surveyDB.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit;
}

// Fetch all surveys
function fetchAllSurveys($mysqli) {
    $sql = "SELECT id, title FROM Surveys";
    $result = $mysqli->query($sql);
    return $result;
}

$surveys = fetchAllSurveys($mysqli);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Surveys</title>
    <style>
        .scrollable-container {
            height: 400px;
            overflow-y: scroll;
            border: 1px solid #ccc;
            padding: 10px;
            margin: 10px 0;
        }
        .survey-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>View Surveys</h1>
    <div class="scrollable-container">
        <?php while ($survey = $surveys->fetch_assoc()): ?>
        <div class="survey-item">
            <span><?php echo htmlspecialchars($survey['title']); ?></span>
            <a href="viewSurveyDetails.php?survey_id=<?php echo $survey['id']; ?>"><button type="button">View</button></a>
        </div>
        <?php endwhile; ?>
    </div>
    <button onclick="window.history.back()">Back</button>
</body>
</html>
