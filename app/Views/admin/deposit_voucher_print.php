<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Voucher - <?= $voucher_no ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .voucher-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .voucher-header {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .voucher-header h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .voucher-header h2 {
            font-size: 20px;
            color: #666;
            font-weight: normal;
        }
        
        .voucher-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-group {
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #3b82f6;
        }
        
        .info-group label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .info-group .value {
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }
        
        .challans-section {
            margin: 30px 0;
        }
        
        .challans-section h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .challans-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .challans-table thead {
            background: #f1f5f9;
        }
        
        .challans-table th {
            padding: 12px;
            text-align: left;
            font-size: 13px;
            color: #475569;
            font-weight: 600;
            border-bottom: 2px solid #cbd5e1;
        }
        
        .challans-table td {
            padding: 10px 12px;
            font-size: 14px;
            color: #334155;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .challans-table tbody tr:hover {
            background: #f8fafc;
        }
        
        .total-section {
            text-align: right;
            padding: 20px;
            background: #f1f5f9;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .total-section .total-label {
            font-size: 16px;
            color: #475569;
            margin-bottom: 5px;
        }
        
        .total-section .total-amount {
            font-size: 28px;
            color: #059669;
            font-weight: 700;
        }
        
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 40px;
            margin-top: 60px;
            padding-top: 40px;
            border-top: 2px solid #e2e8f0;
        }
        
        .signature-box {
            text-align: center;
        }
        
        .signature-line {
            border-top: 2px solid #333;
            margin-bottom: 10px;
            padding-top: 10px;
        }
        
        .signature-label {
            font-size: 13px;
            color: #666;
            font-weight: 600;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .print-button:hover {
            background: #2563eb;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .voucher-container {
                box-shadow: none;
                max-width: 100%;
            }
            
            .print-button {
                display: none;
            }
            
            @page {
                size: A4;
                margin: 15mm;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Print Voucher</button>
    
    <div class="voucher-container">
        <!-- Header -->
        <div class="voucher-header">
            <h1>DEPOSIT VOUCHER</h1>
            <h2>Type-2 Deposit Entry</h2>
        </div>
        
        <!-- Voucher Info -->
        <div class="voucher-info">
            <div class="info-group">
                <label>Voucher No</label>
                <div class="value"><?= $voucher_no ?></div>
            </div>
            
            <div class="info-group">
                <label>Date</label>
                <div class="value"><?= date('d-m-Y', strtotime($deposited_on)) ?></div>
            </div>
            
            <div class="info-group">
                <label>Party Name</label>
                <div class="value"><?= $party_name ?></div>
            </div>
            
            <div class="info-group">
                <label>No of Challans</label>
                <div class="value"><?= $no_of_challan ?></div>
            </div>
            
            <div class="info-group">
                <label>Deposited By</label>
                <div class="value"><?= $deposited_by ?></div>
            </div>
            
            <div class="info-group">
                <label>Deposit Place</label>
                <div class="value"><?= $deposit_place ?></div>
            </div>
        </div>
        
        <!-- Challans List -->
        <div class="challans-section">
            <h3>📋 Challan Details</h3>
            <table class="challans-table">
                <thead>
                    <tr>
                        <th>Sr. No</th>
                        <th>Date</th>
                        <th>DO No</th>
                        <th>Vehicle No</th>
                        <th>Challan No</th>
                        <th style="text-align: right;">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $sr = 1; foreach ($challans as $challan): ?>
                    <tr>
                        <td><?= $sr++ ?></td>
                        <td><?= date('d-m-Y', strtotime($challan->des_date)) ?></td>
                        <td><?= $challan->doreg_no ?></td>
                        <td><?= $challan->vehicle_number ?></td>
                        <td><?= $challan->ref_no ?? '-' ?></td>
                        <td style="text-align: right;">₹<?= number_format($challan->net_amount, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Total Amount -->
        <div class="total-section">
            <div class="total-label">Total Deposit Amount</div>
            <div class="total-amount">₹<?= number_format($total_amount, 2) ?></div>
        </div>
        
        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-label">Prepared By</div>
                </div>
            </div>
            
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-label">Verified By</div>
                </div>
            </div>
            
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-label">Authorized Signatory</div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-print dialog on page load (optional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>
