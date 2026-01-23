<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AllowNullWeightPriceInTonnage extends Migration
{
    public function up()
    {
        // Modify weight and price columns to allow NULL using raw SQL
        try {
            $this->db->query('ALTER TABLE tonnage MODIFY COLUMN weight DECIMAL(10,2) NULL');
        } catch (\Exception $e) {
            // Column might already be nullable or doesn't exist
        }
        
        try {
            $this->db->query('ALTER TABLE tonnage MODIFY COLUMN price DECIMAL(10,2) NULL');
        } catch (\Exception $e) {
            // Column might already be nullable or doesn't exist
        }
    }

    public function down()
    {
        // Revert weight and price columns to NOT NULL using raw SQL
        try {
            $this->db->query('ALTER TABLE tonnage MODIFY COLUMN weight DECIMAL(10,2) NOT NULL');
        } catch (\Exception $e) {
            // Ignore errors
        }
        
        try {
            $this->db->query('ALTER TABLE tonnage MODIFY COLUMN price DECIMAL(10,2) NOT NULL');
        } catch (\Exception $e) {
            // Ignore errors
        }
    }
}
