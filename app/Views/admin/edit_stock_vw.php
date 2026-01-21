<?php include("header.php");?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="page-body-wrapper">
  <?php include("mainsidebar.php"); ?>
  <div class="page-body">
    <div class="container-fluid">
      <div class="page-title">
        <div class="row">
          <div class="col-sm-6 p-0">
            <h3>Edit Stock</h3>
          </div>
        </div>
      </div>
    </div>

    <div class="container-fluid default-dashboard">
      <div class="block">
        <div class="block-title">
          <h2><strong>Edit Stocks Data</strong></h2>
        </div>

        <?php foreach ($cart_dtls as $cartm) {} ?>
        <?php foreach ($stock_edit as $stock) {} ?>

        <div class="uk-card uk-card-body uk-card-small" style="border:solid 1px #ccc;">
          <div class="uk-grid-small uk-child-width-expand@m" uk-grid>
            <div>
              <label>Select Supplier</label>
              <select class="form-control" name="supplier_id" id="supplierSelect">
                <option value="">Select Supplier</option>
                <?php foreach ($vendor as $supp) {
                  if ($supp->type != "Pump") { ?>
                  <option value="<?= $supp->id; ?>" <?= (!empty($cartm) && $cartm->supplier_id == $supp->id) ? 'selected' : ''; ?>><?= $supp->name; ?></option>
                <?php }} ?>
              </select>
            </div>

            <div>
              <label>Location</label>
              <select name="location" id="location" class="form-control">
                <option value="">Select Location</option>
                <?php foreach($location as $loc) { ?>
                <option value="<?= $loc->location_id ?>" <?= (!empty($cartm) && $cartm->location == $loc->location_id) ? 'selected' : ''; ?>><?= $loc->location_name ?></option>
                <?php } ?>
              </select>
            </div>

            <div>
              <label>Date</label>
              <input type="date" name="invoicedate" value="<?= (!empty($stock)) ? date('Y-m-d', strtotime($stock->date)) : date('Y-m-d'); ?>" class="form-control" />
            </div>

            <div>
              <label>Invoice No</label>
              <input type="text" name="invoiceno" id="invoiceno" class="form-control" value="<?= (!empty($cartm)) ? $cartm->invoiceno : ''; ?>" />
            </div>

            <div>
              <label>Select Items</label>
              <select class="form-control select2 product" name="product" id="product">
                <option value="">Select Model No</option>
                <?php foreach ($product as $productt) { ?>
                <option value="<?= $productt->id; ?>" data-details='<?= json_encode($productt); ?>'><?= $productt->item_name; ?></option>
                <?php } ?>
              </select>
            </div>

            <div class="uk-width-auto">
              <p></p>
              <button class="btn btn-primary" id="addButton">Add</button>
            </div>
          </div>
        </div>

        <p></p>

        <div class="table table-responsive uk-margin-top" id="responseContainer">
          <table class="table table-striped table-bordered" style="border:solid 1px #ccc;">
            <thead>
              <tr>
                <th class="text-center">SI.No.</th>
                <th class="text-center">Product Name</th>
                <th class="text-center">Supplier Name</th>
                <th class="text-center">Location Name</th>
                <th class="text-center">Quantity</th>
                <th class="text-center">Rate</th>
                <th class="text-center">Amount</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php $i=1; $total_amount = 0;
              foreach($stock_edit as $stk_dtls) {
                $total_amount += $stk_dtls->quantity * $stk_dtls->rate;
              ?>
              <tr>
                <td><?= $i++; ?></td>
                <td><?= $stk_dtls->item_name ?></td>
                <td><?= $stk_dtls->supplier_name ?></td>
                <td><?= $stk_dtls->location_name ?></td>
                <td><?= $stk_dtls->quantity ?> <?= $stk_dtls->unit_name ?></td>
                <td><?= $stk_dtls->rate ?></td>
                <td><?= $stk_dtls->quantity * $stk_dtls->rate ?></td>
                <td>
                  <a href="javascript:void(0);" 
                     class="btn btn-sm btn-success edit-btn"
                     data-id="<?= $stk_dtls->stock_id ?>"
                     data-item="<?= $stk_dtls->items_id ?>"
                     data-supplier="<?= $stk_dtls->supplier_id ?>"
                     data-location="<?= $stk_dtls->location_id ?>"
                     data-quantity="<?= $stk_dtls->quantity ?>"
                     data-unit="<?= $stk_dtls->unit_name ?>"
                     data-rate="<?= $stk_dtls->rate ?>"
                     data-bs-toggle="modal"
                     data-bs-target="#editModal">
                     Edit
                  </a>
                </td>
              </tr>
              <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <th colspan="4">Total Amount</th>
                <th></th>
                <th></th>
                <th><?= $total_amount; ?></th>
                <th></th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!--modal for edit-->

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="<?= base_url('Admin/update_stock') ?>">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Stock</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="stock_id" id="modal_stock_id">

            <div class="mb-3">
                <label>Item Name</label>
                <select class="form-control select2" id="modal_item" name="item_name" style="width: 100%;">
                    <option value="">Select Supplier</option>
                    <?php foreach($items as $item): ?>
                      <option value="<?= $item->id ?>"><?= $item->item_name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label>Supplier</label>
                <select class="form-control select2" id="modal_supplier" name="supplier_name" style="width: 100%;">
                    <option value="">Select Supplier</option>
                    <?php foreach($vendor as $ven): ?>
                      <option value="<?= $ven->id ?>"><?= $ven->name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label>Location</label>
                <select class="form-control select2" id="modal_location" name="location_name" style="width: 100%;">
                    <option value="">Select Location</option>
                    <?php foreach($location as $loc): ?>
                      <option value="<?= $loc->location_id ?>"><?= $loc->location_name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

          <div class="mb-3">
            <label>Quantity</label>
            <input type="number" class="form-control" id="modal_quantity" name="quantity">
          </div>

          <div class="mb-3">
            <label>Rate</label>
            <input type="number" class="form-control" id="modal_rate" name="rate">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<form id="frm_deleteBanner" action="<?= base_url(); ?>/admin/deletepurchasecart" method="post">
  <input type="hidden" name="user_id" id="user_id" value="">
</form>

<script>
  $(document).ready(function() {
    $('.select2').select2();
  });
</script>

<script>
$(document).ready(function () {
  $("#supplierSelect, #product").select2({
    placeholder: "Select an option",
    allowClear: true
  });

  $('#addButton').on('click', function () {
    var supplierId = $('#supplierSelect').val();
    var invoicedate = $('input[name="invoicedate"]').val();
    var productId = $('#product').val();
    var invoiceno = $('#invoiceno').val();
    var location = $('#location').val();

    if (!supplierId || !invoicedate || !productId) {
      alert('Please enter all required fields.');
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
      },
      success: function (response) {
        $('#responseContainer').html(response);
      },
      error: function (error) {
        console.error('AJAX Error:', error);
      }
    });
  });
});
</script>


<script>
  document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.edit-btn');

    editButtons.forEach(button => {
      button.addEventListener('click', function () {
        document.getElementById('modal_stock_id').value = this.getAttribute('data-id');
        document.getElementById('modal_item').value = this.getAttribute('data-item');
        document.getElementById('modal_supplier').value = this.getAttribute('data-supplier');
        document.getElementById('modal_location').value = this.getAttribute('data-location');
        document.getElementById('modal_quantity').value = this.getAttribute('data-quantity');
        document.getElementById('modal_rate').value = this.getAttribute('data-rate');
      });
    });
  });
</script>

<?php include("footer.php"); ?>