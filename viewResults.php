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

// Load survey completion data from JSON file
$completionData = [];
$jsonFilePath = 'surveyCompletion.json';
if (file_exists($jsonFilePath)) {
    $jsonContent = file_get_contents($jsonFilePath);
    $completionData = json_decode($jsonContent, true);
}

// Add completion times to surveys and sort them
foreach ($surveys_taken as &$survey) {
    $survey['completion_time'] = isset($completionData[$user_id][$survey['id']]) ? $completionData[$user_id][$survey['id']] : null;
}

usort($surveys_taken, function($a, $b) {
    if ($a['completion_time'] === $b['completion_time']) {
        return 0;
    }
    return ($a['completion_time'] > $b['completion_time']) ? -1 : 1;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View My Results</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .header-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }
        .survey-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .survey-item span {
            flex-grow: 1;
            margin-right: 10px;
        }
        #searchBar {
            width: calc(100% - 22px); /* Match the width of the scrollable container, minus padding and border */
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .scrollable-container {
            max-height: 400px; /* Set the max height to make it scrollable */
            overflow-y: auto; /* Enable vertical scrolling */
            border: 1px solid #ccc; /* Optional: Add a border for visual distinction */
            padding: 10px; /* Optional: Add padding inside the scrollable container */
            margin: 10px 0; /* Add margin to the container */
            border-radius: 5px; /* Add border radius */
        }
        .back-button-container {
            align-self: flex-start;
        }
    </style>
    <script>
        function filterSurveys() {
            var input, filter, container, items, title, i;
            input = document.getElementById('searchBar');
            filter = input.value.toUpperCase();
            container = document.getElementById('surveyContainer');
            items = container.getElementsByClassName('survey-item');

            // Create an array of items that match the filter
            var matchingItems = [];
            var nonMatchingItems = [];
            for (i = 0; i < items.length; i++) {
                title = items[i].getElementsByTagName('span')[0];
                if (title) {
                    if (title.innerHTML.toUpperCase().startsWith(filter)) {
                        matchingItems.push(items[i]);
                    } else {
                        nonMatchingItems.push(items[i]);
                    }
                }
            }

            // Clear the container and append matching items at the top
            container.innerHTML = '';
            for (i = 0; i < matchingItems.length; i++) {
                container.appendChild(matchingItems[i]);
            }
            for (i = 0; i < nonMatchingItems.length; i++) {
                container.appendChild(nonMatchingItems[i]);
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header-container">
            <h1>View My Results</h1>
            <div class="back-button-container">
                <button onclick="window.location.href='userHome.php'" class="btn">Back</button>
            </div>
        </div>
        <input type="text" id="searchBar" onkeyup="filterSurveys()" placeholder="Search for surveys...">
        <div class="scrollable-container" id="surveyContainer">
            <?php foreach ($surveys_taken as $survey): ?>
            <div class="survey-item">
                <span><?php echo htmlspecialchars($survey['title']); ?></span>
                <?php
                $completionTime = 'Not completed';
                if (!empty($survey['completion_time'])) {
                    $completionTime = date('Y-m-d H:i:s', strtotime($survey['completion_time']));
                }
                ?>
                <span><?php echo $completionTime; ?></span>
                <a href="viewSurveyUser.php?survey_id=<?php echo $survey['id']; ?>"><button type="button">View</button></a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
