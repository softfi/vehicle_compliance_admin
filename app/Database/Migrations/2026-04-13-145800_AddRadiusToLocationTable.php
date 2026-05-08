<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRadiusToLocationTable extends Migration
{
    public function up()
    {
        $fields = [
            'radius' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
                'after'      => 'opening_balance'
            ],
        ];
        $this->forge->addColumn('location', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('location', 'radius');
    }
}
