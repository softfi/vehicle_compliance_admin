<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (!$db) die("Conn fail");

$tables = ['stock', 'account_vouchers', 'cart'];
foreach ($tables as $t) {
    echo "--- $t ---\n";
    $res = mysqli_query($db, "DESCRIBE $t");
    if ($res) {
        while($row = mysqli_fetch_assoc($res)) {
            echo $row['Field'] . " - " . $row['Type'] . "\n";
        }
    } else {
        echo "Table not found\n";
    }
    echo "\n";
}
?>
