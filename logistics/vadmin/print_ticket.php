<?php
include '../bin/conn.php';

if (!isset($_GET['booking_id'])) {
    die("Booking ID is required");
}

$bookingId = $_GET['booking_id'];

// Fetch details from `hire_tbl`, `drivers`, and `assign_hire`
$sql = "
    SELECT h.booking_id, h.employee_mail, h.destination, h.department, h.departure_date, h.return_date, h.passengers,
           h.departure_time, h.return_time, a.carType, a.driver_full_name, d.regno
    FROM hire_tbl h
    INNER JOIN assign_hire a ON h.booking_id = a.booking_id
    INNER JOIN drivers d ON d.regno = a.regNo
    WHERE h.booking_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $bookingId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No data found for Booking ID: " . htmlspecialchars($bookingId));
}

$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Print Work Ticket</title>
  <style>
    body {
      font-family: Arial, sans-serif;
    }
    .ticket-header {
      text-align: center;
    }
    .ticket-header img {
      width: 100px;
    }
    .ticket-header h1 {
      margin: 5px 0;
    }
    .ticket-details {
      margin: 20px;
    }
    .ticket-details table {
      width: 100%;
      border-collapse: collapse;
    }
    .ticket-details th, .ticket-details td {
      padding: 8px;
      border: 1px solid #ddd;
    }
  </style>
</head>

<body>
  <div class="ticket-header">
    <img src="../css/shofco.png" alt="Logo">
    <h1>Logistics</h1>
    <h2>Work Ticket</h2>
  </div>
  <div class="ticket-details">
    <table>
      <tr><th>Booking ID</th><td><?= htmlspecialchars($data['booking_id']) ?></td></tr>
      <tr><th>Department</th><td><?= htmlspecialchars($data['department']) ?></td></tr>
      <tr><th>Employee Email</th><td><?= htmlspecialchars($data['employee_mail']) ?></td></tr>
      <tr><th>Destination</th><td><?= htmlspecialchars($data['destination']) ?></td></tr>
      <tr><th>Car Type</th><td><?= htmlspecialchars($data['carType']) ?></td></tr>
      <tr><th>Vehicle Registration</th><td><?= htmlspecialchars($data['regno']) ?></td></tr>
      <tr><th>Driver Name</th><td><?= htmlspecialchars($data['driver_full_name']) ?></td></tr>
      <tr><th>Departure Date</th><td><?= htmlspecialchars($data['departure_date']) ?></td></tr>
      <tr><th>Return Date</th><td><?= htmlspecialchars($data['return_date']) ?></td></tr>
      <tr><th>Passengers</th><td><?= htmlspecialchars($data['passengers']) ?></td></tr>
      <tr><th>Departure Time</th><td><?= htmlspecialchars($data['departure_time']) ?></td></tr>
      <tr><th>Return Time</th><td><?= htmlspecialchars($data['return_time']) ?></td></tr>
    </table>
  </div>
  <script>
    window.print();
  </script>
</body>

</html>
