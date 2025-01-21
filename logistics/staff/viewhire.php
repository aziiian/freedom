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

// Fetch hires from the database
$sql = "SELECT booking_id, employee_mail, departure_date, return_date, passengers, destination, department, 
        purpose, departure_time, return_time, shofco_list, hire_list FROM hire_tbl";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Hires</title>
  <link rel="stylesheet" href="../css/adm.css">
</head>

<body>
  <header>
    <img src="../css/shofco.png" alt="Logo">
    <h1>Admin Panel</h1>
    <div class="admin-name">
      <?php echo "Hires, " . htmlspecialchars($adminFullName); ?>
    </div>
  </header>

  <main>
    <div class="table-container">
      <h2>Hire Requests</h2>
      <table>
        <thead>
          <tr>
            <th>Booking ID</th>
            <th>Employee Mail</th>
            <th>Departure Date</th>
            <th>Return Date</th>
            <th>Passengers</th>
            <th>Destination</th>
            <th>Department</th>
            <th>Purpose</th>
            <th>Departure Time</th>
            <th>Return Time</th>
            <th>Shofco List</th>
            <th>Hire List</th>
            <th>Action</th> <!-- New Action column -->
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0) : ?>
            <?php while ($row = $result->fetch_assoc()) : ?>
              <tr>
                <td><?= htmlspecialchars($row['booking_id']) ?></td>
                <td><?= htmlspecialchars($row['employee_mail']) ?></td>
                <td><?= htmlspecialchars($row['departure_date']) ?></td>
                <td><?= htmlspecialchars($row['return_date']) ?></td>
                <td><?= htmlspecialchars($row['passengers']) ?></td>
                <td><?= htmlspecialchars($row['destination']) ?></td>
                <td><?= htmlspecialchars($row['department']) ?></td>
                <td><?= htmlspecialchars($row['purpose']) ?></td>
                <td><?= htmlspecialchars($row['departure_time']) ?></td>
                <td><?= htmlspecialchars($row['return_time']) ?></td>
                <td><?= htmlspecialchars($row['shofco_list']) ?></td>
                <td><?= htmlspecialchars($row['hire_list']) ?></td>
                <td>
                  <!-- Link to assign.php with booking_id, employee_mail, and destination -->
                  <a href="assign.php?booking_id=<?= urlencode($row['booking_id']) ?>&email=<?= urlencode($row['employee_mail']) ?>&destination=<?= urlencode($row['destination']) ?>">Assign Vehicle</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else : ?>
            <tr>
              <td colspan="13">No hire requests found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
      <br>
      <!-- Back to Admin Button -->
    <div class="btn-sec" style="border: solid 1px #000; width: fit-content;">
    <button type="button" class="btn-sec" onclick="window.location.href='../staff.php';" style="background: #895129;">Return to Panel</button>
    </div>
    </div>
  </main>
</body>

</html>
