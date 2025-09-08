<?php
// connection.php

$host = "localhost";   // Database server (usually localhost)
$user = "root";        // Database username (default in XAMPP/WAMP is root)
$pass = "";            // Database password (empty by default in XAMPP/WAMP)
$db   = "indilet"; // Replace with your database name

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
// Log error (don’t show full details to user in production)
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database connection error. Please try again later.");
}
?>
