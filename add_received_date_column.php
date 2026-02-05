<?php
// add_received_date_column.php

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'transport';

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add received_date column
$sql = "ALTER TABLE `voucher_payment` ADD COLUMN `received_date` DATE DEFAULT NULL AFTER `received_amount`";

if ($conn->query($sql) === TRUE) {
    echo "Column received_date added successfully";
} else {
    // It might fail if column exists, which is fine, checking error message might help debugging
    echo "Error adding column (it might already exist): " . $conn->error;
}

$conn->close();
?>
