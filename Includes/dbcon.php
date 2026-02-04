<?php
// Start secure session


// Secure session configurations
ini_set('session.cookie_httponly', 1);     // Prevent JS access to cookies
ini_set('session.use_only_cookies', 1);    // Prevent session fixation
// Enable this ONLY if you are using HTTPS
// ini_set('session.cookie_secure', 1);

// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$db   = "attendancemsystem";

// Create database connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
