<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';

// Custom boot for CLI
$app = CodeIgniter\Boot::bootWeb($paths); 
// Wait, bootWeb will exit! 

// Let's just manually connect
require $paths->systemDirectory . '/Common.php';
require 'vendor/autoload.php';

$db = \Config\Database::connect();
$tables = ['account_vouchers', 'account_voucher_entries'];
foreach($tables as $t) {
    echo "TABLE: $t\n";
    $fields = $db->query("DESCRIBE $t")->getResult();
    foreach($fields as $f) {
        echo " - " . $f->Field . " (" . $f->Type . ") Null: " . $f->Null . "\n";
    }
}
