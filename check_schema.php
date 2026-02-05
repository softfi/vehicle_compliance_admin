<?php
$mysqli = new mysqli("localhost", "root", "", "transport");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$result = $mysqli->query("DESCRIBE despatch");
while ($row = $result->fetch_assoc()) {
    if ($row['Field'] == 'rest_amount') {
        print_r($row);
    }
}
$mysqli->close();
?>
