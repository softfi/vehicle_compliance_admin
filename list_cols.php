<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (!$db) die("Connection failed");

$res = mysqli_query($db, "DESCRIBE `salary_payment`");
$cols = [];
while ($row = mysqli_fetch_assoc($res)) {
    $cols[] = $row['Field'] . " (" . $row['Type'] . ")";
}
echo implode(", ", $cols);
?>
