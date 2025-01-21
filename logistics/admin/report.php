<?php
include '../bin/conn.php';
include '../bin/access.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['adminName'])) {
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

// Fetch data for the pie chart (kilometers covered by car types)
$query = "SELECT d.cartype, COALESCE(SUM(s.mile_end - s.mile_start), 0) AS km_covered
          FROM drivers d 
          INNER JOIN status s ON d.regno = s.regno 
          WHERE s.status = 3 
          GROUP BY d.cartype";
$result = $conn->query($query);

$carTypes = [];
$kmCovered = [];
while ($row = $result->fetch_assoc()) {
    $carTypes[] = $row['cartype'];
    $kmCovered[] = $row['km_covered'];
}

// Fetch top 3 drivers by kilometers covered
$query = "SELECT d.Full_name, COALESCE(SUM(s.mile_end - s.mile_start), 0) AS km_covered 
          FROM drivers d 
          INNER JOIN status s ON d.regno = s.regno 
          WHERE s.status = 3 
          GROUP BY d.Full_name 
          ORDER BY km_covered DESC 
          LIMIT 3";
$topDriversResult = $conn->query($query);

$topDrivers = [];
while ($row = $topDriversResult->fetch_assoc()) {
    // Ensure km_covered is properly fetched and avoid undefined key errors
    $kmCoveredValue = isset($row['km_covered']) ? $row['km_covered'] : 0; // Default to 0 if not set
    $topDrivers[] = [
        'Full_name' => $row['Full_name'],
        'km_covered' => $kmCoveredValue
    ];
}

// Debug: Check if km_covered is populated
// var_dump($topDrivers); // You can enable this for debugging purposes

// Count ride statuses
$query = "SELECT status, COUNT(*) AS count 
          FROM status 
          GROUP BY status";
$rideStatusResult = $conn->query($query);

$rideStatuses = ['active' => 0, 'enroute' => 0, 'finished' => 0];
while ($row = $rideStatusResult->fetch_assoc()) {
    switch ($row['status']) {
        case 1:
            $rideStatuses['active'] = $row['count'];
            break;
        case 2:
            $rideStatuses['enroute'] = $row['count'];
            break;
        case 3:
            $rideStatuses['finished'] = $row['count'];
            break;
    }
}

// Fetch distinct car types for dropdown
$carQuery = "SELECT DISTINCT cartype FROM drivers";
$carResult = $conn->query($carQuery);
$carTypesForDropdown = [];
while ($row = $carResult->fetch_assoc()) {
    $carTypesForDropdown[] = $row['cartype']; // Only add unique car types
}

// Fetch driver names for dropdown
$driverQuery = "SELECT Full_name FROM drivers";
$driverResult = $conn->query($driverQuery);
$drivers = [];
while ($row = $driverResult->fetch_assoc()) {
    $drivers[] = $row['Full_name'];
}

// Search by car type or driver name
$searchResultsByCar = [];
$searchResultsByDriver = [];

if (isset($_GET['driver_name']) || isset($_GET['regno'])) {
    if (isset($_GET['regno'])) {
        $carType = $conn->real_escape_string($_GET['regno']);
        $searchQuery = "SELECT 
                    d.regno, 
                    d.Full_name, 
                    d.cartype, 
                    COALESCE(COUNT(CASE WHEN s.status = 1 THEN 1 END), 0) AS active_rides,
                    COALESCE(COUNT(CASE WHEN s.status = 2 THEN 1 END), 0) AS enroute_rides,
                    COALESCE(COUNT(CASE WHEN s.status = 3 THEN 1 END), 0) AS finished_rides,
                    COALESCE(SUM(TIMESTAMPDIFF(HOUR, s.time_start, s.time_end)), 0) AS hours_covered
                FROM drivers d
                LEFT JOIN status s ON d.regno = s.regno
                WHERE d.cartype = '$carType'
                GROUP BY d.regno, d.Full_name, d.cartype";

        $resultSearchByCar = $conn->query($searchQuery);

        if ($resultSearchByCar->num_rows > 0) {
            while ($row = $resultSearchByCar->fetch_assoc()) {
                $searchResultsByCar[] = $row;
            }
        }
    }

    if (isset($_GET['driver_name'])) {
        $driverName = $conn->real_escape_string($_GET['driver_name']);
        $searchQuery = "SELECT 
                    d.Full_name, 
                    d.regno, 
                    d.cartype, 
                    COALESCE(SUM(s.mile_end - s.mile_start), 0) AS km_covered,
                    COUNT(CASE WHEN s.status = 3 THEN 1 END) AS finished_rides,
                    COALESCE(SUM(TIMESTAMPDIFF(HOUR, s.time_start, s.time_end)), 0) AS hours_covered
                FROM drivers d
                LEFT JOIN status s ON d.regno = s.regno
                WHERE d.Full_name LIKE '%$driverName%'
                GROUP BY d.regno, d.Full_name, d.cartype";

        $resultSearchByDriver = $conn->query($searchQuery);

        if ($resultSearchByDriver->num_rows > 0) {
            while ($row = $resultSearchByDriver->fetch_assoc()) {
                $searchResultsByDriver[] = $row;
            }
        }
    }
}

