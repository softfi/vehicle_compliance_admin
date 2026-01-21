<?php include("header.php");?>
      <!-- Page Body Start-->
      <div class="page-body-wrapper" style="background:#ececec;">
        <?php include("mainsidebar.php"); ?>
        <div class="page-body">
          <div class="container-fluid">        
            <div class="page-title">
              <div class="row">
                <div class="col-sm-6 p-0">
                  <h3>Despatch view </h3>
                </div>
                <div class="col-sm-6 p-0">
                </div>
              </div>
            </div>
          </div>
          <!-- Container-fluid starts-->
          <div class="container-fluid default-dashboard">
            <div class="uk-grid-small" uk-grid>
                <div class="uk-width-1-3@m">
                     <div class="uk-card uk-card-body uk-card-default uk-card-small ">
                    <form action="<?php echo base_url(); ?>/Admin/insert_despatch_entry" enctype="multipart/form-data" method="post">
                        <div class="">
                            <lable>Vehicle No</lable>
                            <select class="form-control" name="vehicle_no" id="single" required>
                            <option value="">Select Vehicle</option>
                            <?php foreach($vehicle as $vec){?>
                            <option value="<?=$vec->id?>"><?=$vec->vehicle_no;?></option>
                            <?php }?>
                            </select>
                            <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('vehicle_no'); ?></span><?php } ?>
                        </div> 
                        <div class="uk-margin-bottom"> 
                         <lable>Date</lable>
                            <input type="date" name="date" class="uk-input" required />
                        </div>
                        <div class="uk-margin-bottom">
                          <lable>Quantity </lable>
                          <input type="text" name="quantity" placeholder="enter Quantity" id="quantity" class="form-control"value="<?= set_value('quantity') ?>" required />
                          <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('quantity'); ?></span><?php } ?>
                      </div>   
                        <div class="uk-margin-bottom">
                        <lable>DO No</lable>
                        <select class="form-control" name="do_no" id = "single1" required>
                            <option value="">Select Do Number</option>
                            <?php foreach($doregistration as $dor){?>
                            <option value="<?=$dor->do_registration_id?>"><?=$dor->do_no;?></option>
                            <?php }?>
                            </select>
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('do_no'); ?></span><?php } ?>
                    </div>   
                        <div class="uk-margin-bottom">
                        <lable>Ref No.</lable>
                        <input type="text" name="ref_no" placeholder="enter Ref No.  " id="ref_no" class="form-control" value="<?= set_value('ref_no') ?>" required />
                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('ref_no'); ?></span><?php } ?>
                    </div>   
                        <div class="uk-margin-bottom">
                     <?php if(in_array(3.1,$jobAssign)){ ?>
                      <button type="submit" class="btn btn-primary">Submit</button>
                      <?php }?>
                      </div>
                     </form>
                       <hr>
                             <a href="<?php echo base_url();?>/sampleexcel/DESPACH_EXCEL.xlsx">click here</a> to download sample excel
                             <form action="<?php echo base_url();?>/Admin/excel_despatch" method="post" enctype="multipart/form-data">
                                 <div class="uk-margin-bottom">
                                
                                <input type="file" name="file" id="file" class="form-control" accept=".csv, .xlsx">
                                </div>
                                <div class="uk-margin-bottom">
                                 <?php if(in_array(3.2,$jobAssign)){ ?>
                                <button type="submit" class="btn btn-primary">Upload Excel</button>
                                <?php }?>
                                </div>
                                </form>
                        </div>
                </div>
                    <div class="uk-width-2-3@m">
                       <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-bottom uk-margin-small">
                           <form method="post" action="<?php echo base_url(); ?>/Admin/despatch_entry">
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
                                         <?php if(in_array(3.3,$jobAssign)){ ?>
                                        <button type="submit" class="uk-button uk-button-primary uk-width-1-1" id="submit_button">Filter</button>
                                        <?php }?>

                                    </div>
                                    <div>
                                        <label for="download_excel">.</label>
                                         <?php if(in_array(3.4,$jobAssign)){ ?>
                                        <a href="#" class="uk-button uk-button-primary uk-width-1-1" id="download_excel">Download Excel</a>
                                        <?php }?>
                                    </div>
                                </div>
                            </form>

                        </div>


                        <div class="uk-card uk-card-body uk-card-default uk-card-small ">
                            <form method="post" action="<?= base_url(); ?>/Admin/delete_multiple_despatch">
                                <div class="table-responsive custom-scrollbar custom-scrollbar">
                                    <table class="display" id="row_create" style="width:100%">
            <thead>
                <tr>
                    <th>Sl no</th>
                    <th><input type="checkbox" id="checkAll"></th>
                    <th>Vehicle No</th>
                    <th>Date</th>
                    <th>Quantity</th>
                    <th>DO No</th>
                    <th>Ref No.</th>
                    <th>Edit</th>
                    <th>Delete</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; foreach($despatch as $des): ?>
                    <tr>
                        <td><?=$i++;?></td>
                        <td><input type="checkbox" class="delete-checkbox" name="select_del[]" value="<?=$des->despatch_id; ?>" /></td>
                        <td><?=$des->vehicle_number;?></td>
                        <td><?= date('d-m-Y', strtotime($des->des_date)); ?></td>
                        <td><?=$des->quantity;?></td>
                        <td><?=$des->doreg_no;?></td>
                        <td><?=$des->ref_no;?></td>
                        <td>
                         <?php if(in_array(3.5,$jobAssign)){ ?>
                            <a href="javascript:void(0);" onClick="edit_despatch('<?=$des->despatch_id; ?>');" class="btn btn-primary">Edit</a>
                             <?php } ?>
                            </td>
                        <td>
                            
                         <?php if(in_array(3.6,$jobAssign)){ ?>
                            <a href="<?php echo base_url(); ?>/Admin/delete_despatch/<?=$des->despatch_id?>" class="btn btn-danger">Delete</a>
                            <?php }?></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Sl no</th>
                    <th></th>
                    <th>Vehicle No</th>
                    <th>Date</th>
                    <th>Quantity</th>
                    <th>DO No</th>
                    <th>Ref No.</th>
                    <th>Edit</th>
                    <th>Delete</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
                                    <?php if(in_array(3.6,$jobAssign)){ ?>
                                    <button type="submit" class="btn btn-danger">Delete multiple</button>
                                    <?php }?>
                                </div>
                            </form>
                        </div> 
                    </div>
                </div>
                <!-- Container-fluid Ends-->
            </div>
        <!-- footer start-->
           </div>   
           </div>
         
         
         
         <script>
