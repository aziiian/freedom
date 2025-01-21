<?php
include '../bin/conn.php';
include '../bin/access.php';

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


// Prevent caching of the page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Fetch all drivers and their service data
$queryDrivers = "
    SELECT d.Full_name, d.regno, v.millage_prev, v.millage_next, v.mile_comp, 
           (SELECT mile_end FROM status s WHERE s.regno = d.regno ORDER BY id DESC LIMIT 1) AS current_mileage,
           (SELECT issues FROM dev_stat WHERE regno = d.regno ORDER BY id DESC LIMIT 1) AS latest_issue
    FROM drivers d
    LEFT JOIN dev_stat v ON d.regno = v.regno
";
$result = $conn->query($queryDrivers);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Driver Maintenance</title>
    <link rel="stylesheet" href="../css/adm.css">
    
    <style>
        .driver-tiles {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .driver-tile {
            background: #2ea2cc;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .bk-btn1{
            background: #2ea2cc;
            border-radius: 6px;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
        }

        .top{
            padding-left: 10px;
        }

    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <img src="viewhire.php" alt="Logo">
        <h1>Logistics Admin Panel</h1>
        <div class="admin-name">
            <?php echo "Admin, " . htmlspecialchars($adminFullName); ?>
        </div>
    </header>
     <div class="top">   
    <h1>Admin Dashboard</h1>
    <br>
        <a href="../admin.php" class="bk-btn1">Back to Admin</a>
        </div>
    <div class="driver-tiles">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="driver-tile">
                <h3><?php echo htmlspecialchars($row['Full_name']); ?></h3>
                <p><strong>Reg No:</strong> <?php echo htmlspecialchars($row['regno']); ?></p>
                <p><strong>Vehicle Type:</strong> Sedan</p> <!-- Replace with actual type if stored -->

                <?php if ($row['millage_next'] - $row['current_mileage'] <= 100): ?>
                    <p><strong>Status:</strong> Close to service</p>
                    <p><strong>Next Service Mileage:</strong> <?php echo $row['millage_next']; ?></p>
                    <p><strong>Issues:</strong> <?php echo $row['latest_issue'] ?: 'No issues found'; ?></p>
                    <button onclick="approveService('<?php echo $row['regno']; ?>')">Approve</button>
                <?php else: ?>
                    <p><strong>Current Mileage:</strong> <?php echo $row['current_mileage']; ?></p>
                    <p><strong>Next Service Mileage:</strong> <?php echo $row['millage_next']; ?></p>
                    <p><strong>Issues:</strong> <?php echo $row['latest_issue'] ?: 'No issues found'; ?></p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

    <script>
        function approveService(regno) {
            if (confirm("Approve service for reg no: " + regno + "?")) {
                window.location.href = "./approve_service.php?regno=" + regno;
            }
        }
    </script>
</body>
</html>

