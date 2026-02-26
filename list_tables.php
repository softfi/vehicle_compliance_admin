<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (!$db) die("Connection failed");

$query = "SHOW TABLES";
$res = mysqli_query($db, $query);
while ($row = mysqli_fetch_array($res)) {
    echo $row[0] . "\n";
}
?>
