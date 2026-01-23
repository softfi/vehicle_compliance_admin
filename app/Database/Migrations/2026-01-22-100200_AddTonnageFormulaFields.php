<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTonnageFormulaFields extends Migration
{
    public function up()
    {
        $fields = [
            'min' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'after'      => 'set_id',
            ],
            'max' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'after'      => 'min',
            ],
            'penalty_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'comment'    => 'percentage or fixed',
                'after'      => 'max',
            ],
            'penalty_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'default'    => 0,
                'after'      => 'penalty_type',
            ],
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

    public function down()
    {
        $this->forge->dropColumn('tonnage', ['min', 'max', 'penalty_type', 'penalty_value', 'bonus_type', 'bonus_value']);
    }
}
