<?php include("header.php");?>
      <!-- Page Body Start-->
      <div class="page-body-wrapper">
        <?php include("mainsidebar.php"); ?>
        <div class="page-body" style="background:#f9f9f9;">
          <div class="container-fluid">        
            <div class="page-title">
              <div class="row">
                <div class="col-sm-6 p-0">
                  <h3>Regular Checkup </h3>
                </div>
                <div class="col-sm-6 p-0">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">
                        <svg class="stroke-icon">
                          <use href="https://admin.pixelstrap.net/dunzo/<?php echo base_url();?>/assets/admin/svg/icon-sprite.svg#stroke-home"></use>
                        </svg></a></li>
                    <li class="breadcrumb-item">Regular Checkup</li>
                    <li class="breadcrumb-item active">Default      </li>
                  </ol>
                </div>
              </div>
            </div>
          </div>
          <!-- Container-fluid starts-->
          <style>
            .select2-container--default .select2-selection--single {
                height: 38px !important;
                border: 1px solid #ced4da !important;
                padding-top: 5px;
            }
            .select2-container {
                display: block !important;
            }
          </style>
          <div class="container-fluid default-dashboard">
            <div class="uk-grid-small uk-child-width-expand" uk-grid>
                <div class="uk-width-1-4">
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                    <form action="<?php echo base_url(); ?>/Admin/submit_vehicle_maintenance" method="post">
            <div class="uk-grid-small" uk-grid>
                <input type="hidden" name="checkup_type" id="checkup_type" value="Regular">
                <div class="uk-width-1-1 uk-margin-bottom-small">
                    <label>Date</label>
                    <input type="date" name="date" class="uk-input" required>
                </div>
                
                <div class="uk-width-1-1 uk-margin-bottom-small">
                    <label>Vehicle No.</label>
                    <select name="vehicle_no" class="uk-select" id="single" required>
                        <option value="">Select Vehicle No.</option>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <option value="<?= $vehicle->id; ?>"><?= $vehicle->vehicle_no; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="uk-width-1-2@s uk-margin-bottom-small regular-field">
                    <label>Engine Oil</label>
                    <input type="checkbox" name="dengine_oil" value='1' class="uk-checkbox"/>
                    <input type="text" name="engine_oil" class="uk-input">
                </div>
                <div class="uk-width-1-2@s uk-margin-bottom-small regular-field">
                    <label>Gear Oil</label>
                    <input type="checkbox" name="dgear_oil" value='1'  class="uk-checkbox">
                    <input type="text" name="gear_oil" class="uk-input">
                </div>
                <div class="uk-width-1-2@s uk-margin-bottom-small regular-field">
                    <label>Crown Oil</label>
                    <input type="checkbox" name="dcrown_oil" value='1' class="uk-checkbox">
                    <input type="text" name="crown_oil" class="uk-input">
                </div>
                <div class="uk-width-1-2@s uk-margin-bottom-small regular-field">
                    <label>Coolent</label>
                    <input type="checkbox" name="dcoolent" value='1' class="uk-checkbox">
                    <input type="text" name="coolent" class="uk-input">
                </div>
                <div class="uk-width-1-2@s uk-margin-bottom-small regular-field">
                    <label>Break Oil</label>
                    <input type="checkbox" name="dbreak_oil" value='1'  class="uk-checkbox">
                    <input type="text" name="break_oil" class="uk-input">
                </div>
                <div class="uk-width-1-2@s uk-margin-bottom-small regular-field">
                    <label>Stell Wat</label>
                    <input type="checkbox" name="dstell_wat" value='1' class="uk-checkbox">
                    <input type="text" name="stell_wat" class="uk-input">
                </div>
                <div class="uk-width-1-2@s uk-margin-bottom-small regular-field">
                    <label>Clutch Oil</label>
                    <input type="checkbox" name="dclutch_oil" value='1'  class="uk-checkbox">
                    <input type="text" name="clutch_oil" class="uk-input">
                </div>
                     <div class="uk-width-1-2@s uk-margin-bottom-small regular-field">
                    <label>Ubolt</label>
                    <input type="checkbox" name="Ubolt" value='1'  class="uk-checkbox">
                    <input type="text" name="clutch_oil" class="uk-input">
                </div>
                <div class="uk-width-1-2@s uk-margin-bottom-small regular-field">
                    <label>Distil Water</label>
                    <input type="checkbox" name="distil_water" value='1'  class="uk-checkbox">
                    <input type="text" name="clutch_oil" class="uk-input">
                </div>
                <div class="uk-width-1-2@s uk-margin-bottom-small regular-field">
                    <label>Hydraulic Oil</label>
                    <input type="checkbox" name="dhydrolic_oil" value='1' class="uk-checkbox">
                    <input type="text" name="hydrolic_oil" class="uk-input">
                </div>
                <div class="uk-width-1-2@s uk-margin-bottom-small regular-field">
                    <label>Greasing</label>
                     <input type="checkbox" name="dgreasing" value='1' class="uk-checkbox">
                    <input type="text" name="greasing" class="uk-input">
                </div>
                <div class="uk-width-1-2@s uk-margin-bottom-small regular-field">
                    <label>Tyre Air Check</label>
                    <input type="checkbox" name="dtyre_air_check" value='1' class="uk-checkbox">
                    <input type="text" name="tyre_air_check" class="uk-input">
                </div>
                <div class="uk-width-1-2@s uk-margin-bottom-small regular-field">
                    <label>Brake Adjustment</label>
                    <input type="checkbox" name="dbrake_adjustment" value='1'  class="uk-checkbox">
                    <input type="text" name="brake_adjustment" class="uk-input">
                </div>

                <div class="uk-width-1-1 uk-margin-bottom-small">
                    <label>Remark</label>
                    <textarea name="remark" class="uk-textarea"></textarea>
                </div>
                <div class="uk-width-1-1 uk-margin-bottom-small">
                    <label>Mechanic Name</label>
                    <select name="mechanic_name" id="mechanic_id" class="form-control select2-search" required>
                        <option value="">Select Mechanic</option>
                        <?php foreach($mechanics as $mechanic): ?>
                            <option value="<?= $mechanic->name; ?>"><?= $mechanic->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="uk-width-1-1 uk-margin-bottom-small">
                    <label>Checked By</label>
                    <select name="checked_by" id="checked_by" class="form-control select2-search" required>
                        <option value="">Select User</option>
                        <?php foreach($users as $user): ?>
                            <option value="<?= $user->id; ?> - <?= $user->full_name; ?>"><?= $user->id; ?> - <?= $user->full_name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="uk-width-1-1">
                    <?php if(in_array(9.1,$jobAssign)){ ?>
                    <button type="submit" class="uk-button uk-button-primary">Submit</button>
                    <?php }?>
                    <a href="<?php echo base_url(); ?>/sampleexcel/rc.pdf" target="_blank" download>Printout Download</a>
                </div>
            </div>
        </form>
                    </div>
                </div>
                <div>
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                        
    <script>
    function toggleCheckupFields() {
        let type = document.getElementById('checkup_type').value;
        let regularFields = document.querySelectorAll('.regular-field');
        if(type === 'Uria') {
            regularFields.forEach(f => f.style.display = 'none');
        } else {
            regularFields.forEach(f => f.style.display = 'block');
        }
    }
    // Initialize on load
    document.addEventListener("DOMContentLoaded", function() {
        toggleCheckupFields();
    });
    </script>
                               <div class="table-responsive custom-scrollbar custom-scrollbar">
                                   <table class="display" id="row_create" style="width:100%">
        <thead>
        <tr>
        <th>Date</th>
        <th>Type</th>
        <th>Mechanic Name</th>
        <th>Vehicle No.</th>
        <th>Engine Oil</th>
        <th>Gear Oil</th>
        <th>Crown Oil</th>
        <th>Coolant</th>
        <th>Brake Oil</th>
        <th>Steering Water</th>
        <th>Clutch Oil</th>
        <th>Ubolt</th>
        <th>Distil Water</th>
        <th>Hydraulic Oil</th>
        <th>Greasing</th>
        <th>Tyre Air Check</th>
        <th>Brake Adjustment</th>
        <th>Remark</th>
        <th>Checked By</th>
        <!--<th>Edit</th>-->
        <th>Delete</th>
    </tr>
    </thead>
    <tbody>
 <?php
