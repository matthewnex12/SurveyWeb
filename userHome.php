<?php
session_start();

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Optional: Load other user-specific data from the database
require_once "surveyDB.php";
// Example: Fetch some data or perform other initialization tasks

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Home</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>User Dashboard</h1>
        <button onclick="window.location.href='takeSurvey.php'">Take a Survey</button>
        <button onclick="window.location.href='viewResults.php'">View My Results</button>
        <button onclick="window.location.href='profile.php'">My Profile</button>
        <button onclick="window.location.href='logoutHandler.php'">Logout</button>
    </div>
</body>
</html>