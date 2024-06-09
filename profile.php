<?php
session_start();

// Include config file
require_once "surveyDB.php";

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION['id'];

// Fetch user details
if ($stmt = $mysqli->prepare("SELECT username, email FROM Users WHERE id = ?")) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username, $email);
    $stmt->fetch();
    $stmt->close();
}

// Process form submission to update user details
$update_success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_password = trim($_POST['password']);
    
    if (!empty($new_username) && !empty($new_email)) {
        if (!empty($new_password)) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("UPDATE Users SET username = ?, email = ?, password_hash = ? WHERE id = ?");
            $stmt->bind_param("sssi", $new_username, $new_email, $password_hash, $user_id);
        } else {
            $stmt = $mysqli->prepare("UPDATE Users SET username = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $new_username, $new_email, $user_id);
        }
        if ($stmt->execute()) {
            $update_success = "Profile updated successfully.";
        } else {
            $update_success = "Error updating profile.";
        }
        $stmt->close();
    }
    // Refresh user details after update
    if ($stmt = $mysqli->prepare("SELECT username, email FROM Users WHERE id = ?")) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($username, $email);
        $stmt->fetch();
        $stmt->close();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="css/styles.css">
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
</head>
<body>
    <div class="container">
        <div class="header-container">
            <h1>My Profile</h1>
            <div class="back-button-container">
                <button onclick="window.location.href='userHome.php'" class="btn">Back</button>
            </div>
        </div>
        <p><strong>ID:</strong> <?php echo htmlspecialchars($user_id); ?></p>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>

        <?php if (!empty($update_success)): ?>
        <p class="success-message"><?php echo $update_success; ?></p>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="username">New Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">New Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">New Password (leave blank to keep current):</label>
                <input type="password" id="password" name="password">
            </div>
            <div class="form-group">
                <button type="submit">Update Profile</button>
            </div>
        </form>
    </div>
</body>
</html>
