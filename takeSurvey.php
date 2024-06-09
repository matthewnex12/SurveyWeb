<?php
session_start();

// Include config file
require_once "surveyDB.php";

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Fetch all surveys
function fetchAllSurveys($mysqli) {
    $sql = "SELECT id, title FROM surveys";
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
    <title>Take a Survey</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Ensure this path is correct -->
    <style>
        .scrollable-container ul {
            list-style-type: none;
            padding: 0;
        }
        .scrollable-container li {
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Take a Survey</h1>
        <div class="scrollable-container">
            <ul>
                <?php while ($survey = $surveys->fetch_assoc()): ?>
                <li>
                    <?php echo htmlspecialchars($survey['title']); ?>
                    <a href="startSurvey.php?survey_id=<?php echo $survey['id']; ?>"><button type="button" class="btn">Take</button></a>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>
        <button onclick="window.history.back()" class="btn">Back</button>
    </div>
</body>
</html>