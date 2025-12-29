<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'USER') {
    header("Location: ../login.html");
    exit();
}

$userId = $_SESSION['user_id'];
$sql = "SELECT r.*, c.CategoryName 
        FROM crimereports r 
        LEFT JOIN crimecategory c ON r.CategoryID = c.CategoryID 
        WHERE UserID = $userId 
        ORDER BY CreatedAt DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - CrimeWatch</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --surface: #ffffff;
            --bg: #f3f4f6;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border: #e5e7eb;
        }
        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            background-color: var(--bg); 
            color: var(--text-main);
        }
        
        /* Navbar */
        .navbar {
            background-color: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 5%;
            height: 64px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .btn-logout {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .btn-logout:hover {
            background-color: #fee2e2;
            color: #ef4444;
        }

        /* Main Layout */
        .main-content {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        .header-section h1 {
            font-size: 2rem;
            margin: 0;
            color: var(--text-main);
        }
        .header-section p {
            color: var(--text-muted);
            margin: 4px 0 0 0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: background-color 0.2s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .btn:hover { background-color: #1d4ed8; }
        .btn-secondary { background-color: white; color: var(--text-main); border: 1px solid var(--border); }
        .btn-secondary:hover { background-color: #f9fafb; }

        /* Card Grid */
        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
        }
        
        .report-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
        }
        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-color: #d1d5db;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        .cat-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            background-color: #f3f4f6;
            color: #4b5563;
            text-transform: uppercase;
        }
        
        .report-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0 0 8px 0;
            color: #111827;
        }
        .report-desc {
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.5;
            flex-grow: 1;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .card-footer {
            border-top: 1px solid var(--border);
            padding-top: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
        }
        .date { color: #9ca3af; }
        
        /* Status Badges */
        .status-badge {
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        .status-Pending { background-color: #fff7ed; color: #c2410c; }
        .status-Assigned { background-color: #eff6ff; color: #1d4ed8; }
        .status-In-Progress { background-color: #f5f3ff; color: #7c3aed; }
        .status-Closed { background-color: #f0fdf4; color: #15803d; }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 80px 0;
            background: white;
            border-radius: 12px;
            border: 1px dashed var(--border);
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="#" class="logo">🛡️ CrimeWatch</a>
        <div class="nav-actions">
            <span style="font-size: 0.9rem; color: #6b7280;">Welcome back</span>
            <a href="../auth/logout.php" class="btn-logout">Sign Out</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="header-section">
            <div>
                <h1>My Reports</h1>
                <p>Track the status of incidents you've reported.</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="report_vehicle.php" class="btn btn-secondary">
                    <span>🚗</span> Vehicle Issue
                </a>
                <a href="report_crime.php" class="btn">
                    <span>+</span> New Incident
                </a>
            </div>
        </div>

        <div class="report-grid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): 
                    $status = $row['Status'];
                    $statusClass = 'status-' . str_replace(' ', '-', $status);
                    $category = !empty($row['CategoryName']) ? $row['CategoryName'] : 'General';
                ?>
                <div class="report-card">
                    <div class="card-header">
                        <span class="cat-badge"><?php echo htmlspecialchars($category); ?></span>
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($status); ?>
                        </span>
                    </div>
                    
                    <h3 class="report-title"><?php echo htmlspecialchars($row['Title']); ?></h3>
                    <div class="report-desc"><?php echo htmlspecialchars($row['Description']); ?></div>
                    
                    <div class="card-footer">
                        <span class="date">Reported on <?php echo date('M d, Y', strtotime($row['CreatedAt'])); ?></span>
                        <span style="color: var(--primary); font-weight: 500;">#<?php echo $row['ReportID']; ?></span>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div style="font-size: 3rem; margin-bottom: 16px;">📝</div>
                    <h3 style="margin: 0; color: #111827;">No reports yet</h3>
                    <p style="color: #6b7280; margin-top: 8px;">Your reported incidents will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>