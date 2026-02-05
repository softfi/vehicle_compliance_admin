<?php
$mysqli = new mysqli("localhost", "root", "", "transport");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$result = $mysqli->query("SELECT * FROM do_registration WHERE do_registration_id = 391");
$row = $result->fetch_assoc();
foreach ($row as $col => $val) {
    if ($val == 1000) {
        echo "Found 1000 in column: $col\n";
    }
}
$mysqli->close();
?>
