<?php
include("../config/db.php");
session_start();

$email = $_POST['email'];
$password = $_POST['password'];

// Secure Prepared Statement
$stmt = mysqli_prepare($conn, "SELECT UserID, Role, PasswordHash FROM users WHERE Email=?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user && password_verify($password, $user['PasswordHash'])) {

    $_SESSION['user_id'] = $user['UserID'];
    $_SESSION['role'] = $user['Role'];

    if ($user['Role'] == 'USER') {
        header("Location: ../user/dashboard.php");
    } elseif ($user['Role'] == 'ADMIN') {
        header("Location: ../admin/dashboard.php");
    } elseif ($user['Role'] == 'POLICE' || $user['Role'] == 'OFFICER') {
        header("Location: ../officer/dashboard.php");
    }

} else {
    header("Location: ../login.html?error=invalid");
    exit();
}
?>
