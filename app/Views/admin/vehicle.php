<?php include("header.php");?>
<?php 
$validation = session('validation') ?? \Config\Services::validation();
?>
<style>
 #row_create thead th {
        position: sticky;
        top: 0;
        background: white; /* Or match your table background */
        z-index: 10;
        border-bottom: 1px solid #ddd; /* optional for better visibility */
    }
</style>
      <!-- Page Body Start-->
      <div class="page-body-wrapper">
        <?php include("mainsidebar.php"); ?>
       
        <div class="page-body">
          <div class="container-fluid">        
            <div class="page-title">
              <div class="row">
                <div class="col-sm-6 p-0">
                  <h3>Vehicle 
             
                  
                  </h3>
                   
                </div>
                <div class="col-sm-6 p-0">
                <?php if(in_array(13.2,$jobAssign)){ ?>
                 <button class="btn btn-warning" style="float:right; margin-right:10px;"  type="button" uk-toggle="target: #addnewvehicle">Add New Vehicle</button>
                 <?php }?>
                 <?php if(in_array(13.1,$jobAssign)){ ?>
                 <button class="btn btn-primary" style="float:right" type="button" data-bs-toggle="modal" data-bs-target="#uploadexcel" data-whatever="@getbootstrap">Upload EXCEL</button>
                 <?php }?> 
                 <?php if(in_array(13.3,$jobAssign)){ ?>
                 <a href="<?php echo base_url(); ?>/Admin/download_vehicle_sample_excel" class="btn btn-primary" style="float:right" type="button" > sample excel</a>
                 <?php }?>
            </div>
          </div
          
          </div>
          <!-- Container-fluid starts-->
          <div class="container-fluid default-dashboard">
              <p></p>
             

<div class="col-sm-12">
                <div class="card">
                  <?php if(session()->getFlashdata('msg')):?>
                                                <div class="alert alert-success">
                                                <?= session()->getFlashdata('msg') ?>
                                                </div>
                                            <?php endif;?>
                                            <?php if(session()->getFlashdata('error')):?>
                                                <div class="alert alert-danger">
                                                    <?= session()->getFlashdata('error') ?>
                                                </div>
                                            <?php endif;?>
                                            
                  <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-top">
                            <div style="max-height: 400px; overflow-y: auto;">
                                <table id="row_create" class="uk-table uk-table-small uk-table-divider" style="width:100%">
    <thead>
        <tr>
            <th>Sr.No</th>
            <th>Vehicle Number</th>
            <th>Vehicle Type</th>
            <th>Chassis No.</th>
            <th>Engine No.</th>
            <th>Fitness Expiry Date</th>
            <th>Fitness Amount</th>
            <th>Tax Expiry Date</th>
            <th>Tax Amount</th>
            <th>Insurance Company</th>
            <th>Insurance Expiry Date</th>
            <th>Ins Amount</th>
            <th>Permit Expiry Date</th>
            <th>Permit Amount</th>
            
            <th>National Permit Expiry Date</th>
            <th>National Permit Amount</th>
            
            <th>Finance</th>
            <th>Deduct Amount</th>
            <th>EMI Account</th>
            <th>Horse Make</th>
            <th>Horse Model</th>
            <th>Horse Rate</th>
            <th>Dala Rate</th>
            <th>Dala Make</th>
            <th>RTO Expenses</th>
            <th>AMC</th>
            <th>AMC Amount</th>
            <th>AMC Frequency</th>
            <th>AMC Expiry</th>
            <th>PUCC</th>
            <th>PUCC Amount</th>
            <th>I3MS Expiry</th>
            <th>I3MS Recharge</th>
            <th>Khanij Expiry</th>
            <th>Khanij amount</th>
            <th>Remark</th>
            <th>Document</th>
            <th>Location</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody> 
       <?php 
