<?php
include '../bin/conn.php';
include '../bin/access.php';
session_start();

// Fetch the admin's full name
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

// Fetch all assignments
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
      max-width: 1000px;
      margin: 0 auto;
      padding: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    table th, table td {
      padding: 10px;
      text-align: left;
      border: 1px solid #ddd;
    }
    .status-active {
      background-color: green;
      color: white;
      padding:3px;
      text-align: center;
      font-weight: bold;
    }
    .status-inprogress {
      background-color: yellow;
      padding:3px;
      color: black;
      text-align: center;
      font-weight: bold;
    }
    .status-finished {
      background-color: gray;
      color: white;
      padding:3px;
      text-align: center;
      font-weight: bold;
    }
    .print-btn {
      background: darkkhaki;
      color: white;
      width: auto;
      padding: 10px 20px;
      border: none;
      cursor: pointer;
    }
    .print-btn:hover {
      background-color: darkgray;
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
              <td>
                <?php
                $statusClass = '';
                $statusText = '';
                switch ($row['status']) {
                  case 1:
                    $statusClass = 'status-active';
                    $statusText = 'Active';
                    break;
                  case 2:
                    $statusClass = 'status-inprogress';
                    $statusText = 'In Progress';
                    break;
                  case 3:
                    $statusClass = 'status-finished';
                    $statusText = 'Finished';
                    break;
                }
                ?>
                <span class="<?= $statusClass ?>"><?= $statusText ?></span>
              </td>
              <td>
                <button class="print-btn" onclick="printTicket('<?= htmlspecialchars($row['booking_id']) ?>')">Print Ticket</button>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table> 
      <br>
      <!-- Back to Admin Button -->
    <div class="btn-sec" style="width: fit-content;">
      <button type="button" class="btn-sec" onclick="window.location.href='../admin.php';">Return to Admin Panel</button>
    </div>   
  </div>
  </main>

  <script>
    // Function to open print preview
    function printTicket(bookingId) {
      const printWindow = window.open('print_ticket.php?booking_id=' + bookingId, '_blank', 'width=800,height=600');
      printWindow.focus();
    }
  </script>
</body>

</html>
