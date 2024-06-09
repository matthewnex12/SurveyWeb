<?php
// Database credentials
$host = 'localhost'; // or IP address like '127.0.0.1'
$db_name = 'personalsurvey'; // your database name
$db_user = 'root'; // your database username
$db_password = ''; // your database password

// Create a new mysqli connection instance
$mysqli = new mysqli($host, $db_user, $db_password, $db_name);

// Check the connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Optionally, you can set the charset, useful for ensuring UTF-8 is used:
$mysqli->set_charset("utf8");

// Use this global $mysqli variable in other PHP scripts to perform database operations
?>
