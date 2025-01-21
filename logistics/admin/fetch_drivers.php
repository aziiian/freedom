<?php
include '../bin/conn.php';

if (isset($_GET['cartype'])) {
    $carType = $_GET['cartype'];
    $sql = "SELECT Full_name, regno FROM drivers WHERE cartype = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $carType);
    $stmt->execute();
    $result = $stmt->get_result();

    $drivers = [];
    while ($row = $result->fetch_assoc()) {
        $drivers[] = $row;
    }

    echo json_encode(['drivers' => $drivers]);
} else {
    echo json_encode(['drivers' => []]);
}
?>
