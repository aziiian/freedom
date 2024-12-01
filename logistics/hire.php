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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Log POST data for debugging
    error_log(print_r($_POST, true));

    $booking_id = $_POST['bid'] ?? null;
    $employee_mail = $_POST['email'] ?? null;
    $departure_date = $_POST['ddate'] ?? null;
    $return_date = $_POST['rdate'] ?? null;
    $passengers = $_POST['passno'] ?? null;
    $destination = $_POST['dest'] ?? null;
    $department = $_POST['dept'] ?? null;
    $purpose = $_POST['pop'] ?? null;
    $departure_time = $_POST['tin'] ?? null;
    $return_time = $_POST['tout'] ?? null;
    $vehicle_category = $_POST['vehicle-category'] ?? null;
    $shofco_list = $_POST['shv-list'] ?? '-';
    $hire_list = $_POST['hrv-list'] ?? '-';

    // Validate required fields
    if (!$booking_id || !$employee_mail || !$departure_date || !$return_date || !$passengers || !$destination ||
        !$department || !$purpose || !$departure_time || !$return_time || !$vehicle_category) {
        echo json_encode(["result" => "error", "error" => "All fields are required"]);
        exit;
    }

    // Ensure at least one of shofco_list or hire_list is populated based on vehicle_category
    if ($vehicle_category === "shv" && $shofco_list === "-") {
        echo json_encode(["result" => "error", "error" => "Please select a SHOFCO vehicle."]);
        exit;
    }
    if ($vehicle_category === "hrv" && $hire_list === "-") {
        echo json_encode(["result" => "error", "error" => "Please select a Hire vehicle."]);
        exit;
    }

    // Insert into database
    $query = "INSERT INTO hire_tbl 
        (booking_id, employee_mail, departure_date, return_date, passengers, destination, department, purpose, 
        departure_time, return_time, shofco_list, hire_list) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "ssssssssssss",
        $booking_id, $employee_mail, $departure_date, $return_date, $passengers, $destination,
        $department, $purpose, $departure_time, $return_time, $shofco_list, $hire_list
    );

    if ($stmt->execute()) {
        echo json_encode(["result" => "success"]);
    } else {
        echo json_encode(["result" => "error", "error" => $stmt->error]);
    }
    exit;
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./css/gstyles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHOFCO Logistics</title>
    <link rel="icon" href="./css/shofco.png" type="image/png">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jwt-decode/3.1.2/jwt-decode.min.js"></script>
    <script src="script.js"></script>
</head>
<body>
    <div class="container">
        <div class="nav">
            <img src="./css/shofco.png" alt="">
            <h1>SHOFCO Logistics</h1>
            <button class="btn1" id="closeButton" onclick="window.location.href='index.php'">Close</button>
        </div>
        <form action="hire.php" method="post" class="cont-form" id="submissionForm" novalidate>
    <div class="fst">
        <div class="entry">
            <label for="bid" class="label">Booking ID</label>
            <input type="text" id="bid" name="bid" readonly>
        </div>
        <div class="entry">
            <label for="email" class="label">Employee Mail</label>
            <input type="email" id="email" name="email" required>
        </div>
    </div>
    <div class="snd">
        <div class="entry">
            <div class="label">Departure Date</div>
            <input type="date" id="ddate" name="ddate">
        </div>
        <div class="entry">
            <div class="label">Return Date</div>
            <input type="date" id="rdate" name="rdate">
        </div>
    </div>
    <div class="trd">
        <div class="entry">
            <div class="label">Number of Passengers</div>
            <input type="text" id="passno" name="passno">
        </div>
        <div class="entry">
            <div class="label">Destination</div>
            <input type="text" id="dest" name="dest">
        </div>
        <div class="entry">
            <div class="label">Department</div>
            <input type="text" id="dept" name="dept">
        </div>
    </div>
    <div class="entry">
        <div class="label">Purpose</div>
        <input type="text" id="pop" name="pop">
    </div>
    <div class="tm">
        <div class="entry">
            <div class="label">Time of Departure</div>
            <input type="time" id="tin" name="tin">
        </div>
        <div class="entry">
            <div class="label">Time of Returning</div>
            <input type="time" id="tout" name="tout">
        </div>
    </div>
    <div class="vhl">
        <label for="vehicle-category">Choose a Vehicle Category:</label>
        <select id="vehicle-category" name="vehicle-category" onchange="showVehicleList()">
            <option value="" disabled selected>Select a category</option>
            <option value="shv">SHOFCO Vehicles</option>
            <option value="hrv">Hire Vehicles</option>
        </select>
        <div id="shofco-vehicles" style="display: none;">
            <label for="shv-list">Choose a SHOFCO vehicle:</label>
            <select id="shv-list" name="shv-list">
                <option value="-" hidden>-</option>
                <option value="Double Cab">Double Cab</option>
                <option value="Van">Van</option>
                <option value="Probox">Probox</option>
                <option value="Water Bowser">Water Bowser</option>
                <option value="Mobile Clinic">Mobile Clinic</option>
                <option value="Tractor">Tractor</option>
                <option value="Bus 67-Seater">Bus 67-Seater</option>
                <option value="Bus 31-Seater">Bus 31-Seater</option>
            </select>
        </div>
        <div id="hire-vehicles" style="display: none;">
            <label for="hrv-list">Choose a Hire vehicle:</label>
            <select id="hrv-list" name="hrv-list">
                <option value="-" hidden>-</option>
                <option value="Bus 33-Seater">Bus 33-Seater</option>
                <option value="Bus 45-Seater">Bus 45-Seater</option>
                <option value="Bus 51-Seater">Bus 51-Seater</option>
                <option value="Bus 60-Seater">Bus 60-Seater</option>
                <option value="Bus 68-Seater">Bus 68-Seater</option>
                <option value="Truck (Lorry)">Truck (Lorry)</option>
                <option value="Water Bowser">Water Bowser</option>
                <option value="Ambulance">Ambulance</option>
                <option value="Pick-up">Pick-up</option>
            </select>
        </div>
    </div>
    <div class="submit">
        <input type="submit" value="Submit" class="btn2">
    </div>
</form>

        <script>
            function submitForm() {
                // Show the success message
                alert("Input successful");
    
                // Clear the form fields
                document.getElementById("myForm").reset();
            }
        </script>
    </div>
</body>
</html>

