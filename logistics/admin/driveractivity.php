<?php
include '../bin/conn.php';
include '../bin/access.php';
session_start();

// Restrict access to logged-in admins or drivers
if (!isset($_SESSION['adminName']) && !isset($_SESSION['driverName'])) {
    header("Location: ../sign-in.php");
    exit;
}

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

// Fetch assigned driver activity data with departure date from hire_table
$sql = "
    SELECT 
        d.regno, 
        d.username, 
        d.full_name, 
        d.cartype, 
        a.id AS assign_hire_id, 
        a.booking_id, 
        a.destination, 
        h.departure_date, 
        s.status, 
        s.mile_start, 
        s.mile_end, 
        s.route
    FROM assign_hire a
    INNER JOIN drivers d ON a.regno = d.regno
    INNER JOIN hire_tbl h ON a.booking_id = h.booking_id
    LEFT JOIN status s ON a.id = s.id
    ORDER BY a.id;
";

$activities = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Activity</title>
    <link rel="stylesheet" href="../css/def.css">
    <style>
        /* General Reset */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }

        /* Header */
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #2ea2cc;
            color: white;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        header img {
            height: 50px;
            width: auto;
        }

        header h1 {
            margin: 0;
            font-size: 24px;
        }

        header .admin-name {
            font-size: 18px;
            font-weight: bold;
        }

        /* Main Content */
        main {
            padding: 20px;
            background: #dddd;
            align-items: center;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            overflow-x: auto;
        }

        .Titlet {
            display: flex;
        }

        .Titlet h1 {
            margin: 0 0 20px;
            font-size: 28px;
            color: #ffff;
            text-align: center;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #2ea2cc;
            color: white;
            font-size: 16px;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #e9f5ff;
        }

        .action-btn {
            background-color: #0056b3;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
        }

        .action-btn:hover {
            background-color: #004494;
        }

        /* Popup Styling */
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 20px;
            z-index: 1000;
        }

        .popup.active {
            display: block;
        }

        .popup h3 {
            margin-top: 0;
            font-size: 20px;
            color: #003366;
            text-align: center;
        }

        .popup form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .popup label {
            font-size: 14px;
            color: #333;
        }

        .popup input[type="text"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
        }

        .popup button {
            background-color: #0056b3;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
        }

        .popup button:hover {
            background-color: #004494;
        }

        .popup button[type="button"] {
            background-color: #ccc;
            color: black;
        }

        .popup button[type="button"]:hover {
            background-color: #bbb;
        }

        /* Back Button */
        .back-btn {
            display: inline-block;
            margin-top: 10px;
            background-color: #2ea2cc;
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
        }

        .back-btn:hover {
            background-color: #0056b3;
        }

    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <img src="../css/shofco.png" alt="Logo">
        <h1>Admin Panel</h1>
        <div class="admin-name">
            <?= "Admin, " . htmlspecialchars($adminFullName) ?>
        </div>
    </header>
    <br>
    <main>
        <div class="activity-container">
            <header class="Titlet">
                <h1>Driver Activity</h1>
                <div>
                    <button type="button" class="back-btn" onclick="window.location.href='../admin.php';">Return to Admin Panel</button>
                </div>
            </header>
            <table>
                <thead>
                    <tr>
                        <th>Car Registration</th>
                        <th>Booking ID</th>
                        <th>Driver Name</th>
                        <th>Destination</th>
                        <th>Departure Date</th>
                        <th>KM Covered</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $activities->fetch_assoc()) : ?>
                <tr>
                    <td><?= htmlspecialchars($row['regno']) ?></td>
                    <td><?= htmlspecialchars($row['booking_id']) ?></td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['destination']) ?></td>
                    <td><?= htmlspecialchars($row['departure_date']) ?></td>
                    <td>
                        <?php
                        $kmCovered = intval($row['mile_end']) - intval($row['mile_start']);
                        echo $kmCovered > 0 ? $kmCovered . " KM" : "N/A";
                        ?>
                    </td>
                    <td>
                        <?php
                        $statusText = '';
                        $statusColor = '';

                        switch ($row['status']) {
                            case 1:
                                $statusText = 'Active';
                                $statusColor = 'green';
                                break;
                            case 2:
                                $statusText = 'Enroute';
                                $statusColor = 'yellow';
                                break;
                            case 3:
                                $statusText = 'Finished';
                                $statusColor = 'gray';
                                break;
                            default:
                                $statusText = 'Unknown';
                                $statusColor = 'red';
                        }
                        ?>
                        <span style="color: <?= $statusColor ?>; font-weight: bold;">
                            <?= $statusText ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Route Popup -->
    <div class="popup" id="route-popup">
        <h3>Edit Route</h3>
        <form action="../bin/process_route.php" method="POST">
            <input type="hidden" name="id" id="popup-id">
            
            <label for="start-location">Start Location</label>
            <input type="text" name="start_location" id="start-location" required>
            
            <label for="end-location">End Location</label>
            <input type="text" name="end_location" id="end-location" required>
            
            <label for="stops">New Stops (comma-separated)</label>
            <input type="text" name="stops" id="stops">
            
            <button type="submit">Save Route</button>
        </form>
    </div>


    <script>
        // Toggle popup
        function togglePopup() {
            document.getElementById('route-popup').classList.toggle('active');
        }

        // Prefill popup for editing route
        function editRoute(data) {
            const routeData = JSON.parse(data.route || '{}');
            document.getElementById('popup-id').value = data.assign_hire_id || '';
            document.getElementById('start-location').value = routeData.start || '';
            document.getElementById('end-location').value = routeData.end || '';
            document.getElementById('stops').value = ''; // Clear stops input for new data
            togglePopup();
        }

    </script>

</body>
</html>
