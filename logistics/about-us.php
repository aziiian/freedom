<?php
include './bin/conn.php';
session_start();

// Redirect logged-in admin to admin dashboard
if (isset($_SESSION['adminName'])) {
    header("Location: admin.php");
    exit;
}

// Redirect logged-in driver to driver dashboard
if (isset($_SESSION['driverId'])) {
    header("Location: driver.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shofco Logistics</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <!-- Header -->
    <header class="header">
    <div class="right-buttons">
        <a href="index.php">
            <img src="css/shofco.png" alt="Shofco Logo" class="logo">
        </a>
        </div>
        <h1 class="title">Shofco Logistics</h1>
        <div class="left-buttons">
            <button onclick="window.location.href='about-us.php'">About Us</button>
            <button onclick="window.location.href='hire.php'">Vehicle Request</button>
            <button onclick="window.location.href='sign-in.php'">Sign In</button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="content">
        <p>Welcome to About Shofco Logistics! Where you get to know the history and functions of shofco logistics.</p>

    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="social-links">
            <a href="https://www.facebook.com/ShiningHopeforCommunities/"><img src="css/icons8-facebook-50.png" alt="Facebook" style="filter: invert(100%); opacity: 90%;"></a>
            <a href="https://x.com/hope2shine"><img src="css/icons8-twitter-50.png" alt="" style="filter: invert(100%); opacity: 90%;"></a>
            <a href="https://instagram.com/shofco" target="_blank"><img src="css/icons8-instagram-50.png" alt="" style="filter: invert(100%); opacity: 90%;"></a>
        </div>
        <p>&copy; 2024 Shofco Logistics. All rights reserved.</p>
        <p>Contact: info@shofco.org | +123-456-7890</p>
    </footer>
</body>

</html>