// Loop through the array and display each record in a row
foreach ($regularcheckup as $record) {
    ?>
    <tr>
        <td><?= $record->date ?></td>
        <td><?= isset($record->checkup_type) ? $record->checkup_type : 'Regular' ?></td>
        <td><?= isset($record->mechanic_name) ? $record->mechanic_name : '' ?></td>
        <td><?= $record->vehicle_no ?></td>
        <td><input class="uk-checkbox" readonly  type="checkbox" <?php if($record->dengine_oil==1){echo "checked" ;}?>/> <?= $record->engine_oil ?></td>
        <td><input class="uk-checkbox" readonly  type="checkbox" <?php if($record->dgear_oil==1){echo "checked" ;}?>/> <?= $record->gear_oil ?></td>
        <td><input class="uk-checkbox" readonly  type="checkbox" <?php if($record->dcrown_oil==1){echo "checked" ;}?>/> <?= $record->gear_oil ?><?= $record->crown_oil ?></td>
        <td><input class="uk-checkbox" readonly  type="checkbox" <?php if($record->dcoolent==1){echo "checked" ;}?>/> <?= $record->gear_oil ?><?= $record->coolent ?></td>
        <td><input class="uk-checkbox" readonly  type="checkbox" <?php if($record->dbreak_oil==1){echo "checked" ;}?>/> <?= $record->gear_oil ?><?= $record->break_oil ?></td>
        <td><input class="uk-checkbox" readonly  type="checkbox" <?php if($record->dstell_wat==1){echo "checked" ;}?>/> <?= $record->gear_oil ?><?= $record->stell_wat ?></td>
        <td><input class="uk-checkbox" readonly  type="checkbox" <?php if($record->dclutch_oil==1){echo "checked" ;}?>/> <?= $record->gear_oil ?><?= $record->clutch_oil ?></td>
        <td><input class="uk-checkbox" readonly  type="checkbox" <?php if($record->dubolt==1){echo "checked" ;}?>/> <?= $record->gear_oil ?><?= $record->ubolt ?></td>
        <td><input class="uk-checkbox" readonly  type="checkbox" <?php if($record->ddistilwater==1){echo "checked" ;}?>/> <?= $record->gear_oil ?><?= $record->distilwater ?></td>
        <td><input class="uk-checkbox" readonly  type="checkbox" <?php if($record->dhydrolic_oil==1){echo "checked" ;}?>/> <?= $record->gear_oil ?><?= $record->hydrolic_oil ?></td>
        <td><input class="uk-checkbox" readonly  type="checkbox" <?php if($record->dgreasing==1){echo "checked" ;}?>/> <?= $record->gear_oil ?><?= $record->greasing ?></td>
        <td><input class="uk-checkbox" readonly  type="checkbox" <?php if($record->dtyre_air_check==1){echo "checked" ;}?>/> <?= $record->gear_oil ?><?= $record->tyre_air_check ?></td>
        <td><input class="uk-checkbox" readonly  type="checkbox" <?php if($record->dbrake_adjustment==1){echo "checked" ;}?>/> <?= $record->gear_oil ?><?= $record->brake_adjustment ?></td>
        <td><?= isset($record->remark) ? $record->remark : '' ?></td>
        <td><?= $record->checked_by ?></td>
        <!--<td><button class="btn btn-primary">Edit</button></td>-->
        <td><a href="<?php echo base_url(); ?>/Admin/delete_regularcheckup/<?= $record->id; ?>" class="btn btn-danger">Delete</a></td>
    </tr>
<?php 
}
?>
    </tbody>
    <tfoot>
    <tr>
        <th>Date</th>
        <th>Type</th>
        <th>Mechanic Name</th>
        <th>Vehicle No.</th>
        <th>Engine Oil</th>
        <th>Gear Oil</th>
        <th>Crown Oil</th>
        <th>Coolant</th>
        <th>Brake Oil</th>
        <th>Steering Water</th>
        <th>Clutch Oil</th>
        <th>Ubolt</th>
        <th>DistilWater</th>
        <th>Hydraulic Oil</th>
        <th>Greasing</th>
        <th>Tyre Air Check</th>
        <th>Brake Adjustment</th>
        <th>Remark</th>
        <th>Checked By</th>
        <!--<th>Edit</th>-->
        <th>Delete</th>
    </tr>
    </tfoot>
    </table>
 
                                    </div>
                        
                    </div>
                    
                </div>
            </div>
          </div>
          <!-- Container-fluid Ends-->
        </div>
        <style>
            table.dataTable [type="checkbox"] {width:30px; height:30px;}
        </style>
        <!-- footer start-->
       <?php include("footer.php");?>
       <script>
           $(document).ready(function() {
               $('#mechanic_id, #checked_by, #single').select2({
                   placeholder: "Select an option",
                   allowClear: true,
                   width: '100%'
               });
           });
       </script>
