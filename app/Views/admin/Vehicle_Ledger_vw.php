<?php
use App\Models\AdminModel;
$db = db_connect();
$AdminModel = new AdminModel($db);

include("header.php");
?>
<!-- Page Body Start-->
<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Vehicle Ledger</h3>
                    </div>
                    <div class="col-sm-6 p-0">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.html">
                                <svg class="stroke-icon">
                                    <use href="<?php echo base_url(); ?>/assets/admin/svg/icon-sprite.svg#stroke-home"></use>
                                </svg>
                            </a></li>
                            <li class="breadcrumb-item">Vehicle Ledger</li>
                            <li class="breadcrumb-item active">Default</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Container-fluid starts-->
        <div id="page-content">
            <div class="row uk-card uk-card-body uk-card-default uk-card-small uk-margin-bottom">
                <form action="<?php echo base_url(); ?>/Admin/Vehicle_Ledger" method="post" class="">
                    <div class="uk-grid-small" uk-grid>
                        <div class="uk-width-1-4@s">
                            <label for="from_date">From Date</label>
                            <input class="uk-input" type="date" id="from_date" name="from_date" value="<?= $filter_data['from_date'] ?>" required>
                        </div>
                        <div class="uk-width-1-4@s">
                            <label for="to_date">To Date</label>
                            <input class="uk-input" type="date" id="to_date" name="to_date" value="<?= $filter_data['to_date'] ?>" required>
                        </div>
                        <div class="uk-width-1-4@s">
                            <label>Vehicle No</label>
                            <select name="vehicle_id" class="uk-input" id="single" required>
                                <option value="">Select Vehicle No</option>
                                <?php foreach ($vehicle as $veh) { ?>
                                    <option value="<?= $veh->id ?>" <?= $filter_data['vehicle_id'] == $veh->id ? "selected" : "" ?>><?= $veh->vehicle_no ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="uk-width-auto@s uk-flex uk-flex-middle">
                            <?php if(in_array(23.1,$jobAssign)){ ?>
                            <button class="uk-button uk-button-primary" type="submit">Submit</button>
                            <?php }?>
                        </div>
                    </div>
                </form>
                <div class="uk-width-auto@s uk-flex uk-flex-middle">
                    <?php if(in_array(23.1,$jobAssign)){ ?>
                        <button id="downloadExcel" class="uk-button uk-button-primary" type="button">Download Excel</button>
                    <?php }?>
                </div>
            </div>

            <div class="uk-grid uk-child-width-1-3@m uk-grid-small uk-grid-match" uk-grid>
                <!-- In-house Maintenance Section -->
                <div>
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                        <h4>In-house Maintenance</h4>
                        <div class="table-responsive custom-scrollbar">
                            <table class="uk-table uk-table-striped uk-table-small">
                                <thead>
                                    <tr>
                                        <th>Sl.No</th>
                                        <th>Driver Name</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    $total_inhouse = 0;
                                    foreach ($inhouse_maintanance as $maintanance) {
                                        $total_inhouse += $maintanance->price;
                                    ?>
                                    <tr>
                                        <td><?= $i++; ?></td>
                                        <td><?= $maintanance->driver_name; ?></td>
                                        <td>
                                            <?php
                                            try {
                                                $date = new DateTime($maintanance->date);
                                                echo $date->format('d-m-Y');
                                            } catch (Exception $e) {
                                                echo 'Invalid date';
                                            }
                                            ?>
                                        </td>
                                        <td><?= $maintanance->price; ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3">Total In-house Maintenance Cost</th>
                                        <th><?= $total_inhouse; ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Outside Maintenance Section -->
                <div>
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                        <h4>Outside Maintenance</h4>
                        <div class="table-responsive custom-scrollbar">
                            <table class="uk-table uk-table-striped uk-table-small">
                                <thead>
                                    <tr>
                                        <th>Sl.No</th>
                                        <th>Vehicle No</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $j = 1;
                                    $total_outside = 0;
                                    foreach ($outside_maintanance as $maint) {
                                        $total_outside += $maint->amount;
                                    ?>
                                    <tr>
                                        <td><?= $j++; ?></td>
                                        <td><?= $maint->vehicle_no; ?></td>
                                        <td>
                                            <?php
                                            try {
                                                $date = new DateTime($maint->date);
                                                echo $date->format('d-m-Y');
                                            } catch (Exception $e) {
                                                echo 'Invalid date';
                                            }
                                            ?>
                                        </td>
                                        <td><?= $maint->amount; ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3">Total Outside Maintenance Cost</th>
                                        <th><?= $total_outside; ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                                               <h4>Statutaty</h4>

                        
                         <div class="table-responsive custom-scrollbar custom-scrollbar">
                            <table class="uk-table uk-table-striped uk-table-small">
                               <thead>
                                   <tr>
                                       <th>Sl no</th>
                                       <th>Vehicle no</th>
                                       <th>Expence type</th>
                                       <th>Amount</th>
                                      
                                       <th>Done by</th>
                                       
                                       
                                   </tr>
                               </thead>
                            <tbody>
                                <?php 
                                $tstatutary=0;
                                $i=1;
                                foreach($satutary_data as $sat){
                                $tstatutary += $sat->amount
                                ?>
                                <tr>
                                    <td><?=$i++; ?></td>
                                    <td><?=$sat->vehicle_no?></td>
                                    <td><?=$sat->expence_type;?></td>
                                    <td><?=$sat->amount?></td>
                                    <td><?=$sat->done_by?></td>
                                    
                                </tr>
                                <?php } ?>
                               
                                </tbody>
                                 <tfoot>
                                    <tr>
                                        <th colspan="3">Total Statutary Cost</th>
                                        <th><?= $tstatutary; ?></th>
                                    </tr>
                                </tfoot>
                             </table>  
                            </div>
                    </div>
                </div>
                 <!-- Staff Salary Detail Section -->
                <div class="uk-width-1-1">
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                        <h4>Staff Salary Detail</h4>
                        <div class="table-responsive custom-scrollbar">
                            <table class="uk-table uk-table-striped uk-table-hover uk-table-responsive">
                                <thead>
                                    <tr>
                                        <th>Sl no</th>
                                        <th>Name</th>
                                       
                                        <th>Working Day</th>
                                        <th>Salary</th>
                                        <th>Opening Balance</th>
                                        <th>Advance</th>
                                        <th>HSD Ltr</th>
                                        <th>HSD Amount</th>
                                        <th>Trip Expense</th>
                                        <th>Adjust Salary</th>
                                        <th>Gross Salary</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $nsalary=0;
                                    $i = 1;
                                    foreach ($alldriver as $staf) {
                                        
                                        // echo '<pre>';
                                        // print_r($staf);
                                        
                                        $ddate = $staf[0]->from_date;
                                        $date = new DateTime($ddate);
                                        $year = $date->format('Y');
                                        $month = $date->format('m');  
                                        
                                        
                                        
                                        // Create a DateTimeImmutable object for the first day of the given month and year
                                        $date = new DateTimeImmutable("$year-$month-01");
                                    
                                        // Get the number of days in the month
                                        $curent_monthday = $date->format('t');
    
    

                                        $hsd_details = $AdminModel->hsd_details($staf[0]->id, $year, $month);
                                        $used_hsd = $hsd_details[0]->used_hsd ?? 0;
                                        $diesel_rate = $hsd_details[0]->diesel_rate ?? 0;

                                        $disel_entry = $AdminModel->vehicle_disel_details($staf[0]->assignment_vehicle_no, $staf[0]->from_date, $staf[0]->to_date);
                                        $total_d_req = array_sum(array_column($disel_entry, 'diesel_for_trip'));

                                        $HSD_LTR = $total_d_req - $used_hsd;
                                        $hsd_amount = $HSD_LTR * $diesel_rate;

                                        $trip_expense = $AdminModel->tripexpence($staf[0]->assignment_vehicle_no, $year, $month);
                                        $total_month_expense = array_sum(array_column($trip_expense, 'day_trip_expense'));

                                        $from_date_obj = new DateTime($staf[0]->from_date);
                                        $to_date_obj = new DateTime($staf[0]->to_date);
                                        $interval = $from_date_obj->diff($to_date_obj);
                                        $days_count = $interval->days + 1;
                                        
                                        $d_salary= $staf[0]->salary/$curent_monthday*$days_count;
                                        $d= is_numeric($staf[0]->total_advance);
                                    ?>
                                    <tr>
                                        <td><?= $i++; ?></td>
                                        <td><?= $staf[0]->name ?></td>
                                       
                                        <td><?= $days_count; ?></td>
                                        <td><?= $d_salary; ?></td>
                                        <td><?= $staf[0]->opening_balance ?></td>
                                        <td><?= $staf[0]->total_advance ?></td>
                                        <td><?= $HSD_LTR; ?></td>
                                        <td><?= $hsd_amount; ?></td>
                                        <td><?= $total_month_expense; ?></td>
                                        <td><?= $staf[0]->amount ?></td>
                                        <td>
                                            <?php 
                                            
                                        // Cast and sanitize all variables involved in the calculation
                                            $total_advance = isset($staf[0]->total_advance) 
                                                             ? (float)preg_replace('/[^0-9.-]/', '', $staf[0]->total_advance) 
                                                             : 0; // Clean any non-numeric characters and cast to float
                                            
                                            $d_salary = isset($d_salary) ? (float)$d_salary : 0;
                                            $hsd_amount = isset($hsd_amount) ? (float)$hsd_amount : 0;
                                            $total_month_expense = isset($total_month_expense) ? (float)$total_month_expense : 0;
                                            $amount = isset($staf[0]->amount) ? (float)$staf[0]->amount : 0;
                                            
                                            // Perform the salary calculation
                                            $tsalary = ($d_salary + $hsd_amount + $total_month_expense + $amount - $total_advance);
                                            
                                            // Output the calculated salary
                                            echo $tsalary;
                                            
                                            // Accumulate the total salary
                                            $nsalary += $tsalary;



                                            ?>
                                            
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                               <tfoot>
                                    <tr>
                                        <th colspan="9">Total Gross Slary </th>
                                        <th><?= $nsalary; ?></th>
                                    </tr>
                               </tfoot>
                            </table>
                        </div>
                    </div>   
                </div>
                
                <!-- Diesel Data Section -->
                <div>
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                        <h4>Diesel Data</h4>
                        <div class="table-responsive custom-scrollbar">
                            <table class="uk-table uk-table-striped uk-table-small">
                                <thead>
                                    <tr>
                                        <th>Diesel Date</th>
                                        <th>Quantity</th>
                                        <th>Rate</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total_diesel = 0;
                                    foreach ($diesel_data as $entry) {
                                        $total_diesel += $entry->qty * $entry->rate;
                                    ?>
                                    <tr>
                                        <td><?= $entry->diesel_date; ?></td>
                                        <td><?= $entry->qty; ?></td>
                                        <td><?= $entry->rate; ?></td>
                                        <td><?= $entry->qty * $entry->rate; ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3">Total Diesel Cost</th>
                                        <th><?= $total_diesel; ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Despatch Details Section -->
                <div>
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                        <h4>Despatch Details</h4>
                        <div class="table-responsive custom-scrollbar">
                            <table class="uk-table uk-table-striped uk-table-hover uk-table-responsive">
                                <thead>
                                    <tr>
                                        <th>DO No</th>
                                        <th>Despatch Date</th>
                                        <th>Quantity</th>
                                        <th>Rate</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_income = 0;
                                    foreach ($despatch_data as $data) { 
                                        $total_income += $data->quantity * $data->rate;
                                    ?>
                                    <tr>
                                        <td><?= $data->doregno; ?></td>
                                        <td><?= $data->des_date; ?></td>
                                        <td><?= $data->quantity; ?></td>
                                        <td><?= $data->rate; ?></td>
                                        <td><?= $data->quantity * $data->rate; ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4">Total Income</th>
                                        <th><?= $total_income; ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                   <h4>Overal Expence</h4>
                        <div class="table-responsive custom-scrollbar">
                            <table class="uk-table uk-table-striped uk-table-hover uk-table-responsive">
                                               <thead>
                                                   <tr>
                                                       <th>Sl no</th>
                                                       <th>Amount</th>
                                                       <th>Date</th>
                                                       <th>Location</th>
                                                       
                                                     
                                                   </tr>
                                               </thead>
                                    <tbody>
                                    <?php $i=1;
                                    $over_expence=0;
                                    foreach($overall_expence as $ovr){ 
                                    $over_expence +=$ovr->amount; ?>
                                    <tr>
                                        <td><?=$i++;?></td>
                                        
                                        <td><?=$ovr->date;?></td>
                                        <td><?=$ovr->location_name;?></td>
                                        <td><?=$ovr->amount;?></td>
                                        
                                        
                                    </tr>
                                    <?php } ?>
                                       
                                        </tbody>
                                        <tfoot>
                                    <tr>
                                        <th colspan="3">Total Overall</th>
                                        <th><?= $over_expence; ?></th>
                                    </tr>
                                     <tr>
                                        <th colspan="3">Total No.of Vehicle</th>
                                        <th><?= $noofvehicle; ?></th>
                                    </tr>
                                    
                                     <tr>
                                        <th colspan="3">Per Vehicle</th>
                                        <th>
                                          <?php
echo $expense_per_vehicle = ($over_expence > 0 && $noofvehicle > 0) ? number_format($over_expence / $noofvehicle, 2) : 0;
?>


                                            </th>
                                    </tr>
                                </tfoot>
                                     </table>
                        </div>
                    </div>
                </div>
                <div>
                      <div class="uk-card uk-card-body uk-card-secondary uk-card-small">
                        <h4>Total Profit/Loss</h4>
                        <div class="table-responsive custom-scrollbar">
                            <table class="uk-table uk-table-striped uk-table-hover uk-table-responsive">
                                <tr>
                                    <tr>
                                        <th >Total In-house Maintenance Cost</th>
                                        <th>-<?= $total_inhouse; ?></th>
                                    </tr>
                                </tr>
                                   <tr>
                                    <th>Outside-Maintainance Total</th>
                                    <th>-<?= $total_outside; ?></th>
                                </tr>
                                   <tr>
                                      <th >Total Statutary Cost</th>
                                        <th>-<?= $tstatutary; ?></th>
                                </tr>
                                   <tr>
                                     <th>Total Gross Slary </th>
                                        <th>-<?= $nsalary; ?></th>
                                </tr>
                                   <tr>
                                   <th>Total Diesel Cost</th>
                                        <th>-<?= $total_diesel; ?></th>
                                </tr>
                                 <tr>
                                  <th>Total Overall</th>
                                        <th>-<?= $expense_per_vehicle; ?></th>
                                </tr>
                                <tr>
                                    <th>Total Income</th>
                                    <th>+<?= $total_income; ?></th>
                                </tr>
                                <tr>
                                    <th>Total Tyer Maintenance</th>
                                    <th>+<?= $totalCost; ?></th>
                                </tr>
                                <tr>
                                    <th>Total Profit/Loss</th>
                                    <th>
                                        <?php 
                                            $total_income = floatval($total_income);
                                            $total_inhouse = floatval($total_inhouse);
                                            $total_outside = floatval($total_outside);
                                            $tstatutary = floatval($tstatutary);
                                            $nsalary = floatval($nsalary);
                                            $total_diesel = floatval($total_diesel);
                                    
                                            // Ensure $expense_per_vehicle is numeric
                                            $expense_per_vehicle = is_numeric($expense_per_vehicle) ? floatval($expense_per_vehicle) : 0;
                                    
                                            // Calculate the total expense
                                            $a = $total_inhouse + $total_outside + $tstatutary + $nsalary + $total_diesel + $expense_per_vehicle;
                                    
                                            // Calculate the total profit/loss
                                            $total_expense = $total_income - $a;
                                    
                                            // Echo the result
                                            echo $total_expense;
                                        ?>
                                    </th>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <!-- Container-fluid Ends-->
        </div>
    <!-- Footer Start -->
    <!-- Add jQuery via CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>

    $(document).ready(function () {

        $('#downloadExcel').click(function () {

            // Get values from input fields

            const fromDate = $('#from_date').val();

            const toDate = $('#to_date').val();


            // Validate input values

            if (!fromDate || !toDate) {

                alert('Please select both From Date and To Date.');

                return;

            }


            // Redirect to the export URL with query parameters

            const exportUrl = '<?= base_url(); ?>/Ledger/exportExcel?from_date=' + encodeURIComponent(fromDate) + '&to_date=' + encodeURIComponent(toDate);

            window.location.href = exportUrl;

        });

    });

</script>
    <?php include("footer.php"); ?>
</div>
