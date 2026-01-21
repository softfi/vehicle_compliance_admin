<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDieselRateFieldToDoRegistration extends Migration
{
    public function up()
    {
        $fields = [
            'diesel_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'after'      => 'rate'
            ],
        ];
        $this->forge->addColumn('do_registration', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('do_registration', 'diesel_rate');
    }
}
