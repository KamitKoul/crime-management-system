<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'USER') {
    header("Location: ../login.html");
    exit();
}

// Fetch categories
$cats = mysqli_query($conn, "SELECT * FROM crimecategory");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Report Vehicle Incident - Crime Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary: #2563eb;
            --bg: #f8fafc;
            --surface: #ffffff;
            --text: #334155;
            --border: #e2e8f0;
        }
        body { 
            font-family: 'Segoe UI', system-ui, sans-serif; 
            margin: 0; 
            background-color: var(--bg); 
            color: var(--text);
            padding-bottom: 40px;
        }
        .container {
            max-width: 700px; 
            margin: 40px auto; 
            background: var(--surface); 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            border: 1px solid var(--border);
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 20px;
        }
        .header h2 { 
            margin: 0 0 10px 0; 
            color: #1e293b;
        }
        .header p {
            margin: 0;
            color: #64748b;
        }
        label { 
            display: block; 
            margin-top: 20px; 
            margin-bottom: 8px;
            font-weight: 600; 
            color: #334155; 
            font-size: 0.95rem;
        }
        input[type="text"], 
        textarea, 
        select { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #cbd5e1; 
            border-radius: 6px; 
            box-sizing: border-box;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        textarea { resize: vertical; min-height: 120px; }
        
        .file-upload-box {
            border: 2px dashed #cbd5e1;
            padding: 20px;
            text-align: center;
            border-radius: 6px;
            background-color: #f8fafc;
            margin-top: 5px;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        .file-upload-box:hover {
            border-color: var(--primary);
            background-color: #f0f9ff;
        }
        input[type="file"] {
            display: block;
            margin: 0 auto;
        }
        
        button { 
            margin-top: 30px; 
            padding: 14px 20px; 
            background: #ef4444; 
            color: white; 
            border: none; 
            cursor: pointer; 
            width: 100%; 
            font-size: 1rem; 
            border-radius: 6px;
            font-weight: 600;
            transition: background 0.2s;
        }
        button:hover { background: #dc2626; }
        
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #64748b;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .back-link:hover { color: var(--primary); }
        
        #locStatus { 
            font-size: 0.9rem; 
            margin-top: 8px;
            padding: 8px 12px;
            background: #f0f9ff;
            border-radius: 4px;
            color: #0369a1;
            display: inline-block;
        }
    </style>
    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }

        function showPosition(position) {
            document.getElementById("latitude").value = position.coords.latitude;
            document.getElementById("longitude").value = position.coords.longitude;
            var locDiv = document.getElementById("locStatus");
            locDiv.innerHTML = "📍 Location Verified: " + position.coords.latitude.toFixed(4) + ", " + position.coords.longitude.toFixed(4);
            locDiv.style.backgroundColor = "#ecfdf5"; // Green bg
            locDiv.style.color = "#047857"; // Green text
            
            if(document.getElementById("location").value === "") {
                document.getElementById("location").value = "Lat: " + position.coords.latitude.toFixed(4) + ", Lng: " + position.coords.longitude.toFixed(4);
            }
        }

        function showError(error) {
            var locDiv = document.getElementById("locStatus");
            locDiv.style.backgroundColor = "#fef2f2"; // Red bg
            locDiv.style.color = "#b91c1c"; // Red text
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    locDiv.innerText = "⚠️ User denied Geolocation. Please enter address manually.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    locDiv.innerText = "⚠️ Location unavailable.";
                    break;
                case error.TIMEOUT:
                    locDiv.innerText = "⚠️ Location request timed out.";
                    break;
                case error.UNKNOWN_ERROR:
                    locDiv.innerText = "⚠️ Unknown error.";
                    break;
            }
        }
        
        window.onload = getLocation;
    </script>
</head>
<body>

    <div class="container">
        <div class="header">
            <h2>Report Vehicle Incident</h2>
            <p>Report accidents, hit-and-run, or traffic violations.</p>
        </div>
        
        <form action="../api/report_vehicle_incident.php" method="POST" enctype="multipart/form-data">
            
            <label>Title</label>
            <input type="text" name="title" required placeholder="e.g. Hit and Run by Red Sedan">
            
            <label>Vehicle Number (License Plate)</label>
            <input type="text" name="vehicle_number" placeholder="e.g. MH 12 AB 1234">

            <label>Description</label>
            <textarea name="description" required placeholder="Describe the incident..."></textarea>
            
            <label>Location</label>
            <div id="locStatus">Detecting your location...</div>
            <input type="text" name="location" id="location" style="margin-top: 10px;" placeholder="Enter specific address or landmark" required>
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            
            <label>Evidence (Video Recommended)</label>
            <div class="file-upload-box">
                <input type="file" name="evidence[]" multiple accept="image/*,video/*">
                <p style="margin: 10px 0 0; color: #64748b; font-size: 0.9rem;">Upload video/images of the vehicle/incident</p>
            </div>
            
            <button type="submit">Submit Vehicle Report</button>
        </form>
        
        <a href="dashboard.php" class="back-link">Cancel and Return to Dashboard</a>
    </div>

</body>
</html>