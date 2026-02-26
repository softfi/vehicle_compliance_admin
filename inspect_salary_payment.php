<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (!$db) die("Connection failed");

echo "--- Table: salary_payment ---\n";
$res = mysqli_query($db, "SHOW COLUMNS FROM `salary_payment`");
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>
