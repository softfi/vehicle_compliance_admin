<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddResignDateToStaffTable extends Migration
{
    public function up()
    {
        // Check if resign_date column already exists
        $fields = $this->db->getFieldNames('staff');

        if (!in_array('resign_date', $fields)) {
            $this->forge->addColumn('staff', [
                'resign_date' => [
                    'type'       => 'DATE',
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'doj',
                ],
            ]);
        }
    }

    public function down()
    {
        $fields = $this->db->getFieldNames('staff');

        if (in_array('resign_date', $fields)) {
            $this->forge->dropColumn('staff', 'resign_date');
        }
    }
}
