<?php
// loadProgress.php

// Function to get a cookie value
function getCookie($name) {
    if (isset($_COOKIE[$name])) {
        return $_COOKIE[$name];
    }
    return null;
}

// Function to convert JSON string to PHP object
function getObjectFromCookie($name) {
    $cookieValue = getCookie($name);
    if ($cookieValue) {
        return json_decode($cookieValue, true);
    }
    return null;
}

// Function to load survey progress
function loadSurveyProgress($surveyId) {
    $progress = getObjectFromCookie('surveyProgress_' . $surveyId);
    if ($progress) {
        return $progress['answers'];
    }
    return [];
}

// Example usage: Load survey progress when the survey page loads
$surveyId = $_GET['surveyId'];
$answers = loadSurveyProgress($surveyId);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Survey</title>
</head>
<body>
    <form id="surveyForm">
        <!-- Survey questions will be loaded here -->
        <?php foreach ($questions as $question): ?>
            <div class="survey-question" data-question-id="<?php echo $question['id']; ?>">
                <p><?php echo $question['text']; ?></p>
                <?php foreach ($question['answers'] as $answer): ?>
                    <label>
                        <input type="radio" name="question_<?php echo $question['id']; ?>" value="<?php echo $answer['id']; ?>"
                            <?php echo isset($answers[$question['id']]) && $answers[$question['id']] == $answer['id'] ? 'checked' : ''; ?>>
                        <?php echo $answer['text']; ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </form>
    <button id="saveProgressButton">Save Progress</button>
    <input type="hidden" id="surveyId" value="<?php echo $surveyId; ?>">
    <script src="saveProgress.js"></script>
</body>
</html>
