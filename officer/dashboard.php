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
    <title>Officer Dashboard - CrimeWatch</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e293b;
            --accent: #3b82f6;
            --bg: #f1f5f9;
            --surface: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }
        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            background-color: var(--bg); 
            color: var(--text-main);
        }
        
        .navbar {
            background-color: var(--primary);
            color: white;
            padding: 0 5%;
            height: 64px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .logo { font-size: 1.25rem; font-weight: 700; color: white; text-decoration: none; }
        .btn-logout { 
            color: #cbd5e1; 
            text-decoration: none; 
            font-size: 0.9rem; 
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #475569;
        }
        .btn-logout:hover { color: white; border-color: white; }

        .main-content {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-flex h1 { margin: 0; font-size: 1.8rem; font-weight: 800; }

        /* Case Card */
        .case-card { 
            background: var(--surface);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
        }
        .case-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
        .case-id { font-size: 0.85rem; font-weight: 700; color: var(--accent); text-transform: uppercase; margin-bottom: 4px; display: block; }
        .case-title { font-size: 1.5rem; font-weight: 700; margin: 0; color: #0f172a; }
        
        .status-badge { 
            padding: 6px 14px; 
            border-radius: 999px; 
            font-size: 0.75rem; 
            font-weight: 700; 
            text-transform: uppercase;
        }
        .status-Assigned { background-color: #eff6ff; color: #1d4ed8; }
        .status-Investigating { background-color: #f5f3ff; color: #7c3aed; }
        .status-Closed { background-color: #f0fdf4; color: #15803d; }

        .meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px; padding: 20px; background: #f8fafc; border-radius: 12px; }
        .meta-item label { display: block; font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
        .meta-item span { font-weight: 600; color: #334155; }

        .description { line-height: 1.6; color: #475569; margin-bottom: 30px; }
        
        textarea { 
            width: 100%; 
            padding: 16px; 
            border: 1px solid #cbd5e1; 
            border-radius: 12px; 
            font-family: inherit; 
            font-size: 1rem;
            margin-bottom: 20px;
            background-color: #fcfcfc;
        }
        textarea:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }

        .actions { display: flex; gap: 12px; }
        .btn { 
            padding: 12px 24px; 
            border-radius: 10px; 
            font-weight: 600; 
            cursor: pointer; 
            border: none;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .btn-primary { background: var(--accent); color: white; }
        .btn-primary:hover { background: #2563eb; transform: translateY(-1px); }
        .btn-danger { background: #fee2e2; color: #ef4444; }
        .btn-danger:hover { background: #fecaca; }

        .empty-state { text-align: center; padding: 80px 20px; background: white; border-radius: 20px; border: 1px dashed #cbd5e1; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="#" class="logo">🛡️ CrimeWatch Officer</a>
        <a href="../auth/logout.php" class="btn-logout">Sign Out</a>
    </nav>

    <div class="main-content">
        <div class="header-flex">
            <h1>Assigned Investigations</h1>
            <span style="color: var(--text-muted); font-weight: 500;">Active Cases: <?php echo mysqli_num_rows($result); ?></span>
        </div>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): 
                 $statusSlug = str_replace(' ', '-', $row['Status']);
            ?>
                <div class="case-card">
                    <div class="case-top">
                        <div>
                            <span class="case-id">Case #<?php echo $row['ReportID']; ?></span>
                            <h2 class="case-title"><?php echo htmlspecialchars($row['Title']); ?></h2>
                        </div>
                        <span class="status-badge status-<?php echo $statusSlug; ?>"><?php echo htmlspecialchars($row['Status']); ?></span>
                    </div>

                    <div class="meta-grid">
                        <div class="meta-item">
                            <label>Category</label>
                            <span><?php echo htmlspecialchars($row['CategoryName']); ?></span>
                        </div>
                        <div class="meta-item">
                            <label>Location</label>
                            <span><?php echo htmlspecialchars($row['LocationText']); ?></span>
                        </div>
                        <div class="meta-item">
                            <label>Reported On</label>
                            <span><?php echo date('M d, Y', strtotime($row['CreatedAt'])); ?></span>
                        </div>
                    </div>

                    <div class="description">
                        <?php echo nl2br(htmlspecialchars($row['Description'])); ?>
                    </div>
                    
                    <?php if($row['Status'] != 'Closed'): ?>
                        <form action="add_note.php" method="POST">
                            <input type="hidden" name="report_id" value="<?php echo $row['ReportID']; ?>">
                            <textarea name="note" rows="3" placeholder="Enter investigation update or findings..." required></textarea>
                            
                            <div class="actions">
                                <button type="submit" class="btn btn-primary">Save Progress Update</button>
                        </form>
                        
                        <form action="close_case.php" method="POST" onsubmit="return confirm('Close this investigation?');">
                            <input type="hidden" name="report_id" value="<?php echo $row['ReportID']; ?>">
                            <button type="submit" class="btn btn-danger">Mark as Resolved</button>
                        </form>
                        </div>
                    <?php else: ?>
                        <div style="background: #f0fdf4; padding: 16px; border-radius: 12px; color: #15803d; font-weight: 600; text-align: center;">
                            ✓ This investigation has been successfully closed.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3 style="margin: 0;">All Clear</h3>
                <p style="color: var(--text-muted); margin-top: 8px;">You currently have no active case assignments.</p>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>