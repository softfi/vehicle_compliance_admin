<?php include("header.php"); ?>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Page Body Start-->
<div class="page-body-wrapper" style="background:#f9f9f9;">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Tyre Transfer</h3>
                    </div>
                    <div class="col-sm-6 p-0"></div>
                </div>
            </div>
        </div>
        <!-- Container-fluid starts-->
        <div class="container-fluid default-dashboard">
            <form name="add_name" id="add_inhouse_maintenance" action="<?php echo base_url();?>/Admin/update_tyer_details" method="post">
                <div class="uk-card uk-card-body uk-card-small" style="border:solid 1px #ccc;">
                    <div class="uk-grid-small uk-child-width-expand@m uk-grid" uk-grid="">
                        <div>
                            <label>From Location</label>
                            <label>To Location</label>
                            <select name="from_location" id="single" class="form-control" onchange="locationChanged(this.value)">
                                <option value="">Select location</option>
                                <?php foreach ($location as $loc) { ?>
                                    <option value="<?= $loc->location_id; ?>" <?= set_select('location', $loc->location_id); ?>><?= $loc->location_name; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div>
                            <label>To Location</label>
                            <select name="to_location" class="form-control" id="single2">
                                <option value="">Select location</option>
                                <?php foreach ($location as $loc) { ?>
                                    <option value="<?= $loc->location_id; ?>" <?= set_select('location', $loc->location_id); ?>><?= $loc->location_name; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                         <div>
                            <label>Date</label>
                            <input type="date" id="date" name="date" class="form-control" value="<?= set_value('date'); ?>">
                        </div>
                    </div>
                </div>
            
                <p>&nbsp;</p>
            
                <div class="uk-grid-small uk-child-width-expand@m uk-grid" uk-grid="">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <table class="table table-bordered table-hover" id="dynamic_field">
                                    <tr>
                                        <td>1</td>
                                        <td>
                                            <select name="tyer_sl_no[]" class="form-control tyer_sl_no" onchange="tyerDetails(this)" id="single1">
                                                <option value="">Select Tyer Sl No</option>
                                            </select>
                                        </td>
                                        <td><input type="text" name="tyer_brand[]" placeholder="Enter Tyer Brand" class="form-control" value="<?= set_value('tyer_brand[0]'); ?>" /></td>
                                        <td><input type="text" name="tyer_model[]" placeholder="Enter Tyer Model" class="form-control" value="<?= set_value('tyer_model[0]'); ?>" /></td>
                                        <td><button type="button" name="add" id="add" class="btn btn-primary">Add More</button></td>
                                    </tr>
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
    var i = 1;

    function updateSerialNumbers() {
        var sl_no = 1;
        $('#dynamic_field tr').each(function () {
            $(this).find('.sl_no').text(sl_no);
            sl_no++;
        });
    }

    $('#add').click(function () {
        var firstRow = $('#dynamic_field tr:first');
        var tyerSlNo = firstRow.find('.tyer_sl_no').val();
        var tyerBrand = firstRow.find('input[name="tyer_brand[]"]').val();
        var tyerModel = firstRow.find('input[name="tyer_model[]"]').val();

        if (tyerSlNo || tyerBrand || tyerModel) {
            i++;
            var newRow = $(
                '<tr id="row' + i + '">' +
                    '<td class="sl_no">' + i + '</td>' +
                    '<td><input type="text" name="tyer_sl_no[]" class="form-control" value="' + tyerSlNo + '" readonly /></td>' +
                    '<td><input type="text" name="tyer_brand[]" class="form-control" value="' + tyerBrand + '" readonly /></td>' +
                    '<td><input type="text" name="tyer_model[]" class="form-control" value="' + tyerModel + '" readonly /></td>' +
                    '<td><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove">X</button></td>' +
                '</tr>'
            );
            $('#dynamic_field').append(newRow);

            // Clear first row
            firstRow.find('.tyer_sl_no').val('');
            firstRow.find('input[name="tyer_brand[]"]').val('');
            firstRow.find('input[name="tyer_model[]"]').val('');

            updateSerialNumbers();
        } else {
            alert('Please fill in tyre details before adding.');
        }
    });

    $(document).on('click', '.btn_remove', function () {
        var button_id = $(this).attr("id");
        $('#row' + button_id).remove();
        updateSerialNumbers();
    });

    function pad(number) {
        return number < 10 ? '0' + number : number;
    }
    var now = new Date();
    $('#date').val(now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()));
});

function locationChanged(locationId) {
    console.log(locationId);
    if (locationId) {
        $.ajax({
            url: "<?= base_url(); ?>/Admin/get_tyers_by_location",
            type: "POST",
            data: { location_id: locationId },
            dataType: "json",
            success: function (data) {
                $('.tyer_sl_no').each(function () {
                    var dropdown = $(this);
                    dropdown.empty().append('<option value="">Select Tyer Sl No</option>');
                    $.each(data, function (key, tyre) {
                        dropdown.append('<option value="' + tyre.tyer_sl_no + '">' + tyre.tyer_sl_no + '</option>');
                    });
                });
            }
        });
    } else {
        $('.tyer_sl_no').html('<option value="">Select Tyer Sl No</option>');
    }
}

function tyerDetails(inputElem) {
    var tyerSlNo = $(inputElem).val();
    var row = $(inputElem).closest('tr');
    if (tyerSlNo) {
        $.ajax({
            url: "<?= base_url(); ?>/Admin/get_tyer_details",
            type: "POST",
            data: { tyer_sl_no: tyerSlNo },
            dataType: "json",
            success: function (data) {
                if (data) {
                    row.find('input[name="tyer_brand[]"]').val(data.brand_name);
                    row.find('input[name="tyer_model[]"]').val(data.tyer_model);
                } else {
                    row.find('input[name="tyer_brand[]"]').val('');
                    row.find('input[name="tyer_model[]"]').val('');
                }
            }
        });
    } else {
        row.find('input[name="tyer_brand[]"]').val('');
        row.find('input[name="tyer_model[]"]').val('');
    }
}
</script>
















