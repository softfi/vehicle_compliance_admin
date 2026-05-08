<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVehicleTypeToVehicleTable extends Migration
{
    public function up()
    {
        // Check if column exists before adding to prevent errors if already added manually
        if (!$this->db->fieldExists('vehicle_type', 'vehicle')) {
            $fields = [
                'vehicle_type' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 1,
                    'null'       => false,
                    'after'      => 'id'
                ],
            ];
            $this->forge->addColumn('vehicle', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('vehicle_type', 'vehicle')) {
            $this->forge->dropColumn('vehicle', 'vehicle_type');
        }
    }
}
