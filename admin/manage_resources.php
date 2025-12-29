<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../login.html");
    exit();
}

$message = "";

// Handle Officer Creation
if (isset($_POST['add_officer'])) {
    $name = $_POST['officer_name'];
    $email = $_POST['officer_email'];
    $phone = $_POST['officer_phone'];
    $password = password_hash($_POST['officer_password'], PASSWORD_DEFAULT);
    
    // Check ENUM type for Role
    $role = 'POLICE'; // Default
    $enumCheck = mysqli_query($conn, "DESCRIBE users Role");
    $row = mysqli_fetch_assoc($enumCheck);
    if (strpos($row['Type'], "'OFFICER'") !== false) {
        $role = 'OFFICER';
    }

    $sql = "INSERT INTO users (Name, Email, Phone, PasswordHash, Role) 
            VALUES ('$name', '$email', '$phone', '$password', '$role')";
            
    if (mysqli_query($conn, $sql)) {
        $message = "<div class='alert success'>Officer added successfully!</div>";
    } else {
        $message = "<div class='alert error'>Error: " . mysqli_error($conn) . "</div>";
    }
}

// Handle Station Creation
if (isset($_POST['add_station'])) {
    $sName = mysqli_real_escape_string($conn, $_POST['station_name']);
    $lat = (float) $_POST['latitude'];
    $lng = (float) $_POST['longitude'];
    $addr = mysqli_real_escape_string($conn, $_POST['address']);

    $sql = "INSERT INTO policestations (StationName, Latitude, Longitude, Address) 
            VALUES ('$sName', $lat, $lng, '$addr')";
            
    if (mysqli_query($conn, $sql)) {
        $message = "<div class='alert success'>Police Station added successfully!</div>";
    } else {
        $message = "<div class='alert error'>Error: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Resources - Admin Panel</title>
    <style>
        :root {
            --primary: #0f172a;
            --accent: #3b82f6;
            --bg: #f1f5f9;
            --surface: #ffffff;
            --text: #334155;
            --border: #e2e8f0;
        }
        body { font-family: 'Segoe UI', system-ui, sans-serif; margin: 0; background-color: var(--bg); color: var(--text); }
        .navbar { background-color: var(--primary); color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.25rem; font-weight: 700; color: white; text-decoration: none; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { background: var(--surface); border-radius: 12px; padding: 30px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #1e293b; border-bottom: 1px solid var(--border); padding-bottom: 15px; }
        label { display: block; margin-top: 15px; font-weight: 600; color: #475569; font-size: 0.9rem; }
        input { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        button { margin-top: 20px; padding: 10px 20px; background: var(--accent); color: white; border: none; border-radius: 6px; cursor: pointer; width: 100%; font-weight: 600; }
        button:hover { background: #2563eb; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; grid-column: 1 / -1; }
        .success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .back-link { display: inline-block; margin-bottom: 20px; color: var(--accent); text-decoration: none; font-weight: 500; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="dashboard.php" class="logo">🛡️ Admin Panel</a>
        <a href="../auth/logout.php" style="color:white; text-decoration:none;">Sign Out</a>
    </nav>

    <div style="max-width: 900px; margin: 20px auto; padding: 0 20px;">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        <?php echo $message; ?>
    </div>

    <div class="container">
        <!-- Add Officer Form -->
        <div class="card">
            <h2>Add New Officer</h2>
            <form method="POST">
                <label>Full Name</label>
                <input type="text" name="officer_name" required placeholder="Officer Name">
                
                <label>Email</label>
                <input type="email" name="officer_email" required placeholder="officer@police.com">
                
                <label>Phone</label>
                <input type="text" name="officer_phone" placeholder="Phone Number">
                
                <label>Password</label>
                <input type="password" name="officer_password" required placeholder="Set Password">
                
                <button type="submit" name="add_officer">Create Officer Account</button>
            </form>
        </div>

        <!-- Add Station Form -->
        <div class="card">
            <h2>Add Police Station</h2>
            <form method="POST">
                <label>Station Name</label>
                <input type="text" name="station_name" required placeholder="e.g. North District Station">
                
                <label>Address</label>
                <input type="text" name="address" required placeholder="Street Address">
                
                <label>Latitude</label>
                <input type="text" name="latitude" required placeholder="e.g. 28.6139">
                
                <label>Longitude</label>
                <input type="text" name="longitude" required placeholder="e.g. 77.2090">
                
                <button type="submit" name="add_station">Add Station</button>
            </form>
        </div>
    </div>

</body>
</html>