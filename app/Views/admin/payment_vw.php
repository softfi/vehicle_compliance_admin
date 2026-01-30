<?php include("header.php"); ?>
<div class="page-body-wrapper voucher-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="filter-card">
                <h5>📋 Payment View</h5>
                <form method="get" action="<?php echo base_url(); ?>/Admin/Payment">
                    <?php 
                    $default_from_date = $filters['from_date'] ?? date('Y-m-01'); 
                    $default_to_date = $filters['to_date'] ?? date('Y-m-d'); 
                    ?>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label for="from_date">From Date</label>
                                <input type="date" id="from_date" name="from_date" class="form-control-custom" value="<?= $default_from_date; ?>"/>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label for="to_date">To Date</label>
                                <input type="date" id="to_date" name="to_date" class="form-control-custom" value="<?= $default_to_date; ?>"/>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label for="party">Party Name</label>
                                <select class="form-control-custom select2" name="party" id="party">
                                    <option value="">Select Party</option>
                                    <?php foreach ($all_party as $p): ?>
                                        <option value="<?= $p->id; ?>" <?= (isset($filters['party']) && $filters['party'] == $p->id) ? 'selected' : '' ?>><?= $p->name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label for="voucher_no">Voucher No.</label>
                                <select class="form-control-custom select2" name="voucher_no" id="voucher_no">
                                    <option value="">Select Voucher</option>
                                    <?php foreach ($all_vouchers as $v): ?>
                                        <option value="<?= $v->group_code; ?>" <?= (isset($filters['voucher_no']) && $filters['voucher_no'] == $v->group_code) ? 'selected' : '' ?>><?= $v->group_code; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="btn-group-custom">
                        <button type="submit" class="btn-custom btn-primary-custom"><i class="fa fa-filter"></i> Apply Filters</button>
                    </div>
                </form>
            </div>

            <div class="table-card">
                <div class="table-wrapper">
                    <table id="paymentTable">
                        <thead>
                            <tr>
                                <th>Party Name</th>
                                <th>Voucher No.</th>
                                <th>Total Net Amount (Auto)</th>
                                <th>Received Date</th>
                                <th>Received Amount</th>
                                <th>Difference (Auto)</th>
                                <th>Adjustment Amount</th>
                                <th>Adjustment Remarks</th>
                                <th>Submit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payment_vouchers as $v): ?>
                            <tr data-id="<?= $v->id; ?>">
                                <td><?= $v->party_name; ?></td>
                                <td><?= $v->group_code; ?></td>
                                <td class="total_net_amount"><?= number_format($v->total_net_amount, 2, '.', ''); ?></td>
                                <td><input type="date" class="form-control received_date" value="<?= $v->received_date ?: ''; ?>"></td>
                                <td><input type="number" class="form-control received_amount" value="<?= $v->received_amount ?: '0.00'; ?>" step="0.01" oninput="calculateDifference(this)"></td>
                                <td class="difference"><?= number_format($v->total_net_amount - $v->received_amount - $v->adjustment_amount, 2, '.', ''); ?></td>
                                <td><input type="number" class="form-control adjustment_amount" value="<?= $v->adjustment_amount ?: '0.00'; ?>" step="0.01" oninput="calculateDifference(this)"></td>
                                <td><input type="text" class="form-control adjustment_remarks" value="<?= $v->adjustment_remarks; ?>"></td>
                                <td><button type="button" class="btn btn-primary btn-sm" onclick="updatePayment(this)">Add</button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>                            
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select option",
            allowClear: true,
            width: '100%'
        });
    });

    function calculateDifference(input) {
        let row = $(input).closest('tr');
        let netAmount = parseFloat(row.find('.total_net_amount').text()) || 0;
        let receivedAmount = parseFloat(row.find('.received_amount').val()) || 0;
        let adjustmentAmount = parseFloat(row.find('.adjustment_amount').val()) || 0;
        
        let difference = netAmount - receivedAmount - adjustmentAmount;
        row.find('.difference').text(difference.toFixed(2));
    }

    function updatePayment(btn) {
        let row = $(btn).closest('tr');
        let id = row.data('id');
        let data = {
            id: id,
            received_date: row.find('.received_date').val(),
            received_amount: row.find('.received_amount').val(),
            adjustment_amount: row.find('.adjustment_amount').val(),
            adjustment_remarks: row.find('.adjustment_remarks').val(),
            "<?= csrf_token() ?>": "<?= csrf_hash() ?>"
        };

        if (!data.received_date) {
            alert('Please select a Received Date');
            return;
        }

        $.ajax({
            url: "<?= base_url('Admin/updateVoucherPayment') ?>",
            type: "POST",
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert('Updated successfully');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred during specific update.');
            }
        });
    }
</script>
<style>
    .voucher-wrapper .page-body { margin-left: 250px; padding: 20px; transition: margin-left 0.3s ease; }
    .filter-card, .table-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
    .form-control-custom { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background: #f8f9fa; color: #333; font-weight: 600; text-align: left; padding: 12px; border-bottom: 2px solid #dee2e6; }
    td { padding: 10px; border-bottom: 1px solid #eee; vertical-align: middle; }
    .btn-custom { padding: 8px 20px; border-radius: 4px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-weight: 500; transition: all 0.3s; }
    .btn-primary-custom { background: #4e73df; color: #fff; }
    .btn-primary-custom:hover { background: #2e59d9; }
</style>
<?php include("footer.php"); ?>
