<?php
include '../bin/conn.php';

if (isset($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];

    // Delete the assignment
    $sql = "DELETE FROM assign_hire WHERE booking_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $booking_id);

    if ($stmt->execute()) {
        header("Location: view_assigned.php?message=Assignment deleted successfully!");
        exit;
    } else {
        echo "Error deleting assignment: " . $stmt->error;
    }
} else {
    header("Location: view_assigned.php?error=Booking ID not provided.");
    exit;
}
?>
