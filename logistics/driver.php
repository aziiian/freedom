<?php
include "./bin/conn.php"; // Database connection
session_start();

// Restrict access to logged-in users
if (!isset($_SESSION['driverRegno'])) {
    header("Location: ../sign-in.php");
    exit;
}

$driverRegno = $_SESSION['driverRegno'];
$driverName = "Driver"; // Default name

// Fetch driver's full name based on regno
if ($stmt = $conn->prepare("SELECT Full_name FROM drivers WHERE regno = ?")) {
    $stmt->bind_param("s", $driverRegno);
    $stmt->execute();
    $stmt->bind_result($fullName);
    if ($stmt->fetch()) {
        $driverName = $fullName;
    }
    $stmt->close();
}

// Fetch total kilometers covered (sum of mile_end - mile_start where status = 3)
$totalKmCovered = 0;
$queryKm = "SELECT SUM(mile_end - mile_start) AS total_km FROM status WHERE regno = ? AND status = 3";
if ($stmt = $conn->prepare($queryKm)) {
    $stmt->bind_param("s", $driverRegno);
    $stmt->execute();
    $stmt->bind_result($totalKm);
    if ($stmt->fetch()) {
        $totalKmCovered = $totalKm ?: 0;
    }
    $stmt->close();
}

// Fetch total rides finished (status = 3)
$totalRidesFinished = 0;
$queryFinishedRides = "SELECT COUNT(*) FROM status WHERE regno = ? AND status = 3";
if ($stmt = $conn->prepare($queryFinishedRides)) {
    $stmt->bind_param("s", $driverRegno);
    $stmt->execute();
    $stmt->bind_result($finishedRides);
    if ($stmt->fetch()) {
        $totalRidesFinished = $finishedRides;
    }
    $stmt->close();
}

// Fetch pending rides (status = 1 or 2)
$totalPendingRides = 0;
$queryPendingRides = "SELECT COUNT(*) FROM status WHERE regno = ? AND status IN (1, 2)";
if ($stmt = $conn->prepare($queryPendingRides)) {
    $stmt->bind_param("s", $driverRegno);
    $stmt->execute();
    $stmt->bind_result($pendingRides);
    if ($stmt->fetch()) {
        $totalPendingRides = $pendingRides;
    }
    $stmt->close();
}

// Fetch the most recent finished ride's mileage (using the highest id from the status table)
$vehicleMileage = 0;
$queryMileage = "SELECT mile_end FROM status WHERE regno = ? AND status = 3 ORDER BY id DESC LIMIT 1";
if ($stmt = $conn->prepare($queryMileage)) {
    $stmt->bind_param("s", $driverRegno);
    $stmt->execute();
    $stmt->bind_result($mileEnd);
    if ($stmt->fetch()) {
        $vehicleMileage = $mileEnd ?: 0;
    }
    $stmt->close();
}

// Fetch total hours covered (sum of hours difference between time_start and time_end for status = 3)
$totalHoursCovered = 0;
$queryHours = "
    SELECT SUM(TIMESTAMPDIFF(HOUR, time_start, time_end)) AS total_hours 
    FROM status 
    WHERE regno = ? AND status = 3
";
if ($stmt = $conn->prepare($queryHours)) {
    $stmt->bind_param("s", $driverRegno);
    $stmt->execute();
    $stmt->bind_result($totalHours);
    if ($stmt->fetch()) {
        $totalHoursCovered = $totalHours ?: 0; // Default to 0 if no hours found
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard</title>
    <link rel="stylesheet" href="./css/driver.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="driver.php"> <h2>Dashboard</h2> </a> 
        <a href="./driver/rides.php">Rides</a>
        <a href="./driver/car_status.php">Car Status</a>
        <a href="./driver/status.php">Status</a>
        <a href="./driver/settings.php">Settings</a>
        <a href="./bin/logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <img src="./css/shofco.png" alt="Logo">
            <h1>Driver Dashboard</h1>
            <div>Driver: <?php echo htmlspecialchars($driverName); ?></div>
        </header>

        <!-- Mileage Tile (Full Width) -->
        <div class="mileage-tile">
            <p><?php echo number_format($vehicleMileage, 2); ?> km</p>
            <h3>Vehicle Mileage</h3>
        </div>

        <!-- Dashboard Tiles in Grid -->
        <div class="tile-container">
            <!-- Total Kilometers Covered -->
            <div class="dashboard-tile">
                <p><?php echo number_format($totalKmCovered, 2); ?></p>
                <h3>Total Kilometers Covered</h3>
            </div>

            <!-- Total Hours Covered -->
            <div class="dashboard-tile">
                <p><?php echo $totalHoursCovered; ?></p>
                <h3>Total Hours Covered</h3>
            </div>

            <!-- Total Rides Finished -->
            <div class="dashboard-tile">
                <p><?php echo $totalRidesFinished; ?></p>
                <h3>Total Rides Finished</h3>
            </div>

            <!-- Pending Rides -->
            <div class="dashboard-tile">
                <p><?php echo $totalPendingRides; ?></p>
                <h3>Pending Rides</h3>
            </div>
        </div>
    </div>
</body>
</html>
