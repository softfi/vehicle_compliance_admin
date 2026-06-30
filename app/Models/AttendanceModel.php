<?php

namespace App\Models;

use CodeIgniter\Model;
use DateTime;

class AttendanceModel extends Model
{
    protected $table = 'staff_attendance';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'staff_id',
        'attendance_date',
        'status',
        'check_in_time',
        'check_out_time',
        'notes',
        'leave_type',
        'created_by',
        'updated_by'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';

    /**
     * Add single attendance record
     */
    public function addAttendance($data)
    {
        return $this->insert($data);
    }

    /**
     * Bulk add multiple attendance records
     */
    public function bulkAddAttendance($records)
    {
        return $this->insertBatch($records);
    }

    /**
     * Update attendance record
     */
    public function updateAttendance($id, $data)
    {
        return $this->update($id, $data);
    }

    /**
     * Delete attendance record (soft delete by setting status=deleted)
     */
    public function deleteAttendance($id)
    {
        return $this->delete($id);
    }

    /**
     * Get duplicate check - prevent duplicate entries
     */
    public function getDuplicateCheck($staff_id, $attendance_date)
    {
        return $this->where('staff_id', $staff_id)
            ->where('attendance_date', $attendance_date)
            ->first();
    }

    /**
     * Get attendance by staff for date range
     */
    public function getAttendanceByStaff($staff_id, $from_date = null, $to_date = null)
    {
        $builder = $this->db->table($this->table)
            ->select('staff_attendance.*, staff.name as staff_name, staff.staff_code')
            ->join('staff', 'staff.id = staff_attendance.staff_id', 'left')
            ->where('staff_attendance.staff_id', $staff_id);

        if ($from_date && $to_date) {
            $builder->where('staff_attendance.attendance_date >=', $from_date);
            $builder->where('staff_attendance.attendance_date <=', $to_date);
        }

        return $builder->orderBy('staff_attendance.attendance_date', 'DESC')
            ->get()->getResult();
    }

    /**
     * Get all attendance with optional filters
     */
    public function getAttendanceReport($from_date = null, $to_date = null, $filters = [])
    {
        $builder = $this->db->table($this->table)
            ->select('staff_attendance.*, staff.name as staff_name, staff.staff_code, location.location_name')
            ->join('staff', 'staff.id = staff_attendance.staff_id', 'left')
            ->join('location', 'location.location_id = staff.location_id', 'left');

        // Apply date range filter
        if ($from_date && $to_date) {
            $builder->where('staff_attendance.attendance_date >=', $from_date);
            $builder->where('staff_attendance.attendance_date <=', $to_date);
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $builder->where('staff_attendance.status', $filters['status']);
        }

        // Apply staff filter
        if (!empty($filters['staff_id'])) {
            $builder->where('staff_attendance.staff_id', $filters['staff_id']);
        }

        // Apply location filter
        if (!empty($filters['location_id'])) {
            $builder->where('staff.location_id', $filters['location_id']);
        }

        // Apply staff type filter
        if (!empty($filters['staff_type'])) {
            $builder->where('staff.user_type', $filters['staff_type']);
        }

        return $builder->orderBy('staff_attendance.attendance_date', 'DESC')
            ->get()->getResult();
    }

