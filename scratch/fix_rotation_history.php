<?php
// Scratch script to fix event_type 12 to 8 in tyer_management_history
$mysqli = new mysqli("localhost", "root", "", "transport");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$sql = "UPDATE tyer_management_history SET event_type = 8 WHERE event_type = 12";

if ($mysqli->query($sql) === TRUE) {
    echo "Records updated successfully: " . $mysqli->affected_rows;
} else {
    echo "Error updating records: " . $mysqli->error;
}

$mysqli->close();
?>
