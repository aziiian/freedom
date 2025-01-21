<?php
include "../bin/conn.php"; // Database connection
session_start();

// Check if driver is logged in
if (!isset($_SESSION['driverRegno'])) {
    header("Location: ../sign-in.php");
    exit;
}

$driverRegno = $_SESSION['driverRegno'];

// Fetch active rides (status = 2) and booking_id from assign_hire by joining on id
$queryEnrouteRides = "SELECT s.id, s.regno, s.mile_start, s.mile_end, a.booking_id, a.employee_mail, a.destination
                      FROM status s
                      JOIN assign_hire a ON s.id = a.id
                      WHERE s.regno = ? AND s.status = 2";
$enrouteRides = [];
if ($stmt = $conn->prepare($queryEnrouteRides)) {
    $stmt->bind_param("s", $driverRegno);
    $stmt->execute();
    $stmt->bind_result($id, $regno, $mileStart, $mileEnd, $bookingId, $employeemail, $destination);
    while ($stmt->fetch()) {
        $enrouteRides[] = [
            'id' => $id,
            'regno' => $regno,
            'mile_start' => $mileStart,
            'mile_end' => $mileEnd,
            'booking_id' => $bookingId,
            'employee_mail' => $employeemail,
            'destination' => $destination
        ];
    }
    $stmt->close();
}

// Fetch finished rides (status = 3) and related information
$queryFinishedRides = "SELECT s.id, s.regno, s.mile_start, s.mile_end, s.route, s.time_end, a.booking_id, a.destination, a.employee_mail 
                       FROM status s 
                       JOIN assign_hire a ON s.id = a.id
                       WHERE s.regno = ? AND s.status = 3";
$finishedRides = [];
if ($stmt = $conn->prepare($queryFinishedRides)) {
    $stmt->bind_param("s", $driverRegno);
    $stmt->execute();
    $stmt->bind_result($id, $regno, $mileStart, $mileEnd, $route, $timeEnd, $bookingId, $destination, $employeemail);
    while ($stmt->fetch()) {
        $finishedRides[] = [
            'id' => $id,
            'regno' => $regno,
            'mile_start' => $mileStart,
            'mile_end' => $mileEnd,
            'route' => $route,
            'time_end' => $timeEnd,
            'booking_id' => $bookingId,
            'employee_mail' => $employeemail,
            'destination' => $destination
        ];
    }
    $stmt->close();
}

// Handle updates for mile_end, route, and time_end
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateMileage'])) {
    $id = $_POST['id'];           // ID of the ride
    $mileEnd = $_POST['mile_end']; // Mileage entered for mile_end
    $route = $_POST['route'];     // Route entered by the driver

    // Set the timezone to ensure accurate time
    date_default_timezone_set('Africa/Nairobi'); // Replace with your desired timezone
    $timeEnd = date("Y-m-d H:i:s"); // Record the current system time as time_end

    if (!empty($mileEnd) && !empty($route)) {
        // Update the `status` table with mile_end, time_end, and route, and set the ride status to completed (status = 3)
        $queryUpdate = "UPDATE status 
                        SET mile_end = ?, time_end = ?, route = ?, status = 3 
                        WHERE id = ?";
        if ($stmt = $conn->prepare($queryUpdate)) {
            $stmt->bind_param("issi", $mileEnd, $timeEnd, $route, $id);
            $stmt->execute();
            $stmt->close();
        }

        // Update the `assign_hire` table to mark the ride as completed (status = 3)
        $queryUpdateAssignHire = "UPDATE assign_hire 
                                  SET status = 3 
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
    <title>Status</title>
    <link rel="stylesheet" href="../css/driver.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="../driver.php"> <h2>Dashboard</h2> </a>
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
            <h1>Ride Status</h1>
        </header>
        
        <!-- Enroute Rides -->
        <section>
            <h2>Enroute Rides</h2>
            <div class="tile-container">
                <div class="dashboard-tile-alt1">
                    <?php if (!empty($enrouteRides)): ?>
                        <?php foreach ($enrouteRides as $ride): ?>
                            <div class="dashboard-tile1">
                                <p><strong>Booking ID:</strong> <?php echo htmlspecialchars($ride['booking_id']); ?></p>
                                <p><strong>Employee Mail:</strong> <?php echo htmlspecialchars($ride['employee_mail']); ?></p>
                                <p><strong>Destination:</strong> <?php echo htmlspecialchars($ride['destination']); ?></p>
                                <p><strong>Mileage Start:</strong> <?php echo htmlspecialchars($ride['mile_start']); ?> km</p>
                                <button class="complete-btn" onclick="openPopup('<?php echo $ride['id']; ?>')">Complete</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No enroute rides.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Finished Rides -->
        <section>
            <h2>Finished Rides</h2>
            <div class="tile-container">
                <div class="dashboard-tile-alt1">
                    <?php if (!empty($finishedRides)): ?>
                        <?php foreach ($finishedRides as $ride): ?>
                            <div class="dashboard-tile1">
                                <p><strong>Booking ID:</strong> <?php echo htmlspecialchars($ride['booking_id']); ?></p>
                                <p><strong>Employee Mail:</strong> <?php echo htmlspecialchars($ride['employee_mail']); ?></p>
                                <p><strong>Mileage Start:</strong> <?php echo htmlspecialchars($ride['mile_start']); ?> km</p>
                                <p><strong>Mileage End:</strong> <?php echo htmlspecialchars($ride['mile_end']); ?> km</p>
                                <p><strong>Total Distance:</strong> <?php echo $ride['mile_end'] - $ride['mile_start']; ?> km</p>
                                <p><strong>Route:</strong> <?php echo htmlspecialchars($ride['route']); ?></p>
                                <p><strong>Time End:</strong> <?php echo htmlspecialchars($ride['time_end']); ?></p>
                                <p><strong>Destination:</strong> <?php echo htmlspecialchars($ride['destination']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No finished rides.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <!-- Popup for Enroute Ride Completion -->
    <div id="popup" class="popup">
        <form method="POST">
            <h3>Enter Ride Details</h3>
            <input type="hidden" id="id" name="id">
            <label for="mile_end">Mileage End:</label>
            <input type="number" name="mile_end" id="mile_end" required>
            <label for="route">Route:</label>
            <textarea name="route" id="route" rows="3" required></textarea>
            <button type="submit" name="updateMileage">Submit</button>
            <button type="button" onclick="closePopup()">Cancel</button>
        </form>
    </div>

    <script>
        function openPopup(id) {
            document.getElementById('popup').style.display = 'block';
            document.getElementById('id').value = id;
        }

        function closePopup() {
            document.getElementById('popup').style.display = 'none';
        }
    </script>
</body>
</html>
