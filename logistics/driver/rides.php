<?php
include "../bin/conn.php";
session_start();

// Restrict access to logged-in users
if (!isset($_SESSION['driverRegno'])) {
    header("Location: ../sign-in.php");
    exit;
}

$driverRegno = $_SESSION['driverRegno'];

// Fetch assigned rides
$queryRides = "SELECT id, booking_id, employee_mail, destination FROM assign_hire WHERE regno = ? AND status = 1";
$rides = [];
if ($stmt = $conn->prepare($queryRides)) {
    $stmt->bind_param("s", $driverRegno);
    $stmt->execute();
    $stmt->bind_result($id, $bookingId, $employeeEmail, $destination);
    while ($stmt->fetch()) {
        $rides[] = [
            'id' => $id,
            'booking_id' => $bookingId,
            'email' => $employeeEmail,
            'destination' => $destination
        ];
    }
    $stmt->close();
}

// Fetch total kilometers covered
$totalKmCovered = 0;
$queryTotalKm = "SELECT SUM(mile_end - mile_start) AS total_km FROM status WHERE regno = ? AND mile_start > 0 AND mile_end > 0";
if ($stmt = $conn->prepare($queryTotalKm)) {
    $stmt->bind_param("s", $driverRegno);
    $stmt->execute();
    $stmt->bind_result($totalKm);
    if ($stmt->fetch()) {
        $totalKmCovered = $totalKm ?: 0;
    }
    $stmt->close();
}

// Fetch completed rides count
$completedRidesCount = 0;
$queryCompletedRides = "SELECT COUNT(*) FROM status WHERE regno = ? AND status = 3";
if ($stmt = $conn->prepare($queryCompletedRides)) {
    $stmt->bind_param("s", $driverRegno);
    $stmt->execute();
    $stmt->bind_result($count);
    if ($stmt->fetch()) {
        $completedRidesCount = $count;
    }
    $stmt->close();
}

// Handle mile_start updates, time_start insertion, and mark both status tables as enroute (status = 2)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateMileage'])) {
    $id = $_POST['id'];           // ID of the ride
    $mileStart = $_POST['mile_end']; // Mileage entered for mile_start

    date_default_timezone_set('Africa/Nairobi'); // To ensure correct time zone
    
    $timeStart = date("Y-m-d H:i:s"); // Current timestamp for time_start

    if (!empty($mileStart)) {
        // Update `status` table with `mile_start` and `time_start`, and set the ride as enroute (status = 2)
        $queryUpdate = "UPDATE status 
                        SET mile_start = ?, time_start = ?, status = 2 
                        WHERE id = ?";
        if ($stmt = $conn->prepare($queryUpdate)) {
            $stmt->bind_param("isi", $mileStart, $timeStart, $id);
            $stmt->execute();
            $stmt->close();
        }

        // Update the `assign_hire` table to mark the ride as enroute (status = 2)
        $queryUpdateAssignHire = "UPDATE assign_hire 
                                  SET status = 2 
                                  WHERE id = ?";
        if ($stmt = $conn->prepare($queryUpdateAssignHire)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rides</title>
    <link rel="stylesheet" href="../css/driver.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="../driver.php"><h2>Dashboard</h2></a>
        <a href="./rides.php">Rides</a>
        <a href="./car_status.php">Car Status</a>
        <a href="./status.php">Status</a>
        <a href="./settings.php">Settings</a>
        <a href="../bin/logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <img src="../css/shofco.png" alt="Logo">
            <h1>Assigned Rides</h1>
        </header>

        <!-- Dashboard Stats -->
        <div class="tile-container">
            <div class="dashboard-tile-alt">
                <p><?php echo number_format($totalKmCovered, 2); ?> km</p>
                <h3>Total Kilometers Covered</h3>
            </div>
            <div class="dashboard-tile-alt">
                <p><?php echo $completedRidesCount; ?></p>
                <h3>Completed Rides</h3>
            </div>
        </div>

        <!-- Assigned Rides -->
        <div class="tile-container">
            <div class="dashboard-tile-alt1"> 
                <?php if (!empty($rides)): ?>
                    <?php foreach ($rides as $ride): ?>
                        <div class="dashboard-tile1">
                            <p><strong>Booking ID:</strong> <?php echo htmlspecialchars($ride['booking_id']); ?></p>
                            <p><strong>Employee Email:</strong> <?php echo htmlspecialchars($ride['email']); ?></p>
                            <p><strong>Destination:</strong> <?php echo htmlspecialchars($ride['destination']); ?></p>
                            <button class="accept-btn" onclick="openPopup('<?php echo $ride['id']; ?>')">Accept</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No rides assigned.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Overlay -->
    <div id="overlay" class="overlay" onclick="closePopup()"></div>

    <!-- Popup for Ride Acceptance -->
    <div id="popup" class="popup">
        <form method="POST">
            <h3>Accept Ride</h3>
            <input type="hidden" id="id" name="id"> <!-- Hidden field for ride ID -->
            <label for="mile_end">Mileage Start:</label>
            <input type="number" name="mile_end" id="mile_end" required>
            <button type="submit" name="updateMileage">Submit</button>
            <button type="button" onclick="closePopup()">Cancel</button>
        </form>
    </div>

    <script>
        // Function to open the popup
        function openPopup(id) {
            document.getElementById('popup').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('id').value = id; // Set ride ID in the hidden input
        }

        // Function to close the popup
        function closePopup() {
            document.getElementById('popup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
    </script>
</body>
</html>
