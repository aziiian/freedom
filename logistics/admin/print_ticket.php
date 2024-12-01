<?php
include '../bin/conn.php';

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

if (isset($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];

    // Fetch details for the work ticket
    $sql = "SELECT ah.*, h.departure_date, h.return_date, h.passengers, h.departure_time, h.return_time
            FROM assign_hire ah
            JOIN hire_tbl h ON ah.booking_id = h.booking_id
            WHERE ah.booking_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        // Output the work ticket
        echo "<h1>Work Ticket</h1>";
        echo "<p><strong>Booking ID:</strong> {$data['booking_id']}</p>";
        echo "<p><strong>Employee Email:</strong> {$data['employee_mail']}</p>";
        echo "<p><strong>Destination:</strong> {$data['destination']}</p>";
        echo "<p><strong>Car Type:</strong> {$data['carType']}</p>";
        echo "<p><strong>Vehicle Reg No:</strong> {$data['regNo']}</p>";
        echo "<p><strong>Driver:</strong> {$data['driver_full_name']}</p>";
        echo "<p><strong>Departure Date:</strong> {$data['departure_date']}</p>";
        echo "<p><strong>Return Date:</strong> {$data['return_date']}</p>";
        echo "<p><strong>Passengers:</strong> {$data['passengers']}</p>";
        echo "<p><strong>Departure Time:</strong> {$data['departure_time']}</p>";
        echo "<p><strong>Return Time:</strong> {$data['return_time']}</p>";
    } else {
        echo "No details found for this booking ID.";
    }
} else {
    echo "Booking ID not provided.";
}
?>
