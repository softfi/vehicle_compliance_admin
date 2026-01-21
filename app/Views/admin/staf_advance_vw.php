<?php include("header.php");?>
  <!-- Page Body Start-->
      <div class="page-body-wrapper uk-background-muted" style="background:#fcfcfc">
            <?php include("mainsidebar.php"); ?>
            
         <div class="page-body">
                  <div class="container-fluid">        
                    <div class="page-title">
                      <div class="row">
                        <div class="col-sm-6 p-0">
                          <h3>Staff Advance </h3>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Container-fluid starts-->    
   <div class="container-fluid default-dashboard">
                    
                  <div class="uk-grid-small" uk-grid>
                        <div class="uk-width-1-4@m">
                             <div class="uk-card uk-card-body uk-card-default uk-card-small ">
                          <form action="<?php echo base_url(); ?>/Admin/insert_staf_advance"  enctype="multipart/form-data" method="post">
                              <div class="uk-grid-small uk-child-width-1-1" uk-grid>
                                                <div class="">
                                                        <label>Staff Type</label>
                                                        <?php
                                                        // Group by user_type
                                                        $user_types = [];
                                                        foreach ($allstaf as $staff) {
                                                            $user_types[$staff->user_type] = $staff->user_type;
                                                        }
                                                        ?>
                                                        <select id="typeFilter" name="type" class="form-control">
                                                            <option value="">Select Type</option>
                                                            <?php foreach ($user_types as $user_type) { ?>
                                                                <option value="<?= htmlspecialchars($user_type) ?>"><?= htmlspecialchars($user_type) ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                <div class="">
                                                        <label>Employ Name</label>
                                                        <select id="staffFilter" name="staff_id" class="uk-input"  required>
                                                            <option value="">Select </option>
                                                            <?php foreach ($allstaf as $staff) { ?>
                                                                <option data-user-type="<?= htmlspecialchars($staff->user_type) ?>" value="<?= htmlspecialchars($staff->id) ?>">
                                                                    <?= htmlspecialchars($staff->name) ?> (<?= htmlspecialchars($staff->staff_code) ?>)
                                                                </option>

                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                <!--<div id="responseContainer">-->
                                                <!--      <label>Vehicle</label>-->
                                                <!--     <input type="text" readonly  class="uk-input"/> -->
                                                <!-- </div>  -->
                                                <div class="">
                                                     <label>Date</label>
                                                     <input type="date" id="date" name="date" class="uk-input" value="<?php echo date('Y-m-d'); ?>"/> 
                                                 </div>
                                                <div class="uk-form-controls">
                                                         <label>Bank/Cash</label>
                                                        <select class="uk-select" name="bank_cash" id="form-stacked-select">
                                                            <option>select</option>
                                                            <option>Cash</option>
                                                            <option>Bank</option>
                                                        </select>
                                                    </div>
                                                <div class="">
                                                    <label>Amount</label>
                                                    <input type="number" name="amount" placeholder="Enter Amount" id="amount" class="uk-input" value="" />
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
                                                <div class="">
                                                    <label>Upload File</label>
                                                    <input type="File" name="upload_file" placeholder="Upload file " id="upload_file" class="uk-input" value="" />
                                                </div>
                                                <div class="">
                                                   <label class="uk-padding-small"></label>
                                                        <?php if(in_array(7.1,$jobAssign)){ ?>
                                                        <button type="submit" class="btn btn-primary">Submit</button>
                                                        <?php }?>
                                                </div>
                                </div>
                                                 
                                        </form>
                                        <hr>
                                       <a href="<?php echo base_url();?>/sampleexcel/staff_advance_new.xlsx">click here</a> to download sample excel
                             <form action="<?php echo base_url();?>/Admin/upload_staf_advance" method="post" enctype="multipart/form-data">
                                 <div class="uk-margin-bottom">
                                
                                <input type="file" name="file" id="file" class="form-control" accept=".csv, .xlsx">
                                </div>
                                
                                <div class="uk-margin-bottom">
                                <?php if(in_array(7.2,$jobAssign)){ ?>
                                <button type="submit" class="btn btn-primary">Upload Excel</button>
                                <?php }?>
                                </div>
                            </form>
                                </div>
                        </div> 
                        
                        <div class="uk-width-3-4@m">
                        <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-bottom uk-margin-small">
                           <form method="post" action="<?php echo base_url(); ?>/Admin/staf_advance">
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
                                    <div class="uk-overflow-auto table-responsive custom-scrollbar custom-scrollbar">
                                        <table class="display uk-table uk-table-small uk-table-divider display nowrap" id="row_create" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Sl no</th>
                                                <th><input type="checkbox" id="checkAll"></th>
                                                <th>Employ Name</th>
                                                <th>Date</th>
                                                <th>Bank/Cash</th>
                                                <th>Amount</th>
                                                <th>Location</th>
                                                <th>File</th>
                                                <th>Edit</th>
                                                <th>Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $sl_no = 1; foreach ($staffadvance as $record) { ?>
                                                <tr>
                                                    <td><?= $sl_no++; ?></td>
                                                    <td><input type="checkbox" class="delete-checkbox" name="select_del[]" value="<?=$record->id; ?>" /></td>
                                                    <td>
                                                        <?= htmlspecialchars($record->name); ?> (<?= htmlspecialchars($record->staff_code); ?>)
                                                    </td>
                                                    <td><?= $record->adv_date; ?></td>
                                                    <td><?= $record->bank_cash; ?></td>
                                                    <td><?= $record->amount; ?></td>
                                                    <td><?= $record->location_name; ?></td>
                                                    <td>
                                                        <?php if (!empty($record->upload_file) && file_exists(FCPATH . $record->upload_file)): ?>
                                                            <a href="<?= base_url($record->upload_file); ?>" target="_blank">View File</a>
                                                        <?php else: ?>
                                                            No Image
                                                        <?php endif; ?>
                                                    </td>
                                    
                                                    <td>
                                                        <?php if(in_array(7.3, $jobAssign)){ ?>
                                                            <a href="<?php echo base_url(); ?>/Admin/editstaf_advance/<?= $record->id; ?>" class="btn btn-success">Edit</a>
                                                        <?php } ?>
                                                    </td>
                                                    <td>
                                                        <?php if(in_array(7.4, $jobAssign)){ ?>
                                                            <a href="<?php echo base_url(); ?>/Admin/delete_StaffAdvance/<?= $record->id; ?>" class="btn btn-danger">Delete</a>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                        <!-- Reusable Modal -->
                                        <div id="modal-center" class="uk-flex-top" uk-modal>
                                            <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
                                                <button class="uk-modal-close-default" type="button" uk-close></button>
                                                <form id="modal-form" action="" method="post" enctype="multipart/form-data">
                                                    <div class="uk-grid-small uk-child-width-1-1" uk-grid>
                                                        <input type="hidden" name="staff_advance_id" id="modal-staff-id"/>
                                                        
                                                        
                                                         <div class="">
                                                                                                <label>Staff Type</label>
                                                                                                <?php
                                                                                                // Group by user_type
                                                                                                $user_types = [];
                                                                                                foreach ($allstaf as $staff) {
                                                                                                    $user_types[$staff->user_type] = $staff->user_type;
                                                                                                }
                                                                                                ?>
                                                                                                <select id="typeFilter" name="type" class="form-control">
                                                                                                    <option value="">Select Type</option>
                                                                                                    <?php foreach ($user_types as $user_type) { ?>
                                                                                                        <option value="<?= htmlspecialchars($user_type) ?>"><?= htmlspecialchars($user_type) ?></option>
                                                                                                    <?php } ?>
                                                                                                </select>
                                                                                            </div>
                                                        
                                                        <div class="">
                                                                                                <label>Employ Name</label>
                                                                                                <select id="staffFilter" name="staff_id" class="uk-input"  required>
                                                                                                    <option value="">Select </option>
                                                                                                    <?php foreach ($allstaf as $staff) { ?>
                                                                                                        <option data-user-type="<?= htmlspecialchars($staff->user_type) ?>" value="<?= htmlspecialchars($staff->id) ?>">
                                                                                                            <?= htmlspecialchars($staff->name) ?>
                                                                                                        </option>
                                                                                                    <?php } ?>
                                                                                                </select>
                                                                                            </div>
                                                                                            
                                                                                            
                                                        <div class="uk-margin-bottom">
                                                            <label>Date</label>
                                                            <input type="date" name="date" id="modal-date" class="uk-input" required>
                                                        </div>
                                                        <div class="uk-margin-bottom">
                                                            <label>Bank/Cash</label>
                                                            <select name="bank_cash" id="modal-bank-cash" class="uk-select">
                                                                <option value="Cash">Cash</option>
                                                                <option value="Bank">Bank</option>
                                                            </select>
                                                        </div>
                                                        <div class="uk-margin-bottom">
                                                            <label>Amount</label>
                                                            <input type="number" name="amount" id="modal-amount" class="uk-input" required>
                                                        </div>
                                                        <div class="uk-margin-bottom">
                                                            <label>Location</label>
                                                            <select name="location_id" id="modal-location" class="uk-input" required>
                                                                <option>Select Location</option>
                                                                <?php foreach($location as $loc){ ?>
                                                                    <option value="<?= $loc->location_id ?>"><?= $loc->location_name ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <div class="uk-margin-bottom">
                                                            <label>Upload File</label>
                                                            <input type="file" name="upload_file" class="uk-input" />
                                                            <a id="modal-file" href="#" target="_blank">View Current File</a>
                                                        </div>
                                                        <div class="uk-margin-bottom">
                                                            <button type="submit" class="btn btn-primary">Submit</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <?php if(in_array(3.6,$jobAssign)){ ?>
                                            <button type="submit" class="btn btn-danger" id="deleteMulti">Delete multiple</button>
                                        <?php }?>
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
    $(document).ready(function() {
        // Check/Uncheck all checkboxes when the 'checkAll' checkbox is clicked
        $('#checkAll').click(function() {
            $('.delete-checkbox').prop('checked', $(this).prop('checked'));
        });

       
    });
</script>
<script>
    $(document).ready(function() {
        $('#deleteMulti').click(function(e) {
            e.preventDefault();
            var selectedIds = [];
            $('input[name="select_del[]"]:checked').each(function() {
                selectedIds.push($(this).val());
            });
            if (selectedIds.length === 0) {
                alert('Please select at least one record to delete.');
                return;
            }
            if (confirm('Are you sure you want to delete the selected records?')) {
                $.ajax({
    
                    url: '<?= base_url("Admin/delete_multiple_staf_advance") ?>',
                    type: 'POST',
                    data: { select_del: selectedIds },
                    success: function(response) {
                        alert('Selected records deleted successfully.');
                        location.reload(); 
    
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred while deleting records: ' + error);
                    }
                });
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('.edit-btn').on('click', function() {
            // Get data attributes from the clicked button
            var id = $(this).data('id');
            var name = $(this).data('name');
            var date = $(this).data('date');
            var bankCash = $(this).data('bank-cash');
            var amount = $(this).data('amount');
            var locationId = $(this).data('location-id');
            var fileLink = $(this).data('file');
            
            // Populate the modal fields
            $('#modal-staff-id').val(id);
            $('#modal-name').val(name);
            $('#modal-date').val(date);
            $('#modal-bank-cash').val(bankCash);
            $('#modal-amount').val(amount);
            $('#modal-location').val(locationId);
            $('#modal-file').attr('href', fileLink);
            
            // Set form action dynamically
            $('#modal-form').attr('action', '/Admin/update_StaffAdvance/' + id);
        });
    });
</script>  
<script>
    $(document).ready(function() {
        $('#typeFilter').change(function() {
            var selectedType = $(this).val();
            $('#staffFilter option').each(function() {
                var userType = $(this).data('user-type');
                if (selectedType === "" || userType === selectedType) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            // Reset the staff selection
            $('#staffFilter').val('');
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('#staffFilter, #date').change(function() {
           
            var staffId = $('#staffFilter').val();
            var date = $('#date').val();
            
           

            if (staffId && date) {
                $.ajax({
                    url: '<?php echo base_url(); ?>/Admin/getvehicledtls', // Replace with your AJAX handler URL
                    method: 'POST',
                    data: {
                        staff_id: staffId,
                        date: date
                    },
                    success: function(response) {
                        $('#responseContainer').html(response); // Display the response in the container
                    },
                    error: function(xhr, status, error) {
                        $('#responseContainer').html('<p>An error occurred while processing your request.</p>'); // Display error message
                    }
                });
            }
        });
    });
    </script>
<script>
    document.getElementById('download_excel').addEventListener('click', function() {
        const fromDate = document.getElementById('from_date').value;
        const toDate = document.getElementById('to_date').value;
        const baseUrl = '<?php echo base_url(); ?>/Admin/download_staffadvance_excel';
        const url = `${baseUrl}?from_date=${fromDate}&to_date=${toDate}`;
        window.location.href = url;
    });
</script> 
<?php include("footer.php");?>