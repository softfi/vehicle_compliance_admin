<?php
$mysqli = new mysqli("localhost", "root", "", "transport");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$result = $mysqli->query("SELECT do_registration_id, do_no, rate FROM do_registration WHERE rate = 1000 LIMIT 10");
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
$mysqli->close();
?>
