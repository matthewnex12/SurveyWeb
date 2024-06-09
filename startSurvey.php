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

// Fetch the survey title
if ($stmt = $mysqli->prepare("SELECT title FROM Surveys WHERE id = ?")) {
    $stmt->bind_param("i", $survey_id);
    $stmt->execute();
    $stmt->bind_result($survey_title);
    $stmt->fetch();
    $stmt->close();
}

// Fetch survey questions and answers
$questions = [];
if ($stmt = $mysqli->prepare("SELECT id, text FROM Questions WHERE survey_id = ?")) {
    $stmt->bind_param("i", $survey_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $questions[$row['id']] = ['question' => $row['text'], 'answers' => []];
    }
    $stmt->close();
}

foreach ($questions as $question_id => $question_data) {
    if ($stmt = $mysqli->prepare("SELECT id, text FROM Answers WHERE question_id = ?")) {
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $questions[$question_id]['answers'][$row['id']] = $row['text'];
        }
        $stmt->close();
    }
}

// Function to get a cookie value
function getCookie($name) {
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
}

// Function to convert JSON string to PHP object
function getObjectFromCookie($name) {
    $cookieValue = getCookie($name);
    return $cookieValue ? json_decode($cookieValue, true) : null;
}

// Load saved progress
$savedProgress = getObjectFromCookie('surveyProgress_' . $survey_id);
$savedAnswers = $savedProgress ? $savedProgress['answers'] : [];
$savedAt = isset($savedProgress['savedAt']) ? $savedProgress['savedAt'] : 'Unknown time';

if ($savedProgress) {
    //echo '<pre>'; print_r($savedProgress); echo '</pre>'; // Debugging message
    //echo "<p>Loaded progress from cookies saved at: " . htmlspecialchars($savedAt) . "</p>";
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id'];
    foreach ($_POST['answers'] as $question_id => $answer_id) {
        // Insert the response
        if ($stmt = $mysqli->prepare("INSERT INTO Responses (user_id, question_id, answer_id) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE answer_id = VALUES(answer_id)")) {
            $stmt->bind_param("iii", $user_id, $question_id, $answer_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Load existing data from JSON file
    $jsonFilePath = 'surveyCompletion.json';
    $completionData = [];
    if (file_exists($jsonFilePath)) {
        $jsonContent = file_get_contents($jsonFilePath);
        $completionData = json_decode($jsonContent, true);
    }

    // Update completion time
    if (!isset($completionData[$user_id])) {
        $completionData[$user_id] = [];
    }
    $completionData[$user_id][$survey_id] = date('Y-m-d H:i:s');

    // Write data back to JSON file
    file_put_contents($jsonFilePath, json_encode($completionData));

    header('Location: thankYou.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($survey_title); ?></title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Ensure this path is correct -->
</head>
<body>
    <div class="container">
        <?php if ($savedProgress): ?>
            <div class="alert alert-info">
                Survey progress loaded from cookies. Last saved at: <?php echo htmlspecialchars($savedAt); ?>
            </div>
        <?php endif; ?>
        <h1><?php echo htmlspecialchars($survey_title); ?></h1>
        <form method="post">
            <?php foreach ($questions as $question_id => $question_data): ?>
                <fieldset>
                    <legend><?php echo htmlspecialchars($question_data['question']); ?></legend>
                    <?php foreach ($question_data['answers'] as $answer_id => $answer_text): ?>
                        <label>
                            <input type="radio" name="answers[<?php echo $question_id; ?>]" value="<?php echo $answer_id; ?>"
                                <?php echo isset($savedAnswers["answers[$question_id]"]) && $savedAnswers["answers[$question_id]"] == $answer_id ? 'checked' : ''; ?> required>
                            <?php echo htmlspecialchars($answer_text); ?>
                        </label><br>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>
            <div class="form-group">
                <input type="submit" value="Submit" class="btn">
            </div>
        </form>
        <button id="saveProgressButton" class="btn">Save Progress</button>
        <button onclick="window.history.back()" class="btn">Back</button>
        <input type="hidden" id="surveyId" value="<?php echo $survey_id; ?>">
    </div>
    <script src="saveProgress.js"></script>
</body>
</html>
