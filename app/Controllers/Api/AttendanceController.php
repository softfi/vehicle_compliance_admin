<?php

namespace App\Controllers\Api;

use App\Models\AttendanceModel;

class AttendanceController extends BaseApiController
{
    protected AttendanceModel $attendanceModel;

    public function __construct()
    {
        parent::__construct();
        $this->attendanceModel = new AttendanceModel();
        date_default_timezone_set('Asia/Kolkata');
    }

    /**
     * GET /api/attendance
     *
     * List attendance for logged-in sub-admin's location (STAFF + MECHANIC only).
     *
     * Query:
     * - attendance_date  (single day, YYYY-MM-DD — defaults to today)
     * - from_date, to_date (date range; ignored if attendance_date is set)
     * - staff_id
     * - status (Present, Absent, Leave, Half-day, Sick-leave, Holiday)
     * - search (staff name or staff code)
     * - user_type (STAFF or MECHANIC)
     */
    public function index()
    {
        $user = $this->authUser();
        $locationId = (int) ($user->location_id ?? 0);

        if ($locationId <= 0) {
            return $this->apiError('17', 'No location assigned to your account.', 400);
        }

        $singleDate = trim((string) ($this->request->getGet('attendance_date') ?? ''));
        $fromDate = trim((string) ($this->request->getGet('from_date') ?? ''));
        $toDate = trim((string) ($this->request->getGet('to_date') ?? ''));

        if ($singleDate !== '') {
            $fromDate = $singleDate;
            $toDate = $singleDate;
        } else {
            if ($fromDate === '') {
                $fromDate = date('Y-m-d');
            }
            if ($toDate === '') {
                $toDate = date('Y-m-d');
            }
        }

        $errors = [];
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
            $errors[] = 'from_date / attendance_date must be YYYY-MM-DD';
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
            $errors[] = 'to_date must be YYYY-MM-DD';
        }
        if ($errors === [] && strtotime($fromDate) > strtotime($toDate)) {
            $errors[] = 'from_date cannot be after to_date';
        }
        if ($errors !== []) {
            return $this->apiError('03', implode('; ', $errors), 400);
        }

        $staffId = (int) ($this->request->getGet('staff_id') ?? 0);
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $search = trim((string) ($this->request->getGet('search') ?? ''));
        $userType = strtoupper(trim((string) ($this->request->getGet('user_type') ?? '')));

        $validStatuses = ['Present', 'Absent', 'Leave', 'Half-day', 'Sick-leave', 'Holiday'];
        if ($status !== '' && ! in_array($status, $validStatuses, true)) {
            return $this->apiError('03', 'Invalid status. Allowed: ' . implode(', ', $validStatuses), 400);
        }

        if ($userType !== '' && ! in_array($userType, ['STAFF', 'MECHANIC'], true)) {
            return $this->apiError('03', 'user_type must be STAFF or MECHANIC.', 400);
        }

        $builder = $this->db->table('staff_attendance sa')
            ->select('sa.*, s.name AS staff_name, s.staff_code, s.user_type, s.location_id, l.location_name')
            ->join('staff s', 's.id = sa.staff_id', 'inner')
            ->join('location l', 'l.location_id = s.location_id', 'left')
            ->where('s.location_id', $locationId)
            ->whereIn('s.user_type', ['STAFF', 'MECHANIC'])
            ->where('sa.attendance_date >=', $fromDate)
            ->where('sa.attendance_date <=', $toDate);

        if ($staffId > 0) {
            $builder->where('sa.staff_id', $staffId);
        }
        if ($status !== '') {
            $builder->where('sa.status', $status);
        }
        if ($userType !== '') {
            $builder->where('s.user_type', $userType);
        }
        if ($search !== '') {
            $builder->groupStart()
                ->like('s.name', $search)
                ->orLike('s.staff_code', $search)
            ->groupEnd();
        }

