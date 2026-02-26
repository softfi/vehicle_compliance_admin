<?php
// Comprehensive debug script for driver assignments
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'transport';

$mysqli = new mysqli($hostname, $username, $password, $database);
if ($mysqli->connect_error) die($mysqli->connect_error);

$vehicle_no = '95';

echo "--- ALL ASSIGNMENTS FOR VEHICLE $vehicle_no ---\n";
$query = "SELECT da.*, s.name as driver_name FROM driver_assignment da LEFT JOIN staff s ON s.id = da.driver WHERE da.vehicle_no = '$vehicle_no' ORDER BY from_date DESC";
$result = $mysqli->query($query);
echo "ID\tDriver\t\tFrom\t\tTo\n";
while ($row = $result->fetch_assoc()) {
    echo $row['id'] . "\t" . str_pad($row['driver_name'], 15) . "\t" . $row['from_date'] . "\t" . ($row['to_date'] ?? 'OPEN') . "\n";
}

echo "\n--- OPEN ASSIGNMENTS (to_date IS NULL) ---\n";
$query = "SELECT da.*, s.name as driver_name FROM driver_assignment da LEFT JOIN staff s ON s.id = da.driver WHERE da.vehicle_no = '$vehicle_no' AND da.to_date IS NULL ORDER BY from_date DESC";
$result = $mysqli->query($query);
while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . ", Driver: " . $row['driver_name'] . ", From: " . $row['from_date'] . "\n";
}

$mysqli->close();
