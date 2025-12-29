<?php
include("../config/db.php");
session_start();

if ($_SESSION['role'] !== 'ADMIN') {
    die("Access denied");
}

$reportId = $_POST['report_id'];
$policeId = $_POST['police_id'];

mysqli_query($conn,
"INSERT INTO caseassignments (ReportID, PoliceID)
 VALUES ($reportId, $policeId)");

mysqli_query($conn,
"UPDATE crimereports SET Status='Assigned' WHERE ReportID=$reportId");

header("Location: ../admin/dashboard.php");
exit();
?>
