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
$stationId = 1; // Default fallback

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

$sql = "INSERT INTO crimereports
(UserID, StationID, CategoryID, Title, Description, LocationText, Latitude, Longitude)
VALUES
($userId, $stationId, $categoryId, '$title', '$description', '$locationText', $lat, $lng)";

if (mysqli_query($conn, $sql)) {
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
                    $evSql = "INSERT INTO evidencefiles (ReportID, FileURL, FileType)
                              VALUES ($reportId, '$targetFile', '$fileType')";
                    mysqli_query($conn, $evSql);
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