    /**
     * Get attendance summary for a month
     */
    public function getAttendanceSummary($staff_id, $month, $year)
    {
        return $this->db->table($this->table)
            ->select('
                MONTH(attendance_date) as month,
                YEAR(attendance_date) as year,
                status,
                COUNT(*) as count
            ')
            ->where('staff_id', $staff_id)
            ->where('MONTH(attendance_date)', $month)
            ->where('YEAR(attendance_date)', $year)
            ->groupBy('status')
            ->get()->getResult();
    }

    /**
     * Get absentee report
     */
    public function getAbsenteeReport($from_date, $to_date)
    {
        return $this->db->table($this->table)
            ->select('
                staff_attendance.*,
                staff.name as staff_name,
                staff.staff_code,
                location.location_name,
                COUNT(staff_attendance.id) as absent_count
            ')
            ->join('staff', 'staff.id = staff_attendance.staff_id', 'left')
            ->join('location', 'location.location_id = staff.location_id', 'left')
            ->where('staff_attendance.status', 'Absent')
            ->where('staff_attendance.attendance_date >=', $from_date)
            ->where('staff_attendance.attendance_date <=', $to_date)
            ->groupBy('staff_attendance.staff_id')
            ->orderBy('absent_count', 'DESC')
            ->get()->getResult();
    }

    /**
     * Get location wise attendance
     */
    public function getLocationWiseAttendance($location_id, $from_date = null, $to_date = null)
    {
        $builder = $this->db->table($this->table)
            ->select('
                location.location_name,
                staff_attendance.status,
                COUNT(staff_attendance.id) as count
            ')
            ->join('staff', 'staff.id = staff_attendance.staff_id', 'left')
            ->join('location', 'location.location_id = staff.location_id', 'left')
            ->where('staff.location_id', $location_id);

        if ($from_date && $to_date) {
            $builder->where('staff_attendance.attendance_date >=', $from_date);
            $builder->where('staff_attendance.attendance_date <=', $to_date);
        }

        return $builder->groupBy('staff_attendance.status')
            ->get()->getResult();
    }

    /**
     * Get staff type wise attendance
     */
    public function getStaffTypeAttendance($staff_type, $from_date = null, $to_date = null)
    {
        $builder = $this->db->table($this->table)
            ->select('
                staff.user_type,
                staff_attendance.status,
                COUNT(staff_attendance.id) as count
            ')
            ->join('staff', 'staff.id = staff_attendance.staff_id', 'left')
            ->where('staff.user_type', $staff_type);

        if ($from_date && $to_date) {
            $builder->where('staff_attendance.attendance_date >=', $from_date);
            $builder->where('staff_attendance.attendance_date <=', $to_date);
        }

        return $builder->groupBy('staff_attendance.status')
            ->get()->getResult();
    }

    /**
     * Get attendance percentage for a staff member
     */
    public function getAttendancePercentage($staff_id, $from_date, $to_date)
    {
        $total = $this->db->table($this->table)
            ->where('staff_id', $staff_id)
            ->where('attendance_date >=', $from_date)
            ->where('attendance_date <=', $to_date)
            ->countAllResults(false);

        $present = $this->db->table($this->table)
            ->where('staff_id', $staff_id)
            ->where('status', 'Present')
            ->where('attendance_date >=', $from_date)
            ->where('attendance_date <=', $to_date)
            ->countAllResults(false);

        if ($total == 0) {
            return 0;
        }

        return round(($present / $total) * 100, 2);
    }

    /**
     * Get present count
     */
    public function getPresentCount($staff_id, $from_date = null, $to_date = null)
    {
        $builder = $this->db->table($this->table)
            ->where('staff_id', $staff_id)
            ->where('status', 'Present');

        if ($from_date && $to_date) {
            $builder->where('attendance_date >=', $from_date);
            $builder->where('attendance_date <=', $to_date);
        }

        return $builder->countAllResults();
    }

    /**
     * Get absent count
     */
    public function getAbsentCount($staff_id, $from_date = null, $to_date = null)
    {
        $builder = $this->db->table($this->table)
            ->where('staff_id', $staff_id)
            ->where('status', 'Absent');

        if ($from_date && $to_date) {
            $builder->where('attendance_date >=', $from_date);
            $builder->where('attendance_date <=', $to_date);
        }

        return $builder->countAllResults();
    }

    /**
     * Get leave count
     */
    public function getLeaveCount($staff_id, $from_date = null, $to_date = null)
    {
        $builder = $this->db->table($this->table)
            ->where('staff_id', $staff_id)
            ->whereIn('status', ['Leave', 'Sick-leave']);

        if ($from_date && $to_date) {
            $builder->where('attendance_date >=', $from_date);
            $builder->where('attendance_date <=', $to_date);
        }

        return $builder->countAllResults();
    }

    /**
     * Get all staff for dropdown
     */
    public function getAllStaff()
    {
        return $this->db->table('staff')
            ->select('id, name, staff_code, location_id')
            ->where('user_type !=', 'DRIVER')
            ->orderBy('name', 'ASC')
            ->get()->getResult();
    }

    /**
     * Get all locations
     */
    public function getAllLocations()
    {
        return $this->db->table('location')
            ->select('location_id, location_name')
            ->where('status !=', 'Inactive')
            ->orderBy('location_name', 'ASC')
            ->get()->getResult();
    }

    /**
     * Get attendance settings
     */
    public function getAttendanceSettings()
    {
        return $this->db->table('attendance_settings')
            ->where('id', 1)
            ->first();
    }

    /**
     * Update attendance settings
     */
    public function updateAttendanceSettings($data)
    {
        return $this->db->table('attendance_settings')
            ->update($data, ['id' => 1]);
    }

    /**
     * Get staff with attendance for specific date
     */
    public function getStaffAttendanceForDate($attendance_date)
    {
        return $this->db->table($this->table)
            ->select('staff_attendance.*, staff.name as staff_name, staff.staff_code')
            ->join('staff', 'staff.id = staff_attendance.staff_id', 'left')
            ->where('staff_attendance.attendance_date', $attendance_date)
            ->orderBy('staff.name', 'ASC')
            ->get()->getResult();
    }

    /**
     * Get attendance statistics for date range
     */
    public function getAttendanceStats($from_date, $to_date, $filters = [])
    {
        $builder = $this->db->table($this->table)
            ->select('
                COUNT(*) as total_records,
                COUNT(CASE WHEN status = "Present" THEN 1 END) as present_count,
                COUNT(CASE WHEN status = "Absent" THEN 1 END) as absent_count,
                COUNT(CASE WHEN status = "Leave" THEN 1 END) as leave_count,
                COUNT(CASE WHEN status = "Half-day" THEN 1 END) as half_day_count,
                COUNT(CASE WHEN status = "Sick-leave" THEN 1 END) as sick_leave_count,
                COUNT(CASE WHEN status = "Holiday" THEN 1 END) as holiday_count
            ')
            ->join('staff', 'staff.id = staff_attendance.staff_id', 'left');

        if ($from_date && $to_date) {
            $builder->where('attendance_date >=', $from_date)
                    ->where('attendance_date <=', $to_date);
        }

        if (!empty($filters['staff_id'])) {
            $builder->where('staff_attendance.staff_id', $filters['staff_id']);
        }

        if (!empty($filters['location_id'])) {
            $builder->where('staff.location_id', $filters['location_id']);
        }

        return $builder->get()->getRow();
    }

    /**
     * Get month-wise attendance trend
     */
    public function getAttendanceTrends($staff_id, $year)
    {
        return $this->db->table($this->table)
            ->select('
                MONTH(attendance_date) as month,
                COUNT(*) as total_days,
                COUNT(CASE WHEN status = "Present" THEN 1 END) as present_days,
                COUNT(CASE WHEN status = "Absent" THEN 1 END) as absent_days,
                ROUND(COUNT(CASE WHEN status = "Present" THEN 1 END) * 100 / COUNT(*), 2) as percentage
            ')
            ->where('staff_id', $staff_id)
            ->where('YEAR(attendance_date)', $year)
            ->groupBy('MONTH(attendance_date)')
            ->orderBy('MONTH(attendance_date)', 'ASC')
            ->get()->getResult();
    }

    /**
     * Search attendance with pagination
     */
    public function searchAttendance($from_date, $to_date, $filters = [], $page = 1, $perPage = 25)
    {
        $offset = ($page - 1) * $perPage;

        $builder = $this->db->table($this->table)
            ->select('staff_attendance.*, staff.name as staff_name, staff.staff_code, location.location_name')
            ->join('staff', 'staff.id = staff_attendance.staff_id', 'left')
            ->join('location', 'location.location_id = staff.location_id', 'left');

        if ($from_date && $to_date) {
            $builder->where('staff_attendance.attendance_date >=', $from_date);
            $builder->where('staff_attendance.attendance_date <=', $to_date);
        }

        if (!empty($filters['status'])) {
            $builder->where('staff_attendance.status', $filters['status']);
        }

        if (!empty($filters['staff_id'])) {
            $builder->where('staff_attendance.staff_id', $filters['staff_id']);
        }

        if (!empty($filters['location_id'])) {
            $builder->where('staff.location_id', $filters['location_id']);
        }

        // Get total count
        $total = $builder->countAllResults(false);

        // Get paginated results
        $results = $builder->orderBy('staff_attendance.attendance_date', 'DESC')
            ->limit($perPage, $offset)
            ->get()->getResult();

        return [
            'data' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }
}
