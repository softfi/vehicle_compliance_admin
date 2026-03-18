<?php
$voucher = $details['voucher'];
$entries = $details['entries'];
?>

<div class="row mb-3">
    <div class="col-6">
        <strong>Voucher No:</strong> <?= $voucher->voucher_no ?><br>
        <strong>Date:</strong> <?= date('d-m-Y', strtotime($voucher->voucher_date)) ?>
    </div>
    <div class="col-6 text-right">
        <strong>Voucher Type:</strong> <?= $voucher->voucher_type ?><br>
        <strong>Amount:</strong> <?= number_format($voucher->total_amount, 2) ?>
    </div>
</div>

<table class="table table-bordered table-sm">
    <thead class="bg-light">
        <tr>
            <th>Particulars (Ledger)</th>
            <th class="text-right">Debit</th>
            <th class="text-right">Credit</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($entries as $e): ?>
            <tr>
                <td>
                    <strong><?= $e->group_name ?></strong>: <?= $e->ledger_name ?><br>
                    <small class="text-muted"><?= $e->narration ?></small>
                </td>
                <td class="text-right"><?= ($e->entry_type == 1) ? number_format($e->amount, 2) : '' ?></td>
                <td class="text-right"><?= ($e->entry_type == 2) ? number_format($e->amount, 2) : '' ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot class="font-weight-bold">
        <tr>
            <td>Total</td>
            <td class="text-right"><?= number_format($voucher->total_amount, 2) ?></td>
            <td class="text-right"><?= number_format($voucher->total_amount, 2) ?></td>
        </tr>
    </tfoot>
</table>

<div class="mt-3">
    <strong>Global Narration:</strong><br>
    <p class="text-muted"><?= $voucher->narration ?: 'N/A' ?></p>
</div>
