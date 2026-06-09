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

        $month = $this->request->getGet('month') ?? date('m');
        $year = $this->request->getGet('year') ?? date('Y');
        $from_date = "$year-$month-01";
        $to_date = date('Y-m-t', strtotime($from_date));

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

        // Get all staff for the grid rows (filtered if needed)
        $staffQuery = $this->db->table('staff')
            ->select('staff.*, location.location_name')
            ->join('location', 'location.location_id = staff.location_id', 'left')
            ->where('staff.user_type', 'STAFF');
        
        // Filter active staff (not resigned)
        $staffQuery->groupStart()
            ->where('staff.resign_date IS NULL')
            ->orWhere('staff.resign_date', '0000-00-00')
            ->orWhere('staff.resign_date >=', $from_date)
            ->groupEnd();
        
        if ($location_id) {
            $staffQuery->where('staff.location_id', $location_id);
        }
        if ($staff_id) {
            $staffQuery->where('staff.id', $staff_id);
        }

        $totalStaff = $staffQuery->countAllResults(false);
        $perPage = 20;
        $staffList = $staffQuery->limit($perPage, ($page - 1) * $perPage)->get()->getResult();

        // Get attendance for these staff members in the date range
        $staffIds = array_column($staffList, 'id');
        $attendance = [];
        if (!empty($staffIds)) {
            $attendanceRecords = $this->db->table('staff_attendance')
                ->whereIn('staff_id', $staffIds)
                ->where('attendance_date >=', $from_date)
                ->where('attendance_date <=', $to_date)
                ->get()->getResult();

            foreach ($attendanceRecords as $record) {
                $attendance[$record->staff_id][$record->attendance_date] = $record;
            }
        }

        // Prepare dates array
        $dates = [];
        $current = new \DateTime($from_date);
        $end = new \DateTime($to_date);
        $end->modify('+1 day');
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($current, $interval, $end);

        foreach ($period as $date) {
            $dates[] = [
                'full' => $date->format('Y-m-d'),
                'day' => $date->format('d'),
                'short_day' => $date->format('D')
            ];
        }

        $data = $this->getCommonData();
        $data['staffList'] = $staffList;
        $data['attendanceMatrix'] = $attendance;
        $data['dates'] = $dates;
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;
        $data['month'] = $month;
        $data['year'] = $year;
        $data['filters'] = $filters;
        $data['locations'] = $this->attendanceModel->getAllLocations();
        $data['staff'] = $this->attendanceModel->getAllStaff();
        $data['pagination'] = [
            'page' => $page,
            'totalPages' => ceil($totalStaff / $perPage),
            'total' => $totalStaff
        ];

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
            'status' => 'required|in_list[Present,Absent,Leave,Half-day,Sick-leave,Holiday]',
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

        // Date validation for join/resign
        $staff_id = $this->request->getPost('staff_id');
        $attendance_date = $this->request->getPost('attendance_date');
        $staff = $this->db->table('staff')->where('id', $staff_id)->get()->getRow();

        if ($staff) {
            if ($staff->doj != '0000-00-00' && !empty($staff->doj) && $attendance_date < $staff->doj) {
                return redirect()->back()->withInput()->with('error', 'Staff joined on ' . date('d-m-Y', strtotime($staff->doj)) . '. Attendance cannot be recorded before this date.');
            }
            if ($staff->resign_date != '0000-00-00' && !empty($staff->resign_date) && $attendance_date > $staff->resign_date) {
                return redirect()->back()->withInput()->with('error', 'Staff resigned on ' . date('d-m-Y', strtotime($staff->resign_date)) . '. Attendance cannot be recorded after this date.');
            }
        }

        $data = [
            'staff_id' => $staff_id,
            'attendance_date' => $attendance_date,
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
            'status' => 'required|in_list[Present,Absent,Leave,Half-day,Sick-leave,Holiday]',
            'check_in_time' => 'permit_empty|valid_date[H:i]',
            'check_out_time' => 'permit_empty|valid_date[H:i]',
            'notes' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        // Date validation for join/resign
        $staff_id = $this->request->getPost('staff_id');
        $attendance_date = $this->request->getPost('attendance_date');
        $staff = $this->db->table('staff')->where('id', $staff_id)->get()->getRow();

        if ($staff) {
            if ($staff->doj != '0000-00-00' && !empty($staff->doj) && $attendance_date < $staff->doj) {
                return redirect()->back()->withInput()->with('error', 'Staff joined on ' . date('d-m-Y', strtotime($staff->doj)) . '. Attendance cannot be recorded before this date.');
            }
            if ($staff->resign_date != '0000-00-00' && !empty($staff->resign_date) && $attendance_date > $staff->resign_date) {
                return redirect()->back()->withInput()->with('error', 'Staff resigned on ' . date('d-m-Y', strtotime($staff->resign_date)) . '. Attendance cannot be recorded after this date.');
            }
        }

        $data = [
            'staff_id' => $staff_id,
            'attendance_date' => $attendance_date,
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

                // Date validation for join/resign
                $staffInfo = $this->db->table('staff')->where('id', $staffId)->get()->getRow();
                if ($staffInfo) {
                    if ($staffInfo->doj != '0000-00-00' && !empty($staffInfo->doj) && $date < $staffInfo->doj) {
                        $errors[] = "Row " . ($key + 1) . ": Attendance date (" . $date . ") is before join date (" . $staffInfo->doj . ").";
                        continue;
                    }
                    if ($staffInfo->resign_date != '0000-00-00' && !empty($staffInfo->resign_date) && $date > $staffInfo->resign_date) {
                        $errors[] = "Row " . ($key + 1) . ": Attendance date (" . $date . ") is after resignation date (" . $staffInfo->resign_date . ").";
                        continue;
                    }
                }

                $status = $row[2];
                $checkIn = $row[3] ?? null;
                $checkOut = $row[4] ?? null;

                // Validate status
                $validStatuses = ['Present', 'Absent', 'Leave', 'Half-day', 'Sick-leave', 'Holiday'];
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

        $month = $this->request->getGet('month') ?? date('m');
        $year = $this->request->getGet('year') ?? date('Y');
        $from_date = "$year-$month-01";
        $to_date = date('Y-m-t', strtotime($from_date));
        $staff_id = $this->request->getGet('staff_id');
        $location_id = $this->request->getGet('location_id');

        // 1. Get filtered staff
        $staffQuery = $this->db->table('staff')
            ->select('staff.*, location.location_name')
            ->join('location', 'location.location_id = staff.location_id', 'left')
            ->where('staff.user_type', 'STAFF');
        
        if ($location_id) $staffQuery->where('staff.location_id', $location_id);
        if ($staff_id) $staffQuery->where('staff.id', $staff_id);

        $staffList = $staffQuery->get()->getResult();

        // 2. Get attendance records for matrix
        $attendance = [];
        if (!empty($staffList)) {
            $staffIds = array_column($staffList, 'id');
            $records = $this->db->table('staff_attendance')
                ->whereIn('staff_id', $staffIds)
                ->where('attendance_date >=', $from_date)
                ->where('attendance_date <=', $to_date)
                ->get()->getResult();

            foreach ($records as $r) {
                $attendance[$r->staff_id][$r->attendance_date] = $r->status;
            }
        }

        // 3. Prepare dates array
        $dates = [];
        $current = new \DateTime($from_date);
        $end = new \DateTime($to_date);
        $end->modify('+1 day');
        $period = new \DatePeriod($current, new \DateInterval('P1D'), $end);
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        // 4. Create Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Attendance Matrix');

        // Headers
        $sheet->setCellValue('A1', 'Employee Name');
        $sheet->setCellValue('B1', 'Location');
        $col = 3;
        foreach ($dates as $date) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue($colLetter . '1', date('d M', strtotime($date))); // Show date and month (e.g., 01 Jan)
            $col++;
        }

        // Style header
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col - 1);
        $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastCol . '1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:' . $lastCol . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE9ECEF');

        // Data Rows
        $rowIdx = 2;
        foreach ($staffList as $staff) {
            $sheet->setCellValue('A' . $rowIdx, $staff->name);
            $sheet->setCellValue('B' . $rowIdx, $staff->location_name ?? '-');

            $colIdx = 3;
            foreach ($dates as $date) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                
                // Tenure Check
                $isJoined = (empty($staff->doj) || $staff->doj == '0000-00-00' || $date >= $staff->doj);
                $isResigned = (!empty($staff->resign_date) && $staff->resign_date != '0000-00-00' && $date > $staff->resign_date);
                
                if ($isJoined && !$isResigned) {
                    $status = $attendance[$staff->id][$date] ?? null;
                    
                    if ($status == 'Present') {
                        $sheet->setCellValue($colLetter . $rowIdx, '✓');
                        $sheet->getStyle($colLetter . $rowIdx)->getFont()->getColor()->setARGB('FF28A745');
                    } elseif ($status == 'Absent') {
                        $sheet->setCellValue($colLetter . $rowIdx, '✘');
                        $sheet->getStyle($colLetter . $rowIdx)->getFont()->getColor()->setARGB('FFDC3545');
                    } elseif ($status == 'Leave' || $status == 'Sick-leave') {
                        $sheet->setCellValue($colLetter . $rowIdx, '✈');
                        $sheet->getStyle($colLetter . $rowIdx)->getFont()->getColor()->setARGB('FFFFC107');
                    } elseif ($status == 'Holiday') {
                        $sheet->setCellValue($colLetter . $rowIdx, '★');
                        $sheet->getStyle($colLetter . $rowIdx)->getFont()->getColor()->setARGB('FFFFC107');
                    } else {
                        $sheet->setCellValue($colLetter . $rowIdx, '-');
                        $sheet->getStyle($colLetter . $rowIdx)->getFont()->getColor()->setARGB('FFADB5BD');
                    }
                    
                    $sheet->getStyle($colLetter . $rowIdx)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                }
                $colIdx++;
            }
            $rowIdx++;
        }

        // Auto-size columns
        foreach (range('A', $lastCol) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Attendance_Matrix_' . $from_date . '_to_' . $to_date . '.xlsx';

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

        $month = $this->request->getGet('month') ?? date('m');
        $year = $this->request->getGet('year') ?? date('Y');
        $from_date = "$year-$month-01";
        $to_date = date('Y-m-t', strtotime($from_date));
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
        $data['month'] = $month;
        $data['year'] = $year;
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
            ->groupStart()
                ->where('resign_date IS NULL')
                ->orWhere('resign_date', '0000-00-00')
                ->orWhere('resign_date >=', date('Y-m-d'))
            ->groupEnd()
            ->like('name', $search)
            ->orderBy('name', 'ASC')
            ->limit(10)
            ->get()->getResult();

        return $this->response->setJSON($staff);
    }

    /**
     * Get staff list filtered by location for bulk attendance modal.
     * Also includes existing attendance status for the given date so UI can disable already-marked rows.
     */
    public function getStaffByLocation()
    {
        if (!$this->checkAuth()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $location_id = $this->request->getGet('location_id');
        $attendance_date = $this->request->getGet('attendance_date') ?: date('Y-m-d');

        $builder = $this->db->table('staff')
            ->select('staff.id, staff.name, staff.staff_code, staff.location_id, staff.doj, staff.resign_date, location.location_name')
            ->join('location', 'location.location_id = staff.location_id', 'left')
            ->where('staff.user_type', 'STAFF');

        // Only show staff who were active on the selected date
        $builder->groupStart()
            ->where('staff.doj IS NULL')
            ->orWhere('staff.doj', '0000-00-00')
            ->orWhere('staff.doj <=', $attendance_date)
        ->groupEnd();

        $builder->groupStart()
            ->where('staff.resign_date IS NULL')
            ->orWhere('staff.resign_date', '0000-00-00')
            ->orWhere('staff.resign_date >=', $attendance_date)
        ->groupEnd();

        if (!empty($location_id)) {
            $builder->where('staff.location_id', $location_id);
        }

        $staffList = $builder->orderBy('staff.name', 'ASC')->get()->getResult();

        // Get existing attendance for these staff on the selected date
        $existing = [];
        if (!empty($staffList)) {
            $staffIds = array_column($staffList, 'id');
            $records = $this->db->table('staff_attendance')
                ->select('staff_id, status')
                ->whereIn('staff_id', $staffIds)
                ->where('attendance_date', $attendance_date)
                ->get()->getResult();
            foreach ($records as $r) {
                $existing[$r->staff_id] = $r->status;
            }
        }

        $result = [];
        foreach ($staffList as $s) {
            $result[] = [
                'id' => $s->id,
                'name' => $s->name,
                'staff_code' => $s->staff_code,
                'location_id' => $s->location_id,
                'location_name' => $s->location_name,
                'already_marked' => isset($existing[$s->id]),
                'existing_status' => $existing[$s->id] ?? null,
            ];
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $result]);
    }

    /**
     * Bulk store attendance for multiple staff on a single date with common status.
     */
    public function quickBulkStore()
    {
        if (!$this->checkAuth()) {
            return $this->checkAuth();
        }

        $staff_ids = $this->request->getPost('staff_ids');
        $attendance_date = $this->request->getPost('attendance_date');
        $status = $this->request->getPost('status');
        $check_in_time = $this->request->getPost('check_in_time') ?: null;
        $check_out_time = $this->request->getPost('check_out_time') ?: null;
        $leave_type = $this->request->getPost('leave_type') ?: null;
        $notes = $this->request->getPost('notes');

        // Basic validation
        if (empty($staff_ids) || !is_array($staff_ids)) {
            return redirect()->back()->with('error', 'Please select at least one staff member.');
        }
        if (empty($attendance_date)) {
            return redirect()->back()->with('error', 'Please select an attendance date.');
        }
        $validStatuses = ['Present', 'Absent', 'Leave', 'Half-day', 'Sick-leave', 'Holiday'];
        if (!in_array($status, $validStatuses)) {
            return redirect()->back()->with('error', 'Invalid attendance status.');
        }

        $userId = $this->session->get('user_id');
        $now = date('Y-m-d H:i:s');

        $records = [];
        $skipped = [];

        foreach ($staff_ids as $staff_id) {
            $staff_id = (int) $staff_id;
            if ($staff_id <= 0) continue;

            $staff = $this->db->table('staff')->where('id', $staff_id)->get()->getRow();
            if (!$staff) {
                $skipped[] = "Staff ID $staff_id not found";
                continue;
            }

            // Tenure validation
            if (!empty($staff->doj) && $staff->doj != '0000-00-00' && $attendance_date < $staff->doj) {
                $skipped[] = $staff->name . ' (joined ' . date('d-m-Y', strtotime($staff->doj)) . ')';
                continue;
            }
            if (!empty($staff->resign_date) && $staff->resign_date != '0000-00-00' && $attendance_date > $staff->resign_date) {
                $skipped[] = $staff->name . ' (resigned ' . date('d-m-Y', strtotime($staff->resign_date)) . ')';
                continue;
            }

            // Duplicate check
            $duplicate = $this->attendanceModel->getDuplicateCheck($staff_id, $attendance_date);
            if ($duplicate) {
                $skipped[] = $staff->name . ' (already marked)';
                continue;
            }

            $records[] = [
                'staff_id' => $staff_id,
                'attendance_date' => $attendance_date,
                'status' => $status,
                'check_in_time' => $check_in_time,
                'check_out_time' => $check_out_time,
                'notes' => $notes,
                'leave_type' => $leave_type,
                'created_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($records)) {
            $msg = 'No new attendance records to add.';
            if (!empty($skipped)) {
                $msg .= ' Skipped: ' . implode(', ', $skipped);
            }
            return redirect()->back()->with('error', $msg);
        }

        if ($this->attendanceModel->bulkAddAttendance($records)) {
            $message = count($records) . ' attendance record(s) added successfully.';
            if (!empty($skipped)) {
                $message .= ' Skipped ' . count($skipped) . ': ' . implode(', ', $skipped);
            }
            return redirect()->to('admin/attendance')->with('msg', $message);
        }

        return redirect()->back()->with('error', 'Failed to add attendance records.');
    }
}
