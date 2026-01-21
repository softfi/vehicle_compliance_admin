<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AdminModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use DateTimeImmutable;
use DateTime;

class Ledger extends BaseController
{
    public $db, $AdminModel, $session;
    public function __construct()
    {
        $db = db_connect();
        $this->db = db_connect();

        $this->AdminModel = new AdminModel($db);
        $this->session = session();
        helper(['form', 'url', 'validation']);
    }
    
    public function exportExcel()
    {
        $from_date = $this->request->getGet('from_date');
        $to_date = $this->request->getGet('to_date');

        ob_start();
    
        $vehicles = $this->AdminModel->Getvehicle();
        $data = [];
        foreach ($vehicles as $vehicle) {
            $data[] = $this->Vehicle_Ledger($vehicle->id, $from_date, $to_date);
        }
    
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Set headers
        $sheet->setCellValue('A1', "Sl No ({$from_date} - {$to_date})");
        $sheet->setCellValue('B1', 'Vehicle No');
        $sheet->setCellValue('C1', 'Total In-House Maintenance');
        $sheet->setCellValue('D1', 'Total Statutory Costs');
        $sheet->setCellValue('E1', 'Total Gross Salary');
        $sheet->setCellValue('F1', 'Total Diesel Cost');
        $sheet->setCellValue('G1', 'Total Overall Expense');
        $sheet->setCellValue('H1', 'Total Tyer Maintenance');
        $sheet->setCellValue('I1', 'Total Income');
        $sheet->setCellValue('J1', 'Total Profit/Loss');
    
        // Populate data rows
        $row = 2;
        foreach ($data as $key => $entry) {
            $sheet->setCellValue('A' . $row, $key + 1);
            $sheet->setCellValue('B' . $row, $entry['vehicle_no'] ?? '');
            $sheet->setCellValue('C' . $row, $entry['total_inhouse'] ?? 0);
            $sheet->setCellValue('D' . $row, $entry['tstatutary'] ?? 0);
            $sheet->setCellValue('E' . $row, $entry['nsalary'] ?? 0);
            $sheet->setCellValue('F' . $row, $entry['total_diesel'] ?? 0);
            $sheet->setCellValue('G' . $row, $entry['expense_per_vehicle'] ?? 0);
            $sheet->setCellValue('H' . $row, $entry['totalCost'] ?? 0);
            $sheet->setCellValue('I' . $row, $entry['total_income'] ?? 0);
            $sheet->setCellValue('J' . $row, $entry['profit_loss'] ?? 0);
            $row++;
        }
    
        $filename = 'Vehicle_Ledger_' . date('Ymd') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    
        // Set headers for downloading the file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0'); // No cache
        header('Cache-Control: max-age=1');
        header('Pragma: public'); // Make it public so cache won't interfere
    
        // Clean the output buffer
        ob_end_clean();
    
        // Save to output
        $writer->save('php://output');
        exit;
    }


    function vehicleBillNo($from_date, $to_date, $vehicle)
    {
        $builder = $this->db->table('tyer_management');
        $builder->select('*');
        $builder->where('tyer_management.asign_date >=', $from_date);
        $builder->where('tyer_management.asign_date <=', $to_date);
        if ($vehicle !== 'all') {
            $builder->where('tyer_management.vehicle_id', $vehicle);
        }
        return $builder->get()->getResult();
    }
    
