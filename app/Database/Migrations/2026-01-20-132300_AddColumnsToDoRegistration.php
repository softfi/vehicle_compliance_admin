<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnsToDoRegistration extends Migration
{
    public function up()
    {
        $fields = [
            'expense_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'party' 
            ],
            'load_tonnage_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'expense_type'
            ],
             'cash_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'rate' 
            ],
            'diesel_payment_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'cash_type'
            ],
        ];

        $this->forge->addColumn('do_registration', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('do_registration', ['expense_type', 'load_tonnage_id', 'cash_type', 'diesel_payment_type']);
    }
}
