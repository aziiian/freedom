<?php
include '../bin/conn.php';
include '../bin/access.php';
session_start();

// Restrict access to logged-in users
if (!isset($_SESSION['adminName'])) {
    header("Location: ../sign-in.php");
    exit;
}

// Check if a driver is being edited
if (isset($_GET['regno'])) {
    $regno = $_GET['regno'];

    // Fetch driver details
    $sql = "SELECT regno, Full_name, cartype, location FROM drivers WHERE regno = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $regno);
    $stmt->execute();
    $stmt->bind_result($driverRegno, $fullName, $carType, $location);
    $stmt->fetch();
    $stmt->close();
} else {
    header("Location: driver_management.php");
    exit;
}

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_driver'])) {
    $regno = $_POST['regno'];
    $full_name = $_POST['full_name'];
    $cartype = $_POST['cartype'];
    $location = $_POST['location'];

    // Update driver details
    $update_sql = "UPDATE drivers SET Full_name = ?, cartype = ?, location = ? WHERE regno = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssss", $full_name, $cartype, $location, $regno);

    if ($stmt->execute()) {
        
        header("Location: driverman.php");
        exit;
    } else {
        $error = "Error updating driver: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Driver</title>
    <link rel="stylesheet" href="../css/adm.css">
    <style>
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background: #ddd;
            color: black;
            border-radius: 12px;
        }
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label {
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 90%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .btn-secondary {
            background-color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Driver</h1>
        <form method="POST" action="">
            <div class="form-group">
                <label for="regno">Driver Reg No:</label>
                <input type="text" name="regno" id="regno" value="<?= htmlspecialchars($driverRegno) ?>" readonly>
            </div>
            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" name="full_name" id="full_name" value="<?= htmlspecialchars($fullName) ?>" required>
            </div>
            <div class="form-group">
                <label for="cartype">Car Type:</label>
                <input type="text" name="cartype" id="cartype" value="<?= htmlspecialchars($carType) ?>" required>
            </div>
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" name="location" id="location" value="<?= htmlspecialchars($location) ?>" required>
            </div>
            <button type="submit" name="update_driver">Save Changes</button>
            <br>
            <button type="button" class="btn-secondary" onclick="window.location.href='driverman.php';">Cancel</button>
        </form>
        <?php if (isset($error)) : ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
