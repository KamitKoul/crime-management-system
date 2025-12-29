<?php
include("config/db.php");

echo "<h1>Creating Default Officer</h1>";

// Check if an officer already exists
$checkSql = "SELECT * FROM users WHERE Role = 'OFFICER' OR Role = 'POLICE' LIMIT 1";
$result = mysqli_query($conn, $checkSql);

if (mysqli_num_rows($result) > 0) {
    echo "<p style='color:orange;'>An officer already exists in the database.</p>";
    $officer = mysqli_fetch_assoc($result);
    echo "Name: " . $officer['Name'] . "<br>Email: " . $officer['Email'];
} else {
    // Insert a new officer
    $name = "Officer John Doe";
    $email = "officer@cms.com";
    $phone = "9876543210";
    $password = password_hash("officer123", PASSWORD_DEFAULT);
    $role = "OFFICER"; // Ensure this matches your enum ('POLICE' or 'OFFICER')

    // First check what the ENUM allows
    $enumCheck = mysqli_query($conn, "DESCRIBE users Role");
    $row = mysqli_fetch_assoc($enumCheck);
    
    // If ENUM contains 'POLICE', use that, otherwise use 'OFFICER'
    if (strpos($row['Type'], "'POLICE'") !== false) {
        $role = "POLICE";
    }

    $sql = "INSERT INTO users (Name, Email, Phone, PasswordHash, Role) 
            VALUES ('$name', '$email', '$phone', '$password', '$role')";

    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green;'>Success! Created officer:</p>";
        echo "<strong>Email:</strong> officer@cms.com<br>";
        echo "<strong>Password:</strong> officer123";
    } else {
        echo "<p style='color:red;'>Error creating officer: " . mysqli_error($conn) . "</p>";
    }
}
echo "<p><a href='admin/dashboard.php'>Go to Admin Dashboard</a></p>";
?>