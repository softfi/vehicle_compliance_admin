<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AttendanceModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

class Attendance extends BaseController
{
    protected $attendanceModel;
    protected $adminModel;
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
        $this->attendanceModel = new AttendanceModel();
        helper(['form', 'url', 'validation']);
        $this->session = session();
        date_default_timezone_set('Asia/Kolkata');
    }

    /**
     * Check authorization
     */
    private function checkAuth()
    {
        if (!$this->session->get('user_id')) {
            return redirect()->to('/admin');
        }

        if ($this->session->get('user_type') != 1 && $this->session->get('user_type') != 2) {
            return redirect()->to('/admin');
        }

        return true;
    }

    /**
     * Get common data for views
     */
    private function getCommonData()
    {
        $adminModel = new \App\Models\AdminModel();
        $user_id = $this->session->get('user_id');
        $data['setting'] = $adminModel->Settingdata();
        $data['singleuser'] = $adminModel->userdata($user_id);
        return $data;
    }

    /**
     * List all attendance records
     */
    public function index()
    {
        if (!$this->checkAuth()) {
            return $this->checkAuth();
        }

        $from_date = $this->request->getGet('from_date') ?? date('Y-m-01');
        $to_date = $this->request->getGet('to_date') ?? date('Y-m-d');
        $staff_id = $this->request->getGet('staff_id');
        $status = $this->request->getGet('status');
        $location_id = $this->request->getGet('location_id');
        $page = $this->request->getGet('page') ?? 1;

        $filters = [];
        if ($staff_id)
            $filters['staff_id'] = $staff_id;
        if ($status)
            $filters['status'] = $status;
        if ($location_id)
            $filters['location_id'] = $location_id;

        $result = $this->attendanceModel->searchAttendance($from_date, $to_date, $filters, $page);

        $data = $this->getCommonData();
        $data['attendance'] = $result['data'];
        $data['pagination'] = $result;
        $data['staff'] = $this->attendanceModel->getAllStaff();
        $data['locations'] = $this->attendanceModel->getAllLocations();
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;
        $data['filters'] = $filters;

        return view('admin/attendance_vw', $data);
    }

    /**
     * Show add attendance form
     */
    public function addAttendance()
    {
        if (!$this->checkAuth()) {
            return $this->checkAuth();
        }

        $data = $this->getCommonData();
        $data['staff'] = $this->attendanceModel->getAllStaff();
        $data['today'] = date('Y-m-d');

        return view('admin/add_attendance_vw', $data);
    }

    /**
     * Store single attendance record
     */
    public function store()
    {
        if (!$this->checkAuth()) {
            return $this->checkAuth();
        }

        $rules = [
            'staff_id' => 'required|numeric|is_not_unique[staff.id]',
            'attendance_date' => 'required|valid_date[Y-m-d]',
            'status' => 'required|in_list[Present,Absent,Leave,Half-day,Sick-leave]',
            'check_in_time' => 'permit_empty|valid_date[H:i]',
            'check_out_time' => 'permit_empty|valid_date[H:i]',
            'notes' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Check for duplicate
        $duplicate = $this->attendanceModel->getDuplicateCheck(
            $this->request->getPost('staff_id'),
            $this->request->getPost('attendance_date')
        );

        if ($duplicate) {
            return redirect()->back()->with('error', 'Attendance record for this staff on this date already exists.');
        }

        $data = [
            'staff_id' => $this->request->getPost('staff_id'),
            'attendance_date' => $this->request->getPost('attendance_date'),
            'status' => $this->request->getPost('status'),
            'check_in_time' => $this->request->getPost('check_in_time') ?: null,
            'check_out_time' => $this->request->getPost('check_out_time') ?: null,
            'notes' => $this->request->getPost('notes'),
            'leave_type' => $this->request->getPost('leave_type') ?: null,
            'created_by' => $this->session->get('user_id')
        ];

        if ($this->attendanceModel->addAttendance($data)) {
            return redirect()->to('admin/attendance')->with('msg', 'Attendance record added successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to add attendance record.');
        }
    }

    /**
     * Show edit attendance form
     */
    public function edit($id)
    {
        if (!$this->checkAuth()) {
            return $this->checkAuth();
        }

        $record = $this->attendanceModel->find($id);
        if (!$record) {
            return redirect()->to('admin/attendance')->with('error', 'Record not found.');
        }

        $data = $this->getCommonData();
        $data['attendance'] = $record;
        $data['staff'] = $this->attendanceModel->getAllStaff();

        return view('admin/edit_attendance_vw', $data);
    }

    /**
     * Get attendance data for AJAX
     */
    public function getAttendanceData($id)
    {
        if (!$this->checkAuth()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $record = $this->attendanceModel->find($id);
        if (!$record) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Record not found']);
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $record]);
    }

    /**
     * Update attendance record
     */
    public function update($id)
    {
        if (!$this->checkAuth()) {
            return $this->checkAuth();
        }

        $record = $this->attendanceModel->find($id);
        if (!$record) {
            return redirect()->to('admin/attendance')->with('error', 'Record not found.');
        }

        $rules = [
            'staff_id' => 'required|numeric|is_not_unique[staff.id]',
            'attendance_date' => 'required|valid_date[Y-m-d]',
            'status' => 'required|in_list[Present,Absent,Leave,Half-day,Sick-leave]',
            'check_in_time' => 'permit_empty|valid_date[H:i]',
            'check_out_time' => 'permit_empty|valid_date[H:i]',
            'notes' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $data = [
            'staff_id' => $this->request->getPost('staff_id'),
            'attendance_date' => $this->request->getPost('attendance_date'),
            'status' => $this->request->getPost('status'),
            'check_in_time' => $this->request->getPost('check_in_time') ?: null,
            'check_out_time' => $this->request->getPost('check_out_time') ?: null,
            'notes' => $this->request->getPost('notes'),
            'leave_type' => $this->request->getPost('leave_type') ?: null,
            'updated_by' => $this->session->get('user_id')
        ];

        if ($this->attendanceModel->updateAttendance($id, $data)) {
            return redirect()->to('admin/attendance')->with('msg', 'Attendance record updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to update attendance record.');
        }
    }

    /**
     * Delete attendance record
     */
    public function delete($id)
    {
        if (!$this->checkAuth()) {
            return $this->checkAuth();
        }

        $record = $this->attendanceModel->find($id);
        if (!$record) {
            return redirect()->to('admin/attendance')->with('error', 'Record not found.');
        }

        if ($this->attendanceModel->deleteAttendance($id)) {
            return redirect()->to('admin/attendance')->with('msg', 'Attendance record deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to delete attendance record.');
        }
    }

    /**
     * Show bulk attendance entry form
     */
    public function bulkAdd()
    {
        if (!$this->checkAuth()) {
            return $this->checkAuth();
        }

        $data = $this->getCommonData();
        $data['staff'] = $this->attendanceModel->getAllStaff();
        $data['today'] = date('Y-m-d');

        return view('admin/bulk_attendance_vw', $data);
    }

    /**
     * Process bulk attendance entry
     */
    public function bulkStore()
    {
        if (!$this->checkAuth()) {
            return $this->checkAuth();
        }

        if (!$this->request->getFile('excel_file')) {
            return redirect()->back()->with('error', 'Please select an Excel file.');
        }

        $file = $this->request->getFile('excel_file');
        if (!$file->isValid()) {
            return redirect()->back()->with('error', 'Invalid file upload.');
        }

        try {
            $reader = new XlsxReader();
            $spreadsheet = $reader->load($file->getTempName());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (count($rows) < 2) {
                return redirect()->back()->with('error', 'Excel file has no data rows.');
            }

            $records = [];
            $errors = [];
            $userId = $this->session->get('user_id');

            // Get all valid staff for mapping staff_code to id
            $validStaff = $this->attendanceModel->getAllStaff();
            $staffMap = [];
            foreach ($validStaff as $s) {
                $staffMap[$s->staff_code] = $s->id;
            }

            foreach ($rows as $key => $row) {
                if ($key == 0)
                    continue; // Skip header row

                // Validate and prepare data
                if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                    $errors[] = "Row " . ($key + 1) . ": Missing required fields.";
                    continue;
                }

                $staffCode = trim((string) $row[0]);

                // Validate staff code exists and get ID
                if (!isset($staffMap[$staffCode])) {
                    $errors[] = "Row " . ($key + 1) . ": Staff Code '$staffCode' does not exist or is not a STAFF member.";
                    continue;
                }

                $staffId = $staffMap[$staffCode];

                $date = $row[1];
                $formattedDate = null;

                // 1. Handle Excel serial date (numeric)
                if (is_numeric($date)) {
                    try {
                        $formattedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('Y-m-d');
                    } catch (\Exception $e) {
                        // fallback to next check
                    }
                }

                // 2. If not already formatted, try parsing as string
                if (!$formattedDate && !empty($date)) {
                    $dateStr = trim((string) $date);

                    // Try YYYY-MM-DD
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
                        $formattedDate = $dateStr;
                    }
                    // Try DD/MM/YYYY or DD-MM-YYYY
                    else {
                        $formats = ['d/m/Y', 'd-m-Y', 'Y/m/d', 'm/d/Y'];
                        foreach ($formats as $f) {
                            $d = \DateTime::createFromFormat($f, $dateStr);
                            if ($d && $d->format($f) === $dateStr) {
                                $formattedDate = $d->format('Y-m-d');
                                break;
                            }
                        }
                    }
                }

                if (!$formattedDate) {
                    $errors[] = "Row " . ($key + 1) . ": Invalid date format '$date'. Expected YYYY-MM-DD or DD/MM/YYYY.";
                    continue;
                }

                $date = $formattedDate;

                $status = $row[2];
                $checkIn = $row[3] ?? null;
                $checkOut = $row[4] ?? null;

                // Validate status
                $validStatuses = ['Present', 'Absent', 'Leave', 'Half-day', 'Sick-leave'];
                if (!in_array($status, $validStatuses)) {
                    $errors[] = "Row " . ($key + 1) . ": Invalid status '$status'.";
                    continue;
                }

                // Check for duplicate
                $duplicate = $this->attendanceModel->getDuplicateCheck($staffId, $date);
                if ($duplicate) {
                    $errors[] = "Row " . ($key + 1) . ": Duplicate entry for staff on this date.";
                    continue;
                }

                $records[] = [
                    'staff_id' => $staffId,
                    'attendance_date' => $date,
                    'status' => $status,
                    'check_in_time' => $checkIn,
                    'check_out_time' => $checkOut,
                    'notes' => $row[5] ?? null,
                    'leave_type' => $row[6] ?? null,
                    'created_by' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }

            if (empty($records)) {
                return redirect()->back()->with('error', 'No valid records to import. Errors: ' . implode(' | ', $errors));
            }

            if ($this->attendanceModel->bulkAddAttendance($records)) {
                $message = "Successfully imported " . count($records) . " records.";
                if (!empty($errors)) {
                    $message .= " Errors in " . count($errors) . " rows were skipped.";
                }
                return redirect()->to('admin/attendance')->with('msg', $message);
            } else {
                return redirect()->back()->with('error', 'Failed to import records.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error processing file: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel template
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add headers
        $sheet->setCellValue('A1', 'Staff Code');
        $sheet->setCellValue('B1', 'Attendance Date (YYYY-MM-DD)');
        $sheet->setCellValue('C1', 'Status');
        $sheet->setCellValue('D1', 'Check-in (HH:MM)');
        $sheet->setCellValue('E1', 'Check-out (HH:MM)');
        $sheet->setCellValue('F1', 'Notes');
        $sheet->setCellValue('G1', 'Leave Type');

        // Style header row
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A1:G1')->getFill()->getStartColor()->setARGB('FFFF00');

        // Add sample data
        $sheet->setCellValue('A2', 'ST001');
        $sheet->setCellValue('B2', date('Y-m-d'));
        $sheet->setCellValue('C2', 'Present');
        $sheet->setCellValue('D2', '09:00');
        $sheet->setCellValue('E2', '18:00');
        $sheet->setCellValue('F2', 'Sample note');
        $sheet->setCellValue('G2', '');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(15);

        $writer = new Xlsx($spreadsheet);
        $filename = 'attendance_template_' . date('Y-m-d_H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Export attendance to Excel
     */
    public function exportToExcel()
    {
        if (!$this->checkAuth()) {
            return $this->checkAuth();
        }

        $from_date = $this->request->getGet('from_date') ?? date('Y-m-01');
        $to_date = $this->request->getGet('to_date') ?? date('Y-m-d');
        $staff_id = $this->request->getGet('staff_id');
        $status = $this->request->getGet('status');
        $location_id = $this->request->getGet('location_id');

        $filters = [];
        if ($staff_id)
            $filters['staff_id'] = $staff_id;
        if ($status)
            $filters['status'] = $status;
        if ($location_id)
            $filters['location_id'] = $location_id;

        $attendance = $this->attendanceModel->getAttendanceReport($from_date, $to_date, $filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance');

        // Add headers
        $headers = ['ID', 'Staff Name', 'Staff Code', 'Date', 'Status', 'Check-in', 'Check-out', 'Location', 'Notes'];
        $col = 1;
        foreach ($headers as $header) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue($column . '1', $header);
            $col++;
        }

        // Style header
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A1:I1')->getFill()->getStartColor()->setARGB('FFCCCCCC');

        // Add data
        $row = 2;
        foreach ($attendance as $record) {
            $sheet->setCellValue('A' . $row, $record->id);
            $sheet->setCellValue('B' . $row, $record->staff_name);
            $sheet->setCellValue('C' . $row, $record->staff_code);
            $sheet->setCellValue('D' . $row, $record->attendance_date);
            $sheet->setCellValue('E' . $row, $record->status);
            $sheet->setCellValue('F' . $row, $record->check_in_time);
            $sheet->setCellValue('G' . $row, $record->check_out_time);
            $sheet->setCellValue('H' . $row, $record->location_name);
            $sheet->setCellValue('I' . $row, $record->notes);
            $row++;
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(20);

        $writer = new Xlsx($spreadsheet);
        $filename = 'attendance_' . $from_date . '_to_' . $to_date . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Show reports page
     */
    public function reports()
    {
        if (!$this->checkAuth()) {
            return $this->checkAuth();
        }

        $from_date = $this->request->getGet('from_date') ?? date('Y-m-01');
        $to_date = $this->request->getGet('to_date') ?? date('Y-m-d');
        $staff_id = $this->request->getGet('staff_id');
        $location_id = $this->request->getGet('location_id');

        $filters = [];
        if ($staff_id)
            $filters['staff_id'] = $staff_id;
        if ($location_id)
            $filters['location_id'] = $location_id;

        $attendance = $this->attendanceModel->getAttendanceReport($from_date, $to_date, $filters);
        $stats = $this->attendanceModel->getAttendanceStats($from_date, $to_date, $filters);

        $data = $this->getCommonData();
        $data['attendance'] = $attendance;
        $data['stats'] = $stats;
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;
        $data['staff'] = $this->attendanceModel->getAllStaff();
        $data['locations'] = $this->attendanceModel->getAllLocations();
        $data['filters'] = $filters;

        return view('admin/attendance_reports_vw', $data);
    }

    /**
     * Show calendar view
     */
    public function calendarView()
    {
        if (!$this->checkAuth()) {
            return $this->checkAuth();
        }

        $monthInput = $this->request->getGet('month');
        $year = $this->request->getGet('year') ?? date('Y');
        $month = date('m');

        if ($monthInput) {
            if (strpos($monthInput, '-') !== false) {
                // Handle YYYY-MM format from <input type="month">
                $parts = explode('-', $monthInput);
                $year = $parts[0];
                $month = $parts[1];
            } else {
                $month = $monthInput;
            }
        }
        $staff_id = $this->request->getGet('staff_id');
        $location_id = $this->request->getGet('location_id');

        $attendance = [];
        if ($staff_id) {
            $fromDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
            $toDate = date('Y-m-t', strtotime($fromDate));
            $attendance = $this->attendanceModel->getAttendanceByStaff($staff_id, $fromDate, $toDate);
        }

        $data = $this->getCommonData();
        $data['attendance'] = $attendance;
        $data['staff'] = $this->attendanceModel->getAllStaff();
        $data['locations'] = $this->attendanceModel->getAllLocations();
        $data['month'] = $month;
        $data['year'] = $year;
        $data['currentDate'] = date('Y-m-d');
        $data['staff_id'] = $staff_id;
        $data['location_id'] = $location_id;

        return view('admin/calendar_view', $data);
    }

    /**
     * Show analytics page
     */
    public function analytics()
    {
        if (!$this->checkAuth()) {
            return $this->checkAuth();
        }

        $staff_id = $this->request->getGet('staff_id');
        $location_id = $this->request->getGet('location_id');
        $year = $this->request->getGet('year') ?? date('Y');

        $trends = [];
        $stats = null;

        if ($staff_id) {
            $trends = $this->attendanceModel->getAttendanceTrends($staff_id, $year);

            $fromDate = $year . '-01-01';
            $toDate = $year . '-12-31';
            $present = $this->attendanceModel->getPresentCount($staff_id, $fromDate, $toDate);
            $absent = $this->attendanceModel->getAbsentCount($staff_id, $fromDate, $toDate);
            $leave = $this->attendanceModel->getLeaveCount($staff_id, $fromDate, $toDate);
            $percentage = $this->attendanceModel->getAttendancePercentage($staff_id, $fromDate, $toDate);

            $stats = (object) [
                'present' => $present,
                'absent' => $absent,
                'leave' => $leave,
                'percentage' => $percentage
            ];
        }

        $data = $this->getCommonData();
        $data['staff'] = $this->attendanceModel->getAllStaff();
        $data['locations'] = $this->attendanceModel->getAllLocations();
        $data['trends'] = $trends;
        $data['stats'] = $stats;
        $data['year'] = $year;
        $data['staff_id'] = $staff_id;
        $data['location_id'] = $location_id;

        return view('admin/attendance_analytics', $data);
    }

    /**
     * Get staff dropdown via AJAX
     */
    public function getStaffList()
    {
        $search = $this->request->getGet('q');
        $staff = $this->db->table('staff')
            ->select('id, name, staff_code')
            ->where('user_type', 'STAFF')
            ->where('status !=', 'Inactive')
            ->like('name', $search)
            ->orderBy('name', 'ASC')
            ->limit(10)
            ->get()->getResult();

        return $this->response->setJSON($staff);
    }
}
