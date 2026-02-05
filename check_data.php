<?php
$mysqli = new mysqli("localhost", "root", "", "transport");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$result = $mysqli->query("SELECT despatch_id, quantity, rest_amount FROM despatch WHERE do_no = 391");
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
$mysqli->close();
?>
