<?php
include '../bin/conn.php';
include '../bin/access.php';
session_start();

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

// Restrict access to logged-in users
if (!isset($_SESSION['adminName'])) {
    header("Location: ../sign-in.php");
    exit;
}

// Fetch drivers categorized by car type
$sql = "SELECT regno, Full_name, cartype, location, username FROM drivers ORDER BY cartype";
$result = $conn->query($sql);

// Function to generate a unique username
function generateUsername($fullName, $conn) {
  // Extract the first letter of the first name and the surname
  $nameParts = explode(" ", $fullName);
  $username = strtolower($nameParts[0][0] . $nameParts[1]);

  // Initialize count to avoid undefined variable issues
  $count = 0;

  // Check if this username already exists
  $checkUsernameSql = "SELECT COUNT(*) FROM drivers WHERE username = ?";
  $stmt = $conn->prepare($checkUsernameSql);
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->bind_result($count); // Ensure $count is initialized here
  $stmt->fetch();
  $stmt->close();

  // If the username exists, append a number to make it unique
  if ($count > 0) {
      $i = 1;
      do {
          $newUsername = $username . $i;
          $stmt = $conn->prepare($checkUsernameSql);
          $stmt->bind_param("s", $newUsername);
          $stmt->execute();
          $stmt->bind_result($count); // Re-initialize $count here
          $stmt->fetch();
          $stmt->close();
          $i++;
      } while ($count > 0);
      $username = $newUsername;
  }

  return $username;
}

// Handle adding a new driver
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_driver'])) {
  $regno = $_POST['regno'];
  $full_name = $_POST['full_name'];
  $cartype = $_POST['cartype'];  // Direct input for car type
  $location = $_POST['location'];

  // Generate the username based on the full name
  $username = generateUsername($full_name, $conn);

  // Generate the default password: SHOFCO<username>
  $password = "SHOFCO" . $username;

  // Insert the new driver into the database
  $insert_sql = "INSERT INTO drivers (regno, Full_name, cartype, location, username, password) 
                 VALUES (?, ?, ?, ?, ?, ?)";

  $stmt = $conn->prepare($insert_sql);
  $stmt->bind_param("ssssss", $regno, $full_name, $cartype, $location, $username, $password);

  if ($stmt->execute()) {
      echo "<script>alert('Driver added successfully!'); window.location.href = 'driverman.php';</script>";
  } else {
      echo "<script>alert('Error: " . htmlspecialchars($stmt->error) . "');</script>";
  }
}

// Handle deleting a driver
if (isset($_GET['delete_driver'])) {
    $regno = $_GET['delete_driver'];

    // Delete the driver from the database
    $delete_sql = "DELETE FROM drivers WHERE regno = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("s", $regno);

    if ($stmt->execute()) {
        echo "<script>alert('Driver deleted successfully!'); window.location.href = 'driverman.php';</script>";
    } else {
        echo "<script>alert('Error: " . htmlspecialchars($stmt->error) . "');</script>";
    }
}

