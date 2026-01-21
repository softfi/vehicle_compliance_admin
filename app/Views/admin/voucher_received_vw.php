<?php include("header.php"); ?>
<?php 
    // Example options — replace with dynamic data if needed
    $financialYears = ['2023-2024', '2024-2025', '2025-2026'];
    $accounts = ['Cash Account', 'Bank Account', 'Sales Account', 'Purchase Account'];
?>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>

    <div class="page-body">
        <div class="container-fluid mt-4">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">

                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Payment Voucher</h4>
                        </div>

                        <div class="card-body">
                            <form id="paymentVoucherForm" action="<?= base_url('/Admin/save_payment_voucher'); ?>" method="post">
                                <?php if (function_exists('csrf_field')) echo csrf_field(); ?>

                                <!-- Financial Year -->
                                <div class="form-group">
                                    <label for="financial_year">Financial Year</label>
                                    <select name="financial_year" id="financial_year" class="form-control" required>
                                        <option value="">Select Year</option>
                                        <?php foreach ($financialYears as $year): ?>
                                            <option value="<?= $year; ?>"><?= $year; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Voucher No -->
                                <div class="form-group">
                                    <label for="voucher_no">Voucher No.</label>
                                    <input type="text" name="voucher_no" id="voucher_no" class="form-control" required>
                                </div>

                                <!-- Date -->
                                <div class="form-group">
                                    <label for="date">Date</label>
                                    <input type="date" name="date" id="date" class="form-control" required>
                                </div>

                                <!-- Cr. -->
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Dr.</label>
                                    <div class="col-sm-6">
                                        <select name="cr_account" id="cr_account" class="form-control">
                                            <option value="">Select Account</option>
                                            <?php foreach ($accounts as $acc): ?>
                                                <option value="<?= $acc; ?>"><?= $acc; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" name="cr_amount" id="cr_amount" class="form-control" placeholder="Amount" step="0.01" min="0">
                                    </div>
                                </div>

                                <!-- Dr. -->
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Cr.</label>
                                    <div class="col-sm-6">
                                        <select name="dr_account" id="dr_account" class="form-control">
                                            <option value="">Select Account</option>
                                            <?php foreach ($accounts as $acc): ?>
                                                <option value="<?= $acc; ?>"><?= $acc; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" name="dr_amount" id="dr_amount" class="form-control" placeholder="Amount" step="0.01" min="0">
                                    </div>
                                </div>

                                <!-- Narration -->
                                <div class="form-group">
                                    <label for="narration">Narration</label>
                                    <textarea name="narration" id="narration" rows="3" class="form-control" required></textarea>
                                </div>

                                <!-- Buttons -->
                                <div class="form-group text-right">
                                    <button type="button" id="btnCancel" class="btn btn-secondary mr-2">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </form>
                        </div> <!-- card-body -->
                    </div> <!-- card -->

                </div>
            </div>
        </div>
    </div>

</div>

<script>
$(document).ready(function(){
    // Reset form on cancel
    $('#btnCancel').on('click', function(){
        if(confirm("Discard changes?")) {
            $('#paymentVoucherForm')[0].reset();
        }
    });

    // Prevent both Cr & Dr amounts being filled
    $('#paymentVoucherForm').on('submit', function(e){
        var cr = $('#cr_amount').val();
        var dr = $('#dr_amount').val();
        if(cr && dr){
            alert("Please fill either Cr. or Dr. amount, not both.");
            e.preventDefault();
        }
    });
});
</script>

<?php include("footer.php"); ?>
