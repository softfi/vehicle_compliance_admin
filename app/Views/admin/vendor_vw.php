<?php include("header.php"); ?>
<!-- Page Body Start-->
<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    <?php foreach ($singleuser as $singledata) {
    } ?>

    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Vendor/party </h3>
                    </div>
                    <div class="col-sm-6 p-0">
                        <div>
                            <label for="download_excel">.</label>
                            <button class="btn btn-primary uk-align-right" type="button" id="download_excel" style="margin: 25px 20px 0px 30px;">Download Excel</button>
                        </div>
                        <?php if (in_array(16.2, $jobAssign)) { ?>
                            <button class="btn btn-primary uk-align-right" type="button" uk-toggle="target: #offcanvas-flip">Add Vendor/Party</button>
                        <?php } ?>
                        <?php if (in_array(16.1, $jobAssign)) { ?>
                            <button class="btn btn-primary" style="float:right" type="button" data-bs-toggle="modal" data-bs-target="#uploadexcel" data-whatever="@getbootstrap">Upload EXCEL</button>
                        <?php } ?>
                        <div id="offcanvas-flip" uk-offcanvas="flip: true; overlay: true">
                            <div class="uk-offcanvas-bar uk-padding-remove uk-margin-remove uk-width-1-2@m" style="background:#fff;">

                                <button class="uk-offcanvas-close" type="button" uk-close></button>
                                <div class="uk-card uk-card-body uk-card-default uk-card-samall">
                                    <form action="<?php echo base_url(); ?>/Admin/AddVendor" enctype="multipart/form-data" method="post">
                                        <div class="modal-body">
                                            <div class="row">

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Name</label>
                                                        <input type="text" class="form-control" name="name" placeholder="Enter Your Name" value="<?= set_value('name') ?>">
                                                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('name'); ?></span><?php } ?>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>GST</label>
                                                        <input type="text" class="form-control" id="gst" name="gst" placeholder="Enter GST number" value="<?= set_value('gst') ?>">
                                                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('gst'); ?></span><?php } ?>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Location</label>
                                                        <select class="form-control" name="location">
                                                            <option value="">Select Location</option>
                                                            <?php foreach ($location as $loc) { ?>
                                                                <option value="<?= $loc->location_id ?>"><?= $loc->location_name ?></option>
                                                            <?php } ?>
                                                        </select>
                                                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('location'); ?></span><?php } ?>
                                                    </div>
                                                </div>



                                                <div class="col-sm-6">
                                                    <label>Party/Pump/Vender</label>
                                                    <select class="form-control" aria-label="Select" name="type">
                                                        <option value="Party" <?= set_value('type') == 'Party' ? 'selected' : '' ?>>PARTY</option>
                                                        <option value="Pump" <?= set_value('type') == 'Pump' ? 'selected' : '' ?>>PUMP</option>
                                                        <option value="Vender" <?= set_value('type') == 'Vendor' ? 'selected' : '' ?>>VENDOR</option>
                                                    </select>
                                                    <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('type'); ?></span><?php } ?>
                                                </div>



                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>PAN No</label>
                                                        <input type="text" class="form-control" id="panNo" name="pan" placeholder="Enter your PAN No." value="<?= set_value('pan') ?>">
                                                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('pan'); ?></span><?php } ?>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Opening Bal</label>
                                                        <input type="number" class="form-control" id="bal" name="bal" placeholder="Enter your opening balance" value="<?= set_value('bal') ?>">
                                                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('bal'); ?></span><?php } ?>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>From Date</label>
                                                        <input type="date" class="form-control" id="fromdate" name="fromdate" placeholder="Enter your from date" value="<?= set_value('fromdate') ?>">
                                                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('fromdate'); ?></span><?php } ?>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label>Rate</label>
                                                        <input type="text" class="form-control" id="rate" name="rate" placeholder="Rate" value="<?= set_value('rate') ?>">
                                                        <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('rate'); ?></span><?php } ?>
                                                    </div>
                                                </div>



                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>
                                    </form>

                                </div>

                            </div>
                        </div>


                    </div>
                </div>
                <!-- Container-fluid starts-->
                <div class="container-fluid default-dashboard">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="card ">

                            </div>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Container-fluid starts-->

            <!-- Container-fluid starts-->
            <div class="container-fluid default-dashboard">





                <div class="uk-grid-small uk-child-width-expand@m" uk-grid>


                    <div>
                        <div class="uk-card uk-card-body uk-card-default uk-card-small">

                            <div class="uk-width-1-4@m">
                                Select type
                                <select id="typeFilter" name="type" class="form-control">
                                    <!--<option value="">Select Type</option>-->
                                    <option value="Party">Party</option>
                                    <option value="Pump">Pump</option>
                                    <option value="Vendor">Vendor</option>
                                </select>
                            </div>
                            <div id="result">
                                <div class="table-responsive">
                                    <table class="display" id="row_create" style="width:100%">

                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Vendor Code</th>
                                                <th>GST</th>
                                                <th>Type</th>
                                                <th>Location</th>
                                                <th>PAN</th>
                                                <th>Balance</th>
                                                <th>From Date</th>
                                                <th>Rate</th>
                                                <th>
                                                    Action
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $i = 1;
                                            foreach ($allvendor as $vendor) { ?>
                                                <tr>
                                                    <td><?= $i++ ?></td>
                                                    <td><?= $vendor->name; ?></td>
                                                    <td><?= $vendor->vendor_code; ?></td>
                                                    <td><?= $vendor->gst; ?></td>
                                                    <td><?= $vendor->type; ?></td>
                                                    <td><?= $vendor->location_name; ?></td>
                                                    <td><?= $vendor->pan; ?></td>
                                                    <td><?= $vendor->bal; ?></td>
                                                    <td><?= $vendor->from_date; ?></td>
                                                    <td><?= $vendor->vendor_rate; ?></td>
                                                    <td>
                                                        <div class="uk-button-group">
                                                            <?php if (in_array(16.3, $jobAssign)) { ?>
                                                                <a href="javascript:void(0);" onClick="editvendor('<?= $vendor->id; ?>');" class="uk-button uk-button-small uk-button-secondary">Edit</a>
                                                            <?php } ?>
                                                            <?php if (in_array(16.4, $jobAssign)) { ?>
                                                                <a href="javascript:void(0);" onClick="vendor_rate('<?= $vendor->id; ?>');" class="uk-button uk-button-small uk-button-primary">view rate</a>
                                                            <?php } ?>
                                                            <?php if (in_array(16.5, $jobAssign)) { ?>
                                                                <a href="<?= base_url('Admin/deletevendor/' . $vendor->id); ?>" onclick="return confirm('Are you sure you want to delete this item?');" class="uk-button uk-button-small uk-button-danger">delete</a>
                                                            <?php } ?>
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
                <!-- END Mini Top Stats Row -->


            </div>
            <!-- END Page Content -->





            <script>
                UIkit.modal('#modal-center<?= session()->getFlashdata('uid') ?>').show();
            </script>





            <?php if (isset($validation)) { ?>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        UIkit.offcanvas('#offcanvas-flip').show();
                    });
                </script>
            <?php } ?>




            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
            <script>
                function editvendor(id) {
                    $.ajax({
                        url: '<?= base_url(); ?>/Admin/edit_vendor', // Replace with your controller method URL
                        type: 'POST',
                        data: {
                            vendor_id: id
                        },
                        success: function(response) {
                            // Assuming 'response' is a JSON object containing vehicle data
                            $('#edit_vendor_form').html(response); // Populate your form with the response data

                            // Open the UIkit off-canvas
                            UIkit.offcanvas('#edit_vendor').show();
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }
            </script>






            <script>
                function vendor_rate(id) {
                    $.ajax({
                        url: '<?= base_url(); ?>/Admin/vendor_rate', // Replace with your controller method URL
                        type: 'POST',
                        data: {
                            vendor_id: id
                        },
                        success: function(response) {
                            // Assuming 'response' is a JSON object containing vehicle data
                            $('#vendor_rate_form').html(response); // Populate your form with the response data

                            // Open the UIkit off-canvas
                            UIkit.offcanvas('#vendor_rate').show();
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }
            </script>








            <div class="modal fade" id="uploadexcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalgetbootstrap" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <form action="<?= base_url('Admin/upload_vendor_excel') ?>" method="post" enctype="multipart/form-data">
                                <label for="file">Choose CSV or Excel File:</label><br>
                                <input type="file" name="file" id="file" class="form-control" accept=".csv, .xlsx">
                                <p><br></p>
                                <button class="btn btn-primary" type="submit">Upload</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>






            <div id="edit_vendor" uk-offcanvas="flip: true; overlay: true">
                <div class="uk-offcanvas-bar uk-padding-remove uk-margin-remove uk-width-1-2" style="background:#fff">

                    <button class="uk-offcanvas-close" type="button" uk-close></button>
                    <div class="uk-card uk-card-body uk-card-small uk-card-default">
                        <div id="edit_vendor_form"></div>
                    </div>
                </div>
            </div>


            <div id="vendor_rate" uk-offcanvas="flip: true; overlay: true">
                <div class="uk-offcanvas-bar uk-padding-remove uk-margin-remove uk-width-1-2" style="background:#fff">

                    <button class="uk-offcanvas-close" type="button" uk-close></button>
                    <div class="uk-card uk-card-body uk-card-small uk-card-default">
                        <div id="vendor_rate_form"></div>
                    </div>
                </div>
            </div>



        </div>
    </div>
</div>
</div>

<script>
    $(document).ready(function() {
        $('#typeFilter').change(function() {
            var selectedType = $(this).val();
            //alert (selectedType);

            $.ajax({
                url: '<?php echo base_url(); ?>/Admin/vendor_filter', // URL to your server-side script
                type: 'POST', // Use 'GET' if you prefer
                data: {
                    type: selectedType
                },
                success: function(response) {
                    // Assuming response contains the HTML
                    $('#result').html(response);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ', status, error);
                    $('#result').html('<p>An error occurred while fetching the data.</p>');
                }
            });
        });
    });
</script>
<script>
    document.getElementById('download_excel').addEventListener('click', function() {
        const baseUrl = '<?php echo base_url(); ?>/AditionalAdminPart/download_excel_vendor';
        const url = `${baseUrl}`;
        F
        window.location.href = url;
    });
</script>
<?php include("footer.php"); ?>