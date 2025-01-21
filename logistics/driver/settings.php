<?php
// Include database connection
include "../bin/conn.php";
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['driverRegno'])) {
    header("Location: ../sign-in.php");
    exit;
}

// Get the logged-in driver's registration number
$driverRegno = $_SESSION['driverRegno'];

// Fetch the driver's current details from the database
$query = "SELECT * FROM drivers WHERE regno = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $driverRegno);
$stmt->execute();
$result = $stmt->get_result();
$driver = $result->fetch_assoc();

// Initialize message variable
$message = "";

// Handle form submissions for each field
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $field = $_POST['field'];
    $value = trim($_POST['value']);

    // Whitelist allowed fields for update
    $allowedFields = ['Full_name', 'regno', 'password', 'cartype', 'location', 'phoneno'];

    if (in_array($field, $allowedFields)) {
        // Additional validation for phone number if updating 'phoneno'
        if ($field === 'phoneno' && !preg_match('/^\d{10}$/', $value)) {
            $message = "Invalid phone number format. Please enter a 10-digit phone number.";
        } elseif (!empty($value)) {
            // Prepare and execute the update query
            $updateQuery = "UPDATE drivers SET $field = ? WHERE regno = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param('ss', $value, $driverRegno);

            if ($updateStmt->execute()) {
                $message = ucfirst($field) . " updated successfully!";
            } else {
                $message = "Failed to update " . ucfirst($field) . ".";
            }
        } else {
            $message = "Please fill in all fields.";
        }
    } else {
        $message = "Invalid field.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
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
            <h1>Driver Settings</h1>
        </header>

        <!-- Message Section -->
        <?php if (!empty($message)): ?>
            <div class="mileage-tile">
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <!-- Settings Form -->
        <div class="tile-container">
            <!-- Update Full Name -->
            <form method="POST" class="dashboard-tile">
                <h3>Update Full Name</h3>
                <br>
                <input 
                    type="text" 
                    name="value" 
                    value="<?php echo htmlspecialchars($driver['Full_name']); ?>" 
                    placeholder="Enter your full name"
                    required>
                <input type="hidden" name="field" value="Full_name">
                <br>
                <button type="submit">Update</button>
            </form>

            <!-- Update Car Registration Number -->
            <form method="POST" class="dashboard-tile">
                <h3>Update Car Registration Number</h3>
                <br>
                <input 
                    type="text" 
                    name="value" 
                    value="<?php echo htmlspecialchars($driver['regno']); ?>" 
                    placeholder="Enter your car registration number"
                    required>
                <input type="hidden" name="field" value="regno">
                <br>
                <button type="submit">Update</button>
            </form>

            <!-- Update Password -->
            <form method="POST" class="dashboard-tile">
                <h3>Update Password</h3>
                <br>
                <input 
                    type="password" 
                    name="value" 
                    placeholder="Enter a new password" 
                    required>
                <input type="hidden" name="field" value="password">
                <br>
                <button type="submit">Update</button>
            </form>

            <!-- Update Car Type -->
            <form method="POST" class="dashboard-tile">
                <h3>Update Car Type</h3>
                <br>
                <input 
                    type="text" 
                    name="value" 
                    value="<?php echo htmlspecialchars($driver['cartype']); ?>" 
                    placeholder="Enter your car type"
                    required>
                <input type="hidden" name="field" value="cartype">
                <br>
                <button type="submit">Update</button>
            </form>

            <!-- Update Location -->
            <form method="POST" class="dashboard-tile">
                <h3>Update Location</h3>
                <br>
                <input 
                    type="text" 
                    name="value" 
                    value="<?php echo htmlspecialchars($driver['location']); ?>" 
                    placeholder="Enter your current location"
                    required>
                <input type="hidden" name="field" value="location">
                <br>
                <button type="submit">Update</button>
            </form>

            <!-- Update Phone Number -->
            <form method="POST" class="dashboard-tile">
                <h3>Update Phone Number</h3>
                <br>
                <input 
                    type="text" 
                    name="value" 
                    value="<?php echo htmlspecialchars($driver['phoneno']); ?>" 
                    placeholder="Enter your phone number"
                    required>
                <input type="hidden" name="field" value="phoneno">
                <br>
                <button type="submit">Update</button>
            </form>
        </div>
    </div>
</body>
</html>
