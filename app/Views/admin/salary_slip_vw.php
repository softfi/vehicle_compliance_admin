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

        @media print {
            body { background: white; padding: 0; margin: 0; }
            .page-container { max-width: 100%; margin: 0; }
            .salary-card { border: none; box-shadow: none; margin-bottom: 0; page-break-after: always; }
            .trip-card { border: none; box-shadow: none; margin-bottom: 0; page-break-before: auto; }
            .btn-print { display: none; }
            .summary-box  { background: #2c3e50 !important; -webkit-print-color-adjust: exact; }
            .trip-table th { background-color: #2c3e50 !important; color: white !important; -webkit-print-color-adjust: exact; }
            .diesel-fill-table th { background-color: #2c3e50 !important; color: white !important; -webkit-print-color-adjust: exact; }
            .oc-header-bar  { background: #2c3e50 !important; -webkit-print-color-adjust: exact; }
            .vehicle-block  { page-break-inside: avoid; }
        }

        /* ── Common ── */
        .header { text-align: center; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #2c3e50; text-transform: uppercase; letter-spacing: 2px; font-size: 26px; }
        .header p  { margin: 5px 0; color: #7f8c8d; font-weight: bold; }

        .info-table, .data-table, .trip-table, .diesel-fill-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 8px; border: 1px solid #eee; font-size: 14px; }
        .label { font-weight: bold; background-color: #f9f9f9; width: 25%; }

        .data-table th, .trip-table th, .diesel-fill-table th { background-color: #2c3e50; color: white; padding: 10px; text-align: left; }
        .data-table td, .trip-table td, .diesel-fill-table td { padding: 10px; border: 1px solid #ddd; font-size: 14px; }

        .total-row { background-color: #ecf0f1; font-weight: bold; }

        .summary-box { background: #2c3e50; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; border-radius: 5px; }
        .summary-box h2 { margin: 0; font-size: 18px; }
        .balance-highlight { color: #f1c40f; font-size: 20px; font-weight: bold; }

        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #999; border-top: 1px dashed #ccc; padding-top: 15px; }
        .signature-section { display: flex; justify-content: space-between; margin-top: 40px; font-weight: bold; color: #555; }
        .btn-print { position: fixed; top: 20px; right: 20px; z-index: 1000; padding: 10px 20px; background: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer; }

        .trip-table tfoot td, .diesel-fill-table tfoot td { background: #f8f9fa; font-weight: bold; padding: 10px; border: 1px solid #ddd; }
        .trip-count-badge { display: inline-block; background: #2c3e50; color: white; border-radius: 12px; padding: 2px 8px; font-size: 12px; font-weight: bold; }
        .vehicle-tag { display: inline-block; background: #eef6ff; color: #2c3e50; border: 1px solid #2c3e50; border-radius: 4px; padding: 1px 6px; font-size: 12px; margin: 1px; }
        .amount-cell { font-weight: bold; color: #333; text-align: right; }

        /* ══ Vehicle Diesel Block ══════════════════════════════════════ */
        .vehicle-block { border: 2px solid #2c3e50; border-radius: 6px; overflow: hidden; margin-bottom: 25px; }

        /* Top bar: Vehicle name + period */
        .veh-title-bar { background: #2c3e50; color: white; padding: 10px 16px; display: flex; justify-content: space-between; align-items: center; }
        .veh-title-bar .veh-name { font-size: 15px; font-weight: bold; }
        .veh-title-bar .veh-period { font-size: 12px; opacity: .8; }

        /* Opening / Closing row — sirf 4 boxes (KM hata diya) */
        .oc-header-bar { background: #1a252f; display: flex; }
        .oc-box { flex: 1; padding: 12px 10px; text-align: center; border-right: 1px solid rgba(255,255,255,.15); }
        .oc-box:last-child { border-right: none; }
        .oc-label { font-size: 10px; color: rgba(255,255,255,.65); text-transform: uppercase; letter-spacing: .8px; margin-bottom: 4px; }
        .oc-value { font-size: 20px; font-weight: bold; }
        .oc-value.yellow { color: #f1c40f; }
        .oc-value.green  { color: #2ecc71; }
        .oc-value.orange { color: #e67e22; }
        .oc-value.red    { color: #e74c3c; }
        .oc-sub { font-size: 10px; color: rgba(255,255,255,.5); margin-top: 2px; }

        /* Fill entries table inside block */
        .diesel-entries-inner { padding: 12px; }
        .diesel-fill-table th { font-size: 12px; padding: 8px 10px; }
        .diesel-fill-table td { font-size: 13px; padding: 8px 10px; }

        /* Bottom summary bar — sirf diesel (KM nahi) */
        .veh-summary-bar { display: flex; background: #eef6ff; border-top: 2px solid #2c3e50; }
        .vsb-box { flex: 1; padding: 10px; text-align: center; border-right: 1px solid #c8dcf0; }
        .vsb-box:last-child { border-right: none; }
        .vsb-label { font-size: 10px; color: #555; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 3px; }
        .vsb-val { font-size: 16px; font-weight: bold; }
        .vsb-val.blue   { color: #2c3e50; }
        .vsb-val.green  { color: #27ae60; }
        .vsb-val.orange { color: #e67e22; }
        .vsb-val.red    { color: #e74c3c; }
    </style>
</head>
<body>

<button class="btn-print" onclick="window.print()">🖨 Print Salary Slip</button>

<div class="page-container">

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- PAGE 1 — SALARY SUMMARY                                        -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="salary-card">
    <div class="header">
        <h1><?= $setting[0]->company_name ?? 'Yasuja Transport' ?></h1>
        <p>Salary Pay Slip | <?= $month_name ?> <?= $year ?></p>
        <p style="font-size:14px; color:#2c3e50; margin-top:5px;">
            Period: <?= date('d-M-Y', strtotime($eff_from)) ?> to <?= date('d-M-Y', strtotime($eff_to)) ?>
        </p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Name</td>
            <td><?= strtoupper($driver->name) ?> (<?= $driver->staff_code ?>)</td>
            <td class="label">Designation</td>
            <td>DRIVER</td>
        </tr>
        <tr>
            <td class="label">Location</td>
            <td><?= strtoupper($driver->location_name) ?></td>
            <td class="label">Total Trips</td>
            <td><?= count($trips) ?></td>
        </tr>
        <tr>
            <td class="label">Working Days</td>
            <td><?= $working_days ?> Days</td>
            <td class="label">Bank Name</td>
            <td><?= $driver->name_bank ?></td>
        </tr>
        <tr>
            <td class="label">IFSC Code</td>
            <td><?= $driver->ifsc ?></td>
            <td class="label">A/C No</td>
            <td><?= $driver->ac_no ?></td>
        </tr>
    </table>

    <div style="display:flex; gap:20px;">
        <table class="data-table" style="flex:1;">
            <thead><tr><th colspan="2">EARNINGS</th></tr></thead>
            <tr><td>Basic Salary</td>                  <td class="amount-cell">₹<?= number_format($basic_salary, 2) ?></td></tr>
            <tr><td>Trip Expenses</td>                 <td class="amount-cell">₹<?= number_format($trip_expenses, 2) ?></td></tr>
            <tr><td>Bonus / Incentives</td>            <td class="amount-cell">₹<?= number_format($bonus, 2) ?></td></tr>
            <tr><td>Opening Balance (Credit)</td>      <td class="amount-cell">₹<?= $opening > 0 ? number_format($opening, 2) : '0.00' ?></td></tr>
            <tr class="total-row">
                <td>Total Earnings</td>
                <td class="amount-cell">₹<?= number_format($basic_salary + $trip_expenses + $bonus + ($opening > 0 ? $opening : 0), 2) ?></td>
            </tr>
        </table>

        <table class="data-table" style="flex:1;">
            <thead><tr><th colspan="2">DEDUCTIONS</th></tr></thead>
            <tr><td>Salary Advance</td>                <td class="amount-cell">₹<?= number_format($salary_advance, 2) ?></td></tr>
            <tr><td>Trip Advance</td>                  <td class="amount-cell">₹<?= number_format($trip_advance, 2) ?></td></tr>
            <tr><td>Adjustments / Penalties</td>       <td class="amount-cell">₹<?= number_format($adjustment, 2) ?></td></tr>
            <tr><td>HSD / Diesel (Extra Usage)</td>    <td class="amount-cell">₹<?= number_format(abs($hsd_amount), 2) ?></td></tr>
            <tr><td>Opening Balance (Debit)</td>       <td class="amount-cell">₹<?= $opening < 0 ? number_format(abs($opening), 2) : '0.00' ?></td></tr>
            <tr class="total-row">
                <td>Total Deductions</td>
                <td class="amount-cell">₹<?= number_format($total_advance + $adjustment + abs($hsd_amount) + ($opening < 0 ? abs($opening) : 0), 2) ?></td>
            </tr>
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

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- PAGE 2 — DIESEL + TRIP STATEMENT                               -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="trip-card">

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- SECTION 1 — VEHICLE-WISE DIESEL STATEMENT                 -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <h3 style="color:#2c3e50; border-bottom:2px solid #2c3e50; padding-bottom:6px; margin-top:0; font-size:18px;">
        Diesel Statement — Vehicle Wise
    </h3>

    <?php if (empty($diesel_by_vehicle)): ?>
        <p style="text-align:center; color:#999; padding:20px 0;">No diesel entries found for this period.</p>
    <?php else: ?>

    <?php
    $grand_filled   = 0;
    $grand_consumed = 0;

    foreach ($diesel_by_vehicle as $vid => $vd):
        $opening_d = (float)$vd['opening_diesel'];
        $filled    = (float)$vd['total_filled'];
        $closing_d = (float)$vd['closing_diesel'];
        $total_av  = $opening_d + $filled;
        // Consumed = Total Available - Closing
        $consumed  = ($closing_d > 0) ? ($total_av - $closing_d) : $total_av;

        $grand_filled   += $filled;
        $grand_consumed += $consumed;
    ?>

    <div class="vehicle-block">

        <!-- Vehicle title bar -->
        <div class="veh-title-bar">
            <span class="veh-name">🚛 &nbsp;<?= htmlspecialchars($vd['vehicle_no']) ?></span>
            <span class="veh-period">
                <?= date('d-M-Y', strtotime($eff_from)) ?> &nbsp;→&nbsp; <?= date('d-M-Y', strtotime($eff_to)) ?>
            </span>
        </div>

        <!-- Opening / Filled / Total Available / Closing / Consumed — SIRF DIESEL, KM NAHI -->
        <div class="oc-header-bar">
            <div class="oc-box">
                <div class="oc-label">Opening Diesel</div>
                <div class="oc-value yellow"><?= number_format($opening_d, 2) ?> L</div>
                <!-- <div class="oc-sub">Assignment shuru pe</div> -->
            </div>
            <div class="oc-box">
                <div class="oc-label">Filled in Period</div>
                <div class="oc-value green"><?= number_format($filled, 2) ?> L</div>
                <div class="oc-sub"><?= count($vd['entries']) ?> fill entries</div>
            </div>
            <div class="oc-box">
                <div class="oc-label">Total Available</div>
                <div class="oc-value yellow"><?= number_format($total_av, 2) ?> L</div>
                <div class="oc-sub">Opening + Filled</div>
            </div>
            <div class="oc-box">
                <div class="oc-label">Closing Diesel</div>
                <div class="oc-value orange"><?= number_format($closing_d, 2) ?> L</div>
                <!-- <div class="oc-sub">Assignment khatam pe</div> -->
            </div>
            <div class="oc-box">
                <div class="oc-label">Consumed</div>
                <div class="oc-value <?= $consumed > 0 ? 'red' : 'green' ?>"><?= number_format($consumed, 2) ?> L</div>
                <div class="oc-sub">Available − Closing</div>
            </div>
        </div>

        <!-- Fill entries table -->
        <div class="diesel-entries-inner">
            <table class="diesel-fill-table">
                <thead>
                    <tr>
                        <th style="width:40px; text-align:center;">SN</th>
                        <th>Date</th>
                        <th>Pump / Vendor</th>
                        <th style="text-align:right;">Qty Filled (L)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $sn = 1; foreach ($vd['entries'] as $de): ?>
                    <tr>
                        <td style="text-align:center;"><?= str_pad($sn++, 2, '0', STR_PAD_LEFT) ?></td>
                        <td><?= date('d-M-Y', strtotime($de->diesel_date)) ?></td>
                        <td><?= htmlspecialchars($de->vendor_name ?: '-') ?></td>
                        <td style="text-align:right; font-weight:bold;"><?= number_format($de->qty, 2) ?> L</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right;">Total Diesel Filled:</td>
                        <td style="text-align:right; color:#2c3e50;"><?= number_format($filled, 2) ?> L</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Bottom summary bar — SIRF DIESEL -->
        <div class="veh-summary-bar">
            <div class="vsb-box">
                <div class="vsb-label">Opening</div>
                <div class="vsb-val blue"><?= number_format($opening_d, 2) ?> L</div>
            </div>
            <div class="vsb-box">
                <div class="vsb-label">+ Filled</div>
                <div class="vsb-val green"><?= number_format($filled, 2) ?> L</div>
            </div>
            <div class="vsb-box">
                <div class="vsb-label">= Available</div>
                <div class="vsb-val blue"><?= number_format($total_av, 2) ?> L</div>
            </div>
            <div class="vsb-box">
                <div class="vsb-label">− Closing</div>
                <div class="vsb-val orange"><?= number_format($closing_d, 2) ?> L</div>
            </div>
            <div class="vsb-box">
                <div class="vsb-label">= Consumed</div>
                <div class="vsb-val <?= $consumed > 0 ? 'red' : 'green' ?>"><?= number_format($consumed, 2) ?> L</div>
            </div>
        </div>

    </div><!-- .vehicle-block -->

    <?php endforeach; ?>

    <!-- Grand total bar (all vehicles) -->
    <div style="display:flex; background:#2c3e50; color:white; border-radius:5px; padding:12px 20px; margin-bottom:30px; gap:10px;">
        <div style="flex:1; text-align:center;">
            <div style="font-size:10px; opacity:.7; text-transform:uppercase; margin-bottom:4px;">Total Filled — All Vehicles</div>
            <div style="font-size:20px; font-weight:bold; color:#f1c40f;"><?= number_format($grand_filled, 2) ?> L</div>
        </div>
        <div style="flex:1; text-align:center;">
            <div style="font-size:10px; opacity:.7; text-transform:uppercase; margin-bottom:4px;">Total Consumed — All Vehicles</div>
            <div style="font-size:20px; font-weight:bold; color:#e74c3c;"><?= number_format($grand_consumed, 2) ?> L</div>
        </div>
    </div>

    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- SECTION 2 — DO-WISE TRIP DETAILS                          -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <h3 style="color:#2c3e50; border-bottom:2px solid #2c3e50; padding-bottom:6px; margin-top:0; font-size:18px;">
        Trip Details — DO Wise (Assignment Period)
    </h3>

    <table class="trip-table">
        <thead>
            <tr>
                <th style="width:45px; text-align:center;">SN</th>
                <th>DO No</th>
                <th>Vehicle(s)</th>
                <th>Route</th>
                <th style="text-align:center;">No. of Trips</th>
                <th style="text-align:right;">Total Diesel Req (L)</th>
                <!-- <th style="text-align:right;">Total Diesel Req (L)</th> -->
            </tr>
        </thead>
        <tbody>
        <?php
        $sn = 1;
        $grand_diesel_total = 0;
        $grand_trip_total   = 0;

        foreach ($do_grouped as $do_key => $do_data):
            $total_diesel_for_do  = $do_data['diesel_per_trip'] * $do_data['trip_count'];
            $grand_diesel_total  += $total_diesel_for_do;
            $grand_trip_total    += $do_data['trip_count'];

            $vehicle_html = implode(' ', array_map(
                fn($v) => '<span class="vehicle-tag">' . htmlspecialchars($v) . '</span>',
                $do_data['vehicles']
            ));
            $dates_title = implode(', ', $do_data['dates']);
        ?>
            <tr>
                <td style="text-align:center;"><?= str_pad($sn++, 2, '0', STR_PAD_LEFT) ?></td>
                <td><strong><?= htmlspecialchars($do_data['do_reg_no'] ?: '-') ?></strong></td>
                <td><?= $vehicle_html ?></td>
                <td><?= htmlspecialchars($do_data['route']) ?></td>
                <td style="text-align:center;">
                    <span class="trip-count-badge" title="Trip dates: <?= htmlspecialchars($dates_title) ?>">
                        <?= $do_data['trip_count'] ?>
                    </span>
                </td>
                <td style="text-align:right;"><?= number_format($do_data['diesel_per_trip'], 2) ?> L</td>
                <!-- <td style="text-align:right; font-weight:bold; color:#e67e22;"><?= number_format($total_diesel_for_do, 2) ?> L</td> -->
            </tr>
        <?php endforeach; ?>

        <?php if (empty($do_grouped)): ?>
            <tr><td colspan="7" style="text-align:center; color:#999; padding:20px;">No trip records found for this period.</td></tr>
        <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right;">Grand Total</td>
                <td style="text-align:center; font-size:15px; color:#2c3e50;"><?= $grand_trip_total ?> Trips</td>
                <td style="text-align:right; font-size:15px; color:#e67e22;"><?= number_format($grand_diesel_total, 2) ?> L</td>
                <!-- <td style="text-align:right;">—</td> -->
            </tr>
        </tfoot>
    </table>

    <!-- Diesel Summary Strip -->
    <?php
    $total_fill_all     = 0;
    $grand_consumed_all = 0;
    foreach ($diesel_by_vehicle as $vd) {
        $total_fill_all += (float)$vd['total_filled'];
        $_op_d = (float)$vd['opening_diesel'];
        $_cl_d = (float)$vd['closing_diesel'];
        $_av   = $_op_d + (float)$vd['total_filled'];
        $grand_consumed_all += ($_cl_d > 0) ? ($_av - $_cl_d) : $_av;
    }
    $diff = $grand_consumed_all - $grand_diesel_total;
    ?>
    <div style="display:flex; gap:15px; padding:15px; border:1px solid #2c3e50; background:#eef6ff; border-radius:5px; margin-top:5px;">
        <div style="flex:1; text-align:center;">
            <div style="font-size:11px; color:#555; text-transform:uppercase; margin-bottom:4px;">Total Consumed</div>
            <div style="font-size:18px; font-weight:bold; color:#27ae60;"><?= number_format($grand_consumed_all, 2) ?> L</div>
        </div>
        <div style="flex:1; text-align:center;">
            <div style="font-size:11px; color:#555; text-transform:uppercase; margin-bottom:4px;">Total Diesel Required (DO)</div>
            <div style="font-size:18px; font-weight:bold; color:#e67e22;"><?= number_format($grand_diesel_total, 2) ?> L</div>
        </div>
        <div style="flex:1; text-align:center;">
            <div style="font-size:11px; color:#555; text-transform:uppercase; margin-bottom:4px;">Difference (Extra Used)</div>
            <div style="font-size:18px; font-weight:bold; color:<?= $diff > 0 ? '#e74c3c' : '#27ae60' ?>;">
                <?= ($diff > 0 ? '+' : '') . number_format($diff, 2) ?> L
            </div>
        </div>
    </div>

    <div class="signature-section">
        <div>Verified By: _________________</div>
        <div>Driver's Signature: _________________</div>
    </div>

    <div class="footer">
        <p>End of Trip Statement for <?= $month_name ?> <?= $year ?>.</p>
    </div>

</div><!-- .trip-card -->
</div><!-- .page-container -->
</body>
</html>
