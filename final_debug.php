<?php
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'transport';

$mysqli = new mysqli($hostname, $username, $password, $database);
if ($mysqli->connect_error) die($mysqli->connect_error);

$vehicle_no = '95';

$query = "SELECT da.*, s.name as driver_name FROM driver_assignment da LEFT JOIN staff s ON s.id = da.driver WHERE da.vehicle_no = '$vehicle_no' ORDER BY from_date DESC, id DESC";
$result = $mysqli->query($query);

echo "Assignment History for Vehicle $vehicle_no:\n";
echo str_pad("ID", 6) . str_pad("Driver Name", 25) . str_pad("From Date", 15) . str_pad("To Date", 15) . "\n";
echo str_repeat("-", 61) . "\n";

while ($row = $result->fetch_assoc()) {
    echo str_pad($row['id'], 6) . 
         str_pad($row['driver_name'], 25) . 
         str_pad($row['from_date'], 15) . 
         str_pad($row['to_date'] ?? 'OPEN (NULL)', 15) . "\n";
}

$mysqli->close();
