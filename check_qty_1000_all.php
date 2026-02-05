<?php
$mysqli = new mysqli("localhost", "root", "", "transport");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$result = $mysqli->query("SELECT despatch_id, do_no, quantity FROM despatch WHERE quantity = 1000 LIMIT 10");
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
$mysqli->close();
?>
