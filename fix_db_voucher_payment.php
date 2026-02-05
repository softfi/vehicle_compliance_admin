<?php
// fix_db_voucher_payment.php

// Database connection parameters
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'transport';

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create table
$sql = "CREATE TABLE IF NOT EXISTS `voucher_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_number` varchar(50) DEFAULT NULL,
  `do_numbers` text DEFAULT NULL COMMENT 'Comma separated DO IDs or Numbers',
  `voucher_ids` text DEFAULT NULL COMMENT 'Comma separated Voucher IDs',
  `total_net_amount` decimal(15,2) DEFAULT '0.00',
  `received_amount` decimal(15,2) DEFAULT '0.00',
  `difference_amount` decimal(15,2) DEFAULT '0.00',
  `adjustment_amount` decimal(15,2) DEFAULT '0.00',
  `adjustment_remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "Table voucher_payment created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
