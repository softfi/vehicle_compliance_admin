<?php include("header.php"); ?>
<!-- Page Body Start-->
<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">        
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>In House Maintenance</h3>
                    </div>
                    <div class="col-sm-6 p-0">
                        <?php if (in_array(5.1, $jobAssign)) { ?>
                            <a class="btn btn-primary" style="float:right;" href="<?php echo base_url(); ?>/Admin/inhouse_maintenance">Add New</a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Container-fluid starts -->
        <div class="container-fluid">
            <!-- Filter form and buttons -->
            <div class="uk-width-2-3@m">
                <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-bottom">
                    <form method="post" action="<?php echo base_url(); ?>/Admin/add_inhouse">
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
                                <label>Location</label>
                                <select name="location" id="single" class="form-control">
                                    <option value="">Select location</option>
                                    <?php foreach ($location as $loc) { ?>
                                        <option value="<?= $loc->location_id; ?>"
                                            <?= (!empty($selected_location_id) && $loc->location_id == $selected_location_id) ? 'selected' : '' ?>>
                                            <?= $loc->location_name; ?>
                                        </option>
                                    <?php } ?>
                                </select>
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

            <!-- Table to display data -->
            <div class="uk-card uk-card-body uk-card-default uk-card-small">
                <table class="display" id="row_create" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vehicle No</th>
                            <th>Driver Name</th>
                            <th>Item Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Remark</th>
                            <th>Check By</th>
                            <th>Location Name</th>
                            <th>Total Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        foreach ($inhousedtls as $row):?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?php echo $row->vehicle_no; ?></td>
                                <td><?php echo $row->driver_name; ?></td>
                                <td><?php echo $row->item_name; ?></td>
                                <td><?php echo $row->date; ?></td>
                                <td><?php echo $row->time; ?></td>
                                <td><?php echo $row->invoiceno; ?></td>
                                <td><?php echo $row->check_by; ?></td>
                                <td><?php echo $row->location_name; ?></td>
                                <td><?php echo $row->price*$row->qty; ?></td>
                                <td>
                                    <?php if (in_array(5.2, $jobAssign)) { ?>
                                        <a class="btn btn-success" href="<?php echo base_url(); ?>/Admin/edit_inhouse/<?php echo $row->order_id; ?>">Edit</a>
                                    <?php } ?>
                                    <?php if (in_array(5.3, $jobAssign)) { ?>
                                        <a class="btn btn-primary" href="javascript:void(0);" onClick="viewitems('<?php echo $row->order_id; ?>');">View</a>
                                        <a class="btn btn-danger" href="<?php echo base_url(); ?>/Admin/delete_inhouse/<?php echo $row->order_id; ?>">Delete</a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Vehicle No</th>
                            <th>Driver Name</th>
                            <th>Item Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Invoice No</th>
                            <th>Check By</th>
                            <th>Location Name</th>
                            <th>Total Amount</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <!-- Container-fluid Ends-->
    </div>
</div>

<!-- JavaScript for AJAX view and Excel download -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    function viewitems(id) {
        $.ajax({
            url: '<?php echo base_url(); ?>/Admin/billing_details',
            type: 'POST',
            data: { orderid_id: id },
            success: function(response) {
                $('#edit_vehicle_form').html(response);
                UIkit.offcanvas('#edit_vehicle').show();
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    document.getElementById('download_excel').addEventListener('click', function() {
        const fromDate = document.getElementById('from_date').value;
        const toDate = document.getElementById('to_date').value;
        const location = document.getElementById('single').value;

        const baseUrl = '<?php echo base_url(); ?>/Admin/downloadInhouseExcel';

        const url = `${baseUrl}?from_date=${fromDate}&to_date=${toDate}&location=${location}`;

        window.location.href = url;
    });

</script>

<!-- Offcanvas for view items -->
<div id="edit_vehicle" uk-offcanvas="flip: true; overlay: true">
    <div class="uk-offcanvas-bar uk-padding-remove uk-margin-remove uk-width-1-2" style="background:#fff">
        <button class="uk-offcanvas-close" type="button" uk-close></button>
        <div class="uk-card uk-card-body uk-card-small uk-card-default">
            <div id="edit_vehicle_form"></div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>
