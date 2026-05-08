<?php include("header.php"); ?>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<!-- Page Body Start-->
<div class="page-body-wrapper" style="background:#f9f9f9;">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>New In House Maintenance</h3>
                    </div>
                    <div class="col-sm-6 p-0"></div>
                </div>
            </div>
        </div>
        <!-- Container-fluid starts-->
        <style>
            .select2-container--default .select2-selection--single {
                height: 38px !important;
                border: 1px solid #ced4da !important;
                padding-top: 5px;
            }
            .select2-container {
                display: block !important;
            }
        </style>
        <div class="container-fluid default-dashboard">
            <form name="add_name" id="add_inhouse_maintenance" action="<?php echo base_url();?>/Admin/insert_inhouse" method="post">
                <div class="uk-card uk-card-body uk-card-small" style="border:solid 1px #ccc;">
                    <div class="uk-grid-small uk-child-width-expand@m uk-grid" uk-grid="">
                        <div class="uk-first-column">
                            <label>Select Vehicle</label>
                            <select name="vehicle" id="vehicle" class="form-control">
                                <option value="">Select vehicle</option>
                                <?php foreach ($vehicles as $vec) { ?>
                                    <option value="<?= $vec->id; ?>"><?= $vec->vehicle_no; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div>
                            <div id="vehicle-details">
                                <label>Driver</label>
                                <input type="text" name="driver" class="form-control" readonly />
                            </div>
                        </div>
                        <?php
                            $default_to_date = $date['date'] ?? date('Y-m-d');
                            $default_time = $date['time'] ?? date('H:i');
                        ?>
                        <div>
                            <label>Date</label>
                            <input type="date" id="date" name="date" class="form-control" value="<?= $default_to_date; ?>">
                        </div>
                        <div>
                            <label>Time</label>
                            <input type="time" id="time" name="time" class="form-control" value="<?= $default_time; ?>" readonly>
                        </div>
                        <div>
                            <label>Remark</label>
                            <input type="text" name="invoiceno" id="invoiceno" class="form-control" placeholder="remark">
                        </div>
                        <div>
                            <label>Location</label>
                            <select name="location" id="single" class="form-control" onchange="locationChanged(this.value)">
                                <option value="">Select location</option>
                                <?php foreach ($location as $loc) { ?>
                                    <option value="<?= $loc->location_id; ?>"><?= $loc->location_name; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div>
                            <label>Checked by</label>
                            <select name="check_by" id="check_by" class="form-control" required>
                                <option value="">Select User</option>
                                <?php foreach($users as $u): ?>
                                    <option value="<?= $u->id; ?> - <?= $u->full_name; ?>"><?= $u->id; ?> - <?= $u->full_name; ?></option>
                                <?php endforeach; ?>
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
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Usage Type</th>
                                            <th>Item Name</th>
                                            <th>Qty/Price Info</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Mechanic Name</th>
                                            <th>Total Price</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tr>
                                        <td>1</td>
                                        <td>
                                            <select class="form-control type-select" name="itemUseAs[]">
                                                <option value="1">Service</option>
                                                <option value="2">Product</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="items[]" class="form-control items">
                                                <!-- Options loaded dynamically -->
                                            </select>
                                        </td>
                                        <td>
                                            <small class="availableqty text-muted">Available: 0 | Unit Price: 0.00</small>
                                        </td>
                                        <td><input type="number" name="qty[]" placeholder="Enter quantity" class="form-control qty" min="0.01" step="any" /></td>
                                        <td><input type="text" name="price[]" placeholder="Auto Price" class="form-control price" readonly /></td>
                                         <td>
                                             <?php
                                             $mechOptsHtml = '<option value="">Select Mechanic</option>';
                                             foreach($mechanics as $mec) {
                                                 $mechOptsHtml .= '<option value="' . $mec->name . '">' . $mec->name . '</option>';
                                             }
                                             ?>
                                             <select name="mechanic_name[]" class="form-control mechanic-select"><?= $mechOptsHtml ?></select>
                                         </td>
                                         <td><input type="text" name="totalprice" placeholder="Total Price" class="form-control tprice" readonly /></td>
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

<script>
    
    // Global mechanic options for dynamic rows
    var mechOptGlobal = '<option value="">Select Mechanic</option>';
    <?php foreach($mechanics as $mec): ?>
    mechOptGlobal += '<option value="<?= $mec->name; ?>"><?= $mec->name; ?></option>';
    <?php endforeach; ?>

    var currentItemsOptions = '';  // latest options
    var i = $('#dynamic_field tr').length;
    var selectedItems = [];

    function loadItemsForLocation(locationId, callback) {
        if (locationId) {
            $.ajax({
                url: '<?= base_url(); ?>/Admin/get_items_by_location',
                type: 'POST',
                data: { location_id: locationId },
                success: function(response) {
                    currentItemsOptions = response;
                    callback();
                },
                error: function() {
                    alert("Failed to fetch items.");
                }
            });
        } else {
            currentItemsOptions = '<option value="">Select items</option>';
            callback();
        }
    }

    function addRow() {
        var mechOptHtml = mechOptGlobal;
        i++;

        // Build options dynamically, excluding already selected items
        var filteredOptions = $('<div>').html(currentItemsOptions).find('option').clone();
        var optionsHtml = '<option value="">Select items</option>';

        filteredOptions.each(function() {
            var optionVal = $(this).attr('value');
            if (!selectedItems.includes(optionVal)) {
                optionsHtml += `<option value="${optionVal}" data-unitsname = "${$(this).data('unitsname')}" data-unitprice="${$(this).data('unitprice')}" data-available="${$(this).data('available')}">${$(this).text()}</option>`;
            }
        });

        var newRow = $(`
            <tr id="row${i}">
                <td>${i}</td>
                <td>
                    <select class="form-control type-select" name="itemUseAs[]">
                        <option value="1">Service</option>
                        <option value="2">Product</option>
                    </select>
                </td>
                <td>
                    <select name="items[]" class="form-control items">${optionsHtml}</select>
                </td>
                <td>
                    <small class="availableqty text-muted d-block mt-1">Available: 0 | Unit Price: 0.00</small>
                </td>
                <td><input type="number" name="qty[]" class="form-control qty" placeholder="Enter quantity" min="0.01" step="any"/></td>
                <td><input type="text" name="price[]" class="form-control price" readonly/></td>
                <td><select name="mechanic_name[]" class="form-control mechanic-select">${mechOptHtml}</select></td>
                <td><input type="text" name="totalprice" placeholder="Total Price" class="form-control tprice" readonly /></td>
                <td><button type="button" class="btn btn-danger btn_remove">X</button></td>
            </tr>
        `);

        $('#dynamic_field').append(newRow);
        applySelect2(newRow);
    }

    function applySelect2(element) {
        element.find('.items').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });
    }

    $(document).ready(function () {
        // Initialize Select2 for main form fields
        $('#vehicle, #single, #check_by').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });

        $('.items').select2({
            placeholder: "Search or select an item",
            allowClear: true,
            width: '100%'
        });

        // Vehicle details listener
        $('#vehicle').on('change', function() {
            var vehicle_id = $(this).val();
            if (vehicle_id) {
                $.ajax({
                    url: '<?= base_url('Admin/get_vehicle_driver'); ?>',
                    type: 'POST',
                    data: { vehicle_id: vehicle_id },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            $('#vehicle-details input[name="driver"]').val(res.driver_name);
                        } else {
                            $('#vehicle-details input[name="driver"]').val('');
                        }
                    }
                });
            } else {
                $('#vehicle-details input[name="driver"]').val('');
            }
        });
    });

    // Add More button
    $(document).on('click', '#add', function () {
        var locationId = $('#single').val();
        if (!locationId) {
            alert('Please select a location first.');
            return;
        }

        selectedItems = [];
        $('.items').each(function() {
            var val = $(this).val();
            if (val) selectedItems.push(val);
        });
        addRow();
    });

    // Remove button
    $(document).on('click', '.btn_remove', function () {
        $(this).closest('tr').remove();
    });

    // Item change
    $(document).on('change', '.items', function () {
        var selected = $(this).find('option:selected');
        var unitPrice = parseFloat(selected.data('unitprice')) || 0;
        var available = parseFloat(selected.data('available')) || 0;
        var unitnames = selected.data('unitsname') || '';

        var row = $(this).closest('tr');
        row.data('unit-price', unitPrice);
        row.find('.availableqty').text('Units: '+ unitnames +' | Available: ' + available + ' | Price: ' + unitPrice.toFixed(2));
        row.find('.qty').attr('max', available);

        calculatePrice(row);
    });

    // Quantity input
    $(document).on('input', '.qty', function () {
        var max = parseFloat($(this).attr('max')) || 0;
        var val = parseFloat($(this).val()) || 0;

        if (val > max) {
            alert('Quantity cannot exceed available stock (' + max + ')');
            $(this).val(max);
            val = max;
        }

        var row = $(this).closest('tr');
        calculatePrice(row);
    });

    function calculatePrice(row) {
        var unitPrice = parseFloat(row.data('unit-price')) || 0;
        var qty = parseFloat(row.find('.qty').val()) || 0;
        row.find('.price').val((unitPrice).toFixed(2));
        row.find('.tprice').val((unitPrice * qty).toFixed(2));
    }

    // Location change
    function locationChanged(locationId) {
        loadItemsForLocation(locationId, function() {
            // Update all existing rows
            $('.items').each(function() {
                var selectedValue = $(this).val(); // preserve selected
                $(this).html(currentItemsOptions).val(selectedValue).trigger('change');
            });
        });
    }
</script>
