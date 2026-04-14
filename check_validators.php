<?php
$mysqli = new mysqli("localhost", "root", "", "transport");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$result = $mysqli->query("SELECT id, name, salary, tel FROM staff ORDER BY id DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
$mysqli->close();
?>
