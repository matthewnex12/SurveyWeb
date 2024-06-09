<?php
session_start();

// Include config file
require_once "surveyDB.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo "Session check failed. Redirecting to login.php.<br>"; // Debugging statement
    header('Location: login.php');
    exit;
}

// Initialize or validate survey id
$id = 0;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
} elseif (isset($_POST['id'])) {
    $id = intval($_POST['id']);
}

if ($id <= 0) {
    die("Invalid Survey ID.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete'])) {
        // Handle survey deletion
        if ($stmt = $mysqli->prepare("DELETE FROM answers WHERE question_id IN (SELECT id FROM questions WHERE survey_id = ?)")) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }

        if ($stmt = $mysqli->prepare("DELETE FROM questions WHERE survey_id = ?")) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }

        if ($stmt = $mysqli->prepare("DELETE FROM surveys WHERE id = ?")) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: admin.php");
        exit;
    } elseif (isset($_POST['title'])) {
        // Handle survey update
        $title = $_POST['title'];
        $questions = $_POST['questions'];

        // Update the survey title
        if ($stmt = $mysqli->prepare("UPDATE surveys SET title = ? WHERE id = ?")) {
            $stmt->bind_param("si", $title, $id);
            if ($stmt->execute()) {
                echo "Survey updated successfully.<br>";
            } else {
                echo "Error updating survey: " . $stmt->error . "<br>";
            }
            $stmt->close();
        }

        // Delete marked questions
        if (isset($_POST['deleted_questions'])) {
            foreach ($_POST['deleted_questions'] as $question_id) {
                if ($stmt = $mysqli->prepare("DELETE FROM answers WHERE question_id = ?")) {
                    $stmt->bind_param("i", $question_id);
                    $stmt->execute();
                    $stmt->close();
                }
                if ($stmt = $mysqli->prepare("DELETE FROM questions WHERE id = ? AND survey_id = ?")) {
                    $stmt->bind_param("ii", $question_id, $id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        // Update or insert questions and answers
        foreach ($questions as $question_id => $question_data) {
            $question = $question_data['question'];
            $answers = $question_data['answers'];

            // Check if question_id is new or existing
            if (strpos($question_id, 'new_') === 0) {
                // Insert new question
                if ($stmt = $mysqli->prepare("INSERT INTO questions (survey_id, text) VALUES (?, ?)")) {
                    $stmt->bind_param("is", $id, $question);
                    $stmt->execute();
                    $new_question_id = $stmt->insert_id;
                    $stmt->close();

                    // Insert new answers for the new question
                    foreach ($answers as $answer_text) {
                        if ($stmt = $mysqli->prepare("INSERT INTO answers (question_id, text) VALUES (?, ?)")) {
                            $stmt->bind_param("is", $new_question_id, $answer_text);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }
                }
            } else {
                // Update existing question
                if ($stmt = $mysqli->prepare("UPDATE questions SET text = ? WHERE id = ? AND survey_id = ?")) {
                    $stmt->bind_param("sii", $question, $question_id, $id);
                    $stmt->execute();
                    $stmt->close();
                }

                // Update answers
                foreach ($answers as $answer_id => $answer_text) {
                    if (strpos($answer_id, 'new_') === 0) {
                        // Insert new answer
                        if ($stmt = $mysqli->prepare("INSERT INTO answers (question_id, text) VALUES (?, ?)")) {
                            $stmt->bind_param("is", $question_id, $answer_text);
                            $stmt->execute();
                            $stmt->close();
                        }
                    } else {
                        // Update existing answer
                        if ($stmt = $mysqli->prepare("UPDATE answers SET text = ? WHERE id = ? AND question_id = ?")) {
                            $stmt->bind_param("sii", $answer_text, $answer_id, $question_id);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }
                }
            }
        }

        $mysqli->close();

        // Redirect to avoid resubmission
        header("Location: editSurvey.php?id=" . $id);
        exit;
    }
}

// Load survey details for editing
$title = '';
$questions = [];
if ($stmt = $mysqli->prepare("SELECT title FROM surveys WHERE id = ?")) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($title);
    $stmt->fetch();
    $stmt->close();
}

if ($stmt = $mysqli->prepare("SELECT id, text FROM questions WHERE survey_id = ?")) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $questions[$row['id']] = ['question' => $row['text'], 'answers' => []];
    }
    $stmt->close();
}

foreach ($questions as $question_id => $question_data) {
    if ($stmt = $mysqli->prepare("SELECT id, text FROM answers WHERE question_id = ?")) {
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $questions[$question_id]['answers'][$row['id']] = $row['text'];
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Survey</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Make sure this path is correct -->
    <script>
        function addQuestion() {
            var questionCount = document.querySelectorAll('fieldset').length + 1;
            var newQuestionId = 'new_' + questionCount;
            var questionHtml = `
                <fieldset id="question_${newQuestionId}">
                    <legend>Question ${questionCount}</legend>
                    <div class="form-group">
                        <label for="question_${newQuestionId}">Question:</label>
                        <input type="text" name="questions[${newQuestionId}][question]" required>
                    </div>
                    <div id="answers_${newQuestionId}" class="form-group">
                        <label for="answer_${newQuestionId}_1">Answer 1:</label>
                        <input type="text" name="questions[${newQuestionId}][answers][new_1]" required>
                    </div>
                    <div class="button-group">
                        <button type="button" onclick="addAnswer('${newQuestionId}')">Add Answer</button>
                        <button type="button" onclick="removeQuestion('${newQuestionId}')">Remove Question</button>
                    </div>
                </fieldset>
            `;
            var form = document.querySelector('form');
            form.insertAdjacentHTML('beforeend', questionHtml);
        }

        function addAnswer(questionId) {
            var answersDiv = document.getElementById('answers_' + questionId);
            var answerCount = answersDiv.querySelectorAll('input').length + 1;
            var newAnswerId = 'new_' + answerCount;
            var answerHtml = `
                <label for="answer_${questionId}_${newAnswerId}">Answer ${answerCount}:</label>
                <input type="text" name="questions[${questionId}][answers][${newAnswerId}]" required>
            `;
            answersDiv.insertAdjacentHTML('beforeend', answerHtml);
        }

        function removeQuestion(questionId) {
            var questionFieldset = document.getElementById('question_' + questionId);
            var deletedQuestions = document.getElementById('deleted_questions');
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'deleted_questions[]';
            input.value = questionId;
            deletedQuestions.appendChild(input);
            questionFieldset.parentNode.removeChild(questionFieldset);
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Edit Survey</h1>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $id); ?>" method="post">
            <div class="form-group">
                <label for="title">Survey Title:</label>
                <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>" required>
            </div>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div id="deleted_questions"></div>
            <?php foreach ($questions as $question_id => $question_data): ?>
                <fieldset id="question_<?php echo $question_id; ?>">
                    <legend>Question <?php echo $question_id; ?></legend>
                    <div class="form-group">
                        <label for="question_<?php echo $question_id; ?>">Question:</label>
                        <input type="text" name="questions[<?php echo $question_id; ?>][question]" id="question_<?php echo $question_id; ?>" value="<?php echo htmlspecialchars($question_data['question']); ?>" required>
                    </div>
                    <div id="answers_<?php echo $question_id; ?>" class="form-group">
                        <?php foreach ($question_data['answers'] as $answer_id => $answer_text): ?>
                            <label for="answer_<?php echo $question_id; ?>_<?php echo $answer_id; ?>">Answer <?php echo $answer_id; ?>:</label>
                            <input type="text" name="questions[<?php echo $question_id; ?>][answers][<?php echo $answer_id; ?>]" id="answer_<?php echo $question_id; ?>_<?php echo $answer_id; ?>" value="<?php echo htmlspecialchars($answer_text); ?>" required>
                        <?php endforeach; ?>
                    </div>
                    <div class="button-group">
                        <button type="button" onclick="addAnswer('<?php echo $question_id; ?>')">Add Answer</button>
                        <button type="button" onclick="removeQuestion('<?php echo $question_id; ?>')">Remove Question</button>
                    </div>
                </fieldset>
            <?php endforeach; ?>
            <div class="button-group">
                <button type="button" onclick="addQuestion()">Add Question</button>
            </div>
            <div class="button-group">
                <input type="submit" name="update" value="Update Survey" class="btn">
                <input type="submit" name="delete" value="Delete Survey" class="btn" onclick="return confirm('Are you sure you want to delete this survey? This action cannot be undone.');">
            </div>
        </form>
        <button onclick="window.history.back()" class="btn">Back</button>
    </div>
</body>
</html>