<?php include("header.php");
foreach($single_payment_voucher as $stf_adv){}
?>
  <!-- Page Body Start-->
      <div class="page-body-wrapper uk-background-muted" style="background:#fcfcfc">
            <?php include("mainsidebar.php"); ?>
            
         <div class="page-body">
                  <div class="container-fluid">        
                    <div class="page-title">
                      <div class="row">
                        <div class="col-sm-6 p-0">
                          <h3>Edit Payment Voucher</h3>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Container-fluid starts-->    
   <div class="container-fluid default-dashboard">
                    
                  <div class="uk-grid-small" uk-grid>
                        <div class="uk-width-3-4@m">
                             <div class="uk-card uk-card-body uk-card-default uk-card-small ">
<form action="<?php echo base_url(); ?>/Admin/update_payment_voucher" enctype="multipart/form-data" method="post">
    <div class="uk-grid-small uk-child-width-1-1" uk-grid>

        <input type="hidden" name="pay_id" value="<?= $stf_adv->pay_id; ?>" />

        <div class="">
            <label>Date</label>
            <input type="date" name="date" class="uk-input" value="<?= $stf_adv->pay_date; ?>" />
        </div>

<!-- User Type Dropdown (with preselect) -->
<div class="">
    <label>User Type</label>
    <select id="userTypeSelect" class="form-control" name="user_type" required>
        <option value="">Select User Type</option>
        <option value="Party" <?= ($stf_adv->user_type == 'Party') ? 'selected' : ''; ?>>PARTY</option>
        <option value="Pump" <?= ($stf_adv->user_type == 'Pump') ? 'selected' : ''; ?>>PUMP</option>
        <option value="Vendor" <?= ($stf_adv->user_type == 'Vendor') ? 'selected' : ''; ?>>VENDOR</option>
        <option value="DRIVER" <?= ($stf_adv->user_type == 'DRIVER') ? 'selected' : ''; ?>>Driver</option>
        <option value="STAFF" <?= ($stf_adv->user_type == 'STAFF') ? 'selected' : ''; ?>>Staff</option>
    </select>
</div>

<!-- Employ Name Dropdown (Populated via AJAX and pre-selected if editing) -->
<div class="">
    <label>Employ Name</label>
    <select id="staffFilter" name="staff_id" class="uk-input" required>
        <option value="">Select</option>
        <!-- Options will be injected by JS -->
    </select>
</div>

<!-- jQuery Required -->



        <div class="">
            <label>Select Bank</label>
            <select name="bank" class="uk-input" required>
                <option>Select Bank</option>
                <?php foreach ($allbank as $bank) { ?>
                    <option value="<?= $bank->id ?>" <?= ($stf_adv->bank == $bank->id) ? 'selected' : ''; ?>>
                        <?= $bank->bank_name ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="">
            <label>Location</label>
            <select name="location_id" class="uk-input" required>
                <option>Select Location</option>
                <?php foreach ($location as $loc) { ?>
                    <option value="<?= $loc->location_id ?>" <?= ($stf_adv->location_id == $loc->location_id) ? 'selected' : ''; ?>>
                        <?= $loc->location_name ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="uk-form-controls">
            <label>Credit/Debit</label>
            <select class="uk-select" name="credit_debit">
                <option value="">Select</option>
                <option value="1" <?= ($stf_adv->credit_debit == 1) ? 'selected' : ''; ?>>Credit</option>
                <option value="2" <?= ($stf_adv->credit_debit == 2) ? 'selected' : ''; ?>>Debit</option>
            </select>
        </div>

        <div class="uk-form-controls">
            <label>Bank/Cash</label>
            <select class="uk-select" name="bank_cash" id="bankCashSelect">
                <option value="">Select</option>
                <option value="1" <?= ($stf_adv->bank_cash == 1) ? 'selected' : ''; ?>>Cash</option>
                <option value="2" <?= ($stf_adv->bank_cash == 2) ? 'selected' : ''; ?>>Upi</option>
            </select>
        </div>

        <div class="uk-form-controls" id="utrField" style="margin-top:10px; <?= ($stf_adv->bank_cash == 2) ? '' : 'display:none;' ?>">
            <label>UTR No.</label>
            <input type="text" name="utr_no" class="uk-input" placeholder="Enter UTR No." value="<?= esc($stf_adv->utr_no) ?>">
        </div>

        <div class="">
            <label>Amount</label>
            <input type="number" name="amount" placeholder="Enter Amount" class="uk-input" value="<?= $stf_adv->amount ?>" />
        </div>

        <div class="">
            <label>Upload File</label>
            <input type="file" name="upload_file" class="uk-input" />
        </div>

        <div class="">
            <label>Remark</label>
            <textarea name="remark" class="uk-input" placeholder="Enter Remark"><?= esc($stf_adv->remark) ?></textarea>
        </div>

        <div class="">
            <button type="submit" class="btn btn-primary">Update</button>
        </div>

    </div>
</form>

                                        <hr>
                                      
                                </div>
                        </div> 
                        
                      
            
         </div>
          <!-- Container-fluid Ends-->
        </div>
</div>      
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function () {
    function loadEmployList(userType, selectedId = '') {
        if (userType !== '') {
            $.ajax({
                url: "<?= base_url(); ?>/Admin/getUsersByType",
                type: "POST",
                data: { user_type: userType },
                success: function (response) {
                    $('#staffFilter').html('<option value="">Select</option>');
                    var data = JSON.parse(response);

                    $.each(data, function (index, user) {
                        let isSelected = (user.id == selectedId) ? 'selected' : '';
                        $('#staffFilter').append(
                            `<option value="${user.id}" ${isSelected}>${user.name} (${user.code || user.staff_code || ''})</option>`
                        );
                    });
                },
                error: function () {
                    alert('Failed to load users');
                }
            });
        } else {
            $('#staffFilter').html('<option value="">Select</option>');
        }
    }

    // On change load employ name
    $('#userTypeSelect').change(function () {
        var userType = $(this).val();
        loadEmployList(userType);
    });

    // Preload on edit
    let preUserType = "<?= $stf_adv->user_type ?>";
    let preStaffId = "<?= $stf_adv->staff_id ?>";
    if (preUserType !== '') {
        loadEmployList(preUserType, preStaffId);
    }
});
</script>
   <script>
    $(document).ready(function () {
        $('#bankCashSelect').on('change', function () {
            if ($(this).val() === '2') {
                $('#utrField').show();
            } else {
                $('#utrField').hide();
            }
        });
    });
</script>
<?php include("footer.php");?>