<?php include("header.php"); ?>

<style>
    .form-group-filter { margin-bottom: 0.5rem; }
    .form-group-filter label { display: block; margin-bottom: 4px; font-size: 13px; color: #333; }
    .form-control, .select2-container--default .select2-selection--single {
        height: 38px !important;
        padding: 6px 12px;
        font-size: 14px;
        border-color: #dee2e6 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 24px !important;
        padding-left: 0 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
</style>

<div class="page-body-wrapper" style="background:#f4f7f6;">
    <?php include("mainsidebar.php"); ?>
    
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="font-weight-bold"><i class="fa fa-book text-primary"></i> Ledger Statement</h3>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="get" action="<?= base_url('admin/ledger_statement'); ?>">
                        <div class="row">
                            <div class="col-md-2 form-group-filter">
                                <label class="font-weight-bold">Voucher Type</label>
                                <select name="voucher_type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="Payment" <?= ($filters['voucher_type'] == 'Payment') ? 'selected' : '' ?>>Payment</option>
                                    <option value="Receipt" <?= ($filters['voucher_type'] == 'Receipt') ? 'selected' : '' ?>>Receipt</option>
                                    <option value="Journal" <?= ($filters['voucher_type'] == 'Journal') ? 'selected' : '' ?>>Journal</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group-filter">
                                <label class="font-weight-bold">Select Group</label>
                                <select name="group_id" id="group_id" class="form-control select2">
                                    <option value="">Select Group...</option>
                                    <?php foreach($groups as $g): ?>
                                        <option value="<?= $g->group_id ?>" <?= ($filters['group_id'] == $g->group_id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($g->group_name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 form-group-filter">
                                <label class="font-weight-bold">Select Particular</label>
                                <select name="ledger_id" id="ledger_id" class="form-control select2" required>
                                    <option value="">Select Particular...</option>
                                    <?php if(!empty($particulars)): ?>
                                        <?php foreach($particulars as $p): ?>
                                            <option value="<?= $p['id'] ?>" <?= ($filters['ledger_id'] == $p['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-2 form-group-filter">
                                <label class="font-weight-bold">From</label>
                                <input type="date" name="from_date" class="form-control" value="<?= $filters['from_date'] ?>">
                            </div>
                            <div class="col-md-2 form-group-filter">
                                <label class="font-weight-bold">To</label>
                                <input type="date" name="to_date" class="form-control" value="<?= $filters['to_date'] ?>">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-primary px-4"><i class="fa fa-search"></i> View Statement</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($statement): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 font-weight-bold"><?= $statement['ledger']->ledger_name ?></h5>
                            <small class="text-muted">Statement from <?= date('d-m-Y', strtotime($filters['from_date'])) ?> to <?= date('d-m-Y', strtotime($filters['to_date'])) ?></small>
                        </div>
                        <div class="btn-group">
                            <button onclick="window.print();" class="btn btn-outline-secondary btn-sm"><i class="fa fa-print"></i> Print</button>
                            <!-- <a href="<?= base_url('admin/export_ledger_excel?ledger_id='.$filters['ledger_id'].'&group_id='.$filters['group_id'].'&voucher_type='.$filters['voucher_type'].'&from_date='.$filters['from_date'].'&to_date='.$filters['to_date']) ?>" class="btn btn-outline-success btn-sm"><i class="fa fa-file-excel-o"></i> Excel</a> -->
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="12%">Date</th>
                                        <th width="15%">Voucher No</th>
                                        <th width="12%">Type</th>
                                        <th>Particulars</th>
                                        <th width="12%" class="text-right">Debit</th>
                                        <th width="12%" class="text-right">Credit</th>
                                        <th width="15%" class="text-right">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?= date('d-m-Y', strtotime($filters['from_date'])) ?></td>
                                        <td colspan="3"><strong>Opening Balance</strong></td>
                                        <td class="text-right"><?= ($statement['opening_bal'] > 0) ? number_format(abs($statement['opening_bal']), 2) : '' ?></td>
                                        <td class="text-right"><?= ($statement['opening_bal'] < 0) ? number_format(abs($statement['opening_bal']), 2) : '' ?></td>
                                        <td class="text-right font-weight-bold">
                                            <?= number_format(abs($statement['opening_bal']), 2) ?> 
                                            <?= ($statement['opening_bal'] >= 0) ? 'Dr' : 'Cr' ?>
                                        </td>
                                    </tr>
                                    <?php 
                                    $running_bal = $statement['opening_bal'];
                                    foreach ($statement['entries'] as $entry): 
                                        $dr = ($entry->entry_type == 1) ? $entry->amount : 0;
                                        $cr = ($entry->entry_type == 2) ? $entry->amount : 0;
                                        $running_bal += ($dr - $cr);
                                    ?>
                                        <tr>
                                            <td><?= date('d-m-Y', strtotime($entry->voucher_date)) ?></td>
                                            <td><span class="badge badge-light"><?= $entry->voucher_no ?></span></td>
                                            <td><?= $entry->voucher_type ?></td>
                                            <td>
                                                <?= $entry->narration ?><br>
                                                <small class="text-muted"><?= $entry->voucher_narration ?></small>
                                            </td>
                                            <td class="text-right text-danger"><?= $dr > 0 ? number_format($dr, 2) : '' ?></td>
                                            <td class="text-right text-success"><?= $cr > 0 ? number_format($cr, 2) : '' ?></td>
                                            <td class="text-right font-weight-bold">
                                                <?= number_format(abs($running_bal), 2) ?> 
                                                <?= ($running_bal >= 0) ? 'Dr' : 'Cr' ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td colspan="6" class="text-right">Closing Balance</td>
                                        <td class="text-right font-weight-bold" style="font-size: 1.1em;">
                                            <?= number_format(abs($running_bal), 2) ?> 
                                            <?= ($running_bal >= 0) ? 'Dr' : 'Cr' ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            <?php elseif ($filters['ledger_id']): ?>
                <div class="alert alert-info">No transactions found for the selected period.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.select2').select2({
        width: '100%'
    });

    $('#group_id').change(function() {
        var group_id = $(this).val();
        var $particularSelect = $('#ledger_id');
        
        $particularSelect.html('<option value="">Loading...</option>').trigger('change');
        
        if (group_id) {
            $.post('<?= base_url('admin/getParticularsByGroup') ?>', {group_id: group_id}, function(data) {
                var options = '<option value="">Select Particular...</option>';
                $.each(data, function(i, item) {
                    options += '<option value="' + item.id + '">' + item.name + '</option>';
                });
                $particularSelect.html(options).trigger('change');
            });
        } else {
            $particularSelect.html('<option value="">Select Particular...</option>').trigger('change');
        }
    });
});
</script>

<style>
    @media print {
        .page-header, .mainsidebar, .card-body form, .btn-group, .footer {
            display: none !important;
        }
        .page-body {
            margin: 0 !important;
            padding: 0 !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .table-responsive {
            overflow: visible !important;
        }
    }
</style>

<?php include("footer.php"); ?>
