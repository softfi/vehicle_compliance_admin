<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVehicleTypesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'type_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Active', 'Inactive'],
                'default'    => 'Active',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('vehicle_types');

        // Insert default types to maintain backward compatibility (Truck=1, Loader=2)
        $db = \Config\Database::connect();
        $db->table('vehicle_types')->insertBatch([
            ['id' => 1, 'type_name' => 'Truck', 'created_at' => date('Y-m-d H:i:s')],
            ['id' => 2, 'type_name' => 'Loader', 'created_at' => date('Y-m-d H:i:s')],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('vehicle_types');
    }
}
