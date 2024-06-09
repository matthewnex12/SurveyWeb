<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Engine</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .centered-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .button-container {
            text-align: center;
        }
        .button-container a {
            display: block;
            margin: 10px 0;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .button-container a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="centered-container">
        <div class="container">
            <header>
                <h1>Welcome to the Survey Engine</h1>
            </header>
            <div class="button-container">
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            </div>
        </div>
    </div>
</body>
</html>
