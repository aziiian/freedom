<?php
include_once './bin/conn.php';
session_start();

// Handle Admin Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query admin table for matching credentials
    $sql = "SELECT * FROM admin WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        $_SESSION['adminName'] = $admin['username']; // Start session with username
        header("Location: admin.php");
        exit;
    } else {
        $adminError = "Invalid username or password!";
    }
}

// Handle Driver Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['regno']) && isset($_POST['password'])) {
    $vehicleReg = $_POST['regno'];
    $driverPassword = $_POST['password'];

    // Query drivers table for matching credentials
    $sql = "SELECT * FROM drivers WHERE regno = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $vehicleReg, $driverPassword);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $driver = $result->fetch_assoc();
        $_SESSION['driverRegno'] = $driver['regno']; // Start session with regno
        header("Location: driver.php");
        exit;
    } else {
        $driverError = "Invalid registration number or password!";
    }
}

    // Redirect to admin.php if admin is already logged in
    if (isset($_SESSION['adminName'])) {
        header("Location: admin.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Shofco Logistics</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="right-buttons">
            <img src="css/shofco.png" alt="Shofco Logo" class="logo">
        </div>
        <h1 class="title">Shofco Logistics</h1>
        <div class="left-buttons">
            <button onclick="window.location.href='index.php'">Home</button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="content">
        <h2>Sign In / Register</h2>

        <div class="auth-container">
            <!-- Admin Login Section -->
            <div class="auth-section">
                <h3>Admin Login</h3>
                <?php if (isset($adminError)) echo "<p class='error'>$adminError</p>"; ?>
                <form action="" method="POST">
                    <label for="admin-username">Username:</label>
                    <input type="text" id="admin-username" name="username" placeholder="Enter Username" required>

                    <label for="admin-password">Password:</label>
                    <input type="password" id="admin-password" name="password" placeholder="Enter Password" required>

                    <button type="submit" class="btn-primary">Login as Admin</button>
                </form>
            </div>

            <!-- Driver Sign In Section -->
            <div class="auth-section">
                <h3>Driver Sign In</h3>
                <?php if (isset($driverError)) echo "<p class='error'>$driverError</p>"; ?>
                <form action="" method="POST">
                    <label for="vehicle-registration">Vehicle Registration:</label>
                    <input type="text" id="regno" name="regno" placeholder="Enter Vehicle Registration" required>

                    <label for="driver-password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter Password" required>

                    <button type="submit" class="btn-primary">Sign In as Driver</button>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="social-links">
            <a href="https://www.facebook.com/ShiningHopeforCommunities/"><img src="css/icons8-facebook-50.png" alt="Facebook"></a>
            <a href="https://x.com/hope2shine"><img src="css/icons8-twitter-50.png" alt="Twitter"></a>
            <a href="https://instagram.com/shofco" target="_blank"><img src="css/icons8-instagram-50.png" alt="Instagram"></a>
        </div>
        <p>&copy; 2024 Shofco Logistics. All rights reserved.</p>
        <p>Contact: info@shofco.org | +123-456-7890</p>
    </footer>
</body>

</html>
