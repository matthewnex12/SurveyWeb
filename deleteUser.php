<?php
session_start();

// Include config file
require_once "surveyDB.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit;
}

// Get the user ID from the form
$user_id = $_POST['user_id'];

// Delete the user from the database
if ($stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?")) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

// Redirect back to the admin page
header('Location: admin.php');
exit;
?>