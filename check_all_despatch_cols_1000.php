<?php
$mysqli = new mysqli("localhost", "root", "", "transport");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$result = $mysqli->query("SELECT * FROM despatch WHERE do_no = 391");
while ($row = $result->fetch_assoc()) {
    foreach ($row as $col => $val) {
        if ($val == 1000) {
            echo "Found 1000 in ID {$row['despatch_id']}, column: $col\n";
        }
    }
}
$mysqli->close();
?>
