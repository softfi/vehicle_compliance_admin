<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRepairFieldsToTyerHistory extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('repair_date', 'tyer_management_history')) {
            $fields = [
                'repair_date' => [
                    'type' => 'DATE',
                    'null' => true,
                    'after' => 'remarks',
                ],
            ];
            $this->forge->addColumn('tyer_management_history', $fields);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('tyer_management_history', 'repair_date');
    }
}
