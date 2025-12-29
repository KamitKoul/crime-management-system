<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'POLICE' && $_SESSION['role'] !== 'OFFICER')) {
    header("Location: ../login.html");
    exit();
}

$officerId = $_SESSION['user_id'];

$sql = "SELECT r.*, c.CategoryName 
        FROM crimereports r
        JOIN caseassignments ca ON r.ReportID = ca.ReportID
        LEFT JOIN crimecategory c ON r.CategoryID = c.CategoryID
        WHERE ca.PoliceID = $officerId
        ORDER BY r.CreatedAt DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officer Dashboard - Crime Management</title>
    <style>
        :root {
            --primary: #1e3a8a;
            --accent: #2563eb;
            --bg: #f1f5f9;
            --surface: #ffffff;
            --text: #334155;
            --border: #e2e8f0;
        }
        body { 
            font-family: 'Segoe UI', system-ui, sans-serif; 
            margin: 0; 
            background-color: var(--bg); 
            color: var(--text);
        }
        .navbar {
            background-color: var(--primary);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }
        .main-content {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .page-header {
            margin-bottom: 30px;
        }
        .page-header h1 {
            margin: 0 0 5px 0;
            color: #1e293b;
        }
        
        /* Case Card Styles */
        .case-card { 
            background: var(--surface);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid var(--border);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .case-card:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); 
        }
        
        .case-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start; 
            margin-bottom: 15px; 
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        .case-title { margin: 0; color: #1e293b; font-size: 1.25rem; }
        .case-meta { color: #64748b; font-size: 0.9rem; margin-top: 5px; }
        
        .description-box { 
            background: #f8fafc; 
            padding: 15px; 
            border-radius: 6px; 
            border: 1px solid var(--border);
            margin-bottom: 20px;
            line-height: 1.6;
            color: #475569;
        }
        
        h4 { margin: 20px 0 10px 0; color: #334155; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.05em; }
        
        textarea { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #cbd5e1; 
            border-radius: 6px; 
            box-sizing: border-box; 
            font-family: inherit; 
            resize: vertical; 
            min-height: 100px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .btn { 
            margin-top: 15px; 
            padding: 10px 20px; 
            background: var(--accent); 
            color: white; 
            border: none; 
            cursor: pointer; 
            border-radius: 6px; 
            font-weight: 600; 
            transition: background 0.2s;
            display: inline-block;
        }
        .btn:hover { background: #1d4ed8; }
        
        .btn-logout {
            background: rgba(255,255,255,0.1);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: background 0.2s;
        }
        .btn-logout:hover { background: rgba(255,255,255,0.2); }
        
        .status-badge { 
            padding: 6px 12px; 
            border-radius: 20px; 
            font-size: 0.8rem; 
            font-weight: 700; 
            text-transform: uppercase;
        }
        .status-badge.Assigned { background-color: #eff6ff; color: #1d4ed8; }
        .status-badge.Under-Investigation { background-color: #f5f3ff; color: #7c3aed; }
        
        .empty-state { 
            padding: 60px; 
            text-align: center; 
            color: #94a3b8; 
            background: var(--surface);
            border-radius: 12px;
            border: 1px solid var(--border);
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="#" class="logo">👮 Officer Panel</a>
        <a href="../auth/logout.php" class="btn-logout">Sign Out</a>
    </nav>

    <div class="main-content">
        <div class="page-header">
            <h1>My Assigned Cases</h1>
        </div>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): 
                 $statusSlug = str_replace(' ', '-', $row['Status']);
            ?>
                <div class="case-card">
                    <div class="case-header">
                        <div>
                            <h3 class="case-title">Case #<?php echo $row['ReportID']; ?>: <?php echo htmlspecialchars($row['Title']); ?></h3>
                            <div class="case-meta">
                                <strong>Category:</strong> <?php echo htmlspecialchars($row['CategoryName']); ?> &bull; 
                                <strong>Location:</strong> <?php echo htmlspecialchars($row['LocationText']); ?>
                            </div>
                        </div>
                        <span class="status-badge <?php echo $statusSlug; ?>"><?php echo htmlspecialchars($row['Status']); ?></span>
                    </div>

                    <div class="description-box">
                        <strong>Incident Description:</strong><br>
                        <?php echo nl2br(htmlspecialchars($row['Description'])); ?>
                    </div>
                    
                    <h4>Investigation Update</h4>
                    <form action="add_note.php" method="POST">
                        <input type="hidden" name="report_id" value="<?php echo $row['ReportID']; ?>">
                        <textarea name="note" rows="3" placeholder="Enter your investigation findings, witness statements, or updates here..." required <?php echo ($row['Status'] == 'Closed') ? 'disabled' : ''; ?>></textarea>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                            <?php if($row['Status'] != 'Closed'): ?>
                                <button type="submit" class="btn">Save Note & Mark Investigating</button>
                            <?php else: ?>
                                <span style="color: #64748b; font-style: italic;">Case is Closed. Read-only.</span>
                            <?php endif; ?>
                    </form>
                    
                    <?php if($row['Status'] != 'Closed'): ?>
                        <form action="close_case.php" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to CLOSE this case? This action indicates the investigation is complete.');">
                            <input type="hidden" name="report_id" value="<?php echo $row['ReportID']; ?>">
                            <button type="submit" class="btn" style="background-color: #ef4444; margin-top: 0;">Close Case</button>
                        </form>
                    <?php endif; ?>
                        </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>No Active Cases</h3>
                <p>You have no cases assigned to you at the moment.</p>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
