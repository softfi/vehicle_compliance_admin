<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyTonnageWeightToVarchar extends Migration
{
    public function up()
    {
        $fields = [
            'weight' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
        ];
        $this->forge->modifyColumn('tonnage', $fields);
    }

    public function down()
    {
        $fields = [
            'weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
        ];
        $this->forge->modifyColumn('tonnage', $fields);
    }
}
