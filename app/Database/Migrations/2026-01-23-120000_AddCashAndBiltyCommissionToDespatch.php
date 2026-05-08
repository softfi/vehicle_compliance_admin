<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCashAndBiltyCommissionToDespatch extends Migration
{
    public function up()
    {
        // Check if cash column exists
        $checkCash = $this->db->query("
            SELECT COUNT(*) as count 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'despatch' 
            AND COLUMN_NAME = 'cash'
        ")->getRow();
        
        if ($checkCash && $checkCash->count == 0) {
            $this->forge->addColumn('despatch', [
                'cash' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'null'       => true,
                    'default'    => 0,
                    'after'      => 'driver_expence',
                    'comment'    => 'Manual cash paid'
                ],
            ]);
        }
        
        // Check if bilty_commission column exists
        $checkBilty = $this->db->query("
            SELECT COUNT(*) as count 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'despatch' 
            AND COLUMN_NAME = 'bilty_commission'
        ")->getRow();
        
        if ($checkBilty && $checkBilty->count == 0) {
            $this->forge->addColumn('despatch', [
                'bilty_commission' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'null'       => true,
                    'default'    => 0,
                    'after'      => 'cash',
                    'comment'    => 'Auto-calculated bilty commission'
                ],
            ]);
        }
    }

    public function down()
    {
        // Check if cash column exists before dropping
        $checkCash = $this->db->query("
            SELECT COUNT(*) as count 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'despatch' 
            AND COLUMN_NAME = 'cash'
        ")->getRow();
        
        if ($checkCash && $checkCash->count > 0) {
            $this->forge->dropColumn('despatch', 'cash');
        }
        
        // Check if bilty_commission column exists before dropping
        $checkBilty = $this->db->query("
            SELECT COUNT(*) as count 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'despatch' 
            AND COLUMN_NAME = 'bilty_commission'
        ")->getRow();
        
        if ($checkBilty && $checkBilty->count > 0) {
            $this->forge->dropColumn('despatch', 'bilty_commission');
        }
    }
}
