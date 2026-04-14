<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AdminModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;  
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;  
use DateTimeImmutable;  
use DateTime;
use mysqli;

class AditionalAdminPart extends BaseController
{
    public $db, $AdminModel, $session, $uri;
    
    public function __construct()
    {
        $db = db_connect();
        $this->db = db_connect();
		date_default_timezone_set('Asia/Kolkata');
        $this->AdminModel = new AdminModel($db);
        $this->session = session();
        helper(['form', 'url', 'validation']);
    }

    public function index()
    {
        return view('admin/login');
    }

    function staffData()
	{
        $builder = $this->db->table('staff');
		$builder->select('staff.*,location.location_name');
		$builder->join('location', 'location.location_id = staff.location_id','left');
		return $builder->get()->getResult();
	}

    public function download_excel()
    {
        $staffadvance = $this->staffData();
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Set header row (removing 'File' column)
        $headers = [
            'Sl no',
            'User Type',
            'Name',
            'Staf Code',
            'Name In Bank',
            'A/c No',
            'IFSC Code',
            'Dl Number',
            'DL Expiry',
            'Salary',
            'Contact No',
            'Dob',
            'Location',
            'Address'
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Populate data
        $row = 2;
        foreach ($staffadvance as $index => $record) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $record->user_type);
            $sheet->setCellValue('C' . $row, $record->name);
            $sheet->setCellValue('D' . $row, $record->staff_code);
            $sheet->setCellValue('E' . $row, $record->name_bank);
            $sheet->setCellValue('F' . $row, $record->ac_no);
            $sheet->setCellValue('G' . $row, $record->ifsc);
            $sheet->setCellValue('H' . $row, $record->dl_number);
            $sheet->setCellValue('I' . $row, date('d-m-Y', strtotime($record->dl_expiry)));
            $sheet->setCellValue('J' . $row, $record->salary);
            $sheet->setCellValue('K' . $row, $record->tel);
            $sheet->setCellValue('L' . $row, date('d-m-Y', strtotime($record->doj)));
            $sheet->setCellValue('M' . $row, $record->location_name);
            $sheet->setCellValue('N' . $row, $record->address);
            $row++;
        }

        // Write to a file and force download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'staffDriver_data_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function Get_vendor()
    {
        // Main query builder
        $builder = $this->db->table('vendor');
        $builder->select('vendor.*, location.location_name, vendor_rate.vendor_rate, vendor_rate.from_date');
        $builder->join('location', 'location.location_id = vendor.location', 'left');
        // Subquery to get the latest vendor_rate for each vendor
        $subquery = $this->db->table('vendor_rate as vr1')
            ->select('vr1.vendor_id, vr1.vendor_rate, vr1.from_date')
            ->join('(SELECT vendor_id, MAX(from_date) as max_date FROM vendor_rate GROUP BY vendor_id) as vr2', 
                'vr1.vendor_id = vr2.vendor_id AND vr1.from_date = vr2.max_date', 'inner', false);
        // Convert subquery to string
        $subquerySql = $subquery->getCompiledSelect(false); // Use false to prevent adding parentheses
        // Join subquery with the main query
        $builder->join("($subquerySql) as vendor_rate", 'vendor_rate.vendor_id = vendor.id', 'left');
        // Group by vendor.id to ensure each vendor appears only once
        $builder->groupBy('vendor.id');
        return $builder->get()->getResult();
    }

    public function download_excel_vendor()
    {
        $staffadvance = $this->Get_vendor();
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Set header row (removing 'File' column)
        $headers = [
            'Sl no',
            'Name',
            'Vendor Code',
            'GST',
            'Vendor Type',
            'Location',
            'Pan',
            'Balance',
            'From Date',
            'Rate'
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Populate data
        $row = 2;
        foreach ($staffadvance as $index => $record) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $record->name);
            $sheet->setCellValue('C' . $row, $record->vendor_code);
            $sheet->setCellValue('D' . $row, $record->gst);
            $sheet->setCellValue('E' . $row, $record->type);
            $sheet->setCellValue('F' . $row, $record->location_name);
            $sheet->setCellValue('G' . $row, $record->pan);
            $sheet->setCellValue('H' . $row, $record->bal);
            $sheet->setCellValue('I' . $row, date('d-m-Y', strtotime($record->from_date)));
            $sheet->setCellValue('J' . $row, $record->vendor_rate);
            $row++;
        }

        // Write to a file and force download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'vendor_data_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function itemdtls()
    {
        $builder = $this->db->table('items');
		$builder->select('items.*, units.unit_id, units.unit_name');
		$builder->join('units', 'items.unit_id = units.unit_id', 'left');
		return $builder->get()->getResult();
    }

