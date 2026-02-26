<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (!$db) die("Connection failed: " . mysqli_connect_error());

$queries = [
    "ALTER TABLE `salary_payment` ADD COLUMN `opening_balance` DECIMAL(10,2) DEFAULT 0 AFTER `month` ",
    "ALTER TABLE `salary_payment` ADD COLUMN `net_salary` DECIMAL(10,2) DEFAULT 0 AFTER `opening_balance` "
];

foreach ($queries as $q) {
    if (mysqli_query($db, $q)) {
        echo "Success: $q\n";
    } else {
        echo "Error: " . mysqli_error($db) . " (Query: $q)\n";
    }
}
?>
