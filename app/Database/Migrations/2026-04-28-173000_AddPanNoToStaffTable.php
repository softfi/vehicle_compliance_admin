<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPanNoToStaffTable extends Migration
{
    public function up()
    {
        // Check if pan_no column already exists (safe to re-run)
        $fields = $this->db->getFieldNames('staff');

        if (!in_array('pan_no', $fields)) {
            $this->forge->addColumn('staff', [
                'pan_no' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'aadhaar_no',
                ],
            ]);
        }
    }

    public function down()
    {
        $fields = $this->db->getFieldNames('staff');

        if (in_array('pan_no', $fields)) {
            $this->forge->dropColumn('staff', 'pan_no');
        }
    }
}
