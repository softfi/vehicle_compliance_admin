<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLocationIdToStaffTable extends Migration
{
    public function up()
    {
        // Change 'address' column from int(11) to varchar(255) for physical text address
        $this->forge->modifyColumn('staff', [
            'address' => [
                'name'       => 'address',
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ],
        ]);

        // Add new 'location_id' column for workstation location (FK to location table)
        $this->forge->addColumn('staff', [
            'location_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
                'after'      => 'address',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('staff', 'location_id');

        $this->forge->modifyColumn('staff', [
            'address' => [
                'name'       => 'address',
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
            ],
        ]);
    }
}
