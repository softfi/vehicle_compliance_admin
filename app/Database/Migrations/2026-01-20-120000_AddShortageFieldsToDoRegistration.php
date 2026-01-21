<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShortageFieldsToDoRegistration extends Migration
{
    public function up()
    {
        $fields = [
            'shortage_qty' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'after'      => 'rate'
            ],
            'shortage_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'after'      => 'shortage_qty'
            ],
            'special_shortage' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'shortage_rate'
            ],
        ];
        $this->forge->addColumn('do_registration', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('do_registration', ['shortage_qty', 'shortage_rate', 'special_shortage']);
    }
}
