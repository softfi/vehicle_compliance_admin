<?php include("header.php"); ?>
<!-- Page Body Start-->
<style>
 #myTable thead th {
        position: sticky;
        top: 0;
        background: white; /* Or match your table background */
        z-index: 10;
        border-bottom: 1px solid #ddd; /* optional for better visibility */
    }
    #searchInput{
        width: 500px;
    }
</style>
<div class="page-body-wrapper" style="background:#ececec;">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Do Registration</h3>
                    </div>
                    <div class="col-sm-6 p-0">
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid starts-->
        <div class="container-fluid default-dashboard">
            <div class="uk-grid-small" uk-grid>
                <div class="uk-width-1-1@m">
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                        <form action="<?php echo base_url(); ?>/Admin/insert_do_registration" enctype="multipart/form-data" method="post">
                            <div class="uk-child-width-1-5@m uk-grid-small " uk-grid>
                            <div class="">
                                <label>DO No </label>
                                <input type="text" name="do_no" placeholder="enter DO No" id="do_no" class="uk-input" value="<?= set_value('do_no') ?>" />
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('do_no'); ?></span><?php } ?>
                            </div>
                            <div class="">
                                <label>Route </label>
                                <select class="js-states form-control uk-padding-reove uk-margin-remove" name="route" id="single">
                                    <option value="">Select Route</option>
                                    <?php foreach ($route as $rut) { ?>
                                    <option value="<?= $rut->id ?>">(<?= $rut->location_shortname ?>) <?= $rut->from_city ?> === <?= $rut->to_city ?></option>
                                    <?php } ?>
                                </select>
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('route'); ?></span><?php } ?>
                            </div>
                            <div class="">
                                <label>Diesel</label>
                                <select class="js-states form-control uk-padding-reove uk-margin-remove" name="diesel_type" id="diesel_type">
                                    <option value="">Select Diesel Trip/Km</option>
                                    <option value="Trip">Trip</option>
                                    <option value="Km">Km</option>
                                </select>
                            </div>
                            <div class="">
                                <label>Value</label>
                               <input type="number" name="diesel" class="form-control" value="<?= set_value('diesel') ?>" />
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('diesel'); ?></span><?php } ?>
                            </div>
                            
                             <div class="">
                                <label>From Date </label>
                                <input type="date" name="from_date" placeholder="Upload from date" id="from_date" class="uk-input" value="<?= set_value('from_date') ?>" />
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('from_date'); ?></span><?php } ?>
                            </div>
                            <div class="">
                                <label>To date </label>
                                <input type="date" name="to_date" placeholder="Upload from date" id="to_date" class="uk-input" value="<?= set_value('to_date') ?>" />
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('to_date'); ?></span><?php } ?>
                            </div>
                             <div class="">
                                <label>Party</label>
                                <select class="js-states form-control uk-padding-reove uk-margin-remove" name="party" id="single1">
                                    <option value="">Select Party</option>
                                    <?php foreach ($partyNames as $pn) { ?>
                                    <option value="<?= $pn->name . ' (' . $pn->id . ')' ?>"><?= $pn->name ?></option>
                                    <?php } ?>
                                </select>
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('party'); ?></span><?php } ?>
                            </div>
                            
                                <div class="" id="load_tonnage_group">
                                    <label>Tonnage Set</label>
                                    <select class="js-states form-control uk-padding-reove uk-margin-remove" name="load_tonnage" id="load_tonnage">
                                        <option value="">Select Set</option>
                                        <?php if(isset($sets)){ foreach ($sets as $s) { ?>
                                        <option value="<?= $s->id ?>" <?= set_select('load_tonnage', $s->id) ?>><?= $s->set_name ?></option>
                                        <?php } } ?>
                                    </select>
                                </div>
                                
                                <div class="trip_expenses_field">
                                    <label>1=Trip Expenses</label>
                                    <input type="text"  placeholder="enter 1Trip Expenses" id="trip_expenses1" class="uk-input" name="1trip_expenses" value="<?= set_value('1trip_expenses') ?>" />
                                    <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('1trip_expenses'); ?></span><?php } ?>
                                </div>
                                
                                <div class="trip_expenses_field">
                                    <label>2=Trip Expenses</label>
                                    <input type="text"  placeholder="enter 2 Trip Expenses" id="trip_expenses2" class="uk-input" name="2trip_expenses" value="<?= set_value('2trip_expenses') ?>" />
                                    <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('2trip_expenses'); ?></span><?php } ?>
                                </div>
                                
                                <div class="trip_expenses_field">
                                    <label>3=Trip Expenses</label>
                                    <input type="text"  placeholder="enter 3 Trip Expenses" id="trip_expenses3" class="uk-input" name="3trip_expenses" value="<?= set_value('3trip_expenses') ?>" />
                                    <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('3trip_expenses'); ?></span><?php } ?>
                                </div>
                                
                                <div class="trip_expenses_field">
                                    <label>4=Trip Expenses</label>
                                    <input type="text"  placeholder="enter 4Trip Expenses" id="trip_expenses4" class="uk-input" name="4trip_expenses" value="<?= set_value('4trip_expenses') ?>" />
                                    <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('4trip_expenses'); ?></span><?php } ?>
                                </div>
                                
                                <div class="trip_expenses_field">
                                    <label>5=Trip Expenses</label>
                                    <input type="text"  placeholder="enter 5 Trip Expenses" id="trip_expenses5" class="uk-input" name="5trip_expenses" value="<?= set_value('5trip_expenses') ?>" />
                                    <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('5trip_expenses'); ?></span><?php } ?>
                                </div>
                                
                                <div class="trip_expenses_field">
                                    <label>6=Trip Expenses</label>
                                    <input type="text"  placeholder="enter 6 Trip Expenses" id="trip_expenses6" class="uk-input" name="6trip_expenses" value="<?= set_value('6trip_expenses') ?>" />
                                    <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('6trip_expenses'); ?></span><?php } ?>
                                </div>
                           
                            <div class="">
                                <label>Cash</label>
                                <select class="js-states form-control uk-padding-reove uk-margin-remove" name="cash_type" id="cash_type">
                                    <option value="">Select Cash Type</option>
                                    <option value="Own" <?= set_select('cash_type', 'Own') ?>>Own</option>
                                    <option value="Party" <?= set_select('cash_type', 'Party') ?>>Party</option>
                                </select>
                            </div>
                            <div class="">
                                <label>Diesel</label>
                                <select class="js-states form-control uk-padding-reove uk-margin-remove" name="diesel_payment_type" id="diesel_payment_type">
                                    <option value="">Select Diesel Source</option>
                                    <option value="Own" <?= set_select('diesel_payment_type', 'Own') ?>>Own</option>
                                    <option value="Party" <?= set_select('diesel_payment_type', 'Party') ?>>Party</option>
                                </select>
                            </div>
                            <div class="">
                                <label>Diesel Rate</label>
                                <input type="number" step="0.01" name="diesel_rate" placeholder="Enter Diesel Rate" id="diesel_rate" class="uk-input" value="<?= set_value('diesel_rate') ?>" />
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('diesel_rate'); ?></span><?php } ?>
                            </div>
                             <div class="">
                                <label>Party Rate </label>
                                <input type="number" name="rate" placeholder="Enter Rate" id="rate" class="uk-input" value="<?= set_value('rate') ?>" />
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $validation->getError('rate'); ?></span><?php } ?>
                            </div>
                            <div class="">
                                <label>TDS Rate (%)</label>
                                <input type="text" class="uk-input" value="2" disabled />
                            </div>
                            <div class="">
                                <label>Shortage Qty</label>
                                <input type="number" step="0.01" name="shortage_qty" id="shortage_qty" placeholder="Enter Shortage Qty" class="uk-input" value="<?= set_value('shortage_qty') ?>" />
                            </div>
                            <div class="">
                                <label>Shortage Rate</label>
                                <input type="number" step="0.01" name="shortage_rate" id="shortage_rate" placeholder="Enter Shortage Rate" class="uk-input" value="<?= set_value('shortage_rate') ?>" />
                            </div>
                            <div class="" style="display: flex; align-items: center; margin-top: 25px;">
                                <input type="checkbox" name="special_shortage" id="special_shortage" class="uk-checkbox" value="1" <?= set_checkbox('special_shortage', '1') ?> />
                                <label for="special_shortage" style="margin-left: 10px; margin-top: 0;">Special Shortage</label>
                            </div>
                            <div class="">
                                 <?php if(in_array(2.1,$jobAssign)){ ?>
                                <button style="margin-top:35px" type="submit" class="btn btn-primary">Submit</button>
                                <?php } ?>
                            </div>
                               <div>
                                        <label for="download_excel">.</label>
                                        <a href="#" class="uk-button uk-button-primary uk-width-1-1" id="download_excel">Download Excel</a>
                                    </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="uk-width-expand@m">
                    <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-top">
                        <input type="text" id="searchInput" class="form-control mb-3" placeholder="Search DO, Party, Route, etc...">
                        <div style="max-height: 400px; overflow-y: auto;">
                            <table id="myTable" class="uk-table uk-table-small uk-table-divider" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Sl no</th>
                                        <th>DO No</th>
                                        <th>Route</th>
                                        <th>Diesel/Trip OR Km</th>
                                        <th>Trip Expenses</th>
                                        <th>Load Tonnage</th>
                                        <!-- <th>Amount</th> -->
                                        <th>Cash</th>
                                        <th>Diesel</th>
                                        <th>Shortage Qty</th>
                                        <th>Shortage Rate</th>
                                        <th>Spl Shortage</th>
                                        <th>Party</th>
                                        <th>From Date</th>
                                        <th>To Date</th>
                                        <th>Party Rate</th>
                                        <th>Diesel Rate</th>
                                        <!-- <th>Add Shortage</th> -->
                                        <th>Change</th>
                                        <th>Edit Price</th>
                                        <th>Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i=1;
                                    foreach ($doregistration as $doreg){ ?>
                                    <tr>
                                        <td><?=$i++;?></td>
                                        <td><?=$doreg->do_no;?></td>
                                        <td><?=$doreg->location_shortname;?></td>
                                        <td><?=$doreg->diesel_type;?></td>
                                        <td>
                                            1<sup>st</sup>=Rs. <?=$doreg->trip_expenses1;?>, 2<sup>nd</sup>=Rs.<?=$doreg->trip_expenses2;?>, <br> 
                                            3<sup>rd</sup>=Rs. <?=$doreg->trip_expenses3;?>, 4<sup>th</sup>=Rs.<?=$doreg->trip_expenses4;?>,<br>  
                                            5<sup>th</sup>=Rs. <?=$doreg->trip_expenses5;?>, 6<sup>th</sup>=Rs. <?=$doreg->trip_expenses6;?>
                                        </td>
                                        <td>
                                            <?= $doreg->tonnage_set_name ?? '-'; ?>
                                        </td>
                                        <td><?= isset($doreg->cash_type) ? $doreg->cash_type : '-'; ?></td>
                                        <td><?= isset($doreg->diesel_payment_type) ? $doreg->diesel_payment_type : '-'; ?></td>
                                        <td><?= isset($doreg->shortage_qty) ? $doreg->shortage_qty : '-'; ?></td>
                                        <td><?= isset($doreg->shortage_rate) ? $doreg->shortage_rate : '-'; ?></td>
                                        <td><?= (isset($doreg->special_shortage) && $doreg->special_shortage == 1) ? 'Yes' : 'No'; ?></td>
                                        <td><?=$doreg->party;?></td>
                                        <td><?=$doreg->from_date;?></td>
                                        <td><?=$doreg->to_date;?></td>
                                        <td><?=$doreg->rate;?></td>
                                        <td><?=$doreg->diesel_rate ?? '-';?></td>
                                        
                                        <!-- <td>
                                            <a style="white-space:nowrap" class="btn btn-primary" href="javascript:void(0);" onClick="addshortage('<?=$doreg->do_registration_id;?>');">Add Shortage</a>
                                        </td> -->
                                        <td>
                                           <?php if(in_array(2.2,$jobAssign)){ ?>
                                            <a style="white-space:nowrap"  class="btn btn-success" href="javascript:void(0);" onClick="changedoprice('<?=$doreg->do_registration_id;?>');">Change Price</a>
                                            <?php } ?>
                                            </td>
                                        <td>
                                            <?php if(in_array(2.3,$jobAssign)){ ?>
                                            <a  class="btn btn-warning" href="javascript:void(0);" onClick="editdoregistration('<?=$doreg->do_registration_id;?>');">Edit</a>
                                            <?php }?>

                                            </td>
                                        <td>
                                            <?php if(in_array(2.4,$jobAssign)){ ?>
                                            <a style="white-space:nowrap" href="javascript:void(0);" onClick="deleteRecord('<?=$doreg->do_registration_id;?>');" class="btn btn-danger">Delete</a>
                                            <?php }?>
                                            </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Sl no</th>
                                        <th>DO No</th>
                                        <th>Route</th>
                                        <th>Diesel/Trip OR Km</th>
                                        <th>Trip Expenses</th>
                                        <th>Load Tonnage</th>
                                        <!-- <th>Amount</th> -->
                                        <th>Cash</th>
                                        <th>Diesel</th>
                                        <th>Shortage Qty</th>
                                        <th>Shortage Rate</th>
                                        <th>Spl Shortage</th>
                                        <th>Party</th>
                                        <th>From Date</th>
                                        <th>To Date</th>
                                        <th>Party Rate</th>
                                        <th>Diesel Rate</th>    
                                        <!-- <th>Add Shortage</th> -->
                                        <th>Change</th>
                                        <th>Edit</th>
                                        <th>Delete</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    document.getElementById("searchInput").addEventListener("keyup", function () {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll("#myTable tbody tr");
    
        rows.forEach(function(row) {
            row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
        });
    });
</script>
<script>
    function editdoregistration(id) {
        $.ajax({
            url: '<?php echo base_url(); ?>/Admin/edit_doregistration', // Replace with your controller method URL
            type: 'POST',
            data: { doreg_id: id },
            success: function(response) {
                // Assuming 'response' is a JSON object containing vehicle data
                $('#edit_do_form').html(response); // Populate your form with the response data
                
                // Open the UIkit off-canvas
                UIkit.offcanvas('#edit_doreg').show();
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
</script>



<script>
    function changedoprice(id) {
        $.ajax({
            url: '<?php echo base_url(); ?>/Admin/changeprice_doregistration', // Replace with your controller method URL
            type: 'POST',
            data: { doreg_id: id },
            success: function(response) {
                // Assuming 'response' is a JSON object containing vehicle data
                $('#edit_do_form').html(response); // Populate your form with the response data
                
                // Open the UIkit off-canvas
                UIkit.offcanvas('#edit_doreg').show();
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
</script>


    <script>
    function addshortage(id) {
        $.ajax({
            url: '<?php echo base_url(); ?>/Admin/addshortage', // Replace with your controller method URL
            type: 'POST',
            data: { doreg_id: id },
            success: function(response) {
                // Assuming 'response' is a JSON object containing vehicle data
                $('#edit_do_form').html(response); // Populate your form with the response data
                
                // Open the UIkit off-canvas
                UIkit.offcanvas('#edit_doreg').show();
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
</script>
    

<div id="edit_doreg" uk-offcanvas="flip: true; overlay: true">
    <div class="uk-offcanvas-bar uk-padding-remove uk-margin-remove uk-width-1-3@m" style="background:#fff">

        <button class="uk-offcanvas-close" type="button" uk-close></button>
        <div class="uk-card uk-card-body uk-card-small uk-card-default">
            <div id="edit_do_form"></div>
        </div>
    </div>
</div>


<form name="frm_deleteBanner" id="frm_deleteBanner" action="<?php echo base_url();?>/admin/delete_doregistration" method="post">
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
<!-- footer start-->

<style>
    .default-dashboard div.dataTables_wrapper table.dataTable tbody > tr > td {
  
  white-space: inherit;
}
</style>
<script>
document.getElementById('download_excel').addEventListener('click', function(e) {
    e.preventDefault(); // prevent default anchor behavior
    const url = '<?= base_url(); ?>/Admin/download_doRegistration_excel';
    window.location.href = url;
});
</script>


<script>
    $(document).ready(function() {
        // Tonnage Set selection logic (if needed in future)
    });
</script>
<?php include("footer.php"); ?>