        $rows = $builder
            ->orderBy('sa.attendance_date', 'DESC')
            ->orderBy('s.name', 'ASC')
            ->get()
            ->getResult();

        $records = [];
        foreach ($rows as $row) {
            $records[] = $this->formatAttendanceRow($row);
        }

        $summary = $this->buildAttendanceSummary($records);

        return $this->apiSuccess('Attendance list loaded.', [
            'filters' => [
                'location_id'     => $locationId,
                'attendance_date' => $singleDate !== '' ? $singleDate : null,
                'from_date'       => $fromDate,
                'to_date'         => $toDate,
                'staff_id'        => $staffId > 0 ? $staffId : null,
                'status'          => $status !== '' ? $status : null,
                'search'          => $search !== '' ? $search : null,
                'user_type'       => $userType !== '' ? $userType : null,
            ],
            'summary' => $summary,
            'total'   => count($records),
            'records' => $records,
        ]);
    }

    /**
     * GET /api/attendance/{id}
     *
     * Single attendance record (must belong to user's location).
     */
    public function show($id = null)
    {
        $attendanceId = (int) ($id ?? 0);
        if ($attendanceId <= 0) {
            return $this->apiError('03', 'attendance id is required.', 400);
        }

        $user = $this->authUser();
        $locationId = (int) ($user->location_id ?? 0);

        if ($locationId <= 0) {
            return $this->apiError('17', 'No location assigned to your account.', 400);
        }

        $row = $this->db->table('staff_attendance sa')
            ->select('sa.*, s.name AS staff_name, s.staff_code, s.user_type, s.location_id, l.location_name')
            ->join('staff s', 's.id = sa.staff_id', 'inner')
            ->join('location l', 'l.location_id = s.location_id', 'left')
            ->where('sa.id', $attendanceId)
            ->where('s.location_id', $locationId)
            ->whereIn('s.user_type', ['STAFF', 'MECHANIC'])
            ->get()
            ->getRow();

        if ($row === null) {
            return $this->apiError('04', 'Attendance record not found.', 404);
        }

        return $this->apiSuccess('Attendance record loaded.', [
            'record' => $this->formatAttendanceRow($row),
        ]);
    }

    /**
     * GET  /api/attendance/date-wise?attendance_date=2026-06-19
     * POST /api/attendance/date-wise  { "attendance_date": "2026-06-19" }
     *
     * Selected date ke liye logged-in user ki location ke saare active
     * STAFF + MECHANIC dikhata hai — kaun Present hai aur kaun nahi.
     * Sirf apni assigned location ka data; doosri location ka data nahi aata.
     */
    public function dateWise()
    {
        $user = $this->authUser();
        $locationId = (int) ($user->location_id ?? 0);

        if ($locationId <= 0) {
            return $this->apiError('17', 'No location assigned to your account.', 400);
        }

        $payload = $this->mergeRequestPayload();
        $attendanceDate = trim((string) (
            $this->request->getGet('attendance_date')
            ?? $this->request->getGet('date')
            ?? $payload['attendance_date']
            ?? $payload['date']
            ?? ''
        ));

        if ($attendanceDate === '') {
            $attendanceDate = date('Y-m-d');
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $attendanceDate)) {
            return $this->apiError('03', 'attendance_date must be YYYY-MM-DD.', 400);
        }

        $search = trim((string) ($this->request->getGet('search') ?? $payload['search'] ?? ''));
        $userType = strtoupper(trim((string) ($this->request->getGet('user_type') ?? $payload['user_type'] ?? '')));

        if ($userType !== '' && ! in_array($userType, ['STAFF', 'MECHANIC'], true)) {
            return $this->apiError('03', 'user_type must be STAFF or MECHANIC.', 400);
        }

        $location = $this->db->table('location')
            ->select('location_id, location_name, location_shordname')
            ->where('location_id', $locationId)
            ->get()
            ->getRow();

        $staffList = $this->getActiveStaffForLocationOnDate($locationId, $attendanceDate, $search, $userType);
        $staffIds = array_map(static fn ($s) => (int) $s->id, $staffList);

        $attendanceMap = [];
        if ($staffIds !== []) {
            $attendanceRows = $this->db->table('staff_attendance')
                ->whereIn('staff_id', $staffIds)
                ->where('attendance_date', $attendanceDate)
                ->get()
                ->getResult();

            foreach ($attendanceRows as $row) {
                $attendanceMap[(int) $row->staff_id] = $row;
            }
        }

        $staff = [];
        $summary = [
            'total_staff'  => 0,
            'present'      => 0,
            'absent'       => 0,
            'leave'        => 0,
            'half_day'     => 0,
            'sick_leave'   => 0,
            'holiday'      => 0,
            'not_marked'   => 0,
        ];

        foreach ($staffList as $member) {
            $staffId = (int) $member->id;
            $attendance = $attendanceMap[$staffId] ?? null;
            $rawStatus = $attendance !== null ? (string) ($attendance->status ?? '') : null;
            $statusMeta = $this->mapAttendanceStatusMeta($rawStatus);

            $staff[] = [
                'staff_id'        => $staffId,
                'staff_name'      => $member->name ?? null,
                'staff_code'      => $member->staff_code ?? null,
                'user_type'       => $member->user_type ?? null,
                'location_id'     => $locationId,
                'location_name'   => $member->location_name ?? ($location->location_name ?? null),
                'attendance_date' => $attendanceDate,
                'attendance_id'   => $attendance ? (int) ($attendance->id ?? 0) : null,
                'status'          => $statusMeta['status'],
                'status_label'    => $statusMeta['status_label'],
                'status_color'    => $statusMeta['status_color'],
                'status_icon'     => $statusMeta['status_icon'],
                'check_in_time'   => $attendance->check_in_time ?? null,
                'check_out_time'  => $attendance->check_out_time ?? null,
                'leave_type'      => $attendance->leave_type ?? null,
                'notes'           => $attendance->notes ?? null,
            ];

            $summaryKey = $statusMeta['summary_key'];
            if (isset($summary[$summaryKey])) {
                $summary[$summaryKey]++;
            }
            $summary['total_staff']++;
        }

        return $this->apiSuccess('Date-wise attendance loaded.', [
            'attendance_date' => $attendanceDate,
            'location'        => [
                'location_id'        => $locationId,
                'location_name'      => $location->location_name ?? null,
                'location_shortname' => $location->location_shordname ?? null,
            ],
            'summary' => $summary,
            'total'   => count($staff),
            'staff'   => $staff,
        ]);
    }

    /**
     * Web admin/attendance grid jaisa status mapping.
     *
     * @return array{status: string, status_label: string, status_color: string, status_icon: string, summary_key: string}
     */
    private function mapAttendanceStatusMeta(?string $rawStatus): array
    {
        if ($rawStatus === null || $rawStatus === '') {
            return [
                'status'       => 'Not Marked',
                'status_label' => 'Not Marked',
                'status_color' => 'none',
                'status_icon'  => 'plus',
                'summary_key'  => 'not_marked',
            ];
        }

        return match ($rawStatus) {
            'Present' => [
                'status'       => 'Present',
                'status_label' => 'Present',
                'status_color' => 'present',
                'status_icon'  => 'check',
                'summary_key'  => 'present',
            ],
            'Absent' => [
                'status'       => 'Absent',
                'status_label' => 'Absent',
                'status_color' => 'absent',
                'status_icon'  => 'times',
                'summary_key'  => 'absent',
            ],
            'Leave' => [
                'status'       => 'Leave',
                'status_label' => 'Leave',
                'status_color' => 'leave',
                'status_icon'  => 'plane',
                'summary_key'  => 'leave',
            ],
            'Half-day' => [
                'status'       => 'Half-day',
                'status_label' => 'Half-day',
                'status_color' => 'half',
                'status_icon'  => 'adjust',
                'summary_key'  => 'half_day',
            ],
            'Sick-leave' => [
                'status'       => 'Sick-leave',
                'status_label' => 'Sick-leave',
                'status_color' => 'leave',
                'status_icon'  => 'plane',
                'summary_key'  => 'sick_leave',
            ],
            'Holiday' => [
                'status'       => 'Holiday',
                'status_label' => 'Holiday',
                'status_color' => 'holiday',
                'status_icon'  => 'star',
                'summary_key'  => 'holiday',
            ],
            default => [
                'status'       => $rawStatus,
                'status_label' => $rawStatus,
                'status_color' => 'none',
                'status_icon'  => 'minus',
                'summary_key'  => 'not_marked',
            ],
        };
    }

    /**
     * @return list<object>
     */
    private function getActiveStaffForLocationOnDate(
        int $locationId,
        string $attendanceDate,
        string $search = '',
        string $userType = ''
    ): array {
        $builder = $this->db->table('staff')
            ->select('staff.id, staff.name, staff.staff_code, staff.user_type, staff.location_id, location.location_name')
            ->join('location', 'location.location_id = staff.location_id', 'left')
            ->where('staff.location_id', $locationId)
            ->whereIn('staff.user_type', ['STAFF', 'MECHANIC'])
            ->groupStart()
                ->where('staff.doj IS NULL', null, false)
                ->orWhere('staff.doj', '0000-00-00')
                ->orWhere('staff.doj <=', $attendanceDate)
            ->groupEnd()
            ->groupStart()
                ->where('staff.resign_date IS NULL', null, false)
                ->orWhere('staff.resign_date', '0000-00-00')
                ->orWhere('staff.resign_date >=', $attendanceDate)
            ->groupEnd();

        if ($userType !== '') {
            $builder->where('staff.user_type', $userType);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('staff.name', $search)
                ->orLike('staff.staff_code', $search)
            ->groupEnd();
        }

        return $builder->orderBy('staff.user_type', 'ASC')
            ->orderBy('staff.name', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * POST /api/attendance/mark-present
     *
     * Mark Present for one or multiple staff. Date and check-in time are set
     * automatically (server time, Asia/Kolkata).
     *
     * Body (single):
     *   { "staff_id": 12 }
     *
     * Body (multiple):
     *   { "staff_ids": [12, 15, 18] }
     *
     * Optional: notes
     */
    public function markPresent()
    {
        $payload = $this->mergeRequestPayload();
        $staffIds = $this->parseStaffIds($payload);

        if ($staffIds === []) {
            return $this->apiError('03', 'staff_id or staff_ids is required.', 400);
        }

        $user = $this->authUser();
        $userId = (int) $user->id;
        $locationId = (int) ($user->location_id ?? 0);

        if ($locationId <= 0) {
            return $this->apiError('17', 'No location assigned to your account.', 400);
        }

        $attendanceDate = date('Y-m-d');
        $checkInTime = date('H:i:s');
        $now = date('Y-m-d H:i:s');
        $notes = trim((string) $this->payloadValue($payload, ['notes', 'note'], ''));

        $marked = [];
        $skipped = [];
        $records = [];

        foreach ($staffIds as $staffId) {
            $result = $this->validateStaffForMarking($staffId, $locationId, $attendanceDate);

            if ($result['error'] !== null) {
                $skipped[] = [
                    'staff_id'    => $staffId,
                    'staff_name'  => $result['staff_name'],
                    'staff_code'  => $result['staff_code'],
                    'reason'      => $result['error'],
                ];
                continue;
            }

            $staff = $result['staff'];
            $duplicate = $this->attendanceModel->getDuplicateCheck($staffId, $attendanceDate);

            if ($duplicate) {
                $skipped[] = [
                    'staff_id'   => $staffId,
                    'staff_name' => $staff->name ?? null,
                    'staff_code' => $staff->staff_code ?? null,
                    'reason'     => 'Attendance already marked for today.',
                ];
                continue;
            }

            $records[] = [
                'staff_id'        => $staffId,
                'attendance_date' => $attendanceDate,
                'status'          => 'Present',
                'check_in_time'   => $checkInTime,
                'check_out_time'  => null,
                'notes'           => $notes !== '' ? $notes : null,
                'leave_type'      => null,
                'created_by'      => $userId,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];

            $marked[] = [
                'staff_id'   => $staffId,
                'staff_name' => $staff->name ?? null,
                'staff_code' => $staff->staff_code ?? null,
                'user_type'  => $staff->user_type ?? null,
            ];
        }

        if ($records === []) {
            return $this->apiError('22', 'No attendance could be marked.', 400, [
                'attendance_date' => $attendanceDate,
                'check_in_time'   => $checkInTime,
                'marked_count'    => 0,
                'skipped_count'   => count($skipped),
                'marked'          => [],
                'skipped'         => $skipped,
            ]);
        }

        if (! $this->attendanceModel->bulkAddAttendance($records)) {
            return $this->apiError('23', 'Failed to save attendance records.', 500);
        }

        $inserted = $this->fetchMarkedAttendanceRows(
            array_column($records, 'staff_id'),
            $attendanceDate
        );

        return $this->apiSuccess('Attendance marked successfully.', [
            'attendance_date' => $attendanceDate,
            'check_in_time'   => $checkInTime,
            'status'          => 'Present',
            'marked_count'    => count($marked),
            'skipped_count'   => count($skipped),
            'marked'          => $marked,
            'records'         => $inserted,
            'skipped'         => $skipped,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return list<int>
     */
    private function parseStaffIds(array $payload): array
    {
        $raw = $payload['staff_ids'] ?? $payload['staff_id'] ?? $payload['staff'] ?? null;

        if ($raw === null || $raw === '') {
            return [];
        }

        if (is_string($raw)) {
            $raw = array_filter(array_map('trim', explode(',', $raw)));
        }

        if (! is_array($raw)) {
            $raw = [$raw];
        }

        $ids = [];
        foreach ($raw as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return array{error: string|null, staff: object|null, staff_name: string|null, staff_code: string|null}
     */
    private function validateStaffForMarking(int $staffId, int $locationId, string $attendanceDate): array
    {
        $staff = $this->db->table('staff')
            ->where('id', $staffId)
            ->get()
            ->getRow();

        if ($staff === null) {
            return [
                'error'      => 'Staff not found.',
                'staff'      => null,
                'staff_name' => null,
                'staff_code' => null,
            ];
        }

        $userType = strtoupper((string) ($staff->user_type ?? ''));
        if (! in_array($userType, ['STAFF', 'MECHANIC'], true)) {
            return [
                'error'      => 'Only staff and mechanic attendance can be marked.',
                'staff'      => $staff,
                'staff_name' => $staff->name ?? null,
                'staff_code' => $staff->staff_code ?? null,
            ];
        }

        if ((int) ($staff->location_id ?? 0) !== $locationId) {
            return [
                'error'      => 'Staff does not belong to your assigned location.',
                'staff'      => $staff,
                'staff_name' => $staff->name ?? null,
                'staff_code' => $staff->staff_code ?? null,
            ];
        }

        $doj = trim((string) ($staff->doj ?? ''));
        if ($doj !== '' && $doj !== '0000-00-00' && $attendanceDate < $doj) {
            return [
                'error'      => 'Staff had not joined on this date.',
                'staff'      => $staff,
                'staff_name' => $staff->name ?? null,
                'staff_code' => $staff->staff_code ?? null,
            ];
        }

        $resignDate = trim((string) ($staff->resign_date ?? ''));
        if ($resignDate !== '' && $resignDate !== '0000-00-00' && $attendanceDate > $resignDate) {
            return [
                'error'      => 'Staff had already resigned on this date.',
                'staff'      => $staff,
                'staff_name' => $staff->name ?? null,
                'staff_code' => $staff->staff_code ?? null,
            ];
        }

        return [
            'error'      => null,
            'staff'      => $staff,
            'staff_name' => $staff->name ?? null,
            'staff_code' => $staff->staff_code ?? null,
        ];
    }

    /**
     * @param list<int> $staffIds
     *
     * @return list<array<string, mixed>>
     */
    private function fetchMarkedAttendanceRows(array $staffIds, string $attendanceDate): array
    {
        if ($staffIds === []) {
            return [];
        }

        $rows = $this->db->table('staff_attendance sa')
            ->select('sa.id, sa.staff_id, sa.attendance_date, sa.status, sa.check_in_time, sa.check_out_time, sa.notes, sa.leave_type, sa.created_at, sa.updated_at, sa.created_by, s.name AS staff_name, s.staff_code, s.user_type, s.location_id, l.location_name')
            ->join('staff s', 's.id = sa.staff_id', 'left')
            ->join('location l', 'l.location_id = s.location_id', 'left')
            ->whereIn('sa.staff_id', $staffIds)
            ->where('sa.attendance_date', $attendanceDate)
            ->orderBy('sa.id', 'DESC')
            ->get()
            ->getResult();

        $records = [];
        foreach ($rows as $row) {
            $records[] = $this->formatAttendanceRow($row);
        }

        return $records;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatAttendanceRow(object $row): array
    {
        return [
            'attendance_id'   => (int) ($row->id ?? 0),
            'staff_id'        => (int) ($row->staff_id ?? 0),
            'staff_name'      => $row->staff_name ?? null,
            'staff_code'      => $row->staff_code ?? null,
            'user_type'       => $row->user_type ?? null,
            'location_id'     => isset($row->location_id) ? (int) $row->location_id : null,
            'location_name'   => $row->location_name ?? null,
            'attendance_date' => $row->attendance_date ?? null,
            'status'          => $row->status ?? null,
            'check_in_time'   => $row->check_in_time ?? null,
            'check_out_time'  => $row->check_out_time ?? null,
            'notes'           => $row->notes ?? null,
            'leave_type'      => $row->leave_type ?? null,
            'created_by'      => isset($row->created_by) ? (int) $row->created_by : null,
            'created_at'      => $row->created_at ?? null,
            'updated_at'      => $row->updated_at ?? null,
        ];
    }

    /**
     * @param list<array<string, mixed>> $records
     *
     * @return array<string, int>
     */
    private function buildAttendanceSummary(array $records): array
    {
        $summary = [
            'present'    => 0,
            'absent'     => 0,
            'leave'      => 0,
            'half_day'   => 0,
            'sick_leave' => 0,
            'holiday'    => 0,
            'other'      => 0,
        ];

        foreach ($records as $record) {
            $status = (string) ($record['status'] ?? '');
            switch ($status) {
                case 'Present':
                    $summary['present']++;
                    break;
                case 'Absent':
                    $summary['absent']++;
                    break;
                case 'Leave':
                    $summary['leave']++;
                    break;
                case 'Half-day':
                    $summary['half_day']++;
                    break;
                case 'Sick-leave':
                    $summary['sick_leave']++;
                    break;
                case 'Holiday':
                    $summary['holiday']++;
                    break;
                default:
                    $summary['other']++;
                    break;
            }
        }

        return $summary;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function apiError(string $responseCode, string $message, int $httpStatus = 400, array $data = [])
    {
        $payload = [
            'status'   => $httpStatus,
            'error'    => true,
            'messages' => [
                'responsecode' => $responseCode,
                'message'      => $message,
            ],
        ];

        if ($data !== []) {
            $payload['messages']['data'] = $data;
        }

        return $this->respond($payload, $httpStatus);
    }
}
