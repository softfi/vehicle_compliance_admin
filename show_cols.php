<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (!$db) die("Connection failed");

$res = mysqli_query($db, "DESCRIBE `salary_payment`");
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . "\n";
}
?>
