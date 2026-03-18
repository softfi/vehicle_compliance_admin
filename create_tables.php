<?php
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'transport';

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql1 = "CREATE TABLE IF NOT EXISTS `driver_material_issue` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `driver_id` INT NOT NULL,
  `item_name` VARCHAR(100) NOT NULL,
  `issued_date` DATE NOT NULL,
  `status` ENUM('Active', 'Replaced', 'Returned') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$sql2 = "CREATE TABLE IF NOT EXISTS `driver_material_reissue` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `driver_id` INT NOT NULL,
  `item_name` VARCHAR(100) NOT NULL,
  `old_item_pic` VARCHAR(255) NOT NULL,
  `new_item_pic` VARCHAR(255) NOT NULL,
  `reissue_date` DATE NOT NULL,
  `remarks` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql1) === TRUE) {
    echo "Table driver_material_issue created successfully\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

if ($conn->query($sql2) === TRUE) {
    echo "Table driver_material_reissue created successfully\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
?>
