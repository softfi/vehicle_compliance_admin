<?php
// Debug script to check driver assignments for a specific vehicle
require 'vendor/autoload.php'; // Adjust path if needed
// Actually, I can just use a raw mysqli script for simplicity since I have the config

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'transport';

$mysqli = new mysqli($hostname, $username, $password, $database);

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

$vehicle_no = '95'; // Example vehicle
$query = "SELECT * FROM driver_assignment WHERE vehicle_no = '$vehicle_no' ORDER BY from_date DESC LIMIT 20";
$result = $mysqli->query($query);

echo "ID\tVehicle\tDriver\tFrom\tTo\n";
while ($row = $result->fetch_assoc()) {
    echo $row['id'] . "\t" . $row['vehicle_no'] . "\t" . $row['driver'] . "\t" . $row['from_date'] . "\t" . ($row['to_date'] ?? 'NULL') . "\n";
}

$mysqli->close();
