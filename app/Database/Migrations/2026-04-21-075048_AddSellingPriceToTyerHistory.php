<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSellingPriceToTyerHistory extends Migration
{
    public function up()
    {
        $fields = [
            'selling_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'after'      => 'buyer_name',
            ],
        ];
        $this->forge->addColumn('tyer_management_history', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tyer_management_history', 'selling_price');
    }
}
