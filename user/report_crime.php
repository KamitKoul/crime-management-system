<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'USER') {
    header("Location: ../login.html");
    exit();
}

$cats = mysqli_query($conn, "SELECT * FROM crimecategory");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Incident - CrimeWatch</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #eff6ff;
            --bg: #f8fafc;
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
            padding-bottom: 60px;
        }
        
        .navbar {
            background-color: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 5%;
            height: 64px;
            display: flex;
            align-items: center;
            margin-bottom: 40px;
        }
        .logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .container {
            max-width: 800px; 
            margin: 0 auto; 
            background: var(--surface); 
            padding: 48px; 
            border-radius: 20px; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 10px 15px -3px rgba(0,0,0,0.1);
            border: 1px solid var(--border);
        }
        .header {
            margin-bottom: 40px;
            text-align: center;
        }
        .header h2 { 
            margin: 0; 
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.025em;
        }
        .header p {
            margin-top: 8px;
            color: var(--text-muted);
            font-size: 1.1rem;
        }
        
        .form-section {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
        }
        
        label { 
            display: block; 
            margin-bottom: 8px;
            font-weight: 600; 
            color: #374151; 
            font-size: 0.9rem;
        }
        input[type="text"], 
        textarea, 
        select { 
            width: 100%; 
            padding: 12px 16px; 
            border: 2px solid #f3f4f6; 
            border-radius: 12px; 
            box-sizing: border-box;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.2s;
            background-color: #f9fafb;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            background-color: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        textarea { resize: vertical; min-height: 140px; }
        
        .location-box {
            background-color: var(--primary-light);
            padding: 24px;
            border-radius: 16px;
            border: 1px solid #dbeafe;
        }
        
        #locStatus { 
            font-size: 0.9rem; 
            font-weight: 500;
            color: #1e40af;
            margin-bottom: 16px;
            display: block;
        }
        
        .btn-location {
            background-color: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-location:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
        }
        
        .file-upload-box {
            border: 2px dashed #cbd5e1;
            padding: 32px;
            text-align: center;
            border-radius: 16px;
            background-color: #f9fafb;
            cursor: pointer;
            transition: all 0.2s;
        }
        .file-upload-box:hover {
            border-color: var(--primary);
            background-color: var(--primary-light);
        }
        
        .btn-submit { 
            margin-top: 20px; 
            padding: 16px; 
            background: #ef4444; 
            color: white; 
            border: none; 
            cursor: pointer; 
            width: 100%; 
            font-size: 1.1rem; 
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);
        }
        .btn-submit:hover { 
            background: #dc2626; 
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.3);
        }
        
        .back-link {
            display: block;
            margin-top: 24px;
            text-align: center;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover { color: var(--primary); }
    </style>
    <script>
        function getLocation() {
            var locDiv = document.getElementById("locStatus");
            locDiv.innerHTML = "⏳ Requesting location access...";

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, showError, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                });
            } else {
                locDiv.innerHTML = "❌ Geolocation is not supported.";
            }
        }

        function showPosition(position) {
            document.getElementById("latitude").value = position.coords.latitude;
            document.getElementById("longitude").value = position.coords.longitude;
            var locDiv = document.getElementById("locStatus");
            locDiv.innerHTML = "✅ Location Verified: " + position.coords.latitude.toFixed(4) + ", " + position.coords.longitude.toFixed(4);
            locDiv.style.color = "#047857";
            
            if(document.getElementById("location").value === "") {
                document.getElementById("location").value = "GPS: " + position.coords.latitude.toFixed(5) + ", " + position.coords.longitude.toFixed(5);
            }
        }

        function showError(error) {
            var locDiv = document.getElementById("locStatus");
            locDiv.style.color = "#b91c1c";
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    locDiv.innerText = "⛔ Permission Denied.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    locDiv.innerText = "⚠️ Location unavailable.";
                    break;
                case error.TIMEOUT:
                    locDiv.innerText = "⏰ Request timed out.";
                    break;
                default:
                    locDiv.innerText = "⚠️ Unknown error.";
            }
        }
    </script>
</head>
<body>

    <nav class="navbar">
        <a href="dashboard.php" class="logo">🛡️ CrimeWatch</a>
    </nav>

    <div class="container">
        <div class="header">
            <h2>Report Incident</h2>
            <p>Your report helps keep the community safe.</p>
        </div>
        
        <form action="../api/report_crime.php" method="POST" enctype="multipart/form-data" class="form-section">
            
            <div>
                <label>Title</label>
                <input type="text" name="title" required placeholder="What happened?">
            </div>
            
            <div>
                <label>Category</label>
                <select name="category_id" required>
                    <option value="">Choose category...</option>
                    <?php while($row = mysqli_fetch_assoc($cats)): ?>
                        <option value="<?php echo $row['CategoryID']; ?>"><?php echo $row['CategoryName']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div>
                <label>Description</label>
                <textarea name="description" required placeholder="Provide as much detail as possible (Who, what, when, where)..."></textarea>
            </div>
            
            <div class="location-box">
                <label>Incident Location</label>
                <span id="locStatus">Please verify your location for faster response.</span>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <button type="button" onclick="getLocation()" class="btn-location">📍 Use My Current Location</button>
                    <input type="text" name="location" id="location" placeholder="Or enter address/landmark manually" required style="flex-grow: 1;">
                </div>
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
            </div>
            
            <div>
                <label>Evidence (Optional)</label>
                <div class="file-upload-box" onclick="document.getElementById('evidence_input').click();">
                    <input type="file" id="evidence_input" name="evidence[]" multiple accept="image/*,video/*" style="display: none;">
                    <span style="font-size: 2rem;">📁</span>
                    <p style="margin: 10px 0 0; color: #64748b; font-weight: 500;">Click to upload photos or videos</p>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Submit Official Report</button>
        </form>
        
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>

</body>
</html>