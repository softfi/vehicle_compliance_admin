<?php include("header.php");?>
      <!-- Page Body Start-->
      <div class="page-body-wrapper">
            <?php include("mainsidebar.php"); ?>
                <div class="page-body">
                  <div class="container-fluid">        
                    <div class="page-title">
                      <div class="row">
                        <div class="col-sm-6 p-0">
                          <h3>Outside Maintenance </h3>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Container-fluid starts-->
                  <div class="container-fluid default-dashboard">
                    
                  <div class="uk-grid-small" uk-grid>
                        <div class="uk-width-1-1@m">
                             <div class="uk-card uk-card-body uk-card-default uk-card-small ">
                          <form action="<?php echo base_url(); ?>/Admin/insert_outside" enctype="multipart/form-data" method="post">
                              
                              <div class="uk-grid-small uk-child-width-expand" uk-grid>
        
                                                 <div class="uk-margin-bottom-small">
                                                    <label>Vehicle No</label>
                                                   <select name="vehicle_id" class="uk-input" id="single" required>
                                                       <option>Select Vehicle</option>
                                                       <?php foreach($vehicle as $veh){?>
                                                            <option value="<?=$veh->id?>"><?=$veh->vehicle_no?></option>
                                                       <?php } ?>
                                                   </select>
                                                </div>
                                                
                                                <div class="uk-margin-bottom-small">
                                                    <label>Date</label>
                                                    <input type="date" name="date"   class="uk-input" value=""  />
                                                </div>
                                                
                                                
                                                 <div class="uk-margin-bottom-small">
                                                    <label>Bill No</label>
                                                    <input type="text" name="bill_no" placeholder="Enter Bill Number " id="bill_no" class="uk-input" value=""  />
                                                </div>
                                                 <div class="uk-margin-bottom-small">
                                                    <label>Amount</label>
                                                    <input type="text" name="amount" placeholder="Enter Amount" id="amount" class="uk-input" value="" required />
                                                </div>
                                                <div class="uk-margin-bottom-small">
                                                    <label>Vendor Name</label>
                                                    <select name="vendor_id" id="vehicle" class="uk-input" required>
                                                       <option>Select vendor</option>
                                                       <?php foreach($vendor as $ven){?>
                                                            <option value="<?=$ven->id?>"><?=$ven->name?></option>
                                                       <?php } ?>
                                                   </select>
                                                </div>
                                               <div class="uk-margin-bottom-small">
                                                    <label>Location</label>
                                                     <select name="location_id" class="uk-input" id="single1" required>
                                                       <option>Select Location</option>
                                                       <?php foreach($location as $loc){?>
                                                            <option value="<?=$loc->location_id?>"><?=$loc->location_name?></option>
                                                       <?php } ?>
                                                   </select>
                                                </div>
                                                <div class="uk-margin-bottom-small">
                                                    <label>Remark</label>
                                                    <input type="text" name="remark" placeholder="remark " id="remark" class="uk-input"  />
                                                </div>
                                                <div class="uk-margin-bottom-small">
                                                    <label>Upload File</label>
                                                    <input type="File" name="upload_file" placeholder="Upload file " id="upload_file" class="uk-input"  />
                                                </div>
                                                <div class="uk-margin-bottom-small">
                                                   <label class="uk-padding-small"></label>
                                                        <?php if(in_array(6.1,$jobAssign)){ ?>
                                                        <button type="submit" class="btn btn-primary">Submit</button>
                                                        <?php }?>
                                                </div>
                                        </div>
                                        </form>
                                </div>
                        </div>
                                             <div class="uk-width-2-3@m">
                <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-bottom">
                    <form method="post" action="<?php echo base_url(); ?>/Admin/outside_mentainance">
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
            </div>
                            <div class="uk-width-1-1@m">
                                <div class="uk-card uk-card-body uk-card-default uk-card-small ">
                                     <div class="table-responsive custom-scrollbar custom-scrollbar">
                                   <table class="display" id="row_create" style="width:100%">
    <thead>
        <tr>
            <th>Sl no</th>
            <th>Vehicle no</th>
            <th>Bill No</th>
            <th>Amount</th>
            <th>Vendor Name</th>
            <th>Location</th>
            <th>Date</th>
            <th>remark</th>
            <th>File</th>
            <!--<th>Edit</th>-->
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            $i = 1;
            foreach ($outside as $index => $row): 
                // Prepare file URL
                    $file = $row->upload_file;

                    // If already contains 'uploads/', use as full path
                    if (strpos($file, 'uploads/') !== false) {
                        $fileUrl = base_url() . $file;
                    } else {
                        // Otherwise prefix uploads/
                        $fileUrl = base_url() . 'uploads/' . $file;
                    }
            ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= $row->vehicle_no ?></td>
                <td><?= $row->bill_no ?></td>
                <td><?= $row->amount ?></td>
                <td><?= $row->name ?></td>
                <td><?= $row->location_name ?></td>
                <td><?= date('d/m/y', strtotime($row->date)); ?></td>
                <td><?= $row->remark ?></td>
                <td><a href="<?= $fileUrl; ?>" target="_blank">View File</a></td>
                <!--<td><a class="btn btn-warning" href="<?= base_url('Admin/edit_outside/' . $row->id) ?>">Edit</a></td>-->
                <td>
                     <?php if(in_array(6.2,$jobAssign)){ ?>
                    <a class="btn btn-danger" href="<?= base_url('Admin/delete_outside/' . $row->id) ?>">Delete</a>
                    <?php } ?>
                    </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th>Sl no</th>
            <th>Vehicle no</th>
            <th>Bill No</th>
            <th>Amount</th>
            <th>Vendor Name</th>
            <th>Location</th>
            <th>File</th>
            <!--<th>Edit</th>-->
            <th>Delete</th>
        </tr>
    </tfoot>
</table>

                                    </div>
                                </div> 
                            </div>
                          </div>
          <!-- Container-fluid Ends-->
        </div>
</div>        
<script>    
        document.getElementById('download_excel').addEventListener('click', function() {
        const fromDate = document.getElementById('from_date').value;
        const toDate = document.getElementById('to_date').value;
        const baseUrl = '<?php echo base_url(); ?>/Admin/export_outside_maintenance_excel';
        const url = `${baseUrl}?from_date=${fromDate}&to_date=${toDate}`;
        window.location.href = url;
    });</script>
        <!-- footer start-->
       <?php include("footer.php");?>