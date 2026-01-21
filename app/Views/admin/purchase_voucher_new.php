<?php include("header.php"); ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">        
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0"><h3>New Purchase Voucher</h3></div>
                </div>
            </div>

            <div class="container-fluid default-dashboard">
                <div class="block">
                    <div class="block-title">
                        <h2><strong>Enter Stocks</strong></h2>
                    </div>

                    <div class="uk-card uk-card-body uk-card-small" style="border:solid 1px #ccc;">
                        <div class="uk-grid-small uk-child-width-expand@m" uk-grid>
                            <div>
                                <label>Select Supplier</label>
                                <select class="form-control" name="supplier_id" id="supplierSelect">
                                    <option value="">Select Supplier</option>
                                    <?php foreach ($vendor as $supp): if ($supp->type != "Pump"): ?>
                                        <option value="<?= $supp->id; ?>"><?= $supp->name; ?></option>
                                    <?php endif; endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label>Location</label>
                                <select name="location" id="location" class="form-control">
                                    <option value="">Select Location</option>
                                    <?php foreach ($location as $loc): ?>
                                        <option value="<?= $loc->location_id ?>"><?= $loc->location_name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label>Date</label>
                                <input type="date" name="invoicedate" value="<?= date('Y-m-d'); ?>" class="form-control"/>
                            </div>

                            <div>
                                <label>Invoice No</label>
                                <input type="text" name="invoiceno" id="invoiceno" class="form-control"/>
                            </div>

                            <div>
                                <label>Select Items</label><br>
                                <select class="form-control select2 product" name="product" id="product">
                                    
                                </select>
                            </div>

                            <div class="uk-width-auto">
                                <button type="button" class="btn btn-primary" id="addButton">Add</button>
                            </div>
                        </div>
                    </div>

                    <div id="responseContainer">
                        <table class="table table-responsive table-striped table-bordered" style="border:solid 1px #ccc;">
                            <thead>
                                <tr>
                                    <th class="text-center">SI.No.</th>
                                    <th class="text-center">Items Name</th>
                                    <th class="text-center">QTY</th>
                                    <th class="text-center">Unit</th>
                                    <th class="text-center">Rate</th>
                                    <th class="text-center">Amount</th>
                                    <td>Action</td>
                                </tr>
                            </thead>
                            <tbody id="TextBoxContainer">
                                <?php $i = 1; foreach ($cart_dtls as $cart): ?>
                                    <tr>
                                        <td><?= $i++; ?></td>
                                        <td><?= $cart->item_name; ?></td>
                                        <td>
                                            <input type="hidden" name="cart_id" value="<?= $cart->cart_id; ?>" class="uk-input uk-form-small cart-data"/>
                                            <input type="number" name="qty" value="<?= $cart->qty; ?>" class="uk-input uk-form-small cart-data" min="0.01" step="any"/>
                                        </td>
                                        <td><?= $cart->unit_name; ?></td>
                                        <td><input type="text" name="rate" value="<?= $cart->rate; ?>" class="uk-input uk-form-small cart-data"/></td>
                                        <td><?= $cart->rate * $cart->qty; ?></td>
                                        <td><a href="javascript:void(0);" onClick="deleteRecord('<?= $cart->cart_id; ?>');" uk-icon="icon: trash" class="uk-text-danger"></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <a href="<?= base_url(); ?>/Admin/Inserpurchasetstock" class="btn btn-primary">Submit</a>
                </div>
            </div>
        </div>
    </div>
</div>

<form name="frm_deleteBanner" id="frm_deleteBanner" action="<?= base_url(); ?>/admin/deletepurchasecart" method="post">
    <?= csrf_field(); ?>
    <input type="hidden" name="cartid" id="cartid" value="">
</form>

<script type="text/javascript">
    function deleteRecord(id) {
        $("#cartid").val(id);
        if (confirm("Are you sure you want to delete this record?")) {
            $("#frm_deleteBanner").submit();
        }
    }
</script>

<script>
    $("#supplierSelect, #product").select2({ placeholder: "Select an option", allowClear: true });

    $('#addButton').on('click', function(e) {
        e.preventDefault();

        var supplierId = $('#supplierSelect').val();
        var invoicedate = $('input[name="invoicedate"]').val();
        var productId = $('#product').val();
        var invoiceno = $('#invoiceno').val();
        var location = $('#location').val();        

        if (!supplierId || !invoicedate || !productId) {
            alert('Please fill all required fields.');
            return;
        }

        $.ajax({
            type: 'POST',
            url: '<?= base_url(); ?>/Admin/frm_addtocartpurchase',
            data: {
                supplierId: supplierId,
                invoicedate: invoicedate,
                productId: productId,
                invoiceno: invoiceno,
                location_id: location,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            success: function(response) {
                $('#TextBoxContainer').html(response);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });

    $('#responseContainer').on('change', 'input.cart-data', function() {
        var row = $(this).closest('tr');

        $.ajax({
            type: 'POST',
            url: '<?= base_url(); ?>/Admin/frm_updatepurchasecart',
            data: {
                cart_id: row.find('input[name="cart_id"]').val(),
                qty: row.find('input[name="qty"]').val(),
                rate: row.find('input[name="rate"]').val(),
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            success: function(response) {
                $('#responseContainer').html(response);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });
    $(document).ready(function () {
        $('#location').on('change', function (e) {
            e.preventDefault();  // ✅ Prevent form submission

            let location_id = $(this).val();
            if(location_id){
                $.ajax({
                    url: '<?= base_url("admin/getItemsDetails") ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        location_id: location_id,
                    },
                    success: function(items){
                        let options = '<option value="">Select Model No</option>';
                        $.each(items, function(i, item){
                            options += '<option value="'+item.id+'">'
                                + item.item_id + ' - ' + item.item_name
                                + ' (₹' + item.amount + ')'
                                + ' | Avl: ' + item.available_qty
                                + '</option>';
                        });
                        $('#product').html(options).trigger('change'); // ✅ updates without reload
                    },
                    error: function(){
                        alert('Error fetching item details.');
                    }
                });
            } else {
                $('#product').html('<option value="">Select Model No</option>').trigger('change');
            }
        });
    });
</script>

<?php include("footer.php"); ?>