$sr_no = 1;
foreach ($vehicle as $vehic) { ?>
    <tr>
        <td><?= $sr_no++; ?></td>
        <td><?= $vehic->vehicle_no; ?></td>
        <td><?= htmlspecialchars($vehic->type_name ?? ''); ?></td>
        <td><?= $vehic->chassis_no; ?></td>
        <td><?= $vehic->engine_no; ?></td>
        <td><?= (new DateTime($vehic->fitness_exp_date))->format('d-m-Y'); ?></td>
        <td><?= $vehic->fitness_amount; ?></td>
        <td><?= (new DateTime($vehic->tax_exp_date))->format('d-m-Y'); ?></td>
        <td><?= $vehic->road_tax_amount; ?></td>
        <td><?= $vehic->ins_company; ?></td>
        <td><?= (new DateTime($vehic->ins_exp_date))->format('d-m-Y'); ?></td>
        <td><?= $vehic->insurance_amount; ?></td>
        <td><?= (new DateTime($vehic->permit_exp_date))->format('d-m-Y'); ?></td>
        <td><?= $vehic->permit_amount; ?></td>
        <td><?= (new DateTime($vehic->npermit_exp_date))->format('d-m-Y'); ?></td>
        <td><?= $vehic->npermit_amount; ?></td>
        <td><?= $vehic->finance; ?></td>
        <td><?= $vehic->deduct_amount; ?></td>
        <td><?= $vehic->emi_account; ?></td>
        <td><?= $vehic->horse_make; ?></td>
        <td><?= $vehic->horse_model; ?></td>
        <td><?= $vehic->horse_rate; ?></td>
        <td><?= $vehic->dala_rate; ?></td>
        <td><?= $vehic->dala_make; ?></td>
        <td><?= $vehic->rto_expenses; ?></td>
        <td><?= $vehic->amc; ?></td>
        
        <td><?= $vehic->amc_amount; ?></td>
        <td><?= $vehic->amc_frequancy; ?></td>
        <td><?= (new DateTime($vehic->amc_expary))->format('d-m-Y'); ?></td>
        <td><?= $vehic->pucc; ?></td>
        <td><?= $vehic->pucc_amount; ?></td>
        <td><?= (new DateTime($vehic->i3ms_expary))->format('d-m-Y'); ?></td>
        <td><?= $vehic->i3ms_recharge; ?></td>
        <td><?= (new DateTime($vehic->khanij_expiri))->format('d-m-Y'); ?></td>
        <td><?= $vehic->khanij_amount; ?></td>
        <td><?= $vehic->remark; ?></td>
        <td><a href="<?= base_url() . 'uploads/documents/' . $vehic->document; ?>">Document</a></td>
        <td><?= $vehic->location_name; ?> </td>
        <td>
            <div class="btn-group">
                <?php if(in_array(13.4,$jobAssign)){ ?>
                <a class="btn btn-primary" href="javascript:void(0);" onClick="editvehicle('<?= $vehic->id ; ?>');">Edit</a> 
                <?php }?>
              <?php if(in_array(13.5,$jobAssign)){ ?>
                <a class="btn btn-danger" href="<?= base_url('Admin/DeleteVehicle/' . $vehic->id); ?>" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                <?php }?>
            </div>
        </td>
    </tr>
<?php } ?>

    </tbody>
</table>

                    </div>
                  </div>
                  </div>
                  </div>

   </div>
        
      </div>
          </div>
          <!-- Container-fluid Ends-->
        </div>
        
        
        
        <div class="modal fade" id="uploadexcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalgetbootstrap" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-body">
                            <form action="<?= base_url('Admin/upload') ?>" method="post" enctype="multipart/form-data">
                                <label for="file">Choose CSV or Excel File:</label><br>
                                <input type="file" name="file" id="file" class="form-control" accept=".csv, .xlsx">
                                <p><br></p>
                                <button  class="btn btn-primary" type="submit">Upload</button>
                            </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                
                
                
                
        
        
         <div id="addnewvehicle" uk-offcanvas="flip: true; overlay: true">
    <div class="uk-offcanvas-bar uk-padding-remove uk-margin-remove uk-width-1-2@m " >

        <button class="uk-offcanvas-close" type="button" uk-close></button>
