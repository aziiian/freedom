<?php
include './bin/conn.php';
session_start();

// Restrict access to admin.php if not logged in
if (!isset($_SESSION['adminName'])) {
    header("Location: sign-in.php");
    exit;
}

// Prevent caching of the page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Fetch the admin's full name from the database
$adminFullName = "Admin"; // Default if query fails
if ($stmt = $conn->prepare("SELECT fullname FROM admin WHERE username = ?")) {
    $stmt->bind_param("s", $_SESSION['adminName']);
    $stmt->execute();
    $stmt->bind_result($fullName);
    if ($stmt->fetch()) {
        $adminFullName = $fullName;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics Admin Panel</title>
    <link rel="stylesheet" href="./css/adm.css">
</head>

<body>
    <!-- Header -->
    <header>
        <img src="./css/shofco.png" alt="Logo">
        <h1>Logistics Admin Panel</h1>
        <div class="admin-name">
            <?php echo "Welcome, " . htmlspecialchars($adminFullName); ?>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="tile" onclick="window.location.href='./vadmin/driverman.php';">
            <h2>Driver Management</h2>
            <p>Manage and view drivers.</p>
        </div>
        <div class="tile" onclick="window.location.href='./vadmin/viewhire.php';">
            <h2>View Hires</h2>
            <p>View all hire requests.</p>
        </div>
        <div class="tile" onclick="window.location.href='./404.php';">
            <h2>Driver Request</h2>
            <p>Manage driver Issues.</p>
        </div>
        <div class="tile" onclick="window.location.href='./vadmin/view_assigned.php';">
            <h2>Assigned Hires</h2>
            <p>Assign Drivers to Requests.</p>
        </div>
        <div class="tile" onclick="window.location.href='./vadmin/driveractivity.php';">
            <h2>Driver Activity</h2>
            <p>View Driver Activity.</p>
        </div>
        <div class="tile" onclick="window.location.href='./vadmin/report.php';">
            <h2>Reports </h2>
            <p>View Logistics Reports.</p>
        </div>
        <div class="tile" onclick="window.location.href='./vadmin/settings.php';">
            <h2>Profile</h2>
            <p>Configure your preferences.</p>
        </div>
        <div class="tile" onclick="logout();">
            <h2>Logout</h2>
            <p>Sign out of your session.</p>
        </div>
    </main>

    <!-- Logout Script -->
    <script>
        function logout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "./bin/logout.php"; // logout script
            }
        }
    </script>
</body>

</html>
