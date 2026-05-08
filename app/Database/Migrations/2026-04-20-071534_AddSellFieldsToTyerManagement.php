<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSellFieldsToTyerManagement extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('buyer_name', 'tyer_management')) {
            $fields = [
                'buyer_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'remark',
                ],
            ];
            $this->forge->addColumn('tyer_management', $fields);
        }

        if (!$this->db->fieldExists('selling_date', 'tyer_management')) {
            $fields = [
                'selling_date' => [
                    'type' => 'DATE',
                    'null' => true,
                    'after' => 'buyer_name',
                ],
            ];
            $this->forge->addColumn('tyer_management', $fields);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('tyer_management', ['buyer_name', 'selling_date']);
    }
}
