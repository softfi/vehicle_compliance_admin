<?php include("header.php");?>
      <!-- Page Body Start-->
      <div class="page-body-wrapper" style= "background:#ececec;">
        <?php include("mainsidebar.php"); ?>
        <div class="page-body">
          <div class="container-fluid">        
            <div class="page-title">
              <div class="row">
                <div class="col-sm-6 p-0">
                  <h3>Diesel Rate Master</h3>
                </div>
              </div>
            </div>
          </div>
          <!-- Container-fluid starts-->
          <div class="container-fluid default-dashboard">
            <div class="uk-grid-small" uk-grid>
                 <div class="uk-width-1-3@m">
                        <div class="uk-card uk-card-body uk-card-default uk-card-small ">
                           <form action="<?php echo base_url();?>/Admin/insert_diesel_rate" method="post">
                                <div class="form-group">
                                    <label>From Date</label>
                                    <input type="date" class="form-control" name="from_date" required/>
                                </div>
                                <div class="form-group">
                                    <label>To Date</label>
                                    <input type="date" class="form-control" name="to_date" required/>
                                </div>
                                <div class="form-group">
                                    <label>Rate (per Litre)</label>
                                    <input type="number" step="0.01" name="rate" class="form-control" required/>
                                </div>
                                <div class="form-group uk-margin-top">
                                    <button class="btn btn-primary" type="submit">Submit</button>
                                </div>
                           </form>
                        </div>
               </div>
        <div class="uk-width-2-3@m">
            <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-bottom">
                 <form method="post" action="<?php echo base_url(); ?>/Admin/diesel_rate">
                    <div class="uk-grid-small uk-child-width-expand@m" uk-grid>
                        <div>
                            <label>From Date:</label>
                            <input type="date" name="from_date" class="uk-input" value="<?= $filter_date['from_date'] ?? ''; ?>" />
                        </div>
                        <div>
                            <label>To Date:</label>
                            <input type="date" name="to_date" class="uk-input" value="<?= $filter_date['to_date'] ?? ''; ?>" />
                        </div>
                        <div class="uk_width_auto" style="padding-top: 25px;">
                            <button type="submit" class="uk-button uk-button-primary">Filter</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="uk-card uk-card-body uk-card-default uk-card-small">
                <div class="table-responsive">
                    <table class="display" id="rate_table" style="width:100%">
                        <thead>
                            <tr>
                                <th>Sl no</th>
                                <th>From Date</th>
                                <th>To Date</th>
                                <th>Rate</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i=1;
                            foreach($rates as $row){?>
                            <tr>
                                <td><?=$i++;?></td>
                                <td><?=date('d-m-Y', strtotime($row->from_date))?></td>
                                <td><?=date('d-m-Y', strtotime($row->to_date))?></td>
                                <td><?=$row->rate;?></td>
                                <td>
                                    <a href="javascript:void(0);" onClick="editRate('<?=$row->id?>')" class="btn btn-primary btn-xs">Edit</a>
                                    <a href="<?php echo base_url();?>/Admin/delete_diesel_rate/<?=$row->id?>" class="btn btn-danger btn-xs" onClick="return confirm('Are you sure?')">Delete</a>
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

    <!-- Edit Modal -->
    <div id="modal-edit-rate" uk-modal>
        <div class="uk-modal-dialog uk-modal-body">
            <button class="uk-modal-close-default" type="button" uk-close></button>
            <h2 class="uk-modal-title">Edit Diesel Rate</h2>
            <div id="edit-form-content-rate"></div>
        </div>
    </div>

    <script>
    function editRate(id) {
        $.ajax({
            url: '<?= base_url('Admin/edit_diesel_rate'); ?>',
            type: 'POST',
            data: { id: id },
            success: function(response) {
                $('#edit-form-content-rate').html(response);
                UIkit.modal('#modal-edit-rate').show();
            }
        });
    }
    </script>
<?php include("footer.php");?>
