<?php
include("../config/db.php");
session_start();

if ($_SESSION['role'] !== 'POLICE') {
    die("Access denied");
}

$reportId = $_POST['report_id'];
$note = $_POST['note'];
$policeId = $_SESSION['user_id'];

mysqli_query($conn,
"INSERT INTO investigationnotes (ReportID, PoliceID, NoteText)
 VALUES ($reportId, $policeId, '$note')");

mysqli_query($conn,
"UPDATE crimereports SET Status='Under Investigation'
 WHERE ReportID=$reportId");

header("Location: dashboard.php");
exit();
?>
