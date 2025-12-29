<?php
include("../config/db.php");
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$userId = $_SESSION['user_id'];

$title = $_POST['title'];
$description = $_POST['description'];
$categoryId = $_POST['category_id'];
$locationText = $_POST['location'];
$lat = !empty($_POST['latitude']) ? $_POST['latitude'] : 0;
$lng = !empty($_POST['longitude']) ? $_POST['longitude'] : 0;

/* Find nearest police station */
$stationId = null;

// 1. Try to find the nearest station based on coordinates
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

// 2. Fallback: If no coordinates or calculation failed, get ANY valid station (e.g., the first one)
if (!$stationId) {
    $fallbackSql = "SELECT StationID FROM policestations LIMIT 1";
    $fallbackResult = mysqli_query($conn, $fallbackSql);
    if ($fallbackResult && mysqli_num_rows($fallbackResult) > 0) {
        $fallback = mysqli_fetch_assoc($fallbackResult);
        $stationId = $fallback['StationID'];
    }
}

// 3. Final Fallback: If table is empty, we cannot proceed safely with foreign key constraints.
// Ideally, the system should have at least one station. We will try ID 1 as a last resort hail mary.
if (!$stationId) {
    $stationId = 1; 
}

// Secure Prepared Statement
$stmt = mysqli_prepare($conn, "INSERT INTO crimereports (UserID, StationID, CategoryID, Title, Description, LocationText, Latitude, Longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "iiisssdd", $userId, $stationId, $categoryId, $title, $description, $locationText, $lat, $lng);

if (mysqli_stmt_execute($stmt)) {
    $reportId = mysqli_insert_id($conn);
    
    // Handle Evidence Upload
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
                    // Prepared statement for evidence
                    $evStmt = mysqli_prepare($conn, "INSERT INTO evidencefiles (ReportID, FileURL, FileType) VALUES (?, ?, ?)");
                    mysqli_stmt_bind_param($evStmt, "iss", $reportId, $targetFile, $fileType);
                    mysqli_stmt_execute($evStmt);
                }
            }
        }
    }

    echo "Crime reported successfully. Report ID: " . $reportId;
    // Redirect back to dashboard after 2 seconds
    echo "<script>setTimeout(function(){ window.location.href = '../user/dashboard.php'; }, 2000);</script>";

} else {
    echo "Error: " . mysqli_error($conn);
}
?>
