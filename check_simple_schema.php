<?php
// Load CI4
require 'vendor/autoload.php';
$db = \Config\Database::connect();

foreach(['account_vouchers', 'account_voucher_entries'] as $table) {
    echo "TABLE: $table\n";
    $query = $db->query("DESC $table");
    foreach($query->getResult() as $row) {
        echo $row->Field . " | " . $row->Type . "\n";
    }
}
