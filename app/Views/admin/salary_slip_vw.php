<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip - <?= $driver->name ?> - <?= $month_name ?> <?= $year ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; padding: 20px; color: #333; margin: 0; }
        
        .page-container { max-width: 900px; margin: auto; }
        
        .salary-card, .trip-card { background: white; padding: 30px; border: 1px solid #ddd; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-bottom: 40px; position: relative; }
        
        /* Page Break for Printing */
        @media print {
            body { background: white; padding: 0; margin: 0; }
            .page-container { max-width: 100%; margin: 0; }
            .salary-card { border: none; box-shadow: none; margin-bottom: 0; page-break-after: always; }
            .trip-card { border: none; box-shadow: none; margin-bottom: 0; page-break-before: auto; }
            .btn-print { display: none; }
            .summary-box { background: #2c3e50 !important; -webkit-print-color-adjust: exact; }
            .trip-table th { background-color: #2c3e50 !important; color: white !important; -webkit-print-color-adjust: exact; }
        }

        /* Common Header */
        .header { text-align: center; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #2c3e50; text-transform: uppercase; letter-spacing: 2px; font-size: 26px; }
        .header p { margin: 5px 0; color: #7f8c8d; font-weight: bold; }
        
        /* Tables */
        .info-table, .data-table, .trip-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 8px; border: 1px solid #eee; font-size: 14px; }
        .label { font-weight: bold; background-color: #f9f9f9; width: 25%; }
        
        .data-table th, .trip-table th { background-color: #2c3e50; color: white; padding: 10px; text-align: left; }
        .data-table td, .trip-table td { padding: 10px; border: 1px solid #ddd; font-size: 14px; }
        
        .total-row { background-color: #ecf0f1; font-weight: bold; }
        
        /* Summary Box Page 1 */
        .summary-box { background: #2c3e50; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; border-radius: 5px; }
        .summary-box h2 { margin: 0; font-size: 18px; }
        .balance-highlight { color: #f1c40f; font-size: 20px; font-weight: bold; }

        /* Trip Section Page 1 */
        .trip-section { margin-top: 20px; font-size: 13px; color: #555; border-top: 1px solid #eee; padding-top: 10px; }
        
        /* Page 2 Specifics */
        .summary-strip { background: #eef6ff; padding: 10px 15px; border-radius: 5px; margin-bottom: 20px; display: flex; justify-content: space-around; border: 1px solid #2c3e50; }
        .summary-strip div { text-align: center; }
        .summary-strip span { display: block; font-size: 11px; color: #555; text-transform: uppercase; }
        .summary-strip strong { font-size: 15px; color: #2c3e50; }

        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .badge-completed { background-color: #d1e7dd; color: #0f5132; }
        .amount-cell { font-weight: bold; color: #333; text-align: right; }
        
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #999; border-top: 1px dashed #ccc; padding-top: 15px; }
        .signature-section { display: flex; justify-content: space-between; margin-top: 40px; font-weight: bold; color: #555; }

        .btn-print { position: fixed; top: 20px; right: 20px; z-index: 1000; padding: 10px 20px; background: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

<button class="btn-print" onclick="window.print()">Print Salary Slip</button>

<div class="page-container">
    <!-- PAGE 1: SALARY SLIP SUMMARY -->
    <div class="salary-card">
        <div class="header">
            <h1><?= $setting[0]->company_name ?? 'Yasuja Transport' ?></h1>
            <p>Salary Pay Slip | <?= $month_name ?> <?= $year ?></p>
            <p style="font-size: 14px; color: #2c3e50; margin-top: 5px;">
                Period: <?= date('d-M-Y', strtotime($eff_from)) ?> to <?= date('d-M-Y', strtotime($eff_to)) ?>
            </p>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Driver Name</td>
                <td><?= strtoupper($driver->name) ?></td>
                <td class="label">Employee Code</td>
                <td><?= $driver->staff_code ?></td>
            </tr>
            <tr>
                <td class="label">Location</td>
                <td><?= strtoupper($driver->location_name) ?></td>
                <td class="label">Designation</td>
                <td>DRIVER</td>
            </tr>
            <tr>
                <td class="label">Working Days</td>
                <td><?= $working_days ?> Days</td>
                <td class="label">Total Trips</td>
                <td><?= count($trips) ?></td>
            </tr>
            <tr>
                <td class="label">Bank Name</td>
                <td><?= $driver->name_bank ?></td>
                <td class="label">A/C No</td>
                <td><?= $driver->ac_no ?></td>
            </tr>
        </table>

        <div style="display: flex; gap: 20px;">
            <table class="data-table" style="flex: 1;">
                <thead>
                    <tr><th colspan="2">EARNINGS</th></tr>
                </thead>
                <tr><td>Basic Salary</td><td class="amount-cell">₹<?= number_format($basic_salary, 2) ?></td></tr>
                <tr><td>Trip Expenses</td><td class="amount-cell">₹<?= number_format($trip_expenses, 2) ?></td></tr>
                <tr><td>Bonus / Incentives</td><td class="amount-cell">₹<?= number_format($bonus, 2) ?></td></tr>
                <tr><td>Opening Balance (Credit)</td><td class="amount-cell">₹<?= $opening > 0 ? number_format($opening, 2) : '0.00' ?></td></tr>
                <tr class="total-row"><td>Total Earnings</td><td class="amount-cell">₹<?= number_format($basic_salary + $trip_expenses + $bonus + ($opening > 0 ? $opening : 0), 2) ?></td></tr>
            </table>

            <table class="data-table" style="flex: 1;">
                <thead>
                    <tr><th colspan="2">DEDUCTIONS</th></tr>
                </thead>
                <tr><td>Salary Advance</td><td class="amount-cell">₹<?= number_format($salary_advance, 2) ?></td></tr>
                <tr><td>Trip Advance</td><td class="amount-cell">₹<?= number_format($trip_advance, 2) ?></td></tr>
                <tr><td>Adjustments / Penalties</td><td class="amount-cell">₹<?= number_format($adjustment, 2) ?></td></tr>
                <tr><td>HSD / Diesel (Extra Usage)</td><td class="amount-cell">₹<?= number_format(abs($hsd_amount), 2) ?></td></tr>
                <tr><td>Opening Balance (Debit)</td><td class="amount-cell">₹<?= $opening < 0 ? number_format(abs($opening), 2) : '0.00' ?></td></tr>
                <tr class="total-row"><td>Total Deductions</td><td class="amount-cell">₹<?= number_format($total_advance + $adjustment + abs($hsd_amount) + ($opening < 0 ? abs($opening) : 0), 2) ?></td></tr>
            </table>
        </div>

        <div class="summary-box">
            <div>
                <h2>Net Salary Payable: ₹<?= number_format($net_salary, 2) ?></h2>
                <small>Paid Amount: ₹<?= number_format($total_paid, 2) ?></small>
            </div>
            <div>
                <span>REMAINING BALANCE: </span>
                <span class="balance-highlight">₹<?= number_format($balance, 2) ?></span>
            </div>
        </div>

        <div class="footer">
            <p>This is a computer-generated salary slip and does not require a physical signature.</p>
            <p><?= $setting[0]->company_name ?? 'Yasuja Transport' ?> © <?= $year ?></p>
        </div>
    </div>

    <!-- PAGE 2: TRIP STATEMENT -->
    <div class="trip-card">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #2c3e50; padding-bottom: 5px; margin-top: 0; font-size: 18px;">Diesel Receipt Statement</h3>
        <table class="trip-table" style="margin-bottom: 20px;">
            <thead>
                <tr>
                    <th style="width: 50px;">SN</th>
                    <th>Date</th>
                    <th>Pump/Location</th>
                    <th style="text-align: right;">Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $d_sn = 1;
                $total_fill = 0;
                foreach($diesel_entries_list as $de): 
                    $total_fill += (float)$de->qty;
                ?>
                <tr>
                    <td><?= str_pad($d_sn++, 2, '0', STR_PAD_LEFT) ?></td>
                    <td><?= date('d-M-y', strtotime($de->diesel_date)) ?></td>
                    <td><?= $de->vendor_name ?: '-' ?></td>
                    <td style="text-align: right;"><?= number_format($de->qty, 2) ?> L</td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($diesel_entries_list)): ?>
                <tr><td colspan="4" style="text-align: center; color: #999; padding: 10px;">No diesel entries found for this period.</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr style="background: #f8f9fa; font-weight: bold;">
                    <td colspan="3" style="text-align: right; padding: 10px;">Total Diesel Received:</td>
                    <td style="text-align: right; padding: 10px;"><?= number_format($total_fill, 2) ?> L</td>
                </tr>
            </tfoot>
        </table>

        <h3 style="color: #2c3e50; border-bottom: 2px solid #2c3e50; padding-bottom: 5px; margin-top: 30px; font-size: 18px;">Trip Details (Assignment Period)</h3>
        <table class="trip-table">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Date</th>
                    <th>Vehicle</th>
                    <th>DO No</th>
                    <th>Location</th>
                    <th>D. Req</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sn = 1;
                $shown_dos = [];
                $total_diesel_req_sum = 0;
                foreach($trips as $trip): 
                    $current_trip_req = '-';
                    if (!in_array($trip->do_no, $shown_dos)) {
                        $current_trip_req = $trip->diesel_required;
                        $total_diesel_req_sum += (float)$trip->diesel_required;
                        $shown_dos[] = $trip->do_no;
                    }
                ?>
                <tr>
                    <td><?= str_pad($sn++, 2, '0', STR_PAD_LEFT) ?></td>
                    <td><?= date('d-M-y', strtotime($trip->des_date)) ?></td>
                    <td><?= $trip->reg_no ?></td>
                    <td><?= $trip->do_reg_no ?></td>
                    <td><?= ($trip->from_city && $trip->trip_location) ? $trip->from_city.' to '.$trip->trip_location : ($trip->trip_location ?: $trip->route_name) ?></td>
                    <td><?= $current_trip_req ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($trips)): ?>
                <tr><td colspan="6" style="text-align: center; color: #999; padding: 20px;">No trip records found for this period.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="margin-top: 20px; text-align: right; padding: 15px; border-top: 2px solid #2c3e50; background: #f9f9f9;">
            <strong style="font-size: 16px; color: #2c3e50;">TOTAL DIESEL REQUIRED (DO): </strong>
            <span style="font-size: 20px; font-weight: bold; color: #e67e22;"><?= number_format($total_diesel_req_sum, 2) ?> L</span>
        </div>

        <div class="signature-section">
            <div>Verified By: _________________</div>
            <div>Driver's Signature: _________________</div>
        </div>

        <div class="footer">
            <p>End of Trip Statement for <?= $month_name ?> <?= $year ?>.</p>
        </div>
    </div>
</div>

</body>
</html>
