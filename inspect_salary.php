<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (!$db) die("Connection failed");

echo "--- Table: staff_salary ---\n";
$res = mysqli_query($db, "SHOW COLUMNS FROM `staff_salary`");
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>
