<?php
// Use Railway-provided environment variables (from the linked MySQL plugin)
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

// Ensure all required variables are set
if (!$host || !$user || !$pass || !$db || !$port) {
    die("Missing database environment variables. Please check Railway MySQL plugin linkage.");
}
var_dump(getenv('MYSQLHOST'), getenv('MYSQLUSER'), getenv('MYSQLDATABASE'));// Debugging line to verify environment variables

// Establish connection
$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set charset to handle special characters correctly
mysqli_set_charset($conn, "utf8mb4");
?>
