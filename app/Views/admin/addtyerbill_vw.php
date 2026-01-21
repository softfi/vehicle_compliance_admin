<?php include("header.php"); ?>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>

<!-- Page Body Start-->
<div class="page-body-wrapper" style="background:#f9f9f9;">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Add New tyer </h3>
                    </div>
                    <div class="col-sm-6 p-0"></div>
                </div>
            </div>
        </div>
        <!-- Container-fluid starts-->
        <div class="container-fluid default-dashboard">
            <form name="add_name" id="add_inhouse_maintenance" action="<?php echo base_url();?>/Admin/insert_tyer" method="post">
                <div class="uk-card uk-card-body uk-card-small" style="border:solid 1px #ccc;">
                    <div class="uk-grid-small uk-child-width-expand@m uk-grid" uk-grid="">
                        <div>
                            <label>Select Vendor</label>
                            <select name="vendor_id" id="single" class="form-control">
                                <option value="">Select Vendor</option>
                                <?php foreach ($vendor as $ven) { ?>
                                    <option value="<?= $ven->id; ?>" <?= set_select('vendor_id', $ven->id); ?>><?= $ven->name; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        
                        <div>
                            <label>Date</label>
                            <input type="date" id="date" name="date" class="form-control" value="<?= set_value('date'); ?>">
                        </div>
                        
                        <div>
                            <label>Bill No</label>
                            <input type="text" id="billno" name="billno" class="form-control" value="<?= set_value('billno'); ?>">
                        </div>
                        
                        <div>
                            <label>Total Amount</label>
                            <input type="text" id="tamount" name="tamount" class="form-control" value="<?= set_value('tamount'); ?>">
                        </div>
                        
                        <div>
                            <label>Select Tyre Brand</label>
                            <select id="brand_name" name="brand_name" class="uk-select">
                                <option value="MRF" <?= set_select('brand_name', 'MRF'); ?>>MRF</option>
                                <option value="CEAT" <?= set_select('brand_name', 'CEAT'); ?>>CEAT</option>
                                <option value="Apollo" <?= set_select('brand_name', 'Apollo'); ?>>Apollo</option>
                                <option value="JK Tyre" <?= set_select('brand_name', 'JK Tyre'); ?>>JK Tyre</option>
                                <option value="Bridgestone" <?= set_select('brand_name', 'Bridgestone'); ?>>Bridgestone</option>
                                <option value="Michelin" <?= set_select('brand_name', 'Michelin'); ?>>Michelin</option>
                                <option value="Goodyear" <?= set_select('brand_name', 'Goodyear'); ?>>Goodyear</option>
                                <option value="Continental" <?= set_select('brand_name', 'Continental'); ?>>Continental</option>
                                <option value="Falken" <?= set_select('brand_name', 'Falken'); ?>>Falken</option>
                                <option value="Other" <?= set_select('brand_name', 'Other'); ?>>Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label>Model</label>
                            <input type="text" id="model" name="model" class="uk-input" placeholder="Enter Model" value="<?= set_value('model'); ?>">
                        </div>
                        
                        <div>
                            <label>Location</label>
                            <select name="location" id="single" class="form-control">
                                <option value="">Select location</option>
                                <?php foreach ($location as $loc) { ?>
                                    <option value="<?= $loc->location_id; ?>" <?= set_select('location', $loc->location_id); ?>><?= $loc->location_name; ?></option>
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
                                    <tr>
                                        <td>1</td>
                                        <td><input type="text" name="tyer_sl_no[]" placeholder="Enter Tyer Sl No" class="form-control tyer_sl_no" value="<?= set_value('tyer_sl_no[0]'); ?>" /></td>
                                        <td><input type="text" name="tyer_type[]" placeholder="Enter Tyer Type" class="form-control" value="<?= set_value('tyer_type[0]'); ?>" /></td>
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
        
        function addRow() {
            i++;
            var newRow = $('<tr id="row' + i + '"><td class="sl_no">' + i + '</td>' +
                '<td><input type="text" name="tyer_sl_no[]" placeholder="Enter Tyer Sl No" class="form-control tyer_sl_no" /></td>' +
                '<td><input type="text" name="tyer_type[]" placeholder="Enter Tyer Type" class="form-control" /></td>' +
                '<td><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove">X</button></td></tr>');
            $('#dynamic_field').append(newRow);
            updateSerialNumbers();
        }
        
        $('#add').click(function () {
            if (!checkDuplicateEntry()) {
                addRow();
            }
        });
        
        $(document).on('click', '.btn_remove', function () {
            var button_id = $(this).attr("id");
            $('#row' + button_id).remove();
            updateSerialNumbers();
        });
        
        // Function to update the serial numbers
        function updateSerialNumbers() {
            var sl_no = 1;
            $('#dynamic_field tr').each(function () {
                $(this).find('.sl_no').text(sl_no);
                sl_no++;
            });
        }
        
        // Function to check for duplicate entries in tyer_sl_no
        function checkDuplicateEntry() {
            var values = [];
            var isDuplicate = false;
            $('.tyer_sl_no').each(function () {
                var val = $(this).val().trim();
                if (val !== "") {
                    if (values.includes(val)) {
                        isDuplicate = true;
                        alert('Duplicate Tyer Sl No detected: ' + val);
                        return false; // Exit loop
                    }
                    values.push(val);
                }
            });
            return isDuplicate;
        }
        
        // Event listener for real-time duplicate check when typing
        $(document).on('input', '.tyer_sl_no', function () {
            checkDuplicateEntry();
        });


    function pad(number) {
        return number < 10 ? '0' + number : number;
    }

    var now = new Date();
    var year = now.getFullYear();
    var month = pad(now.getMonth() + 1);
    var day = pad(now.getDate());

    var currentDate = year + '-' + month + '-' + day;

    var hours = pad(now.getHours());
    var minutes = pad(now.getMinutes());

    var currentTime = hours + ':' + minutes;

    document.getElementById('date').value = currentDate;
});

</script>