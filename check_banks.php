<?php
define('ENVIRONMENT', 'development');
require 'app/Config/Paths.php';
$paths = new \Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';
$db = \Config\Database::connect();

$res = $db->query("SELECT * FROM bank")->getResult();
echo "BANK TABLE CONTENTS:\n";
foreach($res as $r) {
    echo "ID: " . $r->id . " | NAME: " . $r->bank_name . "\n";
}
