<?php
include("../config/db.php");

$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$sql = "INSERT INTO users (Name, Email, Phone, PasswordHash, Role)
        VALUES ('$name', '$email', '$phone', '$password', 'USER')";

if (mysqli_query($conn, $sql)) {
    header("Location: ../login.html");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>