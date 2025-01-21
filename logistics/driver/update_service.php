<?php
include "../bin/conn.php";
session_start();

// Restrict access to logged-in users
if (!isset($_SESSION['driverRegno'])) {
    header("Location: ../sign-in.php");
    exit;
}

$driverRegno = $_SESSION['driverRegno'];

// Check if this is the first service
$query = "SELECT COUNT(*) FROM vehicles WHERE regno = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $driverRegno);
$stmt->execute();
$stmt->bind_result($serviceCount);
$stmt->fetch();
$stmt->close();

// Handle service updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($serviceCount == 0) {
        // First service: driver inputs mileage
        $currentMileage = intval($_POST['currentMileage']);
        $nextMileage = $currentMileage + 5000;

        // Insert service record
        $query = "INSERT INTO vehicles (regno, last_service_date, current_service_mileage, next_service_mileage) 
                  VALUES (?, CURRENT_DATE, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sii", $driverRegno, $currentMileage, $nextMileage);
        if ($stmt->execute()) {
            header("Location: ../driver/car_status.php?success=service_updated");
        } else {
            header("Location: ../driver/car_status.php?error=service_update_failed");
        }
        $stmt->close();
    } else {
        // Subsequent service: mileage fetched from status table
        $query = "
            SELECT MAX(mile_end) 
            FROM status 
            WHERE regno = ? AND status = 3
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $driverRegno);
        $stmt->execute();
        $stmt->bind_result($mileEnd);
        $stmt->fetch();
        $stmt->close();

        if ($mileEnd) {
            $currentMileage = $mileEnd;
            $nextMileage = $currentMileage + 5000;

            // Update vehicle service record
            $query = "
                UPDATE vehicles 
                SET last_service_date = CURRENT_DATE, 
                    current_service_mileage = ?, 
                    next_service_mileage = ? 
                WHERE regno = ?
            ";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iis", $currentMileage, $nextMileage, $driverRegno);
            if ($stmt->execute()) {
                header("Location: ../driver/car_status.php?success=service_updated");
            } else {
                header("Location: ../driver/car_status.php?error=service_update_failed");
            }
            $stmt->close();
        } else {
            header("Location: ../driver/car_status.php?error=no_mileage_data");
        }
    }
}
?>
