<?php include("header.php"); ?>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="font-weight-bold"><i class="fa fa-book text-primary"></i> Location Cashbook</h3>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="get" action="<?= base_url('admin/CashBank'); ?>">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="form-label font-weight-bold">Select Location</label>
                                <select name="location_id" class="form-control select2" required>
                                    <option value="">-- Choose Location --</option>
                                    <?php foreach($locations as $loc): ?>
                                        <option value="<?= $loc->location_id ?>" <?= $filters['location_id'] == $loc->location_id ? 'selected' : '' ?>>
                                            <?= $loc->location_name ?> (<?= $loc->location_shordname ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-weight-bold">From Date</label>
                                <input type="date" name="from_date" class="form-control" value="<?= $filters['from_date'] ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-weight-bold">To Date</label>
                                <input type="date" name="to_date" class="form-control" value="<?= $filters['to_date'] ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="fa fa-filter"></i> View Book</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if($selected_location): ?>
                <!-- Summary Section -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-info text-white border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="mb-1">Opening Balance</h6>
                                <h4 class="mb-0">₹ <?= number_format($opening_balance, 2) ?></h4>
                            </div>
                        </div>
                    </div>
                    <?php 
                        $total_debit = array_sum(array_column($entries, 'debit'));
                        $total_credit = array_sum(array_column($entries, 'credit'));
                        $closing = $opening_balance + $total_credit - $total_debit;
                    ?>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="mb-1">Total Debit (Out)</h6>
                                <h4 class="mb-0">₹ <?= number_format($total_debit, 2) ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="mb-1">Total Credit (In)</h6>
                                <h4 class="mb-0">₹ <?= number_format($total_credit, 2) ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card <?= $closing >= 0 ? 'bg-primary' : 'bg-warning text-dark' ?> text-white border-0 shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="mb-1">Closing Balance</h6>
                                <h4 class="mb-0">₹ <?= number_format($closing, 2) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cashbook Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Cashbook: <strong><?= $selected_location->location_name ?></strong> (<?= date('d M Y', strtotime($filters['from_date'])) ?> to <?= date('d M Y', strtotime($filters['to_date'])) ?>)</h5>
                        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="cashbookTable">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="12%">Date</th>

                                        <th width="12%">Source</th>
                                        <th width="12%" class="text-danger">Debit (Out)</th>
                                        <th width="12%" class="text-success">Credit (In)</th>
                                        <th width="15%">Running Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="3" class="text-right">Opening Balance</td>
                                        <td></td>
                                        <td></td>
                                        <td class="text-right">₹ <?= number_format($opening_balance, 2) ?></td>
                                    </tr>
                                    <?php 
                                        $running = $opening_balance;
                                        $n = 1;
                                        foreach($entries as $e): 
                                            $running += ($e->credit - $e->debit);
                                    ?>
                                        <tr>
                                            <td class="text-center"><?= $n++ ?></td>
                                            <td class="text-center"><?= date('d-m-Y', strtotime($e->date)) ?></td>

                                            <td class="text-center"><span class="badge badge-light text-dark border"><?= $e->source ?></span></td>
                                            <td class="text-right text-danger"><?= $e->debit > 0 ? number_format($e->debit, 2) : '-' ?></td>
                                            <td class="text-right text-success"><?= $e->credit > 0 ? number_format($e->credit, 2) : '-' ?></td>
                                            <td class="text-right font-weight-bold">₹ <?= number_format($running, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td colspan="3" class="text-right">TOTAL</td>
                                        <td class="text-right text-danger">₹ <?= number_format($total_debit, 2) ?></td>
                                        <td class="text-right text-success">₹ <?= number_format($total_credit, 2) ?></td>
                                        <td class="text-right">Closing: ₹ <?= number_format($closing, 2) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center p-5 bg-white shadow-sm rounded">
                    <i class="fa fa-info-circle fa-4x text-muted mb-3"></i>
                    <h4>Please select a location and date range to view the Cashbook.</h4>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.select2').select2({
        width: '100%'
    });
});
</script>

<?php include("footer.php"); ?>