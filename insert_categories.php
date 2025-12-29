<?php
include("config/db.php");

echo "<h1>Inserting Default Categories</h1>";

$categories = [
    "Theft",
    "Assault",
    "Traffic Accident",
    "Vandalism",
    "Harassment",
    "Homicide",
    "Robbery",
    "Burglary",
    "Fraud",
    "Kidnapping"
];

$insertedCount = 0;
foreach ($categories as $catName) {
    // Check if category already exists
    $checkSql = "SELECT CategoryID FROM crimecategory WHERE CategoryName = '$catName'";
    $checkResult = mysqli_query($conn, $checkSql);
    
    if (mysqli_num_rows($checkResult) == 0) {
        $insertSql = "INSERT INTO crimecategory (CategoryName) VALUES ('$catName')";
        if (mysqli_query($conn, $insertSql)) {
            echo "<p style='color:green;'>Inserted: " . htmlspecialchars($catName) . "</p>";
            $insertedCount++;
        } else {
            echo "<p style='color:red;'>Error inserting " . htmlspecialchars($catName) . ": " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color:orange;'>Skipped: " . htmlspecialchars($catName) . " (Already exists)</p>";
    }
}

if ($insertedCount > 0) {
    echo "<h2>" . $insertedCount . " new categories inserted.</h2>";
} else {
    echo "<h2>No new categories were inserted (all already existed or an error occurred).</h2>";
}

echo "<p><a href='user/report_crime.php'>Go back to Report Crime page</a></p>";

?>