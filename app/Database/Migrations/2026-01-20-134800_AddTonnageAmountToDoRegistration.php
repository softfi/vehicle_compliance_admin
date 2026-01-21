<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTonnageAmountToDoRegistration extends Migration
{
    public function up()
    {
        $fields = [
            'tonnage_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'after' => 'load_tonnage_id' 
            ],
        ];

        $this->forge->addColumn('do_registration', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('do_registration', ['tonnage_amount']);
    }
}
