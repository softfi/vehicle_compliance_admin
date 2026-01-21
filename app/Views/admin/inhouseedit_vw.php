<?php include("header.php"); 
foreach ($orderdtls as $orddtls){} // First record for static fields
?>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>

<div class="page-body-wrapper" style="background:#f9f9f9;">
    <?php include("mainsidebar.php"); ?>

    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <h3>Edit In House Maintenance</h3>
            </div>
        </div>

        <div class="container-fluid default-dashboard">
            <form name="add_name" id="add_inhouse_maintenance" action="<?= base_url('Admin/update_inhouse'); ?>" method="post">
                <input type="hidden" name="oorder_id" value="<?= $orddtls->order_id ?>" />

                <div class="uk-card uk-card-body uk-card-small" style="border:solid 1px #ccc;">
                    <div class="uk-grid-small uk-child-width-expand@m uk-grid" uk-grid="">
                        <div>
                            <label>Select Vehicle</label>
                            <select name="vehicle" id="vehicle" class="form-control">
                                <option value="">Select vehicle</option>
                                <?php foreach ($vehicles as $vec): ?>
                                    <option value="<?= $vec->id ?>" <?= ($vec->id == $orddtls->vehicle) ? 'selected' : '' ?>>
                                        <?= $vec->vehicle_no ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label>Driver</label>
                            <input type="text" name="driver" class="form-control" value="<?= $orddtls->driver_name ?>" readonly />
                        </div>

                        <div>
                            <label>Date</label>
                            <input type="date" id="date" name="date" class="form-control" value="<?= $orddtls->date ?>" />
                        </div>

                        <div>
                            <label>Time</label>
                            <input type="time" id="time" name="time" class="form-control" value="<?= $orddtls->time ?>" />
                        </div>

                        <div>
                            <label>Remark</label>
                            <input type="text" name="invoiceno" id="invoiceno" class="form-control" value="<?= $orddtls->invoiceno ?>" />
                        </div>

                        <div>
                            <label>Location</label>
                            <select name="location" id="location" class="form-control" readonly>
                                <option value="">Select location</option>
                                <?php foreach ($location as $loc): ?>
                                    <option value="<?= $loc->location_id ?>" <?= ($loc->location_id == $orddtls->location) ? 'selected' : '' ?>>
                                        <?= $loc->location_name ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label>Checked by</label>
                            <input type="text" name="check_by" class="form-control" value="<?= $orddtls->check_by ?>" />
                        </div>
                    </div>
                </div>

                <hr>

                <table class="table table-bordered table-hover" id="dynamic_field">
                    <?php $i = 1; foreach ($orderdtls as $ordtlsRow): ?>
                    <tr id="row<?= $i; ?>">
                        <td><?= $i; ?></td>
                        <td>
                            <select class="form-control type-select" name="itemUseAs[]">
                                <option value="1" <?= ($ordtlsRow->itemUseAs == 1) ? 'selected' : '' ?>>Service</option>
                                <option value="2" <?= ($ordtlsRow->itemUseAs == 2) ? 'selected' : '' ?>>Product</option>
                            </select>
                        </td>
                        <td>
                            <select name="items[]" class="form-control items-select">
                                <?php 
                                    $itemsForLocation = $location_items_map[$ordtlsRow->order_id] ?? [];
                                    $foundSelected = false;
    
                                    foreach ($itemsForLocation as $item):
                                        if ($ordtlsRow->item == $item->id) $foundSelected = true;
                                    ?>
                                        <option value="<?= $item->id; ?>" 
                                                data-price="<?= $item->amount; ?>" 
                                                data-available="<?= $item->available_qty ?? 0; ?>" 
                                                <?= ($ordtlsRow->item == $item->id) ? 'selected' : ''; ?>>
                                            <?= $item->item_name; ?>
                                        </option>
                                    <?php endforeach; ?>
    
                                    <?php if (!$foundSelected && isset($currentItem[$ordtlsRow->order_id])): ?>
                                        <option value="<?= $currentItem[$ordtlsRow->order_id]->id; ?>" 
                                                data-price="<?= $currentItem[$ordtlsRow->order_id]->amount; ?>" 
                                                data-available="<?= $currentItem[$ordtlsRow->order_id]->available_qty ?? 0; ?>" 
                                                selected>
                                            <?= $currentItem[$ordtlsRow->order_id]->item_name; ?>
                                        </option>
                                <?php endif; ?>
                            </select>
                            <small class="availableqty text-muted d-block mt-1"></small>
                        </td>
                        <td>
                            <input type="number" name="qty[]" value="<?= $ordtlsRow->qty ?>" class="form-control qty" min="1">
                        </td>
                        <td>
                            <input type="text" name="price[]" value="<?= $ordtlsRow->price ?>" class="form-control price" readonly>
                        </td>
                        <td>
                            <!-- Total pre-calculated in PHP -->
                            <input type="text" name="total[]" value="<?= number_format($ordtlsRow->price * $ordtlsRow->qty, 2) ?>" class="form-control total" readonly>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn_remove">X</button>
                        </td>
                    </tr>
                    <?php $i++; endforeach; ?>

                    <!-- Add More Button Row -->
                    <tr id="add_more_row">
                        <td colspan="7" class="text-center">
                            <button type="button" id="add" class="btn btn-primary">Add More</button>
                        </td>
                    </tr>
                </table>
                <button class="btn btn-primary" type="submit">Update</button>
            </form>
        </div>
    </div>

    <?php include("footer.php"); ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script>
