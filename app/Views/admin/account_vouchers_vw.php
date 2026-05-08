<?php include("header.php"); ?>

<div class="page-body-wrapper" style="background:#f4f7f6;">
    <?php include("mainsidebar.php"); ?>
    
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="font-weight-bold"><i class="fa fa-list-alt text-primary"></i> Voucher Register</h3>
                    </div>
                </div>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="get" action="<?= base_url('admin/account_vouchers'); ?>">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">From Date</label>
                                <input type="date" name="from_date" class="form-control" value="<?= $filters['from_date'] ?? '' ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">To Date</label>
                                <input type="date" name="to_date" class="form-control" value="<?= $filters['to_date'] ?? '' ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Type</label>
                                <select name="voucher_type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="Payment" <?= ($filters['voucher_type'] ?? '') == 'Payment' ? 'selected' : '' ?>>Payment</option>
                                    <option value="Receipt" <?= ($filters['voucher_type'] ?? '') == 'Receipt' ? 'selected' : '' ?>>Receipt</option>
                                    <option value="Journal" <?= ($filters['voucher_type'] ?? '') == 'Journal' ? 'selected' : '' ?>>Journal</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
                                    <a href="<?= base_url('admin/account_vouchers'); ?>" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="voucherList">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Voucher No</th>
                                    <th>Type</th>
                                    <th>Total Amount</th>
                                    <th>Narration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($vouchers)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No vouchers found for the selected criteria.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $n=1; foreach($vouchers as $v): ?>
                                        <tr>
                                            <td><?= $n++ ?></td>
                                            <td><?= date('d-m-Y', strtotime($v->voucher_date)) ?></td>
                                            <td><span class="badge badge-light text-dark"><?= $v->voucher_no ?></span></td>
                                            <td>
                                                <?php if($v->voucher_type == 'Payment'): ?>
                                                    <span class="badge badge-danger">Payment</span>
                                                <?php elseif($v->voucher_type == 'Receipt'): ?>
                                                    <span class="badge badge-success">Receipt</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Journal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="font-weight-bold"><?= number_format($v->total_amount, 2) ?></td>
                                            <td><small class="text-muted"><?= $v->narration ?></small></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="javascript:void(0);" onclick="viewVoucher(<?= $v->id ?>)" class="btn btn-outline-info btn-xs" title="View"><i class="fa fa-eye"></i></a>
                                                    <a href="<?= base_url('admin/print_voucher/'.$v->id) ?>" target="_blank" class="btn btn-outline-primary btn-xs" title="Print"><i class="fa fa-print"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="voucherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Voucher Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="voucherModalBody">
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#voucherList').DataTable({
        "order": [[ 1, "desc" ]]
    });
});

function viewVoucher(id) {
    $('#voucherModal').modal('show');
    $('#voucherModalBody').html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div></div>');
    
    $.get('<?= base_url('admin/view_voucher') ?>/' + id, function(data) {
        $('#voucherModalBody').html(data);
    });
}
</script>

<?php include("footer.php"); ?>
