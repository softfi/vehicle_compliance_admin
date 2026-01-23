<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSetIdToTonnageTable extends Migration
{
    public function up()
    {
        $fields = [
            'set_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
        ];

        $this->forge->addColumn('tonnage', $fields);
        
        // Add foreign key constraint (MySQL compatible)
        // Note: Foreign key will be added only if both tables exist
        try {
            $this->db->query('ALTER TABLE tonnage ADD CONSTRAINT fk_tonnage_set FOREIGN KEY (set_id) REFERENCES set_master(id) ON DELETE CASCADE ON UPDATE CASCADE');
        } catch (\Exception $e) {
            // If foreign key already exists or table doesn't exist, skip
            // This allows migration to run even if constraint already exists
        }
    }

    public function down()
    {
        // Drop foreign key first (MySQL compatible)
        try {
            $this->db->query('ALTER TABLE tonnage DROP FOREIGN KEY fk_tonnage_set');
        } catch (\Exception $e) {
            // If foreign key doesn't exist, skip
        }
        
        // Drop column
        $this->forge->dropColumn('tonnage', ['set_id']);
    }
}
