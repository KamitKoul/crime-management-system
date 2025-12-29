<?php
include("../config/db.php");
session_start();

if ($_SESSION['role'] !== 'POLICE' && $_SESSION['role'] !== 'OFFICER') {
    die("Access denied");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportId = $_POST['report_id'];
    
    // Security check: Ensure this case is actually assigned to this officer
    $officerId = $_SESSION['user_id'];
    $checkSql = "SELECT * FROM caseassignments WHERE ReportID = $reportId AND PoliceID = $officerId";
    $checkResult = mysqli_query($conn, $checkSql);
    
    if (mysqli_num_rows($checkResult) > 0) {
        // Update Status to Closed
        $sql = "UPDATE crimereports SET Status='Closed' WHERE ReportID=$reportId";
        
        if (mysqli_query($conn, $sql)) {
            // Add a closing note automatically
            $note = "Case marked as CLOSED by Officer.";
            mysqli_query($conn, "INSERT INTO investigationnotes (ReportID, PoliceID, NoteText) VALUES ($reportId, $officerId, '$note')");
            
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
    } else {
        echo "Error: You are not assigned to this case.";
    }
}
?>