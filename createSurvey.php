<?php
session_start();

require_once 'surveyDB.php'; // Ensure this file contains the correct database connection setup

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Initialize variables
$errorMessage = "";

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["title"])) {
    // Begin database transaction
    $mysqli->begin_transaction();

    try {
        $surveyTitle = $mysqli->real_escape_string(trim($_POST["title"]));
        $creator_id = $_SESSION['user_id'];  // Assumes this is correctly set during login

        // Insert survey
        $insertSurveySql = "INSERT INTO Surveys (title, creator_id) VALUES (?, ?)";
        if ($stmt = $mysqli->prepare($insertSurveySql)) {
            $stmt->bind_param("si", $surveyTitle, $creator_id);
            $stmt->execute();
            $survey_id = $mysqli->insert_id;  // Get the newly created survey ID
            $stmt->close();

            // Insert questions and associated answers
            if (isset($_POST['questions'])) {
                foreach ($_POST['questions'] as $question) {
                    $questionText = $question['question'];
                    $insertQuestionSql = "INSERT INTO Questions (survey_id, text) VALUES (?, ?)";
                    if ($questionStmt = $mysqli->prepare($insertQuestionSql)) {
                        $questionStmt->bind_param("is", $survey_id, $questionText);
                        $questionStmt->execute();
                        $question_id = $mysqli->insert_id; // ID of newly inserted question
                        $questionStmt->close();

                        // Insert answers for each question
                        if (isset($question['answers'])) {
                            foreach ($question['answers'] as $answerText) {
                                $insertAnswerSql = "INSERT INTO Answers (question_id, text) VALUES (?, ?)";
                                if ($answerStmt = $mysqli->prepare($insertAnswerSql)) {
                                    $answerStmt->bind_param("is", $question_id, $answerText);
                                    $answerStmt->execute();
                                    $answerStmt->close();
                                } else {
                                    throw new Exception("Failed to prepare answer insert statement: " . $mysqli->error);
                                }
                            }
                        } else {
                            throw new Exception("No answers provided for question: " . $questionText);
                        }
                    } else {
                        throw new Exception("Failed to prepare question insert statement: " . $mysqli->error);
                    }
                }
            }
            $mysqli->commit();  // Commit transaction
            header("location: admin.php"); // Redirect to admin page or a confirmation page
            exit;
        } else {
            throw new Exception("Failed to prepare survey insert statement: " . $mysqli->error);
        }
    } catch (Exception $e) {
        $mysqli->rollback(); // Rollback transaction on error
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// Close database connection
$mysqli->close();

// Include error handling and display logic if needed
if (!empty($errorMessage)) {
    echo "<p>Error: $errorMessage</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Survey</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Make sure this path is correct -->
    <style>
        .header-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-button-container {
            align-self: flex-start;
        }
    </style>
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
            questionFieldset.parentNode.removeChild(questionFieldset);
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header-container">
            <h1>Create Survey</h1>
            <div class="back-button-container">
                <button onclick="window.location.href='admin.php'" class="btn">Back</button>
            </div>
        </div>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="form-group">
                <label for="title">Survey Title:</label>
                <input type="text" name="title" id="title" required>
            </div>
            <button type="button" onclick="addQuestion()" class="btn">Add Question</button>
            <div class="button-group">
                <input type="submit" value="Create Survey" class="btn">
            </div>
        </form>
    </div>
</body>
</html>