<?php
include("../config/db.php");

$reportId = $_POST['report_id'];
$fileType = $_POST['file_type']; // IMAGE or VIDEO

$targetDir = ($fileType == "IMAGE") ? "../uploads/images/" : "../uploads/videos/";
$fileName = time() . "_" . basename($_FILES["file"]["name"]);
$targetFile = $targetDir . $fileName;

if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {

    $sql = "INSERT INTO evidencefiles (ReportID, FileURL, FileType)
            VALUES ($reportId, '$targetFile', '$fileType')";

    mysqli_query($conn, $sql);
    echo "Evidence uploaded";
} else {
    echo "Upload failed";
}
?>
