<?php include("header.php");?>
      <!-- Page Body Start-->
      <div class="page-body-wrapper" style= "background:#ececec;">
        <?php include("mainsidebar.php"); ?>
        <div class="page-body">
          <div class="container-fluid">        
            <div class="page-title">
              <div class="row">
                <div class="col-sm-6 p-0">
                  <h3>Extra Diesel Issue</h3>
                </div>
              </div>
            </div>
          </div>
          <!-- Container-fluid starts-->
          <div class="container-fluid default-dashboard">
            <div class="uk-grid-small" uk-grid>
                 <div class="uk-width-1-3@m">
                        <div class="uk-card uk-card-body uk-card-default uk-card-small ">
                           <form action="<?php echo base_url();?>/Admin/insert_extra_diesel" method="post">
                                <div class="form-group">
                                    <label>Select Vehicle</label>
                                    <select class="form-control" name="vehicle" id="single1" required>
                                        <option value="">Select Vehicle</option>
                                        <?php foreach($vehicle as $veh){?>
                                        <option value="<?=$veh->id?>"><?=$veh->vehicle_no?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Select Driver</label>
                                    <select class="form-control" name="driver" required>
                                        <option value="">Select Driver</option>
                                        <?php foreach($drivers as $dr){?>
                                        <option value="<?=$dr->id?>"><?=$dr->name?> (<?=$dr->staff_code?>)</option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="date" class="form-control" name="date" value="<?php echo date("Y-m-d"); ?>" required/>
                                </div>
                                <div class="form-group">
                                    <label>QTY (Litres)</label>
                                    <input type="number" step="0.01" name="qty" class="form-control" required/>
                                </div>
                                <div class="form-group">
                                    <label>Issued By</label>
                                    <select class="form-control" name="issued_by" required>
                                        <option value="">Select User</option>
                                        <?php foreach($issuers as $u){?>
                                        <option value="<?=$u->id?>"><?=$u->full_name?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="form-group uk-margin-top">
                                    <button class="btn btn-primary" type="submit">Submit</button>
                                </div>
                           </form>
                        </div>
               </div>
        <div class="uk-width-2-3@m">
             <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-bottom">
                           <form method="post" action="<?php echo base_url(); ?>/Admin/extra_diesel">
                                <div class="uk-grid-small uk-child-width-expand@m" uk-grid>
                                    <div>
                                        <label>From Date:</label>
                                        <input type="date" name="from_date" class="uk-input" value="<?= $filter_date['from_date'] ?? date('Y-m-01'); ?>" />
                                    </div>
                                    <div>
                                        <label>To Date:</label>
                                        <input type="date" name="to_date" class="uk-input" value="<?= $filter_date['to_date'] ?? date('Y-m-d'); ?>" />
                                    </div>
                                    <div>
                                        <label>Vehicle:</label>
                                        <select name="filter_vehicle" class="uk-select">
                                            <option value="">All Vehicles</option>
                                            <?php foreach($vehicle as $v){ ?>
                                            <option value="<?=$v->id?>" <?=($filter_date['vehicle_id']==$v->id)?'selected':''?>><?=$v->vehicle_no?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label>Driver:</label>
                                        <select name="filter_driver" class="uk-select">
                                            <option value="">All Drivers</option>
                                            <?php foreach($drivers as $d){ ?>
                                            <option value="<?=$d->id?>" <?=($filter_date['driver_id']==$d->id)?'selected':''?>><?=$d->name?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="uk-width-auto" style="padding-top: 25px;">
                                        <button type="submit" class="uk-button uk-button-primary">Filter</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
            <div class="uk-card uk-card-body uk-card-default uk-card-small">
                <div class="table-responsive">
                    <table class="display" id="row_create" style="width:100%">
                        <thead>
                            <tr>
                                <th>Sl no</th>
                                <th>Date</th>
                                <th>Vehicle No</th>
                                <th>Driver Name</th>
                                <th>Qty</th>
                                <th>Issued By</th>
                                <th>Remarks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i=1;
                            foreach($extra_diesel as $row){?>
                            <tr>
                                <td><?=$i++;?></td>
                                <td><?=date('d-m-Y', strtotime($row->issue_date))?></td>
                                <td><?=$row->truck_no;?></td>
                                <td><?=$row->driver_name;?></td>
                                <td><?=$row->qty;?></td>
                                <td><?=$row->issued_by_name;?></td>
                                <td><?=$row->remarks;?></td>
                                <td>
                                    <a href="javascript:void(0);" onClick="editRecord('<?= $row->id ; ?>');" class="btn btn-primary btn-xs">Edit</a>
                                    <a href="javascript:void(0);" onClick="deleteRecord('<?= $row->id ; ?>');" class="btn btn-danger btn-xs">Delete</a>
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

    <form name="frm_delete" id="frm_delete" action="<?php echo base_url();?>/Admin/delete_extra_diesel" method="post">
        <input type="hidden" name="id" id="delete_id" value="">
    </form>
    
    <script type="text/javascript">
    function deleteRecord(id){
    	$("#delete_id").val(id);
    	if(confirm("Are you sure want to delete this record?")){
    	   $("#frm_delete").submit();
    	}
    }
    </script> 

    <!-- Edit Modal -->
    <div id="modal-edit" uk-modal>
        <div class="uk-modal-dialog uk-modal-body">
            <button class="uk-modal-close-default" type="button" uk-close></button>
            <h2 class="uk-modal-title">Edit Extra Diesel Issue</h2>
            <div id="edit-form-content"></div>
        </div>
    </div>

    <script>
    function editRecord(id) {
        $.ajax({
            url: '<?= base_url('Admin/edit_extra_diesel'); ?>',
            type: 'POST',
            data: { id: id },
            success: function(response) {
                $('#edit-form-content').html(response);
                UIkit.modal('#modal-edit').show();
            }
        });
    }
    </script>
<?php include("footer.php");?>
