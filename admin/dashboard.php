<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../login.html");
    exit();
}

// Handle Search & Filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filterStatus = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Stats Queries
$totalReports = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM crimereports"))['c'];
$pendingReports = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM crimereports WHERE Status='Pending'"))['c'];
$activeOfficers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE Role IN ('POLICE', 'OFFICER')"))['c'];

// Fetch Pending Reports (Always shown at top)
$pendingSql = "SELECT r.*, c.CategoryName, u.Name as ReporterName 
               FROM crimereports r 
               LEFT JOIN crimecategory c ON r.CategoryID = c.CategoryID
               LEFT JOIN users u ON r.UserID = u.UserID
               WHERE r.Status = 'Pending'
               ORDER BY r.CreatedAt DESC";
$pendingResult = mysqli_query($conn, $pendingSql);

// Fetch Officers for assignment
$officersSql = "SELECT UserID, Name FROM users WHERE Role = 'OFFICER' OR Role = 'POLICE'";
$officersResult = mysqli_query($conn, $officersSql);
$officers = [];
if ($officersResult) {
    while($row = mysqli_fetch_assoc($officersResult)) {
        $officers[] = $row;
    }
}

// Fetch All Reports with Filters
$whereClause = "1=1";
if (!empty($search)) {
    $whereClause .= " AND (r.Title LIKE '%$search%' OR r.Description LIKE '%$search%' OR r.ReportID LIKE '%$search%')";
}
if (!empty($filterStatus)) {
    $whereClause .= " AND r.Status = '$filterStatus'";
}

$allSql = "SELECT r.*, c.CategoryName, u.Name as OfficerName 
           FROM crimereports r 
           LEFT JOIN crimecategory c ON r.CategoryID = c.CategoryID 
           LEFT JOIN caseassignments ca ON r.ReportID = ca.ReportID
           LEFT JOIN users u ON ca.PoliceID = u.UserID
           WHERE $whereClause
           ORDER BY r.CreatedAt DESC";
$allResult = mysqli_query($conn, $allSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Crime Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0f172a;
            --accent: #3b82f6;
            --bg: #f8fafc;
            --surface: #ffffff;
            --text: #334155;
            --border: #e2e8f0;
        }
        body { 
            font-family: 'Inter', system-ui, sans-serif; 
            margin: 0; 
            background-color: var(--bg); 
            color: var(--text);
        }
        
        /* Sidebar/Nav Layout could be used here, but keeping it top-nav for simplicity */
        .navbar {
            background-color: var(--primary);
            color: white;
            padding: 0 30px;
            height: 64px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-logout {
            background: rgba(255,255,255,0.1);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn-logout:hover { background: rgba(255,255,255,0.2); }

        .main-content {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 30px;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            display: block;
            margin-bottom: 4px;
        }
        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Generic Card */
        .card {
            background: var(--surface);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border: 1px solid var(--border);
            overflow: hidden;
        }
        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
        }
        .card-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Filters */
        .filter-bar {
            display: flex;
            gap: 10px;
            background: #f8fafc;
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
        }
        .search-input {
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            width: 250px;
        }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 24px; text-align: left; border-bottom: 1px solid var(--border); }
        th { 
            background-color: #f8fafc; 
            color: #64748b; 
            font-weight: 600; 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 0.05em;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: #f1f5f9; }
        
        select { 
            padding: 8px 12px; 
            border: 1px solid #cbd5e1; 
            border-radius: 6px; 
            background-color: white;
        }
        .btn { 
            padding: 8px 16px; 
            background: var(--accent); 
            color: white; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: 500;
            font-size: 0.9rem;
            transition: background 0.2s;
            text-decoration: none;
        }
        .btn:hover { background: #2563eb; }
        
        /* Status Badges */
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .status-Pending { background-color: #fff7ed; color: #c2410c; }
        .status-Assigned { background-color: #eff6ff; color: #1d4ed8; }
        .status-Investigating { background-color: #f5f3ff; color: #7c3aed; }
        .status-Closed { background-color: #f0fdf4; color: #15803d; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="#" class="logo">🛡️ Admin Panel</a>
        <a href="../auth/logout.php" class="btn-logout">Sign Out</a>
    </nav>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1 style="margin: 0; font-size: 1.8rem;">Dashboard</h1>
            <a href="manage_resources.php" class="btn">+ Manage Officers</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-value"><?php echo $totalReports; ?></span>
                <span class="stat-label">Total Reports</span>
            </div>
            <div class="stat-card">
                <span class="stat-value" style="color: #c2410c;"><?php echo $pendingReports; ?></span>
                <span class="stat-label">Pending Assignment</span>
            </div>
            <div class="stat-card">
                <span class="stat-value" style="color: #2563eb;"><?php echo $activeOfficers; ?></span>
                <span class="stat-label">Active Officers</span>
            </div>
        </div>

        <?php if (mysqli_num_rows($pendingResult) > 0): ?>
        <div class="card" style="border-left: 4px solid #f59e0b;">
            <div class="card-header">
                <h2 class="card-title">⚠️ Action Required: Pending Cases</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Incident</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Reporter</th>
                        <th>Assign To</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($pendingResult)): ?>
                    <tr>
                        <td style="color: #64748b;">#<?php echo $row['ReportID']; ?></td>
                        <td style="font-weight: 600;"><?php echo htmlspecialchars($row['Title']); ?></td>
                        <td><?php echo htmlspecialchars($row['CategoryName']); ?></td>
                        <td><?php echo htmlspecialchars($row['LocationText']); ?></td>
                        <td><?php echo htmlspecialchars($row['ReporterName']); ?></td>
                        <td>
                            <form action="../api/assign_case.php" method="POST" style="margin:0; display: flex; gap: 8px;">
                                <input type="hidden" name="report_id" value="<?php echo $row['ReportID']; ?>">
                                <select name="police_id" required>
                                    <option value="">Select Officer...</option>
                                    <?php foreach($officers as $off): ?>
                                    <option value="<?php echo $off['UserID']; ?>"><?php echo htmlspecialchars($off['Name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn">Assign</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📋 Case Records</h2>
            </div>
            
            <form class="filter-bar" method="GET">
                <input type="text" name="search" class="search-input" placeholder="Search by ID, title..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="Pending" <?php if($filterStatus == 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Assigned" <?php if($filterStatus == 'Assigned') echo 'selected'; ?>>Assigned</option>
                    <option value="Investigating" <?php if($filterStatus == 'Investigating') echo 'selected'; ?>>Investigating</option>
                    <option value="Closed" <?php if($filterStatus == 'Closed') echo 'selected'; ?>>Closed</option>
                </select>
                <button type="submit" class="btn" style="background-color: #64748b;">Filter</button>
                <?php if(!empty($search) || !empty($filterStatus)): ?>
                    <a href="dashboard.php" class="btn" style="background-color: white; color: #64748b; border: 1px solid #cbd5e1;">Clear</a>
                <?php endif; ?>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Assigned Officer</th>
                        <th>Status</th>
                        <th>Date Reported</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($allResult)): 
                        $statusClass = 'status-' . str_replace(' ', '-', $row['Status']);
                        $officerName = !empty($row['OfficerName']) ? htmlspecialchars($row['OfficerName']) : '<span style="color:#94a3b8; font-style:italic;">Unassigned</span>';
                    ?>
                    <tr>
                        <td style="color: #64748b;">#<?php echo $row['ReportID']; ?></td>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($row['Title']); ?></td>
                        <td><?php echo htmlspecialchars($row['CategoryName']); ?></td>
                        <td><?php echo $officerName; ?></td>
                        <td>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($row['Status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($row['CreatedAt'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>