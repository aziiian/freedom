<?php
include '../bin/conn.php'; // Include your database connection
session_start();

// Check if required data is submitted
if (!isset($_POST['id']) || !isset($_POST['start_location']) || !isset($_POST['end_location'])) {
    header("Location: ../driveractivity.php?error=missing_data");
    exit;
}

// Sanitize input data
$id = intval($_POST['id']);
$start = $conn->real_escape_string($_POST['start_location']);
$end = $conn->real_escape_string($_POST['end_location']);
$newStops = isset($_POST['stops']) ? array_map('trim', explode(',', $conn->real_escape_string($_POST['stops']))) : [];

// Retrieve the existing route data
$sql = "SELECT route FROM status WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    header("Location: ../driveractivity.php?error=prepare_failed");
    exit;
}

$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($existingRoute);
$stmt->fetch();
$stmt->close();

// Decode the existing route or initialize a new one
$route = $existingRoute ? json_decode($existingRoute, true) : ['start' => '', 'end' => '', 'stops' => []];
if (!is_array($route)) {
    $route = ['start' => '', 'end' => '', 'stops' => []];
}

// Update the route with new data
$route['start'] = $start; // Update start location
$route['end'] = $end;     // Update end location
$route['stops'] = array_merge($route['stops'], $newStops); // Append new stops

// Encode the updated route as JSON
$updatedRoute = json_encode($route);

// Update the database
$updateSql = "UPDATE status SET route = ? WHERE id = ?";
$updateStmt = $conn->prepare($updateSql);
if ($updateStmt) {
    $updateStmt->bind_param("si", $updatedRoute, $id);
    if ($updateStmt->execute()) {
        header("Location: ../driveractivity.php?success=true");
    } else {
        header("Location: ../driveractivity.php?error=db_error");
    }
    $updateStmt->close();
} else {
    header("Location: ../driveractivity.php?error=update_failed");
}

$conn->close();
?>
