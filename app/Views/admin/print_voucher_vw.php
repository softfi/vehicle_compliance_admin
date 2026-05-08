<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Voucher - <?= $details['voucher']->voucher_no ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { font-size: 14px; padding: 20px; }
        .voucher-header { border-bottom: 2px solid #333; margin-bottom: 20px; padding-bottom: 10px; }
        .company-name { font-size: 24px; font-weight: bold; text-transform: uppercase; }
        .voucher-title { font-size: 18px; font-weight: bold; text-decoration: underline; }
        .footer-sig { margin-top: 50px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container border p-4">
        <div class="voucher-header text-center">
            <div class="company-name"><?= $setting[0]->company_name ?></div>
            <div><?= $setting[0]->address ?></div>
            <div>GST: <?= $setting[0]->gst_no ?></div>
            <div class="voucher-title mt-2"><?= strtoupper($details['voucher']->voucher_type) ?> VOUCHER</div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <strong>Voucher No:</strong> <?= $details['voucher']->voucher_no ?><br>
                <strong>Date:</strong> <?= date('d-m-Y', strtotime($details['voucher']->voucher_date)) ?>
            </div>
            <div class="col-6 text-right">
                <strong>Financial Year:</strong> <?= date('Y', strtotime($details['voucher']->fy_from)) ?>-<?= date('y', strtotime($details['voucher']->fy_to)) ?>
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Particulars</th>
                    <th width="15%" class="text-right">Debit</th>
                    <th width="15%" class="text-right">Credit</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($details['entries'] as $e): ?>
                    <tr>
                        <td>
                            <strong><?= $e->group_name ?></strong>: <?= $e->ledger_name ?><br>
                            <small><?= $e->narration ?></small>
                        </td>
                        <td class="text-right"><?= ($e->entry_type == 1) ? number_format($e->amount, 2) : '' ?></td>
                        <td class="text-right"><?= ($e->entry_type == 2) ? number_format($e->amount, 2) : '' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="font-weight-bold">
                    <td class="text-right">TOTAL</td>
                    <td class="text-right"><?= number_format($details['voucher']->total_amount, 2) ?></td>
                    <td class="text-right"><?= number_format($details['voucher']->total_amount, 2) ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="mb-4">
            <strong>Narration:</strong> <?= $details['voucher']->narration ?>
        </div>

        <div class="row footer-sig text-center">
            <div class="col-4">
                <div style="border-top: 1px solid #000; padding-top: 5px;">Receiver's Signature</div>
            </div>
            <div class="col-4"></div>
            <div class="col-4">
                <div style="border-top: 1px solid #000; padding-top: 5px;">Authorized Signatory</div>
            </div>
        </div>
    </div>

    <div class="text-center mt-3 no-print">
        <button onclick="window.print()" class="btn btn-primary">Print Again</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>
</body>
</html>
