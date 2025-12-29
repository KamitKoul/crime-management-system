<?php
include("config/db.php");

echo "<h1>Inserting Default Police Station</h1>";

// Check if any station exists
$checkSql = "SELECT * FROM policestations LIMIT 1";
$checkResult = mysqli_query($conn, $checkSql);

if (mysqli_num_rows($checkResult) == 0) {
    // Insert a default station
    $sql = "INSERT INTO policestations (StationName, Latitude, Longitude, Address) 
            VALUES ('Central Police HQ', 28.6139, 77.2090, 'Connaught Place, New Delhi')";
            
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green;'>Success: Default 'Central Police HQ' station inserted.</p>";
        $id = mysqli_insert_id($conn);
        echo "<p>Station ID: $id</p>";
    } else {
        echo "<p style='color:red;'>Error inserting station: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color:orange;'>A police station already exists. No action needed.</p>";
    $row = mysqli_fetch_assoc($checkResult);
    echo "<p>Existing Station ID: " . $row['StationID'] . " (" . $row['StationName'] . ")</p>";
}

echo "<p><a href='user/report_crime.php'>Go back to Report Crime</a></p>";
?>