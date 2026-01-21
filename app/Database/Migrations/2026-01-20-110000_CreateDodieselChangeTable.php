<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDodieselChangeTable extends Migration
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
            'dono' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'DO Registration ID',
            ],
            'diesel_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'comment'    => 'Diesel Rate',
            ],
            'from_date' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Effective From Date',
            ],
            'to_date' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Effective To Date',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('dono');
        $this->forge->createTable('dodiesel_change');
    }

    public function down()
    {
        $this->forge->dropTable('dodiesel_change');
    }
}
