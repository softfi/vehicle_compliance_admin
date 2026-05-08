<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReplacementDateToTyerManagement extends Migration
{
    public function up()
    {
        $fields = [
            'replacement_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'asign_date'
            ],
        ];
        $this->forge->addColumn('tyer_management', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tyer_management', 'replacement_date');
    }
}
