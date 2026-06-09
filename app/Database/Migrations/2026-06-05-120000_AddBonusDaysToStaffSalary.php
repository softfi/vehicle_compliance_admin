<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBonusDaysToStaffSalary extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('bonus_days', 'staff_salary')) {
            $this->forge->addColumn('staff_salary', [
                'bonus_days' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => false,
                    'default'    => 0,
                    'after'      => 'working_day',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('bonus_days', 'staff_salary')) {
            $this->forge->dropColumn('staff_salary', 'bonus_days');
        }
    }
}