$(document).ready(function () {
    var selectedItems = [];

    function loadItems(locationId, selectedItemId = null, targetSelect) {
        if (!locationId) return;
        $.ajax({
            url: '<?= base_url("Admin/getItemsByLocationInEdit") ?>',
            type: 'POST',
            data: { location_id: locationId },
            dataType: 'json',
            success: function(items) {
                var options = '<option value="">Select Item</option>';
                $.each(items, function(index, item) {
                    // Include selectedItemId even if already in selectedItems
                    if (selectedItems.includes(item.id) && item.id != selectedItemId) return;
                    var selected = (item.id == selectedItemId) ? 'selected' : '';
                    options += '<option value="'+item.id+'" data-price="'+item.amount+'" data-available="'+item.available_qty+'" '+selected+'>'+item.item_name+'</option>';
                });

                // If selectedItemId is set but not in items (maybe deactivated), add it
                if(selectedItemId && !items.some(i => i.id == selectedItemId)) {
                    var selectedOption = targetSelect.find('option:selected');
                    options += '<option value="'+selectedItemId+'" data-price="'+selectedOption.data('price')+'" data-available="'+selectedOption.data('available')+'" selected>'+selectedOption.text()+'</option>';
                }

                targetSelect.html(options);

                if(selectedItemId) {
                    var selectedOption = targetSelect.find('option:selected');
                    var price = selectedOption.data('price') || 0;
                    var available = selectedOption.data('available') || 0;
                    targetSelect.closest('tr').find('.price').val(price);
                    targetSelect.closest('tr').find('.availableqty').text('Available: '+available+' | Unit Price: '+price.toFixed(2));
                }
            }
        });
    }

    $('#location').on('mousedown', function(e){
        e.preventDefault(); // Prevent opening the dropdown
    });

    // Initialize rows
    $('#dynamic_field tr').not('#add_more_row').each(function() {
        var row = $(this);
        var select = row.find('.items-select');
        var selectedItemId = select.val();
        if(selectedItemId) selectedItems.push(selectedItemId);
        loadItems($('#location').val(), selectedItemId, select);
    });

    // Add More
    $('#dynamic_field').on('click', '#add', function() {
        var i = $('#dynamic_field tr').not('#add_more_row').length + 1;
        var newRow = $('<tr id="row'+i+'">'+
            '<td>'+i+'</td>'+
            '<td><select class="form-control type-select" name="itemUseAs[]"><option value="1">Service</option><option value="2">Product</option></select></td>'+
            '<td><select name="items[]" class="form-control items-select"><option value="">Select Item</option></select><small class="availableqty text-muted d-block mt-1"></small></td>'+
            '<td><input type="number" name="qty[]" class="form-control qty" min="1"></td>'+
            '<td><input type="text" name="price[]" class="form-control price" readonly></td>'+
            '<td><button type="button" class="btn btn-danger btn_remove">X</button></td>'+
        '</tr>');
        $('#add_more_row').before(newRow);
        loadItems($('#location').val(), null, newRow.find('.items-select'));
    });

    // Remove row
    $(document).on('click', '.btn_remove', function() {
        var val = $(this).closest('tr').find('.items-select').val();
        selectedItems = selectedItems.filter(function(e){ return e != val; });
        $(this).closest('tr').remove();
    });

    // Item change
    $(document).on('change', '.items-select', function() {
        var row = $(this).closest('tr');
        var selected = $(this).find(':selected');
        var price = selected.data('price') || 0;
        var available = selected.data('available') || 0;
        row.find('.price').val(price);
        row.find('.availableqty').text('Available: '+available+' | Unit Price: '+price.toFixed(2));

        // Update selectedItems
        selectedItems = [];
        $('.items-select').each(function() {
            var val = $(this).val();
            if(val) selectedItems.push(val);
        });

        // Refresh dropdowns
        $('.items-select').each(function() {
            var currentVal = $(this).val();
            loadItems($('#location').val(), currentVal, $(this));
        });
    });

    // Quantity check
    $(document).on('input', '.qty', function() {
        var row = $(this).closest('tr');
        var maxQty = parseInt(row.find('.items-select option:selected').data('available')) || 0;
        var enteredQty = parseInt($(this).val()) || 0;
        if (enteredQty > maxQty) {
            alert('Quantity cannot exceed available stock (' + maxQty + ')');
            $(this).val(maxQty);
        }
    });
});
$(document).ready(function(){
    // On quantity change, update total
    $(document).on('input', '.qty', function(){
        var row = $(this).closest('tr');
        var price = parseFloat(row.find('.price').val()) || 0;
        var qty = parseFloat($(this).val()) || 0;
        var total = price * qty;
        row.find('.total').val(total.toFixed(2));
    });
});

</script>
