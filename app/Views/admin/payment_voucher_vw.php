<?php include("header.php");?>
  <!-- Page Body Start-->
      <div class="page-body-wrapper uk-background-muted" style="background:#fcfcfc">
            <?php include("mainsidebar.php"); ?>
            
         <div class="page-body">
                  <div class="container-fluid">        
                    <div class="page-title">
                      <div class="row">
                        <div class="col-sm-6 p-0">
                          <h3>Add Payment Voucher</h3>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Container-fluid starts-->    
   <div class="container-fluid default-dashboard">
                    
                  <div class="uk-grid-small" uk-grid>
                        <div class="uk-width-1-4@m">
                             <div class="uk-card uk-card-body uk-card-default uk-card-small ">
                          <form action="<?php echo base_url(); ?>/Admin/insert_payment_voucher"  enctype="multipart/form-data" method="post">
                              <div class="uk-grid-small uk-child-width-1-1" uk-grid>
                                            <div class="">
                                                <label>Date</label>
                                                <input type="date" id="date" name="date" class="uk-input" value="<?php echo date('Y-m-d'); ?>"/> 
                                            </div>
                                            <div class="">
                                                <label>User Type</label>
                                                <select id="userTypeSelect" class="form-control" name="user_type" required>
                                                    <option value="">Select User Type</option>
                                                    <option value="Party">PARTY</option>
                                                    <option value="Pump">PUMP</option>
                                                    <option value="Vendor">VENDOR</option>
                                                    <option value="DRIVER">Driver</option>
                                                    <option value="STAFF">Staff</option>
                                                </select>
                                            </div>

                                            <!-- Employ Name Dropdown -->
                                            <div class="">
                                                <label>Employ Name</label>
                                                <select id="staffFilter" name="staff_id" class="uk-input" required>
                                                    <option value="">Select</option>
                                                    <!-- Options will be populated by AJAX -->
                                                </select>
                                            </div>
                                            <div class="">
                                                <label>Select Bank</label>
                                                    <select name="bank" class="uk-input" required>
                                                    <option>Select Bank</option>
                                                    <?php foreach($allbank as $bank){?>
                                                        <option value="<?=$bank->id?>"><?=$bank->bank_name?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="">
                                                <label>Location</label>
                                                    <select name="location_id" class="uk-input" required>
                                                    <option>Select Location</option>
                                                    <?php foreach($location as $loc){?>
                                                        <option value="<?=$loc->location_id?>"><?=$loc->location_name?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                              <div class="uk-form-controls">
                                                         <label>Credit/Debit</label>
                                                        <select class="uk-select" name="credit_debit" id="form-stacked-select">
                                                            <option>select</option>
                                                            <option value="1">Credit</option>
                                                            <option value="2">Debit</option>
                                                        </select>
                                                    </div>
                                               
                                              <div class="uk-form-controls">
                                                <label>Bank/Cash</label>
                                                <select class="uk-select" name="bank_cash" id="bankCashSelect">
                                                    <option>Select</option>
                                                    <option value="1">Cash</option>
                                                    <option value="2">Upi</option>
                                                </select>
                                            </div>
                                            <div class="uk-form-controls" id="utrField" style="display: none; margin-top:10px;">
                                                <label>UTR No.</label>
                                                <input type="text" name="utr_no" class="uk-input" placeholder="Enter UTR No.">
                                            </div>
                                                <div class="">
                                                    <label>Amount</label>
                                                    <input type="number" name="amount" placeholder="Enter Amount" id="amount" class="uk-input" value="" />
                                                </div>
                                              
                                                <div class="">
                                                    <label>Upload File</label>
                                                    <input type="File" name="upload_file" placeholder="Upload file " id="upload_file" class="uk-input" value="" />
                                                </div>
                                            <div class="">
                                            <label>Remark</label>
                                            <textarea type="text" name="remark" class="uk-input" placeholder="Enter Remark"></textarea>
                                            </div>
                                                <div class="">
                                                    <?php if(in_array(40.1,$jobAssign)){ ?>
                                                    <label class="uk-padding-small"></label>
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                    <?php }?>
                                                </div>
                                            </div>
                                                 
                                        </form>
                                        <hr>
                                       <a href="<?php echo base_url();?>/sampleexcel/staff_advance.xlsx">click here</a> to download sample excel
                             <form action="<?php echo base_url();?>/Admin/upload_staf_advance" method="post" enctype="multipart/form-data">
                                 <div class="uk-margin-bottom">
                                
                                <input type="file" name="file" id="file" class="form-control" accept=".csv, .xlsx">
                                </div>
                                
                                <div class="uk-margin-bottom">
                                <?php if(in_array(40.2,$jobAssign)){ ?>
                                <button type="submit" class="btn btn-primary">Upload Excel</button>
                                <?php }?>
                                </div>
                            </form>
                                </div>
                        </div> 
                        
                        <div class="uk-width-3-4@m">
                        <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-bottom uk-margin-small">
                           <form method="post" action="<?php echo base_url(); ?>/Admin/payment_voucher">
                                <?php
                                $default_from_date = $date['from_date'] ?? date('Y-m-01');
                                $default_to_date = $date['to_date'] ?? date('Y-m-d');
                                ?>
                                <div class="uk-grid-small uk-child-width-expand" uk-grid>
                                    <div>
                                        <label for="from_date">From Date:</label>
                                        <input type="date" id="from_date" name="from_date" class="uk-input" value="<?= $default_from_date; ?>" />
                                    </div>
                                    <div>
                                        <label for="to_date">To Date:</label>
                                        <input type="date" id="to_date" name="to_date" class="uk-input" value="<?= $default_to_date; ?>" />
                                    </div>
                                    <div>
                                        <label for="submit_button">.</label>
                                        <button type="submit" class="uk-button uk-button-primary uk-width-1-1" id="submit_button">Filter</button>
                                    </div>
                                    <div>
                                        <label for="download_excel">.</label>
                                        <a href="#" class="uk-button uk-button-primary uk-width-1-1" id="download_excel">Download Excel</a>
                                    </div>
                                </div>
                            </form>

                        </div>
                        <div class="uk-width-expand@m">
                            <div class="uk-card uk-card-body uk-card-default uk-card-small ">
                                    <div class="table-responsive custom-scrollbar custom-scrollbar">
                                        <table class="display" id="row_create" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Sl no</th>
                                                <th>User Type</th>
                                                <th>Employ Name</th>
                                                <th>Date</th>
                                                <th>Bank Name</th>
                                                <th>Location</th>
                                                <th>Bank/Cash</th>
                                                <th>UTR No.</th>
                                                <th>Credit/Debit</th>
                                                <th>Amount</th>
                                                <th>File</th>
                                                <th>Edit</th>
                                                <th>Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $sl_no = 1;
                                             foreach ($AllPaymentVoucher as $record) { ?>
                                                <tr>
                                                    <td><?= $sl_no++; ?></td>
                                                    <?php
                                                    // Mapping for display
                                                    $userTypeMap = [
                                                        'Party'    => 'PARTY',
                                                        'Pump'     => 'PUMP',
                                                        'Vendor'   => 'VENDOR',
                                                        'DRIVER'   => 'Driver',
                                                        'STAFF'    => 'Staff',
                                                        'MECHANIC' => 'Mechanic'
                                                    ];
                                                    ?>
                                                    <td><?= isset($userTypeMap[$record->user_type]) ? $userTypeMap[$record->user_type] : $record->user_type; ?></td>
                                                    <td>
                                                        <?php if (in_array($record->user_type, ['DRIVER', 'STAFF', 'MECHANIC'])): ?>
                                                            <?= $record->staff_name . ' (' . $record->staff_code . ')' ?>
                                                        <?php else: ?>
                                                            <?= $record->vendor_name . ' (' . $record->vendor_type . ')' ?>
                                                        <?php endif; ?>
                                                    </td> 
                                                    <td><?= $record->pay_date; ?></td>
                                                    <td><?= $record->bank_name; ?></td>
                                                    <td><?= $record->location_name; ?></td>
                                                    <td><?= $record->bank_cash == 1 ? 'Cash' : ($record->bank_cash == 2 ? 'UPI' : ''); ?></td>
                                                    <td><?= $record->utr_no; ?></td>
                                                    <td><?= $record->credit_debit == 1 ? 'Credit' : ($record->credit_debit == 2 ? 'Debit' : ''); ?></td>
                                                    <td><?= $record->amount; ?></td>
                                                    <td>
                                                        <?php if (!empty($record->upload_file) && file_exists(FCPATH . $record->upload_file)): ?>
                                                            <a href="<?= base_url($record->upload_file); ?>" target="_blank">View File</a>
                                                        <?php else: ?>
                                                            No Image
                                                        <?php endif; ?>
                                                    </td>
                                    
                                                    <td>
                                                        <?php if(in_array(40.3,$jobAssign)){ ?>
                                                        <a href="<?php echo base_url(); ?>/Admin/editpayment_vouccher/<?= $record->pay_id; ?>" class="btn btn-success">Edit</a>
                                                        <?php }?>
                                                    </td>
                                                    <td>
                                                        <?php if(in_array(40.4,$jobAssign)){ ?>
                                                        <a href="<?php echo base_url(); ?>/Admin/delete_payment_vouccher/<?= $record->pay_id; ?>" class="btn btn-danger">Delete</a>
                                                        <?php }?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                        <!-- Reusable Modal -->
                                       
                                    </div>
                            </div> 
                        </div>
                    </div>
                </div>
                <!-- Container-fluid Ends-->
            </div>
        </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script>
    document.getElementById('download_excel').addEventListener('click', function() {
        const fromDate = document.getElementById('from_date').value;
        const toDate = document.getElementById('to_date').value;
        const baseUrl = '<?php echo base_url(); ?>/Admin/download_payment_voucher_excel';
        const url = `${baseUrl}?from_date=${fromDate}&to_date=${toDate}`;
        window.location.href = url;
    });
</script> 
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function () {
    $('#userTypeSelect').change(function () {
        var userType = $(this).val();

        if (userType !== '') {
            $.ajax({
                url: "<?= base_url(); ?>/Admin/getUsersByType",  // Your controller method
                type: "POST",
                data: { user_type: userType },
                success: function (response) {
                    $('#staffFilter').html('<option value="">Select</option>');
                    var data = JSON.parse(response);

                    $.each(data, function (index, user) {
                        $('#staffFilter').append(
                            `<option value="${user.id}">${user.name} (${user.code || user.staff_code || ''})</option>`
                        );
                    });
                },
                error: function () {
                    alert('Failed to load users');
                }
            });
        } else {
            $('#staffFilter').html('<option value="">Select</option>');
        }
    });
});
</script>
   <script>
    $(document).ready(function () {
        $('#bankCashSelect').on('change', function () {
            if ($(this).val() === '2') {
                $('#utrField').show();
            } else {
                $('#utrField').hide();
            }
        });
    });
</script>
<?php include("footer.php");?>