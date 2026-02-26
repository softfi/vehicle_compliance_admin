<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (!$db) die("Connection failed");

$table = 'do_registration';
$res = mysqli_query($db, "DESCRIBE `$table`");
if (!$res) {
    echo "Error describing table $table: " . mysqli_error($db) . "\n";
    exit;
}
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . "\n";
}
?>
