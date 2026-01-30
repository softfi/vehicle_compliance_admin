<?php
class UpdateReceiptImageColumn {
    public function run() {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        $fields = [
            'receipt_image' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ];

        $forge->modifyColumn('voucher', $fields);
        echo "Column receipt_image updated successfully to TEXT.\n";
    }
}

$migration = new UpdateReceiptImageColumn();
$migration->run();
