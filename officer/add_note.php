<?php
include("../config/db.php");
session_start();

if ($_SESSION['role'] !== 'POLICE') {
    die("Access denied");
}

$reportId = $_POST['report_id'];
$note = $_POST['note'];
$policeId = $_SESSION['user_id'];

// 1. Insert Note (Secure)
$stmt1 = mysqli_prepare($conn, "INSERT INTO investigationnotes (ReportID, PoliceID, NoteText) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt1, "iis", $reportId, $policeId, $note);
mysqli_stmt_execute($stmt1);
mysqli_stmt_close($stmt1);

// 2. Update Status to 'Investigating' (Secure)
$stmt2 = mysqli_prepare($conn, "UPDATE crimereports SET Status='Investigating' WHERE ReportID=?");
mysqli_stmt_bind_param($stmt2, "i", $reportId);
mysqli_stmt_execute($stmt2);
mysqli_stmt_close($stmt2);

header("Location: dashboard.php");
exit();
?>
