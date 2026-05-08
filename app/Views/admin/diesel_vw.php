<?php include("header.php");?>
      <!-- Page Body Start-->
      <div class="page-body-wrapper" style= "background:#ececec;">
        <?php include("mainsidebar.php"); ?>
        <div class="page-body">
          <div class="container-fluid">        
            <div class="page-title">
              <div class="row">
                <div class="col-sm-6 p-0">
                  <h3>Diesel Entry </h3>
                </div>
                <div class="col-sm-6 p-0">
                  <?php if (session()->getFlashdata('success')): ?>
                      <div class="alert alert-success alert-dismissible fade show" role="alert">
                          <?= session()->getFlashdata('success') ?>
                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>
                  <?php endif; ?>
                  <?php if (session()->getFlashdata('error')): ?>
                      <div class="alert alert-danger alert-dismissible fade show" role="alert">
                          <?= session()->getFlashdata('error') ?>
                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <!-- Container-fluid starts-->
          <div class="container-fluid default-dashboard">
           <div class="uk-grid-small" uk-grid>
                <div class="uk-width-1-3@m">
                        <div class="uk-card uk-card-body uk-card-default uk-card-small ">
                           <form action="<?php echo base_url();?>/Admin/insert_diesel" method="post">
                               <div class="form-group">
                                        <label>Select Pump</label>
                                        <select class="form-control" name="vendor" id="petrol">
                                            <option value="">Select vendor</option>
                                            <?php foreach($vendor as $loc){
                                            if($loc->type=="Pump"){
                                            ?>
                                            <option value="<?=$loc->id?>"><?=$loc->name?></option>
                                            <?php } } ?>
                                        </select>
                                        <?php if(isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('vendor'); ?></span><?php } ?>
                                    </div>
                                <div class="form-group">
                                    <label>Select Vehicle</label>
                                    <select class="form-control" name="vehicle" id="single1">
                                        <option value="">Select Vehicle</option>
                                        <?php foreach($vehicle as $veh){?>
                                        <option value="<?=$veh->id?>"><?=$veh->vehicle_no?></option>
                                        <?php } ?>
                                    </select>
                                    <?php if(isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('vehicle'); ?></span><?php } ?>
                                </div>
                                <div class="form-group">
                                    <lable>Date</lable>
                                    <input type="date" class="form-control" name="date" value="<?php echo date("Y-m-d"); ?>" id="date"/>
                                    <?php if(isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('date'); ?></span><?php } ?>
                                </div>
                                <div class="form-group">
                    <label>QTY.</label>
                    <input type="text" name="qty" class="form-control"/>
                    <?php if(isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('qty'); ?></span><?php } ?>
                </div>
                
                                
                
                                <div class="form-group">
                    <label>Rate.</label>
                   
                    
                    <div id="getpetrol">
                         <input type="text" name="rate" class="form-control"/>
                    </div>
                    <?php if(isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('rate'); ?></span><?php } ?>
                </div>
                <div class="form-group uk-margin-top">
                <?php if(in_array(4.1,$jobAssign)){ ?>
                <button class="btn btn-primary" type="submit">Submit</button>
                <?php }?>
                </div>
                
                
                           </form>
                           <hr>
                           <a href="<?php echo base_url();?>/admin/diesel_entry/download_sample">click here</a> to download sample excel
                           <small class="text-muted d-block">Format: Vendor Name | Vehicle No | Date (dd/mm/yyyy) | Qty | Rate</small>
                             <form action="<?php echo base_url();?>/Admin/excel_dieselentry" method="post" enctype="multipart/form-data">
                                 <div class="uk-margin-bottom">
                                
                                <input type="file" name="file" id="file" class="form-control" accept=".csv, .xlsx">
                                </div>
                                
                                <div class="uk-margin-bottom">
                                 <?php if(in_array(4.2,$jobAssign)){ ?>
                                <button type="submit" class="btn btn-primary">Upload Excel</button>
                                <?php }?>
                                </div>
                            </form>
                        </div>
               </div>
        <div class="uk-width-2-3@m">
            
             <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-bottom uk-margin-small">
                           <form method="post" action="<?php echo base_url(); ?>/Admin/diesel_entry">
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
                                        <?php if(in_array(4.3,$jobAssign)){ ?>
                                        <button type="submit" class="uk-button uk-button-primary uk-width-1-1" id="submit_button">Filter</button>
                                    <?php }?>
                                    </div>
                                    <div>
                                        <label for="download_excel">.</label>
                                         <?php if(in_array(4.4,$jobAssign)){ ?>
                                        <a href="#" class="uk-button uk-button-primary uk-width-1-1" id="download_excel">Download Excel</a>
                                    <?php }?>
                                    </div>
                                </div>
                            </form>

                        </div>
                        
                        
                        
            <div class="uk-card uk-card-body uk-card-default uk-card-small">
                  <form method="post" action="<?= base_url(); ?>/Admin/delete_multiple_diesel">
                <div class="table-responsive">
            <table class="display" id="row_create" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Sl no</th>
                                        <?php if(in_array(4.8,$jobAssign)){ ?>
                                        <th><input type="checkbox" id="checkAll"></th>
                                        <?php }?>

                                        <th>vendor Name</th>
                                        <th>Vehicle No</th>
                                        <th>Date</th>
                                        <th>Qty</th>
                                        <th>Rate</th>
                                        <th>Delete</th>
                                        <th>Edit</th>
                                    </tr>
                                    
                                </thead>
                                <tbody>
                                    <?php 
                                    $i=1;
                                    foreach($diesel as $desl){?>
                                    <tr>
                                        <td><?=$i++;?></td>
                                        <td><input type="checkbox" class="delete-checkbox" name="select_del[]" value="<?= $desl->diselentry_id ; ?>" /></td>
                                        <td><?=$desl->vendor_name;?></td>
                                        <td><?=$desl->vehicle_no;?></td>
                                        <td> <?=date('d-m-Y', strtotime($desl->diesel_date))?></td>
                                        <td><?=$desl->qty;?></td>
                                        <td><?=$desl->rate;?></td>
                                        <td>
                                            <?php if(in_array(4.5,$jobAssign)){ ?>
                                            <a href="javascript:void(0);" onClick="deleteRecord('<?= $desl->diselentry_id ; ?>');" class="btn btn-danger">Delete</a></td>
                                        <?php }?>
                                        <td>
                                        <?php if(in_array(4.6,$jobAssign)){ ?>
                                            <a href="javascript:void(0);" onClick="editRecord(<?= $desl->diselentry_id; ?>);"
                                            title="edit" class="btn btn-xs btn-primary">Edit</a>                                        <?php }?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                   
                                     
                                </tbody>
                                <tfoot>
                                    <tr>
                                         <th>Sl no</th>
                                         <th></th>
                                        <th>vendor Name</th>
                                        <th>Vehicle No</th>
                                        <th>Date</th>
                                        <th>Qty</th>
                                        <th>Rate</th>
                                        <th>Delete</th>
                                        <th></th>
                                    </tr>
                                    
                                </tfoot>
                            </table></div>
                            <?php if(in_array(4.7,$jobAssign)){ ?>
                             <button type="submit" class="btn btn-danger">Delete multiple</button>
                             <?php }?>
                   </form>         
                            </div>
            </div>
  </div>    
          <!-- Container-fluid Ends-->
    </div>
        <!-- footer start-->
        </div>
        </div>
        
    <form name="frm_deleteBanner" id="frm_deleteBanner" action="<?php echo base_url();?>/admin/delete_dieselentry" method="post">
     <input type="hidden" name="user_id" id="user_id" value="">
     </form>
    <script type="text/javascript">
    function deleteRecord(id){
    	$("#user_id").val(id);
    	var conf=confirm("Are you sure want to delete this record");
    	if(conf){
    	   $("#frm_deleteBanner").submit();
    	}
    }
    </script> 
<!-- Modal Structure -->
<div id="modal-edit" uk-modal>
    <div class="uk-modal-dialog uk-modal-body uk-width-1-2@m">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <div class="uk-card uk-card-body uk-card-default uk-card-small">
            <h3 class="uk-text-danger">Edit Diesel</h3>
            <div id="user-details">
                <!-- Dynamic content will be injected here -->
            </div>
        </div>
    </div>
</div>

<script>
   function editRecord(user_Id) {
    $.ajax({
        url: '<?= base_url(); ?>/Admin/edit_diesel',  // Ensure this URL is correct
        type: 'POST',
        data: { id: user_Id },
        success: function (response) {
            console.log(response);  // Check the response returned from the PHP controller
            if (response) {
                $('#user-details').html(response);  // Inject form HTML into the modal
                UIkit.modal('#modal-edit').show();  // Show the modal
            } else {
                alert("No response received or data is empty.");
            }
        },
        error: function(xhr, status, error) {
            console.log(xhr.responseText);  // Log the full error response
            alert("An error occurred while fetching the user details: " + xhr.responseText);  // Display the full error message
        }
    });
}
</script>

        
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
<script>
$(document).ready(function() {
    $('#petrol').on('change', function() {
        var vendorId = $(this).val();
        var date = $('#date').val(); // Get the value of the input with id 'date'
        
        if (vendorId) {
            $.ajax({
                url: '<?php echo base_url();?>/admin/get_petrol_rate', // Update this with your actual URL
                method: 'POST',
                data: { 
                    vendor_id: vendorId,
                    date: date // Add the date to the data being sent
                },
                success: function(response) {
                    $('#getpetrol').html(response);
                },
                error: function(xhr, status, error) {
                    console.error('Error: ' + error);
                    $('#getpetrol').html('<p>An error occurred</p>');
                }
            });
        } else {
            $('#getpetrol').html('<p>Please select a vendor</p>'); // Changed to correct target element
        }
    });
});
</script>

 
   <script>
document.getElementById('download_excel').addEventListener('click', function() {
    const fromDate = document.getElementById('from_date').value;
    const toDate = document.getElementById('to_date').value;
    const baseUrl = '<?php echo base_url(); ?>/Admin/download_diesel_excel';
    const url = `${baseUrl}?from_date=${fromDate}&to_date=${toDate}`;
    window.location.href = url;
});
</script>     
        

<script>
    $(document).ready(function() {
        // Check/Uncheck all checkboxes when the 'checkAll' checkbox is clicked
        $('#checkAll').click(function() {
            $('.delete-checkbox').prop('checked', $(this).prop('checked'));
        });
    });       
        
</script> 
<!-- Select2 CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('#petrol').select2({
        placeholder: "Search and Select Pump",
        allowClear: true,
        width: '100%'
    });
    
    $('#single1').select2({
        placeholder: "Search and Select Vehicle",
        allowClear: true,
        width: '100%'
    });
});
</script>

<?php include("footer.php");?>
           