// Calculate total kilometers covered for finished rides (status = 3)
$queryTotalKm = "SELECT COALESCE(SUM(s.mile_end - s.mile_start), 0) AS total_km 
                 FROM status s 
                 WHERE s.status = 3"; // Considering status 3 as 'finished'
$totalKmResult = $conn->query($queryTotalKm);
$totalKmRow = $totalKmResult->fetch_assoc();
$totalKmCovered = $totalKmRow['total_km'];

// Fetch total hours covered (sum of hours difference between time_start and time_end for status = 3)
$totalHoursCovered = 0;
$queryHours = "
    SELECT SUM(TIMESTAMPDIFF(HOUR, time_start, time_end)) AS total_hours 
    FROM status 
    WHERE regno = ? AND status = 3
";
if ($stmt = $conn->prepare($queryHours)) {
    $stmt->bind_param("s", $driverRegno);
    $stmt->execute();
    $stmt->bind_result($totalHours);
    if ($stmt->fetch()) {
        $totalHoursCovered = $totalHours ?: 0; // Default to 0 if no hours found
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report</title>
    <link rel="stylesheet" href="../css/adm.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
            overflow-y: auto; /* Allow vertical scrolling */
        }

        header {
            padding: 20px;
            text-align: center;
            background-color: #2ea2cc;
            color: white;
        }

        h2 {
            color: black;
            font-style: italic;
        }

        .top-section {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            margin: 20px auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 90%;
        }

        .chart-container,
        .summary-container,
        .driver-container {
            flex: 1;
            margin: 10px;
            background-color: #ffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-section {
            display: flex;
            justify-content: space-around;
            padding: 20px;
            margin: 20px auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 90%;
        }

        select,
        button {
            padding: 10px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            max-height: 400px; /* Optional: add max-height for scrollable tables */
            overflow-y: auto;
            display: block;
        }

        table th,
        table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
            width: 15%;
        }

        .search-results {
            max-width: 90%;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow-x: auto; /* Allow horizontal scrolling if needed */
        }
    </style>
</head>

