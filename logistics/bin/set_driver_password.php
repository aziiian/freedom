<?php
include_once './bin/conn.php';
session_start();

// Check if the session exists for temporary driver regno
if (!isset($_SESSION['tempDriverRegno'])) {
    header("Location: ../sign-in.php.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $newPassword = $_POST['new_password'];
    $regno = $_SESSION['tempDriverRegno'];

    // Update the drivers table with the new password
    $sql = "UPDATE drivers SET password = ? WHERE regno = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $newPassword, $regno);
    if ($stmt->execute()) {
        // Clear temporary session and redirect to login
        unset($_SESSION['tempDriverRegno']);
        echo "<script>alert('Password successfully set. Please login with your new password.'); window.location.href = '../sign-in.php';</script>";
        exit;
    } else {
        $error = "Error updating password. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Password - Shofco Logistics</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <main class="content">
        <h2>Set Your Password</h2>
        <p>It seems this is your first login. Please set your password to continue.</p>

        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        
        <form action="" method="POST">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" placeholder="Enter New Password" required>
            
            <button type="submit" class="btn-primary">Save Password</button>
        </form>
    </main>
</body>
</html>
