<?php
include("../config/db.php");
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$userId = $_SESSION['user_id'];

$title = $_POST['title'];
$description = $_POST['description'];
$vehicleNumber = $_POST['vehicle_number'];
$locationText = $_POST['location'];

// Determine Category (Default to 'Traffic Accident' or similar if not provided)
$categoryId = 3; // Fallback default
$catResult = mysqli_query($conn, "SELECT CategoryID FROM crimecategory WHERE CategoryName LIKE '%Traffic%' OR CategoryName LIKE '%Accident%' LIMIT 1");
if ($catResult && mysqli_num_rows($catResult) > 0) {
    $catRow = mysqli_fetch_assoc($catResult);
    $categoryId = $catRow['CategoryID'];
}

// Handle default 0 for lat/lng if missing
$lat = !empty($_POST['latitude']) ? $_POST['latitude'] : 0;
$lng = !empty($_POST['longitude']) ? $_POST['longitude'] : 0;

/* Find nearest police station (Fallback to ID 1) */
$stationId = 1;
if ($lat != 0 && $lng != 0) {
    $stationQuery = "
    SELECT StationID FROM policestations
    ORDER BY (POWER(Latitude - $lat, 2) + POWER(Longitude - $lng, 2))
    LIMIT 1";

    $stationResult = mysqli_query($conn, $stationQuery);
    if ($stationResult && mysqli_num_rows($stationResult) > 0) {
        $station = mysqli_fetch_assoc($stationResult);
        $stationId = $station['StationID'];
    }
}

// 1. Insert into Crimereports
$sql = "INSERT INTO crimereports
(UserID, StationID, CategoryID, Title, Description, LocationText, Latitude, Longitude)
VALUES
($userId, $stationId, $categoryId, '$title', '$description', '$locationText', $lat, $lng)";

if (mysqli_query($conn, $sql)) {
    $reportId = mysqli_insert_id($conn);
    
    // 2. Insert into Vehiclereports
    if (!empty($vehicleNumber)) {
        $vSql = "INSERT INTO vehiclereports (ReportID, VehicleNumber) VALUES ($reportId, '$vehicleNumber')";
        if (!mysqli_query($conn, $vSql)) {
            // Log error but don't stop the process
            error_log("Error inserting vehicle report: " . mysqli_error($conn));
        }
    }
    
    // 3. Handle Evidence Upload
    if (isset($_FILES['evidence'])) {
        $total = count($_FILES['evidence']['name']);
        for ($i = 0; $i < $total; $i++) {
            $fileName = $_FILES['evidence']['name'][$i];
            $tmpName = $_FILES['evidence']['tmp_name'][$i];
            
            if ($fileName) {
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $fileType = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) ? 'IMAGE' : 'VIDEO';
                $targetDir = ($fileType == "IMAGE") ? "../uploads/images/" : "../uploads/videos/";
                
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                
                $newFileName = time() . "_" . $i . "_" . basename($fileName);
                $targetFile = $targetDir . $newFileName;
                
                if (move_uploaded_file($tmpName, $targetFile)) {
                    $evSql = "INSERT INTO evidencefiles (ReportID, FileURL, FileType)
                              VALUES ($reportId, '$targetFile', '$fileType')";
                    mysqli_query($conn, $evSql);
                }
            }
        }
    }

    echo "Vehicle incident reported successfully. Report ID: " . $reportId;
    echo "<script>setTimeout(function(){ window.location.href = '../user/dashboard.php'; }, 2000);</script>";

} else {
    echo "Error: " . mysqli_error($conn);
}
?>