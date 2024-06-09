<?php
session_start();

// Include config file
require_once "surveyDB.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit;
}

// Fetch user details
$user_id = $_GET['id'];
if ($stmt = $mysqli->prepare("SELECT username, email, is_admin FROM Users WHERE id = ?")) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username, $email, $is_admin);
    $stmt->fetch();
    $stmt->close();
} else {
    die("Error fetching user details.");
}

// Fetch surveys taken by the user
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
    <title>View User</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Ensure this path is correct -->
    <style>
        .hidden {
            display: none;
        }
    </style>
    <script>
        function toggleEditForm() {
            var form = document.getElementById('edit-user-form');
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>View User</h1>
        <form method="post" action="deleteUser.php" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');" class="delete-button">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <input type="submit" value="Delete User" class="btn">
        </form>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        <p><strong>Admin:</strong> <?php echo $is_admin ? 'Yes' : 'No'; ?></p>
        <button type="button" onclick="toggleEditForm()" class="btn">Edit</button>
        <form id="edit-user-form" class="hidden" method="post" action="editUser.php">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required value="<?php echo htmlspecialchars($username); ?>">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($email); ?>">
            </div>
            <div class="form-group">
                <label for="password">Password (leave blank to keep current):</label>
                <input type="password" name="password" id="password">
            </div>
            <div class="button-group">
                <input type="submit" value="Update User" class="btn">
                <button type="button" onclick="toggleEditForm()" class="btn">Cancel</button>
            </div>
        </form>
        <h2>Surveys Taken</h2>
        <div class="scrollable-container">
            <?php foreach ($surveys_taken as $survey): ?>
            <div class="survey-item">
                <span><?php echo htmlspecialchars($survey['title']); ?></span>
                <a href="viewAnswers.php?user_id=<?php echo $user_id; ?>&survey_id=<?php echo $survey['id']; ?>"><button type="button" class="btn">View Answers</button></a>
            </div>
            <?php endforeach; ?>
        </div>
        <button onclick="window.history.back()" class="btn">Back</button>
    </div>
</body>
</html>