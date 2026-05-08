<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVendorExchangeFields extends Migration
{
    public function up()
    {
        $fields = [
            'replaced_from_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'status',
                'comment'    => 'ID of the tyre this record replaced (Warranty/Claim)',
            ],
            'replaced_to_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'replaced_from_id',
                'comment'    => 'ID of the new tyre that replaced this record',
            ],
        ];
        
        $this->forge->addColumn('tyer_management', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tyer_management', ['replaced_from_id', 'replaced_to_id']);
    }
}
