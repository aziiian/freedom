<?php
// Database credentials
$host = 'localhost'; // Change if your database is on a different host
$dbname = 'log_adm';
$username = 'root';
$passw = ''; // No password

// Create connection
$conn = new mysqli($host, $username, $passw, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
?>
