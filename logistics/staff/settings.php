<?php
include '../bin/conn.php';
include '../bin/access.php';
session_start();

// Restrict access to logged-in users
if (!isset($_SESSION['adminName'])) {
    header("Location: ../sign-in.php");
    exit;
}

// Fetch current admin details
$adminId = $_SESSION['adminName'];
$sql = "SELECT username, fullname, password, phoneno FROM admin WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $adminId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
} else {
    echo "Admin details not found.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = $_POST['username'];
    $newFullname = $_POST['fullname'];
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $newPhone = $_POST['phoneno'];

    // Validate passwords match
    if ($newPassword !== $confirmPassword) {
        $errorMessage = "Passwords do not match!";
    } else {
        
        $updateSql = "UPDATE admin SET username = ?, fullname = ?, password = ?, phoneno = ? WHERE username = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("sssss", $newUsername, $newFullname, $confirmPassword, $newPhone, $adminId);

        if ($updateStmt->execute()) {
            $_SESSION['username'] = $newUsername; // Update session username
        } else {
            $errorMessage = "Error updating profile.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings</title>
  <link rel="stylesheet" href="../css/adm.css">

  <style>
    body{
      overflow-y: scroll;
    }
  </style>
</head>

<body>
  <header>
    <img src="../css/shofco.png" alt="Logo">
    <h1>Admin Panel</h1>
    <div class="admin-name">
      <?php echo "Settings, " . htmlspecialchars($admin['fullname']); ?>
    </div>
  </header>

  <main>
    <div class="settings-container">
      <div class="user-details">
        <img src="../css/usr.png" alt="User Icon" class="user-icon">
        <h1 class="user-fullname"><?= htmlspecialchars($admin['fullname']) ?></h1>
      </div>

      <h2>Update Profile</h2>
      <?php if (!empty($successMessage)) echo "<p class='success'>$successMessage</p>"; ?>
      <?php if (!empty($errorMessage)) echo "<p class='error'>$errorMessage</p>"; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label for="username">Username:</label>
          <input type="text" id="username" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>
        </div>
        <div class="form-group">
          <label for="fullname">Full Name:</label>
          <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($admin['fullname']) ?>" required>
        </div>
        <div class="form-group">
          <label for="password">New Password:</label>
          <input type="password" id="password" name="password" placeholder="Enter new password" required>
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm Password:</label>
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
        </div>
        <div class="form-group">
          <label for="phoneno">Phone Number:</label>
          <input type="text" id="phoneno" name="phoneno" value="<?= htmlspecialchars($admin['phoneno']) ?>" required>
        </div>

        <div class="button-container">
          <button type="submit" class="btn-primary">Update</button>
          <button type="button" class="btn-secondary" onclick="window.location.href='../staff.php';">Return to Panel</button>
        </div>
      </form>
    </div>
  </main>
</body>

</html>
