<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAttendanceTables extends Migration
{
    public function up()
    {
        // Create staff_attendance table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'staff_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'attendance_date' => [
                'type' => 'DATE',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['Present', 'Absent', 'Leave', 'Half-day', 'Sick-leave'],
                'default' => 'Present',
            ],
            'check_in_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'check_out_time' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'leave_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Annual, Casual, Sick, Unpaid',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'updated_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['staff_id', 'attendance_date'], 'unique_staff_date');
        $this->forge->addKey('staff_id', false, false, 'idx_staff_id');
        $this->forge->addKey('attendance_date', false, false, 'idx_attendance_date');
        $this->forge->addKey('status', false, false, 'idx_status');
        $this->forge->addKey('created_by', false, false, 'idx_created_by');
        $this->forge->addKey('updated_by', false, false, 'idx_updated_by');

        $this->forge->addForeignKey('staff_id', 'staff', 'id', 'RESTRICT', 'RESTRICT', 'fk_staff_attendance_staff');
        $this->forge->addForeignKey('created_by', 'user', 'id', 'RESTRICT', 'RESTRICT', 'fk_staff_attendance_created_by');
        $this->forge->addForeignKey('updated_by', 'user', 'id', 'SET NULL', 'SET NULL', 'fk_staff_attendance_updated_by');

        $this->forge->createTable('staff_attendance', true);

        // Create attendance_settings table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'working_hours_per_day' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 8.00,
            ],
            'working_days_per_week' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 6,
            ],
            'leave_days_per_month' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 2,
            ],
            'leave_days_per_year' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 20,
            ],
            'late_threshold_minutes' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 15,
            ],
            'early_leave_threshold_minutes' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 15,
            ],
            'weekend_days' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'Saturday,Sunday',
                'comment' => 'Comma-separated: Sunday,Saturday',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('attendance_settings', true);

        // Insert default settings
        $data = [
            'working_hours_per_day' => 8.00,
            'working_days_per_week' => 6,
            'leave_days_per_month' => 2,
            'leave_days_per_year' => 20,
            'late_threshold_minutes' => 15,
            'early_leave_threshold_minutes' => 15,
            'weekend_days' => 'Saturday,Sunday',
        ];
        $this->db->table('attendance_settings')->insert($data);
    }

    public function down()
    {
        $this->forge->dropTable('staff_attendance', true);
        $this->forge->dropTable('attendance_settings', true);
    }
}
