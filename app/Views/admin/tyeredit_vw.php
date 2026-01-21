<?php include("header.php"); ?>

<?php 
if (isset($tyer_data) && !empty($tyer_data)) {
    $tyer = $tyer_data[0]; // Assuming you're fetching a single tyre record to edit
}
?>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>

<!-- Page Body Start-->
<div class="page-body-wrapper" style="background:#f9f9f9;">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Edit Tyre</h3>
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid starts-->
        <div class="container-fluid default-dashboard">
            <form action="<?php echo base_url(); ?>/Admin/update_tyer" method="post">
                <div class="uk-card uk-card-body uk-card-small" style="border:solid 1px #ccc;">
                    <div class="uk-grid-small uk-child-width-expand@m uk-grid" uk-grid="">
                        
                        <div>
                            <label>Select Vendor</label>
                            <select name="vendor_id" class="form-control">
                                <option value="">Select Vendor</option>
                                <?php foreach ($vendor as $ven) { ?>
                                    <option value="<?= $ven->id; ?>" <?= isset($tyer) && $tyer->vendor_id == $ven->id ? 'selected' : ''; ?>>
                                        <?= $ven->name; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div>
                            <label>Date</label>
                            <input type="date" id="date" name="date" value="<?= isset($tyer) ? $tyer->date : ''; ?>" class="form-control">
                        </div>

                        <div>
                            <label>Bill No</label>
                            <input type="text" name="billno" value="<?= isset($tyer) ? $tyer->bill_no : ''; ?>" class="form-control">
                        </div>

                        <div>
                            <label>Total Amount</label>
                            <input type="text" name="tamount" value="<?= isset($tyer) ? $tyer->price : ''; ?>" class="form-control">
                        </div>

                        <div>
                            <label>Select Tyre Brand</label>
                            <select name="brand_name" class="uk-select">
                                <option <?= isset($tyer) && $tyer->brand_name == "MRF" ? 'selected' : ''; ?> value="MRF">MRF</option>
                                <option <?= isset($tyer) && $tyer->brand_name == "CEAT" ? 'selected' : ''; ?> value="CEAT">CEAT</option>
                                <option <?= isset($tyer) && $tyer->brand_name == "Apollo" ? 'selected' : ''; ?> value="Apollo">Apollo</option>
                                <option <?= isset($tyer) && $tyer->brand_name == "JK Tyre" ? 'selected' : ''; ?> value="JK Tyre">JK Tyre</option>
                                <option <?= isset($tyer) && $tyer->brand_name == "Bridgestone" ? 'selected' : ''; ?> value="Bridgestone">Bridgestone</option>
                                <option <?= isset($tyer) && $tyer->brand_name == "Michelin" ? 'selected' : ''; ?> value="Michelin">Michelin</option>
                                <option <?= isset($tyer) && $tyer->brand_name == "Goodyear" ? 'selected' : ''; ?> value="Goodyear">Goodyear</option>
                                <option <?= isset($tyer) && $tyer->brand_name == "Continental" ? 'selected' : ''; ?> value="Continental">Continental</option>
                                <option <?= isset($tyer) && $tyer->brand_name == "Falken" ? 'selected' : ''; ?> value="Falken">Falken</option>
                                <option <?= isset($tyer) && $tyer->brand_name == "Other" ? 'selected' : ''; ?> value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label>Model</label>
                            <input type="text" name="model" value="<?= isset($tyer) ? $tyer->model : ''; ?>" class="uk-input" placeholder="Enter Model">
                        </div>

                        <div>
                            <label>Location</label>
                            <select name="location" class="form-control">
                                <option value="">Select location</option>
                                <?php foreach ($location as $loc) { ?>
                                    <option value="<?= $loc->location_id; ?>" <?= isset($tyer) && $tyer->location_id == $loc->location_id ? 'selected' : ''; ?>>
                                        <?= $loc->location_name; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>

                <p>&nbsp;</p>

                <div class="uk-grid-small uk-child-width-expand@m uk-grid" uk-grid="">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <table class="table table-bordered table-hover" id="dynamic_field">
                                    <?php 
                                    $i = 1;
                                    foreach ($tyer_data as $tyer_detail) { ?>
                                    <tr id="row<?= $i; ?>">
                                        <td><?= $i; ?></td>
                                        <td>
                                            <input type="hidden" name="tyer_id[]" value="<?= $tyer_detail->id; ?>" class="form-control" />
                                            <input type="text" name="tyer_sl_no[]" value="<?= $tyer_detail->tyer_sl_no; ?>" class="form-control" /></td>
                                        <td><input type="text" name="tyer_type[]" value="<?= $tyer_detail->tyer_type; ?>" class="form-control" /></td>
                                        <td>
                                            <?php if ($i == 1) { ?>
                                                <button type="button" name="add" id="add" class="btn btn-primary">Add More</button>
                                            <?php } else { ?>
                                                <a type="button"  href="<?= base_url('admin/delete_tyersingle/'.$tyer_detail->id) ?>" onclick="return confirm('Are you sure you want to delete this item?')"  class="btn btn-danger btn_remove">X</a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php $i++; } ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button class="btn btn-primary" type="submit">Submit</button>
            </form>
        </div>
        <!-- Container-fluid Ends-->
    </div>
    <!-- Footer start-->
    <?php include("footer.php"); ?>

<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
<script src='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js'></script>

<script>
    $(document).ready(function () {
        var i = <?= $i; ?>;

        function addRow() {
            i++;
            var newRow = $('<tr id="row' + i + '"><td>' + i + '</td><td><input type="hidden" name="tyer_id[]" value="0" class="form-control" /><input type="text" name="tyer_sl_no[]" placeholder="Enter Tyer Sl No" class="form-control" /></td><td><input type="text" name="tyer_type[]" placeholder="Enter Tyer Type" class="form-control" /></td><td><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove">X</button></td></tr>');
            $('#dynamic_field').append(newRow);
        }

        $('#add').click(function () {
            addRow();
        });

        $(document).on('click', '.btn_remove', function () {
            var button_id = $(this).attr("id");
            $('#row' + button_id).remove();
        });

        // Set the date input to the current date if it is empty
        if ($('#date').val() === '') {
            var now = new Date();
            var year = now.getFullYear();
            var month = (now.getMonth() + 1).toString().padStart(2, '0');
            var day = now.getDate().toString().padStart(2, '0');
            var currentDate = year + '-' + month + '-' + day;
            $('#date').val(currentDate);
        }
    });
</script>
