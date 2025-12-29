<?php
include("../config/db.php");
session_start();

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE Email='$email'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if ($user && password_verify($password, $user['PasswordHash'])) {

    $_SESSION['user_id'] = $user['UserID'];
    $_SESSION['role'] = $user['Role'];

    if ($user['Role'] == 'USER') {
        header("Location: ../user/dashboard.php");
    } elseif ($user['Role'] == 'ADMIN') {
        header("Location: ../admin/dashboard.php");
    } elseif ($user['Role'] == 'POLICE') {
        header("Location: ../officer/dashboard.php");
    }

} else {
    echo "Invalid credentials";
}
?>
