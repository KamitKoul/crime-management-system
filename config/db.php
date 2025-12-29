<?php
// Railway provides these variables automatically when you link a MySQL plugin
$host = getenv('MYSQLHOST') ?: "localhost";
$user = getenv('MYSQLUSER') ?: "root";
$pass = getenv('MYSQLPASSWORD') ?: "";
$db   = getenv('MYSQLDATABASE') ?: "crime_management";
$port = getenv('MYSQLPORT') ?: "3306";

// Establish connection
$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set charset to handle special characters correctly
mysqli_set_charset($conn, "utf8mb4");
?>