<body>
    <header>
        <h1>Report</h1>
        <a href="../admin.php" class="back-btn">Return to Admin</a>
        <div class="admin-name">
            <?php echo "Welcome, " . htmlspecialchars($adminFullName); ?>
        </div>
    </header>

    <!-- Top Section -->
    <div class="top-section">
        <div class="chart-container">
            <canvas id="carTypeChart"></canvas>
        </div>

        <!-- Summary Section -->
        <div class="summary-container">
            <h2>Summary</h2>
            <table>
                <tr>
                    <th>Summary</th>
                    <th>Details</th>
                </tr>
                <tr>
                    <td>Active Rides</td>
                    <td><?= $rideStatuses['active']; ?></td>
                </tr>
                <tr>
                    <td>Enroute Rides</td>
                    <td><?= $rideStatuses['enroute']; ?></td>
                </tr>
                <tr>
                    <td>Finished Rides</td>
                    <td><?= $rideStatuses['finished']; ?></td>
                </tr>
                <tr>
                    <td>Total KM Covered</td>
                    <td><?= number_format($totalKmCovered, 2); ?> km</td> <!-- Total KM Covered in the table -->
                </tr>
            </table>
        </div>


        <!-- Top 3 Drivers -->
        <div class="driver-container">
            <h2>Top 3 Drivers</h2>
            <table>
                <tr>
                    <th>#</th>
                    <th>Driver</th>
                    <th>KM Covered</th>
                </tr>
                <?php 
                $driverNumber = 1; // Starting number for ranking
                foreach ($topDrivers as $driver) { ?>
                    <tr>
                        <td><?= $driverNumber++; ?></td> <!-- Display the driver number -->
                        <td><?= $driver['Full_name']; ?></td>
                        <td><?= number_format($driver['km_covered'], 2); ?> km</td> <!-- Display kilometers covered -->
                    </tr>
                <?php } ?>
            </table>
        </div>

    </div>

    <!-- Search Section -->
    <div class="search-section">
        <form method="get">
            <select name="regno">
                <option value="">Select Car Type</option>
                <?php foreach ($carTypesForDropdown as $carType) { ?>
                    <option value="<?= $carType; ?>"><?= $carType; ?></option>
                <?php } ?>
            </select>
            <select name="driver_name">
                <option value="">Select Driver</option>
                <?php foreach ($drivers as $driver) { ?>
                    <option value="<?= $driver; ?>"><?= $driver; ?></option>
                <?php } ?>
            </select>
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- Search Results -->
    <div class="search-results">
        <h2>Search Results</h2>
        <h3>By Car Type</h3>
        <table>
            <tr>
                <th>Driver</th>
                <th>Regno</th>
                <th>Active Rides</th>
                <th>Enroute Rides</th>
                <th>Finished Rides</th>
                <th>Hours Covered</th> <!-- New column for hours covered -->
            </tr>
            <?php foreach ($searchResultsByCar as $result) { ?>
                <tr>
                    <td><?= $result['Full_name']; ?></td>
                    <td><?= $result['regno']; ?></td>
                    <td><?= $result['active_rides']; ?></td>
                    <td><?= $result['enroute_rides']; ?></td>
                    <td><?= $result['finished_rides']; ?></td>
                    <td><?= number_format($result['hours_covered'], 2); ?> hours</td> <!-- Display hours covered -->
                </tr>
            <?php } ?>
        </table>

        <h3>By Driver</h3>
        <table>
            <tr>
                <th>Driver</th>
                <th>Regno</th>
                <th>Car Type</th>
                <th>Km Covered</th>
                <th>Finished Rides</th>
                <th>Hours Covered</th> <!-- New column for hours covered -->
            </tr>
            <?php foreach ($searchResultsByDriver as $result) { ?>
                <tr>
                    <td><?= $result['Full_name']; ?></td>
                    <td><?= $result['regno']; ?></td>
                    <td><?= $result['cartype']; ?></td>
                    <td><?= $result['km_covered']; ?> km</td>
                    <td><?= $result['finished_rides']; ?></td>
                    <td><?= number_format($result['hours_covered'], 2); ?> hours</td> <!-- Display hours covered -->
                </tr>
            <?php } ?>
        </table>        
        
        <!-- Export to CSV -->
        <div style="text-align: center; margin-top: 20px;">
            <form method="post" action="export.php">
                <input type="hidden" name="search_results_car" value='<?php echo json_encode($searchResultsByCar); ?>'>
                <input type="hidden" name="search_results_driver" value='<?php echo json_encode($searchResultsByDriver); ?>'>
                <button type="submit">Download Search</button>
            </form>
        </div>

    </div>

    <script>
        // Chart.js Code for Car Type Distribution
        var ctx = document.getElementById('carTypeChart').getContext('2d');
        var carTypeChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($carTypes); ?>,
                datasets: [{
                    label: 'KM Covered:',
                    data: <?php echo json_encode($kmCovered); ?>,
                    backgroundColor: ['#FF5733', '#33FF57', '#3357FF', '#FF33A1', '#FF8133'],
                    borderColor: ['#ffffff', '#ffffff', '#ffffff', '#ffffff', '#ffffff'],
                    borderWidth: 1
                }]
            }
        });
    </script>
</body>

</html>

