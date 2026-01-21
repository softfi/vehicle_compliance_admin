<?php include("header.php"); ?>
<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">        
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Repair Report</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <!-- Add the 'table-searchable' class for search functionality -->
                        <table class="display table table-striped table-bordered" id="row_create" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Sl.No</th>
                                    <th>Tyre Sl. No</th>
                                    <th>Bill No</th>
                                    <th>Status</th>
                                    <th>Exchange Vendor Name</th>
                                    <th>Remark</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $i = 1;
                                foreach ($tyer_data as $report) {
                                    // Assign the appropriate label based on the status
                                    $statusLabel = '';
                                    if ($report->status == 4) {
                                        $statusLabel = 'Repair';
                                    }
                                ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= $report->tyer_sl_no ?></td>
                                        <td><?= $report->bill_no ?></td>
                                        <td><?= $statusLabel ?></td> <!-- Display the status label -->
                                        <td><?= $report->exchange_vendorname ?></td>
                                        <td><?= $report->remark ?></td>
                                        <td>
                                            <a class="uk-button uk-button-primary editBtn" 
                                               href="#modal-sections" uk-toggle
                                               data-tyer-id="<?= $report->id ?>"
                                               data-tyer-sl-no="<?= $report->tyer_sl_no ?>"
                                               data-vendor="<?= $report->exchange_vendorname ?>"
                                               data-location="<?= $report->location_id ?>"
                                               data-date="<?= $report->date ?>">
                                               Edit
                                            </a>
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
<div id="modal-sections" uk-modal>
    <div class="uk-modal-dialog">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <div class="uk-modal-header">
            <h2 class="uk-modal-title">Back To Stock</h2>
        </div>
        <div class="uk-modal-body">
            <form id="editForm" method="post" action="<?php echo base_url();?>/Admin/update_tyer_repair">
                <input type="hidden" name="id" id="tyer_id">
                <input type="hidden" name="tyer_sl_no" id="tyer_sl_no" value="">

                <div class="mb-3">
                    <label>Vendor</label>
                    <select class="form-control" name="vendor" id="vendor">
                        <option value="">Select Vendor</option>
                        <?php foreach($vendor as $v) { ?>
                            <option value="<?= $v->id ?>"><?= $v->name ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Location</label>
                    <select class="form-control" name="location" id="location">
                        <option value="">Select Location</option>
                        <?php foreach($location as $loc) { ?>
                            <option value="<?= $loc->location_id ?>"><?= $loc->location_name ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Date</label>
                    <input type="date" class="form-control" name="date" id="date">
                </div>
                <div class="uk-modal-footer uk-text-right">
                    <button class="uk-button uk-button-default uk-modal-close" type="button">Cancel</button>
                    <button type="submit" class="uk-button uk-button-primary" id="saveBtn">Save</button>
                </div>
            </form>
        </div>
        
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).on('click', '.editBtn', function() {
        let tyerId = $(this).data('tyer-id');
        let tyerSlNo = $(this).data('tyer-sl-no');  // ✅ Get the actual tyer_sl_no
        let vendor = $(this).data('vendor');
        let location = $(this).data('location');
        let date = $(this).data('date');
        
        // Set form values
        $('#tyer_id').val(tyerId);
        $('#tyer_sl_no').val(tyerSlNo);  // ✅ Use tyerSlNo instead of tyerId
        $('#vendor').val(vendor);
        $('#location').val(location);
        $('#date').val(date);
    });
</script>

<?php include("footer.php"); ?>
