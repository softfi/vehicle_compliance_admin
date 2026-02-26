<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}
$query = "ALTER TABLE staff_advance ADD COLUMN despatch_id INT(11) NULL AFTER staff_id";
if (mysqli_query($db, $query)) {
    echo "Column despatch_id added successfully to staff_advance table.\n";
} else {
    echo "Error adding column: " . mysqli_error($db) . "\n";
}
?>
