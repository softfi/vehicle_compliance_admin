<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTyreExchangeHistoryTable extends Migration
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
            'vehicle_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'from_tyre_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Old tyre removed from vehicle',
            ],
            'to_tyre_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'New tyre installed on vehicle',
            ],
            'tyre_position' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'exchange_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
            ],
        ]);
        $this->forge->addKey('id', true);
        if (!$this->db->tableExists('tyre_exchange_history')) {
            $this->forge->createTable('tyre_exchange_history');
        }
    }

    public function down()
    {
        $this->forge->dropTable('tyre_exchange_history', true);
    }
}
