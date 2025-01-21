<?php
// Include the database connection
include "../bin/conn.php";
session_start();

// Restrict access to logged-in drivers
if (!isset($_SESSION['driverRegno'])) {
    header("Location: ../sign-in.php");
    exit;
}

$driverRegno = $_SESSION['driverRegno']; // Driver's registration number
$errors = [];
$successMessage = "";

// Handle form submission for service mileage
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_mileage'])) {
    $previousServiceMileage = intval($_POST['current_service_mileage']);
    $increment = intval($_POST['increment']);

    if ($increment !== 5000 && $increment !== 7000) {
        $errors[] = "Invalid increment value selected.";
    } else {
        $nextServiceMileage = $previousServiceMileage + $increment;

        // Update the vehicles table
        $updateQuery = "UPDATE vehicles SET current_service_mileage = ?, next_service_mileage = ? WHERE regno = ?";
        if ($stmt = $conn->prepare($updateQuery)) {
            $stmt->bind_param("iis", $previousServiceMileage, $nextServiceMileage, $driverRegno);
            if ($stmt->execute()) {
                $successMessage = "Service mileage updated successfully!";
            } else {
                $errors[] = "Failed to update mileage.";
            }
            $stmt->close();
        }
    }
}

// Handle form submission for reporting vehicle issues
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['report_issue'])) {
    $issueDescription = trim($_POST['vehicle_issue']);

    if (empty($issueDescription)) {
        $errors[] = "Issue description cannot be empty.";
    } else {
        // Update the vehicles table with the reported issue
        $issueQuery = "UPDATE vehicles SET vehicle_issues = ?, issue_status = 'Pending' WHERE regno = ?";
        if ($stmt = $conn->prepare($issueQuery)) {
            $stmt->bind_param("ss", $issueDescription, $driverRegno);
            if ($stmt->execute()) {
                $successMessage = "Vehicle issue reported successfully!";
            } else {
                $errors[] = "Failed to report the issue.";
            }
            $stmt->close();
        }
    }
}

// Fetch the most recent mile_end for the vehicle from the status table
$latestMileEnd = 0;
$statusQuery = "SELECT mile_end FROM status WHERE regno = ? AND status = 3 ORDER BY id DESC LIMIT 1";
if ($stmt = $conn->prepare($statusQuery)) {
    $stmt->bind_param("s", $driverRegno);
    $stmt->execute();
    $stmt->bind_result($latestMileEnd);
    $stmt->fetch();
    $latestMileEnd = intval($latestMileEnd); // Ensure it's an integer
    $stmt->close();
}

// Fetch vehicle information from the vehicles table
$currentMileage = $nextServiceMileage = $issueDescription = "";
$vehicleQuery = "SELECT current_service_mileage, next_service_mileage, vehicle_issues FROM vehicles WHERE regno = ?";
if ($stmt = $conn->prepare($vehicleQuery)) {
    $stmt->bind_param("s", $driverRegno);
    $stmt->execute();
    $stmt->bind_result($currentMileage, $nextServiceMileage, $issueDescription);
    $stmt->fetch();
    $currentMileage = intval($currentMileage); // Ensure it's an integer
    $nextServiceMileage = intval($nextServiceMileage); // Ensure it's an integer
    $stmt->close();
}

// Check if the next service is near (100 km threshold)
$isServiceNear = ($nextServiceMileage - $latestMileEnd <= 100);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Status</title>
    <link rel="stylesheet" href="../css/driver.css">
</head>
<body>
    <div class="sidebar">
        <a href="../driver.php"><h2>Dashboard</h2></a>
        <a href="./rides.php">Rides</a>
        <a href="./car_status.php">Car Status</a>
        <a href="./status.php">Status</a>
        <a href="./settings.php">Settings</a>
        <a href="../bin/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <header>
            <h1>Car Status</h1>
        </header>

        <?php if ($errors): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($successMessage): ?>
            <div class="success-message">
                <p><?php echo htmlspecialchars($successMessage); ?></p>
            </div>
        <?php endif; ?>

        <br>
        <!-- Service Mileage Section -->
        <div class="dash-tile">
            <h3>Update Service Mileage</h3>
            <form method="POST">
                <label for="current_service_mileage">Previous Service Mileage:</label>
                <input type="number" id="current_service_mileage" name="current_service_mileage" value="<?php echo htmlspecialchars($currentMileage); ?>" required>
                <label for="increment">Choose Increment:</label>
                <select id="increment" name="increment" required>
                    <option value="5000">5,000</option>
                    <option value="7000">7,000</option>
                </select>
                <button type="submit" name="update_mileage">Update Mileage</button>
            </form>
            <?php if ($isServiceNear): ?>
                <p>Service is near. Please request service!</p>
                <button onclick="requestService()">Request Service</button>
            <?php endif; ?>
        </div>

        <br>
        <!-- Report Vehicle Issues Section -->
        <div class="dash-tile">
            <h3>Report Vehicle Issues</h3>
            <?php if (empty($issueDescription)): ?>
                <form method="POST">
                    <label for="vehicle_issue">Describe the Issue:</label>
                    <br>
                    <textarea id="vehicle_issue" name="vehicle_issue" required></textarea>
                    <br>
                    <button type="submit" name="report_issue">Submit</button>
                </form>
            <?php else: ?>
                <p>Reported Issue: <?php echo htmlspecialchars($issueDescription); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function requestService() {
            alert("Service request functionality is not yet implemented.");
        }
    </script>
</body>
</html>
