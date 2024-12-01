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

// Fetch all assignments from the database
$sql_assignments = "SELECT * FROM assign_hire";
$assignments_result = $conn->query($sql_assignments);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Assigned Hires</title>
  <link rel="stylesheet" href="../css/adm.css">
  <style>
    body {
      font-family: Arial, sans-serif;
    }
    .assigned-hires-container {
      max-width: 800px;
      margin: 0 auto;
      padding: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    table thead{
      padding: 10px;
      border: solid black 1px;
    }
    table th, table td {
      padding: 10px;
      text-align: left;
      border: 1px solid #ddd;
    }
    .print-btn {
      background-color: #f44336;
      color: white;
      padding: 10px 20px;
      border: none;
      cursor: pointer;
    }
    .print-btn:hover {
      background-color: #e91e63;
    }
  </style>
</head>

<body>
  <header>
    <img src="../css/shofco.png" alt="Logo">
    <h1>Assigned Hires</h1>
    <div class="admin-name">
      <?php echo "Admin: " . htmlspecialchars($adminFullName); ?>
    </div>
  </header>

  <main>
    <div class="assigned-hires-container">
      <h2>Assigned Vehicles</h2>
      <table>
        <thead>
          <tr>
            <th>Booking ID</th>
            <th>Employee Email</th>
            <th>Destination</th>
            <th>Vehicle Type</th>
            <th>Driver</th>
            <th>Reg No</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $assignments_result->fetch_assoc()) : ?>
            <tr>
              <td><?= htmlspecialchars($row['booking_id']) ?></td>
              <td><?= htmlspecialchars($row['employee_mail']) ?></td>
              <td><?= htmlspecialchars($row['destination']) ?></td>
              <td><?= htmlspecialchars($row['carType']) ?></td>
              <td><?= htmlspecialchars($row['driver_full_name']) ?></td>
              <td><?= htmlspecialchars($row['regNo']) ?></td>
              <td><?= htmlspecialchars($row['status']) ?></td>
              <td>
                <a href="print_ticket.php?booking_id=<?= htmlspecialchars($row['booking_id']) ?>" class="print-btn" target="_blank">Print Ticket</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>

</body>

</html>
