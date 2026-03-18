<?php
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'transport';

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Alter table to support comma separated items
$sql = "ALTER TABLE `driver_material_issue` MODIFY `item_name` TEXT NOT NULL";

if ($conn->query($sql) === TRUE) {
    echo "Table driver_material_issue updated successfully\n";
} else {
    echo "Error updating table: " . $conn->error . "\n";
}

$conn->close();
?>
