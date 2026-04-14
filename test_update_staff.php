<?php
$mysqli = new mysqli("localhost", "root", "", "transport");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
// Try to update record 1450
$sql = "UPDATE staff SET location_id = 2, address = 'Test Update' WHERE id = 1450";
if ($mysqli->query($sql) === TRUE) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $mysqli->error;
}
$mysqli->close();
?>