function edit_despatch(id) {
    // Send AJAX request to get item data
    $.ajax({
        url: '<?= base_url(); ?>/Admin/edit_despatchd', // Update with your actual URL
        type: 'GET',
        data: { did: id },
        success: function(response) {
            // Populate the content in the off-canvas component
            $('#edit-item-content').html(response);

            // Open the off-canvas component
            UIkit.offcanvas('#offcanvas-edit').show();
        },
        error: function(xhr, status, error) {
            console.error('Error fetching item data:', error);
        }
    });
}
</script>

<div id="offcanvas-edit" uk-offcanvas="flip: true; overlay: true">
    <div class="uk-offcanvas-bar uk-margin-remove uk-padding-remove">
        <div class="uk-card uk-card-body uk-card-default uk-card-small">
        <button class="uk-offcanvas-close" type="button" uk-close></button>
            <div id="edit-item-content"></div>
        </div>
    </div>
</div>





           
           
           <div id="modal-center" class="uk-flex-top" uk-modal>
    <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">

        <button class="uk-modal-close-default" type="button" uk-close></button>
                              <form action="" method="post">
                        <div class="uk-margin-bottom">
                            <lable>Vehicle No</lable>
                            <input type="text" name="name" placeholder="enter Vehicle No" id="" class="uk-input"/>
                        </div> 
                        <div class="uk-margin-bottom">
                          <lable>Quantity </lable>
                          <input type="number" name="name" placeholder="enter Quantity" id="" class="uk-input"/>
                      </div>   
                      <div class="uk-margin-bottom">
                        <lable>DO No</lable>
                        <input type="text" name="name" placeholder="enter DO No" id="" class="uk-input"/>
                    </div>   
                    <div class="uk-margin-bottom">
                        <lable>Ref No.</lable>
                        <input type="text" name="name" placeholder="enter Ref No.  " id="" class="uk-input"/>
                    </div>   
                    <div class="uk-margin-bottom">
                        <lable>Upload Excel</lable>
                        <input type="file" name="name" placeholder="enter Upload Excel " id="" class="uk-input"/>
                    </div>   
                    <div class="uk-margin-bottom">
                      <button type="submit" class="btn btn-primary">Submit</button>
                      </div>
                      </form>
    </div>
</div>


<script>
document.getElementById('download_excel').addEventListener('click', function() {
    const fromDate = document.getElementById('from_date').value;
    const toDate = document.getElementById('to_date').value;
    const baseUrl = '<?php echo base_url(); ?>/Admin/download_despatch_excel';
    const url = `${baseUrl}?from_date=${fromDate}&to_date=${toDate}`;
    window.location.href = url;
});
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Check/Uncheck all checkboxes when the 'checkAll' checkbox is clicked
        $('#checkAll').click(function() {
            $('.delete-checkbox').prop('checked', $(this).prop('checked'));
        });

       
    });
</script>



       <?php include("footer.php");?>
