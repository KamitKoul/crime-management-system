<?php
// Use Railway-provided environment variables (from the MySQL service)
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

// Ensure all required variables are set, or fall back to local defaults
if (!$host || !$user || !$db || !$port) {
    // Local Development Fallback
    $host = 'localhost';
    $user = 'root';
    $pass = ''; // Default XAMPP password is empty
    $db   = 'crime_management';
    $port = 3306;
}

// Debugging line (optional) – shows values to confirm they’re set
// Remove this in production
// var_dump($host, $user, $db, $port);

// Establish connection
$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set charset to handle special characters correctly
mysqli_set_charset($conn, "utf8mb4");
?>
