<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLatitudeLongitudeToLocationTable extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('latitude', 'location')) {
            $this->forge->addColumn('location', [
                'latitude' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,7',
                    'null'       => true,
                    'after'      => 'radius',
                ],
            ]);
        }

        if (! $this->db->fieldExists('longitude', 'location')) {
            $this->forge->addColumn('location', [
                'longitude' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '11,7',
                    'null'       => true,
                    'after'      => 'latitude',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('longitude', 'location')) {
            $this->forge->dropColumn('location', 'longitude');
        }

        if ($this->db->fieldExists('latitude', 'location')) {
            $this->forge->dropColumn('location', 'latitude');
        }
    }
}
