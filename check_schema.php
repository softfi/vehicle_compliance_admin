<?php
define('ENVIRONMENT', 'development');
require 'app/Config/Paths.php';
$paths = new \Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';
$db = \Config\Database::connect();

$fields = $db->getFieldNames('account_vouchers');
echo "COLUMNS IN account_vouchers:\n";
print_r($fields);

$fields2 = $db->getFieldNames('account_voucher_entries');
echo "\nCOLUMNS IN account_voucher_entries:\n";
print_r($fields2);