    function Vehicle_Ledger($vehicle_id, $from_date, $to_date)
    {
        $user_id = $this->session->get('user_id');
        $from_date = $from_date;
        $to_date = $to_date;
        $vehicle = $vehicle_id;

        // Set default values if not provided
        if ($from_date == '') {
            $from_date = date('Y-m-01');
        }
        if ($to_date == '') {
            $to_date = date('Y-m-t');
        }
        if ($vehicle == '') {
            $vehicle = 0;
        }

        // Prepare data for the view
        $data['filter_data'] = [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'vehicle_id' => $vehicle,
        ];
        
        //vehicle no show in view file
        $vehicle_bill_no = $this->vehicleBillNo($from_date, $to_date, $vehicle);

        $Billnumber = [];
        foreach ($vehicle_bill_no as $vehicle_bill) {
            $query = $this->db->query("SELECT DISTINCT bill_no FROM tyer_management WHERE tyer_sl_no = ?", [$vehicle_bill->tyer_sl_no]);
            $result = $query->getResult();
            foreach ($result as $row) {
                $Billnumber[] = $row->bill_no;
            }
        }
        
        // $Billnumber = array_unique($Billnumber); // Ensure unique bill numbers
        
        $resultCount = [];
        $billPrices = [];
        
        // Get tyre count and bill price for each bill
        foreach ($Billnumber as $Billnumber_single) {
            $query = $this->db->query("SELECT COUNT(*) AS tyre_count, price FROM tyer_management WHERE bill_no = ?", [$Billnumber_single]);
            $result = $query->getRow();
        
            if ($result) {
                $resultCount[$Billnumber_single] = $result->tyre_count;
                $billPrices[$Billnumber_single] = $result->price;
            }
        }
        
        // Calculate per-tyre cost and total cost
        $totalCost = 0;
        foreach ($Billnumber as $bill_no) {
            if (isset($resultCount[$bill_no]) && isset($billPrices[$bill_no]) && $resultCount[$bill_no] > 0) {
                $per_tyre_cost = $billPrices[$bill_no] / $resultCount[$bill_no];
                $totalCost += $per_tyre_cost;
            }
        }
        
        // echo "Total Cost for Tyres: " . $totalCost;

        
        // // echo '<pre>';
        // // print_r($TyerSlNo);
        // exit;

        $data['totalCost'] = $totalCost;
        
        
        $inhouse_maintanance = $this->AdminModel->vehicle_inhouse($from_date, $to_date, $vehicle);
        $outside_maintanance = $this->AdminModel->vehicle_outside($from_date, $to_date, $vehicle);
        $satutary_data = $this->AdminModel->satury_data($from_date, $to_date, $vehicle);
        $diesel_data = $this->AdminModel->vehicle_deisel($from_date, $to_date, $vehicle);
        $despatch_data = $this->AdminModel->despatch_data($from_date, $to_date, $vehicle);
        
        $vehicle_data = $this->db->table('vehicle')
            ->select('vehicle_no')
            ->where('id', $vehicle)
            ->get()
            ->getRow();

        $location_dtls = $this->db->query("SELECT * FROM vehicle  where  id='$vehicle'")->getResult();

        $loc_id = 0;
        foreach ($location_dtls as $loc) {
            $loc_id = $loc->location_id;
        }
        $numberofvehicle = $this->db->query("SELECT * FROM vehicle  where  location_id='$loc_id'")->getResult();
        $noofvehicle = count($numberofvehicle);

        $overall_expence = $this->AdminModel->overalexpence_data($loc_id, $from_date, $to_date);


        // Extract year and month from from_date
        $date = new DateTime($from_date);
        $year = $date->format('Y');
        $month = $date->format('m');

        // Initialize array to hold driver salary details
        $alldriver = [];

        // Fetch driver data and accumulate salary details
        $driver_data = $this->AdminModel->driver_data($from_date, $to_date, $vehicle);
        // echo "<pre>";
        // print_r($driver_data);exit;
        foreach ($driver_data as $driver) {
            $alldriver[] = $this->AdminModel->driver_salary_details_eport($year, $month, $driver->driver);
        }
        
        ?>
        
    
        <?php
        $total_inhouse = 0;
        foreach ($inhouse_maintanance as $maintanance) {
            $total_inhouse += $maintanance->price;
         } ?>
        <?php
        $total_outside = 0;
        foreach ($outside_maintanance as $maint) {
            $total_outside += $maint->amount;
        }?>
                                    
                
        <?php 
        $tstatutary=0;
            foreach($satutary_data as $sat){
            $tstatutary += $sat->amount;
        }
        ?>
                                
                
       <?php
            $nsalary = 0;
            foreach ($alldriver as $staf) {
                $ddate = $staf[0]->from_date;
                $date = new DateTime($ddate);
                $year = $date->format('Y');
                $month = $date->format('m');
            
                $date = new DateTimeImmutable("$year-$month-01");
            
                // Get the number of days in the month
                $curent_monthday = $date->format('t');
            
                $hsd_details = $this->AdminModel->hsd_details($staf[0]->id, $year, $month);
                $used_hsd = $hsd_details[0]->used_hsd ?? 0;
                $diesel_rate = $hsd_details[0]->diesel_rate ?? 0;
            
                $disel_entry = $this->AdminModel->vehicle_disel_details($staf[0]->assignment_vehicle_no, $staf[0]->from_date, $staf[0]->to_date);
                $total_d_req = array_sum(array_column($disel_entry, 'diesel_for_trip'));
            
                $HSD_LTR = $total_d_req - $used_hsd;
                $hsd_amount = $HSD_LTR * $diesel_rate;
            
                $trip_expense = $this->AdminModel->tripexpence($staf[0]->assignment_vehicle_no, $year, $month);
                $total_month_expense = array_sum(array_column($trip_expense, 'day_trip_expense'));
            
                $from_date_obj = new DateTime($staf[0]->from_date);
                $to_date_obj = new DateTime($staf[0]->to_date);
                $interval = $from_date_obj->diff($to_date_obj);
                $days_count = $interval->days + 1;
            
                $d_salary = $staf[0]->salary / $curent_monthday * $days_count;
            
                // Sanitize and validate variables
                $total_advance = isset($staf[0]->total_advance) 
                    ? (float)preg_replace('/[^0-9.-]/', '', $staf[0]->total_advance) 
                    : 0;
            
                $d_salary = isset($d_salary) ? (float)$d_salary : 0;
                $hsd_amount = isset($hsd_amount) ? (float)$hsd_amount : 0;
                $total_month_expense = isset($total_month_expense) ? (float)$total_month_expense : 0;
                $amount = isset($staf[0]->amount) ? (float)$staf[0]->amount : 0;
            
                // Perform the salary calculation
                $tsalary = ($d_salary + $hsd_amount + $total_month_expense + $amount - $total_advance);
            
                // Output the calculated salary
                //echo $tsalary;
            
                // Accumulate the total salary
                $nsalary += $tsalary;
            }
            ?>

                
                
                
            <?php
            $total_diesel = 0;
            foreach ($diesel_data as $entry) {
                $total_diesel += $entry->qty * $entry->rate;
            }?>
                                
            
            <?php 
            $total_income = 0;
            foreach ($despatch_data as $data) { 
                $total_income += $data->quantity * $data->rate;
            }?>
                                    
                
            <?php
            $over_expence=0;
            foreach($overall_expence as $ovr){
            $over_expence +=$ovr->amount;
            } ?>
                  <?php
                     $expense_per_vehicle = ($over_expence > 0 && $noofvehicle > 0) ? number_format($over_expence / $noofvehicle, 2) : 0;
                    ?>

            <?php
        
                    $total_income = floatval($total_income);
                    $total_inhouse = floatval($total_inhouse);
                    $total_outside = floatval($total_outside);
                    $tstatutary = floatval($tstatutary);
                    $nsalary = floatval($nsalary);
                    $total_diesel = floatval($total_diesel);
                    $expense_per_vehicle = is_numeric($expense_per_vehicle) ? floatval($expense_per_vehicle) : 0;
                
                    // Calculate total expense
                    $total_expenses = $total_inhouse + $total_outside + $tstatutary + $nsalary + $total_diesel + $expense_per_vehicle;
                
                    // Calculate profit/loss
                    $profit_loss = $total_income - $total_expenses;
                
                    $abcd = [
                        'vehicle_no'=>    $vehicle_data->vehicle_no,
                        'total_income'=>    $total_income,
                        'total_inhouse'=>    $total_inhouse,
                        'total_outside'=>    $total_outside,
                        'tstatutary'=>    $tstatutary,
                        'nsalary'=>    $nsalary,
                        'total_diesel'=>    $total_diesel,
                        'totalCost'=>    $totalCost,
                        'expense_per_vehicle'=>    $expense_per_vehicle,
                        'profit_loss'=>    $profit_loss,
                    ];
                    // Return the calculated value
                    return $abcd;
        
    }
}