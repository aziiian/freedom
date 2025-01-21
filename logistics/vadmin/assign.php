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

// Check if the necessary GET parameters are present
if (isset($_GET['booking_id'], $_GET['email'], $_GET['destination'])) {
    $booking_id = $_GET['booking_id'];
    $employee_mail = $_GET['email'];
    $destination = $_GET['destination'];
} else {
    header("Location: viewhires.php");
    exit;
}

// Fetch available car types
$sql_car_types = "SELECT DISTINCT cartype FROM drivers";
$car_types_result = $conn->query($sql_car_types);

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carType = $_POST['carType'];
    $regNo = $_POST['regNo'];
    $driver_full_name = $_POST['driver_full_name'];

    $conn->begin_transaction();
    try {
        // Insert into assign_hire
        $insert_sql = "INSERT INTO assign_hire (booking_id, employee_mail, destination, regNo, driver_full_name, carType, status) 
                       VALUES (?, ?, ?, ?, ?, ?, '1')";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ssssss", $booking_id, $employee_mail, $destination, $regNo, $driver_full_name, $carType);
        $stmt->execute();

        // Update status table
        $status_sql = "INSERT INTO status (regno, status) VALUES (?, 1)
                       ON DUPLICATE KEY UPDATE status = 1";
        $stmt_status = $conn->prepare($status_sql);
        $stmt_status->bind_param("s", $regNo);
        $stmt_status->execute();

        $conn->commit();

        // Redirect to view_assigned.php
        header("Location: view_assigned.php");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Assign Hire</title>
  <link rel="stylesheet" href="../css/adm.css">
  <style>
    body {
        font-family: Arial, sans-serif;
    }
    .assign-container {
        max-width: 600px;
        margin: 0 auto;
        background: #008CBA;
        padding: 40px;
        border-radius: 12px;
        width: 40%;
    }
    .receipt-header, .receipt-details {
        margin-bottom: 20px;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        color: #000;
    }
    .receipt-details{
        background: cadetblue;
    }
    .receipt-header{
        background: #ffff;
    }
    .receipt-header h2 {
        margin: 0;
    }
    .receipt-details p {
        margin: 5px 0;
        font-style: italic;;
    }
    .form-group label {
        font-weight: bold;
    }
    .form-group select {
        width: 100%;
        padding: 8px;
        margin: 10px 0;
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
    .back-btn button {
        background-color: silver;
        width: auto;
    }
    .back-btn button:hover {
        background-color: #005f73;
    }
    .receipt-container {
        margin-top: 20px;
        padding: 20px;
        border: 1px solid #ddd;
        background-color: #f9f9f9;
        display: none;
    }
    .receipt-container h3 {
        text-align: center;
    }
    .receipt-container table {
        width: 100%;
        border-collapse: collapse;
    }
    .receipt-container table th, .receipt-container table td {
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
    .button-cont{
      display: flex;
      height: 30px;
      gap: 30%; /* Space between buttons */
      justify-content: center; /* Center align the buttons */
    }
  </style>
</head>

<body>
  <header>
    <img src="../css/shofco.png" alt="Logo">
    <h1>Assign Vehicle</h1>
    <div class="admin-name">
      <?php echo "Admin: " . htmlspecialchars($adminFullName); ?>
    </div>
  </header>

  <main>
    <div class="assign-container">
      <div class="receipt-header">
        <h2>Hire Details</h2>
        <div class="receipt-details">
          <p><strong>Booking ID:</strong> <?= htmlspecialchars($booking_id) ?></p>
          <p><strong>Employee Email:</strong> <?= htmlspecialchars($employee_mail) ?></p>
          <p><strong>Destination:</strong> <?= htmlspecialchars($destination) ?></p>
        </div>
      </div>

      <form method="POST" action="">
        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking_id) ?>">
        <input type="hidden" name="employee_mail" value="<?= htmlspecialchars($employee_mail) ?>">
        <input type="hidden" name="destination" value="<?= htmlspecialchars($destination) ?>">

        <div class="form-group">
          <label for="carType">Select Car Type:</label>
          <select name="carType" id="carType" required>
            <option value="">Select Car Type</option>
            <?php while ($row = $car_types_result->fetch_assoc()) : ?>
              <option value="<?= htmlspecialchars($row['cartype']) ?>"><?= htmlspecialchars($row['cartype']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="form-group" id="driver-selection-container">
          <label for="driver">Select Driver:</label>
          <select name="driver_full_name" id="driver" required>
            <option value="">Select a driver</option>
          </select>
        </div>

        <div class="form-group" id="regNo-container">
          <label for="regNo">Select Vehicle (Reg No):</label>
          <select name="regNo" id="regNo" required>
            <option value="">Select Vehicle</option>
          </select>
        </div>
        <div class="button-cont">
            <button type="submit">Assign Vehicle</button>
        
        <div class="back-btn">
        <button onclick="window.location.href='../vadmin/viewhire.php'">Back to View Hires</button>
        </div>
        </div> 
      </form>

      <div id="receiptContainer">
        <button id="printBtn" class="print-btn" onclick="printReceipt()" style="display:none;">Print Work Ticket</button>
      </div>

    </div>
  </main>

  <script>
    // Fetch drivers and registration numbers based on car type
    function fetchAvailableDrivers(carType) {
        fetch('fetch_drivers.php?cartype=' + carType)
            .then(response => response.json())
            .then(data => {
                const driverSelect = document.getElementById('driver');
                driverSelect.innerHTML = '<option value="">Select a driver</option>';

                if (data.drivers.length > 0) {
                    data.drivers.forEach(driver => {
                        driverSelect.innerHTML += `<option value="${driver.Full_name}" data-regno="${driver.regno}">${driver.Full_name}</option>`;
                    });
                }
                resetRegNo();
            })
            .catch(err => console.error('Error fetching drivers:', err));
    }

    // Update regNo dropdown when a driver is selected
    document.getElementById('driver').addEventListener('change', function () {
        const selectedDriver = this.options[this.selectedIndex];
        const regNoSelect = document.getElementById('regNo');
        const regNo = selectedDriver.getAttribute('data-regno');

        regNoSelect.innerHTML = '<option value="">Select Vehicle</option>';
        if (regNo) {
            regNoSelect.innerHTML += `<option value="${regNo}">${regNo}</option>`;
        }
    });

    // Reset regNo dropdown
    function resetRegNo() {
        document.getElementById('regNo').innerHTML = '<option value="">Select Vehicle</option>';
    }

    // Trigger fetching drivers when car type changes
    document.getElementById('carType').addEventListener('change', function () {
        const carType = this.value;
        if (carType) {
            fetchAvailableDrivers(carType);
        } else {
            resetRegNo();
        }
    });
  </script>
</body>
</html>
