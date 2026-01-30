<?php
require 'app/Config/Paths.php';
require 'vendor/autoload.php';
require 'system/Test/Bootstrap.php';

$db = db_connect();
$forge = \Config\Database::forge();

$fields = [
    'received_date' => [
        'type' => 'DATE',
        'null' => true,
    ],
    'received_amount' => [
        'type' => 'DECIMAL',
        'constraint' => '15,2',
        'default' => 0.00,
    ],
    'adjustment_amount' => [
        'type' => 'DECIMAL',
        'constraint' => '15,2',
        'default' => 0.00,
    ],
    'adjustment_remarks' => [
        'type' => 'TEXT',
        'null' => true,
    ],
];

echo "Adding columns to 'voucher' table...\n";

foreach ($fields as $fieldName => $fieldConfig) {
    if (!$db->fieldExists($fieldName, 'voucher')) {
        $forge->addColumn('voucher', [$fieldName => $fieldConfig]);
        echo "Added column: $fieldName\n";
    } else {
        echo "Column already exists: $fieldName\n";
    }
}

echo "Done.\n";
