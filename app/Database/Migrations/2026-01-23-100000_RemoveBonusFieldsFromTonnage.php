<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveBonusFieldsFromTonnage extends Migration
{
    public function up()
    {
        // Drop bonus columns from tonnage table (if they exist)
        // Check if columns exist before dropping
        $db = \Config\Database::connect();
        $query = $db->query("SHOW COLUMNS FROM tonnage LIKE 'bonus_type'");
        if($query->getNumRows() > 0) {
            try {
                $this->db->query('ALTER TABLE tonnage DROP COLUMN bonus_type');
            } catch (\Exception $e) {
                // Ignore if already dropped
            }
        }
        
        $query = $db->query("SHOW COLUMNS FROM tonnage LIKE 'bonus_value'");
        if($query->getNumRows() > 0) {
            try {
                $this->db->query('ALTER TABLE tonnage DROP COLUMN bonus_value');
            } catch (\Exception $e) {
                // Ignore if already dropped
            }
        }
    }

    public function down()
    {
        // Re-add bonus columns if rollback needed
        $fields = [
            'bonus_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'comment'    => 'percentage or fixed',
                'after'      => 'penalty_value',
            ],
            'bonus_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'default'    => 0,
                'after'      => 'bonus_type',
            ],
        ];
        $this->forge->addColumn('tonnage', $fields);
    }
}
