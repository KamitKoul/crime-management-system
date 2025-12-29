<?php
include("../config/db.php");
session_start();

if ($_SESSION['role'] !== 'POLICE' && $_SESSION['role'] !== 'OFFICER') {
    die("Access denied");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportId = $_POST['report_id'];
    $officerId = $_SESSION['user_id'];
    
    // 1. Security Check: Ensure this case is assigned to this officer
    $checkStmt = mysqli_prepare($conn, "SELECT AssignmentID FROM caseassignments WHERE ReportID = ? AND PoliceID = ?");
    mysqli_stmt_bind_param($checkStmt, "ii", $reportId, $officerId);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);
    
    if (mysqli_stmt_num_rows($checkStmt) > 0) {
        mysqli_stmt_close($checkStmt);
        
        // 2. Update Status to Closed
        $updateStmt = mysqli_prepare($conn, "UPDATE crimereports SET Status='Closed' WHERE ReportID = ?");
        mysqli_stmt_bind_param($updateStmt, "i", $reportId);
        
        if (mysqli_stmt_execute($updateStmt)) {
            mysqli_stmt_close($updateStmt);
            
            // 3. Add a closing note automatically
            $note = "Case marked as CLOSED by Officer.";
            $noteStmt = mysqli_prepare($conn, "INSERT INTO investigationnotes (ReportID, PoliceID, NoteText) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($noteStmt, "iis", $reportId, $officerId, $note);
            mysqli_stmt_execute($noteStmt);
            mysqli_stmt_close($noteStmt);
            
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
