<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advance Payment Receipt - <?= $advance->staff_name ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 30px; background-color: #fff; color: #333; line-height: 1.6; }
        .receipt-container { max-width: 600px; margin: auto; border: 2px solid #2c3e50; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #2c3e50; text-transform: uppercase; font-size: 24px; letter-spacing: 1px; }
        .header p { margin: 5px 0; color: #7f8c8d; font-size: 14px; font-weight: bold; }
        .receipt-details { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .receipt-details td { padding: 12px 10px; border-bottom: 1px solid #eee; font-size: 15px; }
        .label { font-weight: bold; color: #555; width: 35%; background-color: #f8f9fa; }
        .amount-box { text-align: center; background: #2c3e50; color: white; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .amount-box h2 { margin: 0; font-size: 28px; }
        .footer { margin-top: 40px; }
        .signature-grid { display: flex; justify-content: space-between; margin-top: 50px; font-weight: bold; color: #34495e; }
        .signature-line { border-top: 1px solid #2c3e50; padding-top: 5px; width: 180px; text-align: center; }
        .btn-print { display: block; width: 100px; margin: 20px auto; padding: 10px; background: #2c3e50; color: white; text-align: center; text-decoration: none; border-radius: 4px; cursor: pointer; border: none; font-weight: bold; }
        
        @media print {
            .btn-print { display: none; }
            body { padding: 0; }
            .receipt-container { box-shadow: none; border: 2px solid #000; margin: 0 auto; }
        }
    </style>
</head>
<body>

<button class="btn-print" onclick="window.print()">🖨 Print</button>

<div class="receipt-container">
    <div class="header">
        <h1><?= $setting[0]->company_name ?? 'Yasuja Transport' ?></h1>
        <p>Advance Payment Receipt</p>
    </div>

    <table class="receipt-details">
        <tr>
            <td class="label">Receipt No.</td>
            <td>#ADV-<?= str_pad($advance->id, 5, '0', STR_PAD_LEFT) ?></td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td><?= date('d-M-Y', strtotime($advance->adv_date)) ?></td>
        </tr>
        <tr>
            <td class="label">Employee Name</td>
            <td><strong><?= strtoupper($advance->staff_name) ?></strong> (<?= $advance->staff_code ?>)</td>
        </tr>
        <tr>
            <td class="label">Payment Mode</td>
            <td><?= $advance->bank_cash ?></td>
        </tr>
        <tr>
            <td class="label">Cash Paid By</td>
            <td><?= $advance->paid_by ?: 'N/A' ?></td>
        </tr>
        <tr>
            <td class="label">Location</td>
            <td><?= $advance->location_name ?: 'Global' ?></td>
        </tr>
        <tr>
            <td class="label">Narration</td>
            <td>Advance amount issued for personal/official use.</td>
        </tr>
    </table>

    <div class="amount-box">
        <p style="margin-bottom: 5px; opacity: 0.8; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Amount Issued</p>
        <h2>₹<?= number_format($advance->amount, 2) ?></h2>
    </div>

    <div class="signature-grid">
        <div class="signature-line">Authorized Signatory</div>
        <div class="signature-line">Receiver's Signature</div>
    </div>

</div>

</body>
</html>
