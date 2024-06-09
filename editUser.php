<?php
session_start();

// Include config file
require_once "surveyDB.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit;
}

// Get the user data from the form
$user_id = $_POST['user_id'];
$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];

// Update the user data in the database
if ($stmt = $mysqli->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?")) {
    $stmt->bind_param("ssi", $username, $email, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Update the password if it was provided
if (!empty($password)) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    if ($stmt = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE id = ?")) {
        $stmt->bind_param("si", $password_hash, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Redirect back to the admin page
header('Location: admin.php');
exit;
?>