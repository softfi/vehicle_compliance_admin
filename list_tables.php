<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (!$db) { die('Connect failed: ' . mysqli_connect_error()); }
$r = mysqli_query($db, 'SHOW TABLES');
while ($row = mysqli_fetch_array($r)) {
    echo $row[0] . "\n";
}
mysqli_close($db);
