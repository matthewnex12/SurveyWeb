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

// Load survey completion data from JSON file
$completionData = [];
$jsonFilePath = 'surveyCompletion.json';
if (file_exists($jsonFilePath)) {
    $jsonContent = file_get_contents($jsonFilePath);
    $completionData = json_decode($jsonContent, true);
}

// Fetch and sort surveys by completion time
$surveyList = [];
while ($survey = $surveys->fetch_assoc()) {
    $completionTime = 'Not taken';
    if (isset($completionData[$_SESSION['id']][$survey['id']])) {
        $completionTime = date('Y-m-d H:i:s', strtotime($completionData[$_SESSION['id']][$survey['id']]));
    }
    $survey['completion_time'] = $completionTime;
    $surveyList[] = $survey;
}

usort($surveyList, function($a, $b) {
    if ($a['completion_time'] == 'Not taken') return 1;
    if ($b['completion_time'] == 'Not taken') return -1;
    return strtotime($b['completion_time']) - strtotime($a['completion_time']);
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take a Survey</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Ensure this path is correct -->
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
            margin: 5px 0;
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
            <h1>Take a Survey</h1>
            <div class="back-button-container">
                <button onclick="window.history.back()" class="btn">Back</button>
            </div>
        </div>
        <input type="text" id="searchBar" onkeyup="filterSurveys()" placeholder="Search for surveys...">
        <div class="scrollable-container" id="surveyContainer">
            <ul>
                <?php foreach ($surveyList as $survey): ?>
                <li class="survey-item">
                    <span><?php echo htmlspecialchars($survey['title']); ?></span>
                    <span><?php echo $survey['completion_time']; ?></span>
                    <a href="startSurvey.php?survey_id=<?php echo $survey['id']; ?>"><button type="button" class="btn">Take</button></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</body>
</html>
