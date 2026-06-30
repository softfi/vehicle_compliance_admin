<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFaceEmbeddingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'staff_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'embedding' => [
                'type' => 'TEXT',
                'null' => false,
                'comment' => 'JSON encoded array of the facial embedding',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('staff_id');
        $this->forge->addForeignKey('staff_id', 'staff', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('face_embeddings');
    }

    public function down()
    {
        $this->forge->dropTable('face_embeddings');
    }
}
