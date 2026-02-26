<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (!$db) die("Connection failed");

echo "--- Table: staff ---\n";
$res = mysqli_query($db, "SHOW COLUMNS FROM `staff`");
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . "\n";
}
?>