<div class="uk-card uk-card-body uk-card-default uk-card-small ">
    
     <form action="<?php echo base_url(); ?>/Admin/Insertvehicle" enctype="multipart/form-data" method="post">
    <div class="modal-body">
        
        <?php if ($validation->getErrors()): ?>
            <div class="alert alert-danger">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0">
                    <?php foreach ($validation->getErrors() as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Vehicle Type -->
        <div class="form-group">
            <label>Vehicle Type</label>
            <select class="form-control" name="vehicle_type" id="vehicle_type" required>
                <option value="">Select Type</option>
                <?php foreach($vehicle_types as $type): ?>
                    <option value="<?= $type->id ?>" <?= old('vehicle_type') == $type->id ? 'selected' : '' ?>><?= htmlspecialchars($type->type_name) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($validation->hasError('vehicle_type')): ?>
                <span class="text-danger" style="color: red !important; display: block; font-size: 12px;"><?= $validation->getError('vehicle_type') ?></span>
            <?php endif; ?>
        </div>

        <div class="uk-child-width-1-2@m uk-grid-small" uk-grid>
            
            <!-- Common Fields -->
            <div class="form-group">
                <label>Truck/Loader Number</label>
                <input class="form-control" type="text" name="vehicle_no" value="<?= old('vehicle_no') ?>">
                <?php if ($validation->hasError('vehicle_no')): ?>
                    <span class="text-danger" style="color: red !important; display: block; font-size: 12px;"><?= $validation->getError('vehicle_no') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Chassis Number</label>
                <input class="form-control" type="text" name="chassis_no" value="<?= old('chassis_no') ?>">
                <?php if ($validation->hasError('chassis_no')): ?>
                    <span class="text-danger" style="color: red !important; display: block; font-size: 12px;"><?= $validation->getError('chassis_no') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Engine Number</label>
                <input class="form-control" type="text" name="engine_no" value="<?= old('engine_no') ?>">
                <?php if ($validation->hasError('engine_no')): ?>
                    <span class="text-danger" style="color: red !important; display: block; font-size: 12px;"><?= $validation->getError('engine_no') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Fitness Expiry Date</label>
                <input class="form-control" type="date" name="fitness_exp_date" value="<?= old('fitness_exp_date') ?>">
                <?php if ($validation->hasError('fitness_exp_date')): ?>
                    <span class="text-danger" style="color: red !important; display: block; font-size: 12px;"><?= $validation->getError('fitness_exp_date') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Fitness Amount</label>
                <input class="form-control" type="text" name="fitness_amount" value="<?= old('fitness_amount') ?>">
                <?php if ($validation->hasError('fitness_amount')): ?>
                    <span class="text-danger" style="color: red !important; display: block; font-size: 12px;"><?= $validation->getError('fitness_amount') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Road Tax Expiry Date</label>
                <input class="form-control" type="date" name="tax_exp_date" value="<?= old('tax_exp_date') ?>">
                <?php if (isset($validation) && is_object($validation) && $validation->hasError('tax_exp_date')): ?>
                    <div class="text-danger small"><?= $validation->getError('tax_exp_date') ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Road Tax Amount</label>
                <input class="form-control" type="text" name="road_tax_amount" value="<?= old('road_tax_amount') ?>">
                <?php if (isset($validation) && is_object($validation) && $validation->hasError('road_tax_amount')): ?>
                    <div class="text-danger small"><?= $validation->getError('road_tax_amount') ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Insurance Company</label>
                <input class="form-control" type="text" name="ins_company" value="<?= old('ins_company') ?>">
                <?php if (isset($validation) && is_object($validation) && $validation->hasError('ins_company')): ?>
                    <div class="text-danger small"><?= $validation->getError('ins_company') ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Insurance Expiry Date</label>
                <input class="form-control" type="date" name="ins_exp_date" value="<?= old('ins_exp_date') ?>">
                <?php if (isset($validation) && is_object($validation) && $validation->hasError('ins_exp_date')): ?>
                    <div class="text-danger small"><?= $validation->getError('ins_exp_date') ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Insurance Amount</label>
                <input class="form-control" type="text" name="Insurance_Amount" value="<?= old('Insurance_Amount') ?>">
                <?php if (isset($validation) && is_object($validation) && $validation->hasError('Insurance_Amount')): ?>
                    <div class="text-danger small"><?= $validation->getError('Insurance_Amount') ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="example-nf-email">PUCC</label>
                <input class="form-control" type="date" name="Pucc" id="Pucc" value="<?= old('Pucc') ?>">
                <?php if (isset($validation) && is_object($validation) && $validation->hasError('Pucc')): ?>
                    <div class="text-danger small"><?= $validation->getError('Pucc') ?></div>
                <?php endif; ?>
            </div>
                        
            <div class="form-group">
                <label for="example-nf-email">PUCC Amount </label>
                <input class="form-control" type="text" name="Pucc_amount" id="Pucc_amount" value="<?= old('Pucc_amount') ?>">
                <?php if (isset($validation) && is_object($validation) && $validation->hasError('Pucc_amount')): ?>
                    <div class="text-danger small"><?= $validation->getError('Pucc_amount') ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="example-nf-email">Finance company/ Funding bank  </label>
                <input class="form-control" type="text" name="finance" id="finance" value="<?= old('finance') ?>">
                <?php if (isset($validation) && is_object($validation) && $validation->hasError('finance')): ?>
                    <div class="text-danger small"><?= $validation->getError('finance') ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="example-nf-email">Deduct Amount</label>
                <input class="form-control" type="text" name="deduct_Amount" id="deduct_Amount" value="<?= old('deduct_Amount') ?>">
                <?php if (isset($validation) && is_object($validation) && $validation->hasError('deduct_Amount')): ?>
                    <div class="text-danger small"><?= $validation->getError('deduct_Amount') ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="example-nf-email">Account from EMI deducted</label>
                <input class="form-control" type="text" name="emi_account" id="emi_account" value="<?= old('emi_account') ?>">
                <?php if (isset($validation) && is_object($validation) && $validation->hasError('emi_account')): ?>
                    <div class="text-danger small"><?= $validation->getError('emi_account') ?></div>
                <?php endif; ?>
            </div>

        </div>

        <!-- TRUCK ONLY FIELDS -->
        <div id="truck_fields" style="display:none;">

            <div class="uk-child-width-1-2@m uk-grid-small" uk-grid>

                <div class="form-group">
                    <label>Permit Expiry Date</label>
                <input class="form-control" type="date" name="permit_exp_date" value="<?= old('permit_exp_date') ?>">
                <?php if (isset($validation) && is_object($validation) && $validation->hasError('permit_exp_date')): ?>
                    <div class="text-danger small"><?= $validation->getError('permit_exp_date') ?></div>
                <?php endif; ?>
            </div>

                <div class="form-group">
                    <label>Permit Amount</label>
                <input class="form-control" type="text" name="Permit_Amount" value="<?= old('Permit_Amount') ?>">
                <?php if (isset($validation) && is_object($validation) && $validation->hasError('Permit_Amount')): ?>
                    <div class="text-danger small"><?= $validation->getError('Permit_Amount') ?></div>
                <?php endif; ?>
            </div>

                <div class="form-group">
                    <label>National Permit Expiry Date</label>
                <input class="form-control" type="date" name="npermit_exp_date" value="<?= old('npermit_exp_date') ?>">
                <?php if (isset($validation) && is_object($validation) && $validation->hasError('npermit_exp_date')): ?>
                    <div class="text-danger small"><?= $validation->getError('npermit_exp_date') ?></div>
                <?php endif; ?>
            </div>

                <div class="form-group">
                    <label>National Permit Amount</label>
                <input class="form-control" type="text" name="nPermit_Amount" value="<?= old('nPermit_Amount') ?>">
                <?php if (isset($validation) && is_object($validation) && $validation->hasError('nPermit_Amount')): ?>
                    <div class="text-danger small"><?= $validation->getError('nPermit_Amount') ?></div>
                <?php endif; ?>
            </div>

                <div class="form-group">
                    <label>Horse Make</label>
                    <input class="form-control" type="text" name="horsemake" value="<?= old('horsemake') ?>">
                    <?php if (isset($validation) && is_object($validation) && $validation->hasError('horsemake')): ?>
                        <div class="text-danger small"><?= $validation->getError('horsemake') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Horse Model</label>
                    <input class="form-control" type="text" name="HorseModel" value="<?= old('HorseModel') ?>">
                    <?php if (isset($validation) && is_object($validation) && $validation->hasError('HorseModel')): ?>
                        <div class="text-danger small"><?= $validation->getError('HorseModel') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Horse Rate</label>
                    <input class="form-control" type="text" name="HorseRate" value="<?= old('HorseRate') ?>">
                    <?php if (isset($validation) && is_object($validation) && $validation->hasError('HorseRate')): ?>
                        <div class="text-danger small"><?= $validation->getError('HorseRate') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Dala Rate</label>
                    <input class="form-control" type="text" name="DalaRate" value="<?= old('DalaRate') ?>">
                    <?php if (isset($validation) && is_object($validation) && $validation->hasError('DalaRate')): ?>
                        <div class="text-danger small"><?= $validation->getError('DalaRate') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Dala Make</label>
                    <input class="form-control" type="text" name="DalaMake" value="<?= old('DalaMake') ?>">
                    <?php if (isset($validation) && is_object($validation) && $validation->hasError('DalaMake')): ?>
                        <div class="text-danger small"><?= $validation->getError('DalaMake') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>RTO Expenses</label>
                    <input class="form-control" type="text" name="RTOExpenses" value="<?= old('RTOExpenses') ?>">
                    <?php if (isset($validation) && is_object($validation) && $validation->hasError('RTOExpenses')): ?>
                        <div class="text-danger small"><?= $validation->getError('RTOExpenses') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>AMC</label>
                    <input class="form-control" type="text" name="amc" value="<?= old('amc') ?>">
                    <?php if (isset($validation) && is_object($validation) && $validation->hasError('amc')): ?>
                        <div class="text-danger small"><?= $validation->getError('amc') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>AMC Frequency</label>
                    <input class="form-control" type="text" name="amc_frequency" value="<?= old('amc_frequency') ?>">
                    <?php if (isset($validation) && is_object($validation) && $validation->hasError('amc_frequency')): ?>
                        <div class="text-danger small"><?= $validation->getError('amc_frequency') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>AMC Monthly Amount</label>
                    <input class="form-control" type="text" name="amcamount" value="<?= old('amcamount') ?>">
                    <?php if (isset($validation) && is_object($validation) && $validation->hasError('amcamount')): ?>
                        <div class="text-danger small"><?= $validation->getError('amcamount') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>AMC Expiry Date</label>
                    <input class="form-control" type="date" name="amc_expary" value="<?= old('amc_expary') ?>">
                    <?php if (isset($validation) && is_object($validation) && $validation->hasError('amc_expary')): ?>
                        <div class="text-danger small"><?= $validation->getError('amc_expary') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>I3MS Expiry</label>
                    <input class="form-control" type="date" name="I3MSexpairy" value="<?= old('I3MSexpairy') ?>">
                    <?php if (isset($validation) && is_object($validation) && $validation->hasError('I3MSexpairy')): ?>
                        <div class="text-danger small"><?= $validation->getError('I3MSexpairy') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>I3MS Recharge</label>
                    <input class="form-control" type="text" name="I3MSRECHARGE" value="<?= old('I3MSRECHARGE') ?>">
                    <?php if (isset($validation) && is_object($validation) && $validation->hasError('I3MSRECHARGE')): ?>
                        <div class="text-danger small"><?= $validation->getError('I3MSRECHARGE') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>KHANIJ Expiry</label>
                    <input class="form-control" type="date" name="KHANIJEXPIRI" value="<?= old('KHANIJEXPIRI') ?>">
                    <?php if (isset($validation) && is_object($validation) && $validation->hasError('KHANIJEXPIRI')): ?>
                        <div class="text-danger small"><?= $validation->getError('KHANIJEXPIRI') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>KHANIJ Amount</label>
                    <input class="form-control" type="text" name="khanij_amount" value="<?= old('khanij_amount') ?>">
                    <?php if (isset($validation) && is_object($validation) && $validation->hasError('khanij_amount')): ?>
                        <div class="text-danger small"><?= $validation->getError('khanij_amount') ?></div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- Location -->
        <div class="uk-margin-bottom">
            <label>Location Name</label>
            <select class="form-control" name="location_name" required>
                <option value="">Select Location</option>
                <?php foreach ($locations as $loc) { ?>
                    <option value="<?= $loc->location_id ?>" <?= old('location_name') == $loc->location_id ? 'selected' : '' ?>><?= $loc->location_name ?></option>
                <?php } ?>
            </select>
            <?php if ($validation->hasError('location_name')): ?>
                <span class="text-danger" style="color: red !important; display: block; font-size: 12px;"><?= $validation->getError('location_name') ?></span>
            <?php endif; ?>
        </div>

        <!-- Remark -->
        <div class="form-group">
            <label>Remark</label>
            <input class="form-control" type="text" name="remark">
        </div>

        <!-- Document -->
        <div class="form-group">
            <label>Document</label>
            <input type="file" name="document" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>

    </div>
</form>



</div>
    </div>
</div>
                    
                    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- JS to toggle truck/loader -->
<script>
$(document).ready(function() {
    function toggleTruckFields() {
        var type = $('#vehicle_type').val();
        if (type === "1") {
            $('#truck_fields').show();
        } else {
            $('#truck_fields').hide();
        }
    }

    // Initial check
    toggleTruckFields();

    // On change
    $('#vehicle_type').on('change', function() {
        toggleTruckFields();
    });
});
</script>
<script>
    function editvehicle(id) {
        $.ajax({
            url: '<?php echo base_url(); ?>/Admin/edit_vehicle', // Replace with your controller method URL
            type: 'POST',
            data: { vehicle_id: id },
            success: function(response) {
                // Assuming 'response' is a JSON object containing vehicle data
                $('#edit_vehicle_form').html(response); // Populate your form with the response data
                
                // Open the UIkit off-canvas
                UIkit.offcanvas('#edit_vehicle').show();
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
</script>

    
    
    

<div id="edit_vehicle" uk-offcanvas="flip: true; overlay: true">
    <div class="uk-offcanvas-bar uk-padding-remove uk-margin-remove uk-width-1-2" style="background:#fff">

        <button class="uk-offcanvas-close" type="button" uk-close></button>
        <div class="uk-card uk-card-body uk-card-small uk-card-default">
            <div id="edit_vehicle_form"></div>
        </div>
    </div>
</div>
    
               
<script>
    $(document).ready(function() {
        // Get today's date
        var today = new Date();

        // Loop through each row in the DataTable
        $('#row_create tbody tr').each(function() {
            // Find the date cell within each row (adjust this selector based on your actual structure)
            var fitnessExpiryCell = $(this).find('td:eq(4)'); // Fitness Expiry Date cell index, adjust as needed
            var taxExpiryCell = $(this).find('td:eq(6)'); // Tax Expiry Date cell index, adjust as needed
            var insExpiryCell = $(this).find('td:eq(9)'); // Insurance Expiry Date cell index, adjust as needed
            var permitExpiryCell = $(this).find('td:eq(11)'); // Permit Expiry Date cell index, adjust as needed
            var nPermitExpiryCell = $(this).find('td:eq(13)'); // National Permit Expiry Date cell index, adjust as needed
            var amcExpiryCell = $(this).find('td:eq(25)'); // AMC Expiry Date cell index, adjust as needed
            var i3msExpiryCell = $(this).find('td:eq(27)'); // I3MS Expiry Date cell index, adjust as needed
            var khanijExpiryCell = $(this).find('td:eq(29)'); // Khanij Expiry Date cell index, adjust as needed

            // Process each date cell to check the condition
            checkAndHighlight(fitnessExpiryCell);
            checkAndHighlight(taxExpiryCell);
            checkAndHighlight(insExpiryCell);
            checkAndHighlight(permitExpiryCell);
            checkAndHighlight(nPermitExpiryCell);
            checkAndHighlight(amcExpiryCell);
            checkAndHighlight(i3msExpiryCell);
            checkAndHighlight(khanijExpiryCell);
        });

        // Function to check date and apply highlight if within 15 days
        function checkAndHighlight(cell) {
            var dateString = cell.text().trim();
            var dateParts = dateString.split('-');
            var expiryDate = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]); // Assuming d-m-Y format

            // Calculate the difference in days
            var diffDays = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));

            // Check if the expiry date is within 15 days from today
            if (diffDays <= 15 && diffDays >= 0) {
                cell.addClass('highlight-red');
            }
        }
    });
</script>

<style>
    .highlight-red {
        background-color: #ffcccc; /* Light red background */
    }
</style>

        <!-- footer start-->
       <?php include("footer.php");?>
       
       <?php if (isset($validation) && is_object($validation) && $validation->getErrors()): ?>
       <script>
           $(document).ready(function() {
               UIkit.offcanvas('#addnewvehicle').show();
           });
       </script>
       <?php endif; ?>
