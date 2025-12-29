<?php
include("../config/db.php");
session_start();

if ($_SESSION['role'] !== 'ADMIN') {
    die("Access denied");
}

$reportId = $_POST['report_id'];
$policeId = $_POST['police_id'];

// Check if officer is already assigned to an active case (Keep this check, secure it slightly although inputs are likely ints)
$checkSql = "SELECT ca.AssignmentID 
             FROM caseassignments ca
             JOIN crimereports cr ON ca.ReportID = cr.ReportID
             WHERE ca.PoliceID = $policeId AND cr.Status != 'Closed'";
$checkResult = mysqli_query($conn, $checkSql);

if (mysqli_num_rows($checkResult) > 0) {
    header("Location: ../admin/dashboard.php?error=officer_busy");
    exit();
}

// Proceed with assignment (Secure INSERT)
$stmt1 = mysqli_prepare($conn, "INSERT INTO caseassignments (ReportID, PoliceID) VALUES (?, ?)");
mysqli_stmt_bind_param($stmt1, "ii", $reportId, $policeId);
mysqli_stmt_execute($stmt1);
mysqli_stmt_close($stmt1);

// Secure UPDATE
$stmt2 = mysqli_prepare($conn, "UPDATE crimereports SET Status='Assigned' WHERE ReportID=?");
mysqli_stmt_bind_param($stmt2, "i", $reportId);
mysqli_stmt_execute($stmt2);
mysqli_stmt_close($stmt2);

header("Location: ../admin/dashboard.php?success=assigned");
exit();
?>
