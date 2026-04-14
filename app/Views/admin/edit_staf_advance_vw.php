<?php include("header.php");
foreach($single_stafadv as $stf_adv){}
?>
  <!-- Page Body Start-->
      <div class="page-body-wrapper uk-background-muted" style="background:#fcfcfc">
            <?php include("mainsidebar.php"); ?>
            
         <div class="page-body">
                  <div class="container-fluid">        
                    <div class="page-title">
                      <div class="row">
                        <div class="col-sm-6 p-0">
                          <h3>Advance </h3>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Container-fluid starts-->    
   <div class="container-fluid default-dashboard">
                    
                  <div class="uk-grid-small" uk-grid>
                        <div class="uk-width-3-4@m">
                             <div class="uk-card uk-card-body uk-card-default uk-card-small ">
                          <form action="<?php echo base_url(); ?>/Admin/update_StaffAdvance"  enctype="multipart/form-data" method="post">
                              <div class="uk-grid-small uk-child-width-1-1" uk-grid>
                                  
                                                <input type="hidden" name="adv_id" value="<?=$stf_adv->id;?>" />
                                                <div class="" style="display:none;">
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
                                                                <option  <?php if($stf_adv->staff_id==$staff->id){echo "selected";}?>  data-user-type="<?= htmlspecialchars($staff->user_type) ?>" data-location-id="<?= htmlspecialchars($staff->address) ?>" value="<?= htmlspecialchars($staff->id) ?>">
                                                                    <?= htmlspecialchars($staff->name) ?>
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
                                                     <input type="date" id="date" name="date" class="uk-input" value="<?=$stf_adv->adv_date;?>"/> 
                                                 </div>
                                                <div class="uk-form-controls">
                                                         <label>Bank/Cash</label>
                                                        <select class="uk-select" name="bank_cash" id="form-stacked-select">
                                                            <option>select</option>
                                                            <option <?php if($stf_adv->bank_cash=="Cash"){echo "selected";}?> >Cash</option>
                                                            <option <?php if($stf_adv->bank_cash=="Bank"){echo "selected";}?> >Bank</option>
                                                        </select>
                                                     </div>
                                                 <div class="uk-form-controls">
                                                         <label>Cash Paid By</label>
                                                        <select class="uk-select" name="paid_by">
                                                            <option value="">Select</option>
                                                            <option value="Self" <?php if(($stf_adv->paid_by ?? '')=="Self"){echo "selected";}?> >Self</option>
                                                            <option value="Admin" <?php if(($stf_adv->paid_by ?? '')=="Admin"){echo "selected";}?> >Admin</option>
                                                            <option value="Dispatcher" <?php if(($stf_adv->paid_by ?? '')=="Dispatcher"){echo "selected";}?> >Dispatcher</option>
                                                            <option value="Pump" <?php if(($stf_adv->paid_by ?? '')=="Pump"){echo "selected";}?> >Pump</option>
                                                        </select>
                                                     </div>
                                                <div class="">
                                                    <label>Amount</label>
                                                    <input type="number" name="amount" placeholder="Enter Amount" id="amount" class="uk-input" value="<?=$stf_adv->amount;?>" />
                                                </div>
                                                <div class="">
                                                    <label>Location</label>
                                                     <select name="location_id" class="uk-input" required>
                                                       <option>Select Location</option>
                                                       <?php foreach($location as $loc){?>
                                                            <option <?php if($stf_adv->location_id==$loc->location_id){echo "selected";}?> value="<?=$loc->location_id?>"><?=$loc->location_name?></option>
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
                                      
                                </div>
                        </div> 
                        
                      
            
         </div>
          <!-- Container-fluid Ends-->
        </div>
</div>      
            
            
            
        
            

  
  
  
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

    $('#staffFilter').change(function() {
        var selectedOption = $(this).find('option:selected');
        var userType = selectedOption.data('user-type');
        var locationId = selectedOption.data('location-id');

        // Automatically select the location if the staff type is Staff Master (STAFF)
        if (userType === 'STAFF' && locationId) {
            $('select[name="location_id"]').val(locationId);
        }
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