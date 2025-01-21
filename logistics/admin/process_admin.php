<?php
include '../bin/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Update existing user
    if ($action === 'edit') {
        $aid = $_POST['admin_id'];
        $username = $_POST['username'];
        $fullname = $_POST['fullname'];
        $password = $_POST['password'];
        $phoneno = $_POST['phoneno'];
        $accessLevel = $_POST['aid'];

        // If a password is provided, hash it before saving
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin SET username = ?, fullname = ?, password = ?, phoneno = ?, aid = ? WHERE aid = ?");
            $stmt->bind_param("ssssii", $username, $fullname, $hashedPassword, $phoneno, $accessLevel, $aid);
        } else {
            // Update without changing the password
            $stmt = $conn->prepare("UPDATE admin SET username = ?, fullname = ?, phoneno = ?, aid = ? WHERE aid = ?");
            $stmt->bind_param("sssii", $username, $fullname, $phoneno, $accessLevel, $aid);
        }

        if ($stmt->execute()) {
            header("Location: adminman.php"); // Redirect back to the admin page
            exit;
        } else {
            echo "Error updating record: " . $stmt->error;
        }
        $stmt->close();
    }

    // Add and Delete logic here (if applicable)
}
$conn->close();
?>