    public function download_excel_item()
    {
        $staffadvance = $this->itemdtls();
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Set header row (removing 'File' column)
        $headers = [
            'Sl no',
            'Item Code',
            'Item Name',
            'Unit',
            'Avg Price Rate',
            'Amount'
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Populate data
        $row = 2;
        foreach ($staffadvance as $index => $record) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $record->item_id);
            $sheet->setCellValue('C' . $row, $record->item_name);
            $sheet->setCellValue('D' . $row, $record->unit_name);
            $sheet->setCellValue('E' . $row, $record->avg_price_rate);
            $sheet->setCellValue('F' . $row, $record->amount);
            $row++;
        }

        // Write to a file and force download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'vendor_data_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }


    public function download_excel_unit()
    {
        $recordData = $this->db->query("SELECT * FROM units")->getResult();
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Set header row (removing 'File' column)
        $headers = [
            'Sl no',
            'Unit Name',
            'Short Name',
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Populate data
        $row = 2;
        foreach ($recordData as $index => $record) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $record->unit_name);
            $sheet->setCellValue('C' . $row, $record->unit_short_name);
            $row++;
        }

        // Write to a file and force download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'unit_data_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function download_excel_location()
    {
        $recordData = $this->db->query("SELECT * FROM location WHERE (status IS NULL OR status='Active')")->getResult();
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Set header row (removing 'File' column)
        $headers = [
            'Sl no',
            'Location Name',
            'Location Short Name',
            'Opening Balance',
            'Radius',
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Populate data
        $row = 2;
        foreach ($recordData as $index => $record) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $record->location_name);
            $sheet->setCellValue('C' . $row, $record->location_shordname);
            $sheet->setCellValue('D' . $row, $record->opening_balance);
            $sheet->setCellValue('E' . $row, $record->radius);
            $row++;
        }

        // Write to a file and force download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Location_data_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function download_excel_route()
    {
        $recordData = $this->db->query("
                            SELECT route.*, location.location_name 
                            FROM route 
                            JOIN location ON location.location_id = route.location_id
                        ")->getResult();
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Set header row (removing 'File' column)
        $headers = [
            'Sl no',
            'Location Name',
            'Short Name',
            'Form',
            'To',
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Populate data
        $row = 2;
        foreach ($recordData as $index => $record) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $record->location_name);
            $sheet->setCellValue('C' . $row, $record->location_shortname);
            $sheet->setCellValue('D' . $row, $record->from_city);
            $sheet->setCellValue('E' . $row, $record->to_city);
            $row++;
        }

        // Write to a file and force download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Route_data_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    function bank(){
	    $builder = $this->db->table('bank');
		$builder->select('*');
		return $builder->get()->getResult();
	}

    public function download_excel_bank()
    {
        $recordData = $this->bank();
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Set header row (removing 'File' column)
        $headers = [
            'Sl no',
            'Bank Name',
            'IFSC Code',
            'A/c No.',
            'Short Name',
            'Opening Balance',
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Populate data
        $row = 2;
        foreach ($recordData as $index => $record) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $record->bank_name);
            $sheet->setCellValue('C' . $row, $record->ifsc_code);
            $sheet->setCellValue('D' . $row, $record->ac_no);
            $sheet->setCellValue('E' . $row, $record->short_name);
            $sheet->setCellValue('F' . $row, $record->opening_balance);
            $row++;
        }

        // Write to a file and force download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Route_data_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function downloadDatabase()
    {
        $DB_HOST = "localhost";
        $DB_USER = "u929406983_yasujalogistic";
        $DB_PASS = "&Qw73Q/SI";
        $DB_NAME = "u929406983_yasujalogistic";
    
        $con = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    
        if ($con->connect_error) {
            die("Connection failed: " . $con->connect_error);
        }
    
        $tables = [];
        $result = $con->query("SHOW TABLES");
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
    
        $handle = fopen('backup.sql', 'w+');
    
        foreach ($tables as $table) {
            $createTableQuery = $con->query("SHOW CREATE TABLE " . $table)->fetch_row();
            fwrite($handle, 'DROP TABLE IF EXISTS `' . $table . "`;\n" . $createTableQuery[1] . ";\n\n");
    
            $result = $con->query("SELECT * FROM " . $table);
            while ($row = $result->fetch_assoc()) {
                $values = array_map(function ($value) use ($con) {
                    return is_null($value) ? 'NULL' : "'" . $con->real_escape_string($value) . "'";
                }, array_values($row));
                fwrite($handle, 'INSERT INTO `' . $table . '` VALUES(' . implode(',', $values) . ");\n");
            }
            fwrite($handle, "\n\n");
        }
    
        fclose($handle);
    
        $user_id = $this->session->get('user_id');
        $data['setting'] = $this->AdminModel->Settingdata();
        $data['singleuser'] = $this->AdminModel->userdata($user_id);
        return view('admin/database_vw', $data);
    }



    
}