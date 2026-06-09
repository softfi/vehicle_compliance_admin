<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHolidayToAttendanceStatus extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE `staff_attendance` MODIFY COLUMN `status` ENUM('Present', 'Absent', 'Leave', 'Half-day', 'Sick-leave', 'Holiday') NOT NULL DEFAULT 'Present'");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `staff_attendance` MODIFY COLUMN `status` ENUM('Present', 'Absent', 'Leave', 'Half-day', 'Sick-leave') NOT NULL DEFAULT 'Present'");
    }
}
