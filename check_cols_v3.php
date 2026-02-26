<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (!$db) die("Connection failed");

$tables = ['staff_advance', 'payment_voucher', 'despatch', 'voucher'];
foreach ($tables as $table) {
    echo "--- $table ---\n";
    $res = mysqli_query($db, "DESCRIBE $table");
    while ($row = mysqli_fetch_assoc($res)) {
        echo "{$row['Field']} - {$row['Type']}\n";
    }
}
?>
