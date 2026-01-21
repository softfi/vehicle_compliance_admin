<?php include("header.php"); ?>
<!-- Page Body Start-->
<div class="page-body-wrapper" style="background:#ececec;">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Items </h3>
                    </div>
                    <div class="col-sm-6 p-0">
                        <div>
                            <label for="download_excel">.</label>
                            <button class="btn btn-primary uk-align-right" type="button" id="download_excel" style="margin: 25px 20px 0px 30px;">Download Excel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid starts-->
        <div class="container-fluid default-dashboard">
            <div class="uk-grid-small" uk-grid>
                <div class="uk-width-1-3@m">
                    <div class="uk-card uk-card-body uk-card-default uk-card-small ">
                        <form action="<?php echo base_url(); ?>/Admin/insert_items" enctype="multipart/form-data" method="post">
                            <div class="uk-margin-bottom">
                                <lable>Item Code </lable>
                                <input type="text" name="item_id" placeholder="enter Item Code" id="item_id" class="uk-input" value="<?= set_value('item_id') ?>" />
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('item_id'); ?></span><?php } ?>
                            </div>
                            <div class="uk-margin-bottom">
                                <lable>Item Name </lable>
                                <input type="text" name="item_name" placeholder="enter Item Name" id="item_name" class="uk-input" value="<?= set_value('item_name') ?>" />
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('item_name'); ?></span><?php } ?>
                            </div>
                            <div class="uk-margin-bottom">
                                <lable>Unit Of Measurement </lable>

                                <select class="uk-select" name="unit_of_measurement">
                                    <option value="">Select</option>
                                    <?php foreach ($units as $unit) { ?>
                                        <option value="<?= $unit->unit_id; ?>"><?= $unit->unit_name; ?></option>
                                    <?php  } ?>
                                </select>
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('unit_of_measurement'); ?></span><?php } ?>
                            </div>
                            <div class="uk-margin-bottom">
                                <lable>Avg Price Rate</lable>
                                <input type="number" name="avg_price_rate" placeholder="enter avg. price rate" id="avg_price_rate" class="uk-input" value="<?= set_value('avg_price_rate') ?>" />
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('avg_price_rate'); ?></span><?php } ?>
                            </div>
                            <div class="uk-margin-bottom">
                                <lable>Amount</lable>
                                <input type="text" name="amount" placeholder="enter  amount " id="amount" class="uk-input" value="<?= set_value('amount') ?>" />
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('amount'); ?></span><?php } ?>
                            </div>
                            

                            <div class="uk-margin-bottom">
                                <lable>Upload Photo </lable>
                                <input type="file" name="upload_photo" placeholder="Upload ur Photo" id="upload_photo" class="uk-input" value="<?= set_value('upload_photo') ?>" />
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('upload_photo'); ?></span><?php } ?>
                            </div>
                            <div class="uk-margin-bottom">
                                <?php if (in_array(17.1, $jobAssign)) { ?>
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                <?php } ?>
                            </div>
                        </form>
                        <h3>Enter Item Master</h3>
                        <hr>

                        <a href="<?php echo base_url(); ?>/sampleexcel/items_entry.xlsx" target="_blank">click here</a> for sample excel
                        <form action="<?php echo base_url(); ?>/Admin/excel_items" method="post" enctype="multipart/form-data">
                            <div class="uk-margin-bottom">

                                <input type="file" name="file" id="file" class="form-control" accept=".csv, .xlsx">
                            </div>
                            <div class="uk-margin-bottom">
                                <?php if (in_array(17.2, $jobAssign)) { ?>
                                    <button type="submit" class="btn btn-primary">Upload Excel</button>
                                <?php } ?>
                            </div>
                        </form>

                    </div>
                </div>
                <div class="uk-width-expand@m">
                    <div class="uk-card uk-card-body uk-card-default uk-card-small ">
                        <div class="table-responsive custom-scrollbar custom-scrollbar">
                            <table class="display" id="row_create" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Sl no</th>
                                        <th>item code</th>
                                        <th>Item Name</th>
                                        <th>Unit </th>
                                        <th>Avg Price Rate</th>
                                        <th>Amount</th>
                                        <th>Edit</th>
                                        <th>Delete</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php
                                    $i = 1;
                                    foreach ($items as $itm) { ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= $itm->item_id; ?></td>
                                            <td><?= $itm->item_name ?></td>
                                            <td><?= $itm->unit_name ?></td>
                                            <td><?= $itm->avg_price_rate ?></td>
                                            <td><?= $itm->amount ?></td>

                                            <td>
                                                <?php if (in_array(17.4, $jobAssign)) { ?>
                                                    <a href="javascript:void(0);" onClick="edit_items('<?= $itm->id; ?>');" class="btn btn-primary">Edit</a>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?php if (in_array(17.5, $jobAssign)) { ?>
                                                    <a href="<?php echo base_url(); ?>/Admin/delete_items/<?= $itm->id ?>" class="btn btn-danger">Delete</a>
                                                <?php } ?>
                                            </td>
                                            <td></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Sl no</th>
                                        <th>item code</th>
                                        <th>Item Name</th>
                                        <th>Unit </th>
                                        <th>Avg Price Rate</th>
                                        <th>Amount</th>
                                        <!--<th>Opening Stock</th>-->
                                        <!--<th>Location</th>-->
                                        <th>Edit</th>
                                        <th>Delete</th>
                                        <th></th>
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
</div>


<script>
    function edit_items(id) {
        // Send AJAX request to get item data
        $.ajax({
            url: '<?= base_url(); ?>/Admin/edit_item_data', // Update with your actual URL
            type: 'GET',
            data: {
                id: id
            },
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
<script>
    document.getElementById('download_excel').addEventListener('click', function() {
        const baseUrl = '<?php echo base_url(); ?>/AditionalAdminPart/download_excel_item';
        const url = `${baseUrl}`;
        window.location.href = url;
    });
</script>
<?php include("footer.php"); ?>