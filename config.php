<?php
// Database configuration
$db_host = 'localhost';      // Replace with your database host
$db_user = 'user';           // Replace with your database username
$db_pass = '10190919Ifp';               // Replace with your database password
$db_name = 'php_pokemon';        // Replace with your database name

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to ensure proper display of special characters
$conn->set_charset("utf8mb4");
?>