// Handle updating driver details
if (isset($_POST['update_driver'])) {
    $regno = $_POST['regno'];
    $full_name = $_POST['full_name'];
    $cartype = $_POST['cartype'];  // Direct input for car type
    $location = $_POST['location'];

    // Update driver details
    $update_sql = "UPDATE drivers SET Full_name = ?, cartype = ?, location = ? WHERE regno = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssss", $full_name, $cartype, $location, $regno);

    if ($stmt->execute()) {
        echo "<script>alert('Driver updated successfully!'); window.location.href = 'driverman.php';</script>";
    } else {
        echo "<script>alert('Error: " . htmlspecialchars($stmt->error) . "');</script>";
    }

    if ($stmt->execute()) {
        header("Location: driver_management.php?message=" . urlencode("Driver added successfully!"));
        exit;
    } else {
        header("Location: driver_management.php?error=" . urlencode("Error: " . $stmt->error));
        exit;
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Driver Management</title>
  <link rel="stylesheet" href="../css/adm.css">
  <style>
    /* Basic styling */
    body {
        font-family: Arial, sans-serif;
        overflow-y: scroll;
    }
    .container {
        max-width: 900px;
        margin: 0 auto;
        padding: 30px;
        background: #ddd;
        color: black;
        border-radius: 12px;
    }
    .header {
        text-align: center;
        margin-bottom: 20px;
        border-radius: 6px;
    }
    .driver-table {
        width: 100%;
        border-collapse: collapse;
    }
    .driver-table th, .driver-table td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
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
    .edit-btn, .delete-btn {
        background-color: #ffa500;
        color: white;
        padding: 5px 10px;
        border: none;
        cursor: pointer;
    }
    .delete-btn {
        background-color: #f44336;
    }
    .edit-btn:hover, .delete-btn:hover {
        opacity: 0.8;
    }
  </style>
</head>
<body>
<header>
        <img src="../css/shofco.png" alt="Logo">
        <h1>Admin Panel</h1>
        <div class="admin-name">
            <?php echo "Welcome, " . htmlspecialchars($adminFullName); ?>
        </div>
    </header>
    <br>
  <div class="container">
    <header class="header">
      <h1>Driver Management</h1>
      <p>Manage your drivers and vehicle details.</p>
    </header>
    <?php if (isset($_GET['message'])): ?>
    <div class="alert success">
        <?= htmlspecialchars($_GET['message']) ?>
    </div>
    <?php elseif (isset($_GET['error'])): ?>
    <div class="alert error">
        <?= htmlspecialchars($_GET['error']) ?>
    </div>
    <?php endif; ?>
    <!-- Form to add a new driver -->
    <h2>Add New Driver</h2>
    <form method="POST" action="">
      <div class="form-group">
        <label for="regno">Driver Reg No:</label>
        <input type="text" name="regno" id="regno" required>
      </div>
      <div class="form-group">
        <label for="full_name">Full Name:</label>
        <input type="text" name="full_name" id="full_name" required>
      </div>
      <div class="form-group">
        <label for="cartype">Car Type:</label>
        <input type="text" name="cartype" id="cartype" required placeholder="Enter car type (e.g., Sedan, SUV)">
      </div>
      <div class="form-group">
        <label for="location">Location:</label>
        <input type="text" name="location" id="location" required>
      </div>
      <button type="submit" name="add_driver">Add Driver</button>
      <br>
      <button type="button" class="btn-secondary" onclick="window.location.href='../sadmin.php';">Return to Admin Panel</button>
    </form>

    <!-- Displaying the list of drivers -->
    <h2>Driver List</h2>
    <table class="driver-table">
      <thead>
        <tr>
          <th>Reg No</th>
          <th>Full Name</th>
          <th>Car Type</th>
          <th>Location</th>
          <th>Username</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0) : ?>
          <?php 
          // Displaying drivers grouped by car type
          $current_cartype = "";
          while ($row = $result->fetch_assoc()) :
            if ($row['cartype'] != $current_cartype) {
              if ($current_cartype != "") echo "</tbody>";
              $current_cartype = $row['cartype'];
              echo "<thead><tr><th colspan='6'>Car Type: $current_cartype</th></tr></thead><tbody>";
            }
          ?>
            <tr>
              <td><?= htmlspecialchars($row['regno']) ?></td>
              <td><?= htmlspecialchars($row['Full_name']) ?></td>
              <td><?= htmlspecialchars($row['cartype']) ?></td>
              <td><?= htmlspecialchars($row['location']) ?></td>
              <td><?= htmlspecialchars($row['username']) ?></td> <!-- Display the generated username -->
              <td>
                <a href="edit_driver.php?regno=<?= htmlspecialchars($row['regno']) ?>" class="edit-btn">Edit</a>
                <a href="?delete_driver=<?= htmlspecialchars($row['regno']) ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this driver?')">Delete</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else : ?>
          <tr>
            <td colspan="6">No drivers found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
