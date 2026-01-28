<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTdsPercentageToDoRegistration extends Migration
{
    public function up()
    {
        // Check if column exists before adding
        $checkColumn = $this->db->query("
            SELECT COUNT(*) as count 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'do_registration' 
            AND COLUMN_NAME = 'tds_percentage'
        ")->getRow();
        
        if ($checkColumn && $checkColumn->count == 0) {
            // Column doesn't exist, add it
            $this->forge->addColumn('do_registration', [
                'tds_percentage' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,2',
                    'null'       => true,
                    'default'    => 2.00,
                    'after'      => 'rate',
                    'comment'    => 'TDS percentage (default 2%)'
                ],
            ]);
        }
    }

    public function down()
    {
        // Check if column exists before dropping
        $checkColumn = $this->db->query("
            SELECT COUNT(*) as count 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'do_registration' 
            AND COLUMN_NAME = 'tds_percentage'
        ")->getRow();
        
        if ($checkColumn && $checkColumn->count > 0) {
            // Column exists, drop it
            $this->forge->dropColumn('do_registration', 'tds_percentage');
        }
    }
}
