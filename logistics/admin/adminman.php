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

?>


