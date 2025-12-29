<?php
include("../config/db.php");

$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// 1. Prepare the statement structure (use ? as placeholders)
$stmt = mysqli_prepare($conn, "INSERT INTO users (Name, Email, Phone, PasswordHash, Role) VALUES (?, ?, ?, ?, 'USER')");

if ($stmt) {
    // 2. Bind parameters (s = string)
    // "ssss" means 4 strings: name, email, phone, password
    mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $phone, $password);

    // 3. Execute
    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../login.html?success=registered");
        exit();
    } else {
        echo "Error: " . mysqli_stmt_error($stmt);
    }
    
    // Close statement
    mysqli_stmt_close($stmt);
} else {
    echo "Database Error: " . mysqli_error($conn);
}
?>