<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch data from the POST request
    $searchResultsCar = isset($_POST['search_results_car']) ? json_decode($_POST['search_results_car'], true) : [];
    $searchResultsDriver = isset($_POST['search_results_driver']) ? json_decode($_POST['search_results_driver'], true) : [];

    // Set headers to force download as CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=search_results.csv');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Write Car Type Search Results to CSV
    if (!empty($searchResultsCar)) {
        fputcsv($output, ['Search Results by Car Type']); // Section title
        fputcsv($output, ['Driver', 'Regno', 'Active Rides', 'Enroute Rides', 'Finished Rides', 'Hours Covered']);
        foreach ($searchResultsCar as $row) {
            fputcsv($output, [
                $row['Full_name'],
                $row['regno'],
                $row['active_rides'],
                $row['enroute_rides'],
                $row['finished_rides'],
                number_format($row['hours_covered'], 2) . ' hours'
            ]);
        }
        fputcsv($output, []); // Empty line between sections
    }

    // Write Driver Search Results to CSV
    if (!empty($searchResultsDriver)) {
        fputcsv($output, ['Search Results by Driver']); // Section title
        fputcsv($output, ['Driver', 'Regno', 'Car Type', 'Km Covered', 'Finished Rides', 'Hours Covered']);
        foreach ($searchResultsDriver as $row) {
            fputcsv($output, [
                $row['Full_name'],
                $row['regno'],
                $row['cartype'],
                $row['km_covered'] . ' km',
                $row['finished_rides'],
                number_format($row['hours_covered'], 2) . ' hours'
            ]);
        }
    }

    // Close output stream
    fclose($output);
    exit;
} else {
    header("Location: report.php");
    exit;
}
