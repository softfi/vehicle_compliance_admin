<?php include("header.php"); ?>
<style>
    #myTable {
        min-width: 2500px;
    }

    #myTable thead th:nth-child(1),
    #myTable tbody td:nth-child(1) {
        min-width: 60px;
        width: 60px;
    }

    #myTable thead th:nth-child(2),
    #myTable tbody td:nth-child(2) {
        min-width: 100px;
        width: 100px;
    }

    #myTable thead th:nth-child(3),
    #myTable tbody td:nth-child(3) {
        min-width: 90px;
        width: 90px;
    }

    #myTable thead th:nth-child(4),
    #myTable tbody td:nth-child(4) {
        min-width: 110px;
        width: 110px;
    }

    #myTable thead th:nth-child(5),
    #myTable tbody td:nth-child(5) {
        min-width: 100px;
        width: 100px;
    }

    #myTable thead th:nth-child(6),
    #myTable tbody td:nth-child(6) {
        min-width: 100px;
        width: 100px;
    }

    #myTable thead th:nth-child(7),
    #myTable tbody td:nth-child(7) {
        min-width: 80px;
        width: 80px;
    }

    #myTable thead th:nth-child(8),
    #myTable tbody td:nth-child(8) {
        min-width: 80px;
        width: 80px;
    }

    #myTable thead th:nth-child(9),
    #myTable tbody td:nth-child(9) {
        min-width: 100px;
        width: 100px;
    }

    #myTable thead th:nth-child(10),
    #myTable tbody td:nth-child(10) {
        min-width: 100px;
        width: 100px;
    }

    #myTable thead th:nth-child(11),
    #myTable tbody td:nth-child(11) {
        display: none;
    }

    #myTable thead th:nth-child(12),
    #myTable tbody td:nth-child(12) {
        display: none;
    }

    #myTable thead th:nth-child(13),
    #myTable tbody td:nth-child(13) {
        min-width: 110px;
        width: 110px;
    }

    #myTable thead th:nth-child(14),
    #myTable tbody td:nth-child(14) {
        min-width: 100px;
        width: 100px;
    }

    #myTable thead th:nth-child(15),
    #myTable tbody td:nth-child(15) {
        min-width: 110px;
        width: 110px;
    }

    #myTable thead th:nth-child(16),
    #myTable tbody td:nth-child(16) {
        min-width: 90px;
        width: 90px;
    }

    #myTable thead th:nth-child(17),
    #myTable tbody td:nth-child(17) {
        min-width: 100px;
        width: 100px;
    }

    #myTable thead th:nth-child(18),
    #myTable tbody td:nth-child(18) {
        min-width: 80px;
        width: 80px;
    }

    #myTable thead th:nth-child(19),
    #myTable tbody td:nth-child(19) {
        min-width: 90px;
        width: 90px;
    }

    #myTable thead th:nth-child(20),
    #myTable tbody td:nth-child(20) {
        min-width: 100px;
        width: 100px;
    }

    #myTable thead th:nth-child(21),
    #myTable tbody td:nth-child(21) {
        min-width: 150px;
        width: 150px;
    }

    /* Group ID - Increased width */
    #myTable thead th:nth-child(22),
    #myTable tbody td:nth-child(22) {
        min-width: 80px;
        width: 80px;
    }

    /* Action column */

    .row-checkbox {
        width: 16px;
        height: 16px;
        cursor: pointer;
        margin-right: 5px;
        vertical-align: middle;
    }

    .bilty-inline-wrapper {
        display: flex;
        gap: 8px;
        align-items: flex-end;
    }

    .bilty-inline-wrapper input {
        flex: 1;
    }

    .bilty-inline-wrapper button {
        white-space: nowrap;
        padding: 10px 16px;
    }

    .bilty-inline-wrapper small {
        display: block;
        color: #64748b;
        font-size: 11px;
        margin-top: 4px;
    }

    /* Group ID Badge Styling */
    .group-id-badge {
        display: inline-block;
        padding: 4px 8px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 140px;
    }

    .badge-primary {
        background-color: #3b82f6;
        color: white;
    }
</style>
<?php
$records_per_page = isset($_GET['per_page']) && $_GET['per_page'] === 'all' ? 'all' : (isset($records_per_page) ? $records_per_page : 10);
$current_page = isset($current_page) ? $current_page : 1;
$total_records = isset($total_count) ? $total_count : count($despatch);
if ($records_per_page === 'all') {
    $total_pages = 1;
    $current_page = 1;
} else {
    $total_pages = ceil($total_records / $records_per_page);
}
?>
<div class="page-body-wrapper voucher-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="filter-card">
                <h5>📋 Collection View</h5>
                <form method="get" action="<?php echo base_url(); ?>/Admin/Collection">
                    <?php $default_from_date = $date['from_date'] ?? date('Y-m-01');
                    $default_to_date = $date['to_date'] ?? date('Y-m-d'); ?>
                    <input type="hidden" name="per_page" id="hidden_per_page" value="<?= $records_per_page ?>">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group-custom"><label for="from_date">From Date</label><input type="date"
                                    id="from_date" name="from_date" class="form-control-custom"
                                    value="<?= $default_from_date; ?>" onchange="updateDoRegistrations()" /></div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group-custom"><label for="to_date">To Date</label><input type="date"
                                    id="to_date" name="to_date" class="form-control-custom"
                                    value="<?= $default_to_date; ?>" onchange="updateDoRegistrations()" /></div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group-custom"><label for="do_no">DO No.</label><select
                                    class="form-control-custom" name="do_no" id="single" onchange="updateVouchers()">
                                    <option value="">Select DO No.</option><?php foreach ($doregistration as $do): ?>
                                        <option value="<?= $do->do_registration_id; ?>" <?= isset($date['do_no']) && $date['do_no'] == $do->do_registration_id ? 'selected' : '' ?>><?= $do->do_no; ?>
                                        </option><?php endforeach; ?>
                                </select></div>
                        </div>
                        <!-- <div class="col-md-3">
                            <div class="form-group-custom">
                                <label for="voucher_id">Voucher</label>
                                <select class="form-control-custom" name="voucher_id" id="single2">
                                    <option value="">All Vouchers</option>
                                    <?php if (isset($vouchers) && !empty($vouchers)):
                                        foreach ($vouchers as $v): ?>
                                    <option value="<?= $v->id; ?>" <?= isset($date['voucher_id']) && $date['voucher_id'] == $v->id ? 'selected' : '' ?>>
                                        <?= $v->group_code; ?>
                                    </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                        </div> -->
                        <div class="col-md-3">
                            <div class="form-group-custom"><label for="chalan_status">Chalan Status</label><select
                                    class="form-control-custom" name="chalan_status" id="chalan_status">
                                    <option value="" <?= !isset($date['chalan_status']) || $date['chalan_status'] === '' ? 'selected' : '' ?>>All</option>
                                    <option value="1" <?= isset($date['chalan_status']) && $date['chalan_status'] == '1' ? 'selected' : '' ?>>Received</option>
                                    <option value="2" <?= isset($date['chalan_status']) && $date['chalan_status'] == '2' ? 'selected' : '' ?>>Not Received</option>
                                </select></div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label for="search">Search</label>
                                <input type="text" name="search" id="search" class="form-control-custom"
                                    placeholder="Vehicle / Challan / DO No" value="<?= $date['search'] ?? ''; ?>">
                            </div>
                        </div>
                        <!-- <div class="col-md-3"><div class="form-group-custom"><label for="payment_status">Payment Status</label><select class="form-control-custom" name="payment_status" id="payment_status"><option value="" <?= !isset($date['payment_status']) || $date['payment_status'] === '' ? 'selected' : '' ?>>All</option><option value="1" <?= isset($date['payment_status']) && $date['payment_status'] == '1' ? 'selected' : '' ?>>Paid</option><option value="0" <?= isset($date['payment_status']) && $date['payment_status'] == '0' ? 'selected' : '' ?>>Unpaid</option></select></div></div> -->
                        <!-- <div class="col-md-3"><div class="form-group-custom"><label for="deposited_status">Added Status</label><select class="form-control-custom" name="deposited_status" id="deposited_status"><option value="" <?= !isset($date['deposited_status']) || $date['deposited_status'] === '' ? 'selected' : '' ?>>All</option><option value="1" <?= isset($date['deposited_status']) && $date['deposited_status'] == '1' ? 'selected' : '' ?>>Added</option><option value="0" <?= isset($date['deposited_status']) && $date['deposited_status'] == '0' ? 'selected' : '' ?>>Not Added</option></select></div></div> -->
                    </div>
                    <div class="btn-group-custom">
                        <button type="submit" class="btn-custom btn-primary-custom"><i class="fa fa-filter"></i> Apply
                            Filters</button>
                        <button type="button" onclick="exportExcel()" class="btn-custom btn-success-custom"><i
                                class="fa fa-file-excel-o"></i> Export Excel</button>
                        <button type="button" onclick="exportPDF()" class="btn-custom btn-danger-custom"
                            style="background-color: #dc3545; color: white;"><i class="fa fa-file-pdf-o"></i> Export
                            PDF</button>
                    </div>
                </form>
            </div>

            <!-- Action Controls Bar -->
            <div class="action-controls-bar"
                style="display: flex; align-items: center; flex-wrap: wrap; gap: 12px; padding: 15px 20px; background: #f8fafc; border-radius: 8px; margin-bottom: 20px;">
                <!-- Bulk Actions Group -->
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" class="btn-custom btn-success-custom"
                        style="padding: 8px 16px; display: flex; align-items: center; gap: 8px;"
                        onclick="bulkUpdateSelected('<?= isset($singleuser[0]->full_name) ? htmlspecialchars($singleuser[0]->full_name, ENT_QUOTES) : ''; ?>')">
                        <i class="fa fa-refresh"></i> Save Changes
                    </button>

                    <div class="btn-group" role="group" aria-label="Group Tools" style="display: flex; gap: 5px;">
                        <button type="button" class="btn-custom btn-primary-custom"
                            style="padding: 8px 16px; display: flex; align-items: center; gap: 8px;"
                            onclick="createCollectionGroup()">
                            <i class="fa fa-plus-circle"></i> Create Voucher
                        </button>
                        <!-- <button type="button" class="btn-custom" style="background-color: #17a2b8; color: white; padding: 8px 16px; display: flex; align-items: center; gap: 8px;" onclick="openAddToGroupModal()">
                            <i class="fa fa-folder-open"></i> Add to Voucher
                        </button>
                        <button type="button" class="btn-custom" style="background-color: #dc3545; color: white; padding: 8px 16px; display: flex; align-items: center; gap: 8px;" onclick="removeFromGroup()">
                            <i class="fa fa-unlink"></i> Remove from Voucher
                        </button> -->
                    </div>
                </div>

                <!-- Bilty Bulk Controls -->
                <div class="bilty-bulk-controls"
                    style="display: flex; align-items: center; gap: 8px; padding-left: 20px; border-left: 1px solid #e2e8f0;">
                    <input type="number" id="bulk_bilty_commission" step="0.01" class="form-control-custom"
                        placeholder="Bilty Bulk" style="width: 100px;" />
                    <button type="button" class="btn-custom btn-primary-custom" style="padding: 8px 16px;"
                        onclick="updateAllBiltyCommission()">
                        <i class="fa fa-refresh"></i> Apply Bilty
                    </button>
                </div>

                <!-- Search Box -->
                <div class="search-box" style="flex: 1; max-width: 300px; margin-left: auto;">
                    <input type="text" id="tableSearch" class="form-control-custom" placeholder="🔍 Search in table..."
                        value="<?= $date['search'] ?? ''; ?>"
                        onkeypress="if(event.keyCode==13){ window.location.href='?search='+this.value; }">
                </div>

                <!-- Records Per Page -->
                <div class="records-per-page" style="display: flex; align-items: center; gap: 8px;">
                    <label for="per_page" style="margin: 0; white-space: nowrap;">Records per page:</label>
                    <select id="per_page" class="form-control-custom" onchange="changePerPage()" style="width: 100px;">
                        <option value="10" <?= $records_per_page == 10 ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= $records_per_page == 25 ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= $records_per_page == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $records_per_page == 100 ? 'selected' : '' ?>>100</option>
                        <option value="all" <?= $records_per_page == 'all' ? 'selected' : '' ?>>Show All</option>
                    </select>
                </div>
            </div>

            <div class="table-wrapper">
                <table id="myTable">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"> Sl No</th>
                            <th>Date</th>
                            <th>DO No</th>
                            <th>Vehicle No</th>
                            <th>Ref No</th>
                            <th>Challan No</th>
                            <th>Qty</th>
                            <th>Party Rate</th>
                            <th>Received Qty</th>
                            <th>Min Qty</th>
                            <th>Shortage</th>
                            <th>Freight</th>
                            <th>Shortage Price</th>
                            <th>Diesel Qty</th>
                            <th>Diesel Amount</th>
                            <th>Cash</th>
                            <th>Bilty Comm</th>
                            <th>TDS</th>
                            <th>Net Amount</th>
                            <th>Added By</th>
                            <th>Voucher ID</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // $i = ($records_per_page === 'all') ? 1 : ($current_page - 1) * $records_per_page + 1; 
                        // foreach ($despatch as $des): 
                        //     // Base values
                        //     $qty = $des->quantity;
                        //     $rate = $des->rate;
                        //     $received = $des->rest_amount ?? 0;
                        //     $do_min = $des->min_qty ?? 0;
                        //     $s_rate = $des->shortage_rate ?? 0;
                        //     $d_qty = $des->dieselQty ?? 0;
                        //     $d_rate = $des->diesel_rate ?? 0;
                        //     $cash = $des->cash ?? 0;
                        //     $bilty = $des->bilty_commission ?? 0;
                        //     $tds_p = $des->tds_percentage ?? 2.00;
                        //     $special_shortage = $des->special_shortage ?? 0;
                        
                        //     // Calculations
                        //     $actual_min = min($qty, $received);
                        //     $actual_shortage = max(0, $qty - $received);
                        //     $freight = $actual_min * $rate;
                        
                        //     if ($actual_shortage <= 0) {
                        //         $s_price = 0;
                        //     } else {
                        //         if ($special_shortage == 1) {
                        //             $chargeable_shortage = max(0, $actual_shortage - $do_min);
                        //         } else {
                        //             $chargeable_shortage = $actual_shortage;
                        //         }
                        //         $s_price = $chargeable_shortage * ($s_rate > 0 ? $s_rate : $rate);
                        //     }
                        //     $shortage = $actual_shortage; // Displayed shortage
                        //     $d_amount = $d_qty * $d_rate;
                        //     $d_type = !empty($des->diesel_payment_type) ? $des->diesel_payment_type : 'Party';
                        //     $tds = ($actual_min * $rate * $tds_p) / 100;
                        //     $net = $freight - $s_price - $d_amount + $cash - $bilty - $tds;
                        
                        $i = ($records_per_page === 'all') ? 1 : ($current_page - 1) * $records_per_page + 1;
                        foreach ($despatch as $des):
                            // Base values
                            $qty = $des->quantity;
                            $rate = $des->rate;
                            $received = $des->rest_amount; // ✅ Keep NULL if not entered
                            $do_min = $des->min_qty ?? 0;
                            $s_rate = $des->shortage_rate ?? 0;
                            $d_qty = $des->dieselQty ?? 0;
                            $d_rate = $des->diesel_rate ?? 0;
                            $cash = $des->cash ?? 0;
                            $bilty = $des->bilty_commission ?? 0;
                            $tds_p = $des->tds_percentage ?? 2.00;
                            $special_shortage = $des->special_shortage ?? 0;

                            // ✅ Only calculate if received qty is entered
                            if ($received !== null && $received !== '' && $received > 0) {
                                // Calculations when received qty is entered
                                $actual_min = min($qty, $received);
                                $actual_shortage = max(0, $qty - $received);
                                $freight = $actual_min * $rate;

                                if ($actual_shortage <= 0) {
                                    $s_price = 0;
                                } else {
                                    if ($special_shortage == 1) {
                                        $chargeable_shortage = max(0, $actual_shortage - $do_min);
                                    } else {
                                        $chargeable_shortage = $actual_shortage;
                                    }
                                    $s_price = $chargeable_shortage * ($s_rate > 0 ? $s_rate : $rate);
                                }
                                $shortage = $actual_shortage;
                                $tds = ($actual_min * $rate * $tds_p) / 100;
                            } else {
                                // ✅ Not entered - show zeros
                                $actual_min = 0;
                                $shortage = 0;
                                $freight = 0;
                                $s_price = 0; // ✅ This is the key fix
                                $tds = 0;
                            }

                            $d_amount = $d_qty * $d_rate;
                            $d_type = !empty($des->diesel_payment_type) ? $des->diesel_payment_type : 'Party';
                            $net = $freight - $s_price - $d_amount + $cash - $bilty - $tds;

                            ?>
                            <tr data-id="<?= $des->despatch_id; ?>" data-do-id="<?= $des->do_no; ?>"
                                data-min-qty="<?= $do_min; ?>" data-shortage-rate="<?= $s_rate; ?>"
                                data-diesel-rate="<?= $d_rate; ?>" data-tds-percentage="<?= $tds_p; ?>"
                                data-special-shortage="<?= $special_shortage; ?>" data-diesel-type="<?= $d_type; ?>">
                                <td style="min-width: 60px;"><input type="checkbox" class="row-checkbox"
                                        onchange="updateBulkBar()"> <?= $i++; ?></td>
                                <td style="min-width: 100px;"><?= date('d-m-Y', strtotime($des->des_date)); ?></td>
                                <td style="min-width: 90px;"><?= $des->doreg_no; ?></td>
                                <td style="min-width: 110px;"><?= $des->vehicle_number; ?></td>
                                <td style="min-width: 100px;"><?= $des->ref_no ?? '-'; ?></td>
                                <td style="min-width: 100px;"><?= $des->challan_no ?? '-'; ?></td>
                                <td class="quantity" style="min-width: 80px; font-weight: 600;">
                                    <?= number_format($qty, 2); ?></td>
                                <td class="rate" style="min-width: 80px; font-weight: 600;"><?= number_format($rate, 2); ?>
                                </td>
                                <td style="min-width: 100px;"><input type="number" step="0.01" class="uk-input rest_amount"
                                        value="<?= $received > 0 ? $received : ''; ?>" oninput="calculateRow(this)"
                                        style="width: 90px;"></td>
                                <td style="min-width: 100px;"><input type="text" class="uk-input min_qty_col"
                                        value="<?= number_format($actual_min, 2); ?>" readonly style="width: 90px;"></td>
                                <td style="min-width: 90px;"><input type="text" class="uk-input shortage"
                                        value="<?= number_format($shortage, 2); ?>" readonly style="width: 80px;"></td>
                                <td style="min-width: 100px;"><input type="text" class="uk-input freight"
                                        value="<?= number_format($freight, 2); ?>" readonly style="width: 90px;"></td>
                                <td style="min-width: 110px;"><input type="text" class="uk-input shortage_price"
                                        value="<?= number_format($s_price, 2); ?>" readonly style="width: 100px;"></td>
                                <td style="min-width: 100px;"><input type="number" step="0.01" class="uk-input dieselQty"
                                        value="<?= $d_qty; ?>" oninput="calculateDieselAmount(this)" style="width: 90px;">
                                </td>
                                <td style="min-width: 110px;"><input type="text" class="uk-input diesel_amount"
                                        value="<?= number_format($d_amount, 2); ?>" readonly style="width: 100px;"></td>

                                <td style="min-width: 90px;"><input type="number" step="0.01" class="uk-input cash"
                                        value="<?= $cash; ?>" oninput="calculateRow(this)" style="width: 80px;"></td>
                                <td style="min-width: 100px;"><input type="number" step="0.01"
                                        class="uk-input bilty_commission" value="<?= $bilty; ?>"
                                        oninput="calculateRow(this)" style="width: 90px;"></td>
                                <td style="min-width: 80px;"><input type="text" class="uk-input tds"
                                        value="<?= number_format($tds, 2); ?>" readonly style="width: 70px;"></td>
                                <td style="min-width: 110px;"><input type="text" class="uk-input net_amount"
                                        value="<?= number_format($net, 2); ?>" readonly
                                        style="width: 100px; font-weight: 600; color: #059669;"></td>
                                <td style="min-width: 100px;"><input type="text" class="uk-input added_by"
                                        value="<?= $des->deposit_by ?? ''; ?>" readonly style="width: 90px;"></td>
                                <td style="min-width: 150px;"><span class="badge badge-primary group-id-badge"
                                        title="<?= $des->group_code ?? '-'; ?>"><?= $des->group_code ?? '-'; ?></span></td>
                                <td style="min-width: 80px; text-align: center;"><button type="button"
                                        onclick="updateRow(this, '<?= isset($singleuser[0]->full_name) ? htmlspecialchars($singleuser[0]->full_name, ENT_QUOTES) : ''; ?>')">Add</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="records-info"><?php if ($records_per_page === 'all'): ?>Showing all <?= $total_records ?>
                    records<?php else: ?>Showing <?= min(($current_page - 1) * $records_per_page + 1, $total_records) ?> to
                    <?= min($current_page * $records_per_page, $total_records) ?> of <?= $total_records ?>
                    records<?php endif; ?></div>
            <?php if ($records_per_page !== 'all' && $total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    $current_params = array_filter([
                        'from_date' => $date['from_date'] ?? '',
                        'to_date' => $date['to_date'] ?? '',
                        'do_no' => $date['do_no'] ?? '',
                        'voucher_id' => $date['voucher_id'] ?? '',
                        'chalan_status' => $date['chalan_status'] ?? '',
                        'payment_status' => $date['payment_status'] ?? '',
                        'deposited_status' => $date['deposited_status'] ?? '',
                        'search' => $date['search'] ?? '',
                        'per_page' => $records_per_page
                    ], function ($val) {
                        return $val !== '' && $val !== null; });

                    if ($current_page > 1):
                        $prev_params = $current_params;
                        $prev_params['page'] = $current_page - 1;
                        $prev_url = '?' . http_build_query($prev_params);
                        ?>
                        <a href="<?= $prev_url ?>">&laquo; Prev</a>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);

                    for ($i = $start_page; $i <= $end_page; $i++):
                        $page_params = $current_params;
                        $page_params['page'] = $i;
                        $page_url = '?' . http_build_query($page_params);
                        ?>
                        <a href="<?= $page_url ?>" class="<?= $i == $current_page ? 'current' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages):
                        $next_params = $current_params;
                        $next_params['page'] = $current_page + 1;
                        $next_url = '?' . http_build_query($next_params);
                        ?>
                        <a href="<?= $next_url ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add to Group Modal -->
<div id="addToGroupModal" class="modal" tabindex="-1" role="dialog"
    style="display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
    <div class="modal-dialog" role="document" style="margin: 10% auto; max-width: 500px;">
        <div class="modal-content"
            style="background-color: #fefefe; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div class="modal-header"
                style="padding: 15px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                <h5 class="modal-title" style="margin: 0; font-size: 1.25rem;">Select Existing Voucher</h5>
                <button type="button" class="close" onclick="closeAddToGroupModal()"
                    style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <p>Select a voucher to add the selected records to:</p>
                <select id="groupSelect" class="form-control-custom" style="width: 100%;">
                    <option value="">Loading vouchers...</option>
                </select>
            </div>
            <div class="modal-footer"
                style="padding: 15px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-custom" onclick="addToGroup()"
                    style="background-color: #3b82f6; color: white;">Add to Voucher</button>
                <button type="button" class="btn-custom" onclick="closeAddToGroupModal()"
                    style="background-color: #6c757d; color: white;">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    // ... existing search script ...
    document.getElementById('tableSearch').addEventListener('input', function () {
        const term = this.value.toLowerCase(); const rows = document.querySelectorAll('#myTable tbody tr');
        rows.forEach(row => { row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none'; });
    });

    // Helper to get selected DO ID
    function getSelectedDoId() {
        const selected = document.querySelectorAll(".row-checkbox:checked");
        if (selected.length === 0) return null;
        const firstDo = selected[0].closest('tr').getAttribute('data-do-id');
        for (let i = 1; i < selected.length; i++) {
            if (selected[i].closest('tr').getAttribute('data-do-id') !== firstDo) {
                return false; // Mixed DOs
            }
        }
        return firstDo;
    }

    // Modal Functions
    function openAddToGroupModal() {
        const selected = document.querySelectorAll(".row-checkbox:checked");
        if (selected.length === 0) {
            alert('Please select records to add to a voucher!');
            return;
        }

        const doId = getSelectedDoId();
        if (doId === false) {
            alert("Error: You can only add challans from the SAME DO Number to a voucher. Please deselect challans from other DOs.");
            return;
        }

        // Pass DO ID to fetchGroups
        fetchGroups(doId);
        document.getElementById('addToGroupModal').style.display = 'block';
    }

    function closeAddToGroupModal() {
        document.getElementById('addToGroupModal').style.display = 'none';
    }

    // Close modal if clicked outside
    window.onclick = function (event) {
        if (event.target == document.getElementById('addToGroupModal')) {
            closeAddToGroupModal();
        }
    }

    function fetchGroups(doId) {
        // Destroy existing Select2 to prevent duplication if modal is re-opened
        if ($('#groupSelect').hasClass("select2-hidden-accessible")) {
            $('#groupSelect').select2('destroy');
        }

        const select = document.getElementById('groupSelect');
        select.innerHTML = '<option value="">Loading...</option>';

        $.ajax({
            url: "<?= base_url('Admin/get_active_groups') ?>",
            type: "GET",
            data: { do_id: doId }, // Pass DO ID to filter groups
            success: function (r) {
                if (r.status === 'success') {
                    if (r.groups.length > 0) {
                        let html = '<option value="">-- Select Voucher --</option>';
                        r.groups.forEach(g => {
                            html += `<option value="${g.id}">${g.group_code} (${new Date(g.created_at).toLocaleDateString()})</option>`;
                        });
                        select.innerHTML = html;
                        // Initialize Select2
                        $('#groupSelect').select2({
                            dropdownParent: $('#addToGroupModal'),
                            width: '100%',
                            placeholder: "-- Select Voucher --",
                            allowClear: true
                        });
                    } else {
                        select.innerHTML = '<option value="">No compatible vouchers found for this DO</option>';
                    }
                } else {
                    select.innerHTML = '<option value="">Error loading vouchers</option>';
                }
            },
            error: function () {
                select.innerHTML = '<option value="">Error loading vouchers</option>';
            }
        });
    }

    function addToGroup() {
        const selected = document.querySelectorAll(".row-checkbox:checked");
        const groupId = document.getElementById('groupSelect').value;

        if (!groupId) {
            alert('Please select a voucher!');
            return;
        }

        const doId = getSelectedDoId();
        if (doId === false) {
            alert("Error: You can only add challans from the SAME DO Number to a voucher.");
            return;
        }

        const ids = Array.from(selected).map(cb => cb.closest('tr').getAttribute('data-id'));
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Processing...';

        $.ajax({
            url: "<?= base_url('Admin/manage_collection_group') ?>",
            type: "POST",
            data: {
                ids: ids,
                action: 'add',
                group_id: groupId,
                <?= csrf_token() ?>: "<?= csrf_hash() ?>"
            },
            success: function (r) {
                if (r.status === 'success') {
                    alert(r.message);
                    location.reload();
                } else {
                    alert('Error: ' + r.message);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            },
            error: function (err) {
                console.error(err);
                alert('An error occurred');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    }

    function removeFromGroup() {
        const selected = document.querySelectorAll(".row-checkbox:checked");
        if (selected.length === 0) {
            alert('Please select records to remove from voucher!');
            return;
        }

        if (!confirm(`Are you sure you want to remove ${selected.length} records from their vouchers?`)) return;

        const ids = Array.from(selected).map(cb => cb.closest('tr').getAttribute('data-id'));

        $.ajax({
            url: "<?= base_url('Admin/manage_collection_group') ?>",
            type: "POST",
            data: {
                ids: ids,
                action: 'remove',
                <?= csrf_token() ?>: "<?= csrf_hash() ?>"
            },
            success: function (r) {
                if (r.status === 'success') {
                    alert(r.message);
                    location.reload();
                } else {
                    alert('Error: ' + r.message);
                }
            },
            error: function (err) {
                console.error(err);
                alert('An error occurred');
            }
        });
    }

    function updateDoRegistrations() {
        var from = $("#from_date").val();
        var to = $("#to_date").val();
        if (from && to) {
            $.ajax({
                url: "<?= base_url('Admin/getDoNumbers') ?>",
                type: "POST",
                data: {
                    from_date: from,
                    to_date: to,
                    <?= csrf_token() ?>: "<?= csrf_hash() ?>"
                },
                success: function (r) {
                    $("#single").html(r);
                    // Clear vouchers when date changes
                    $("#single2").html('<option value="">All Vouchers</option>');
                }
            });
        }
    }

    function updateVouchers() {
        var do_id = $("#single").val();
        if (do_id) {
            $.ajax({
                url: "<?= base_url('Admin/getVouchersByDo') ?>",
                type: "POST",
                data: {
                    do_id: do_id,
                    <?= csrf_token() ?>: "<?= csrf_hash() ?>"
                },
                success: function (r) {
                    $("#single2").html(r);
                }
            });
        } else {
            $("#single2").html('<option value="">All Vouchers</option>');
        }
    }
    function changePerPage() { var p = document.getElementById('per_page').value; var url = new URL(window.location.href); url.searchParams.set('per_page', p); url.searchParams.set('page', '1'); window.location.href = url.toString(); }

    // Bulk Selection Helpers
    function toggleSelectAll(master) {
        document.querySelectorAll(".row-checkbox").forEach(cb => cb.checked = master.checked);
        updateBulkBar();
    }

    function updateBulkBar() {
        const count = document.querySelectorAll(".row-checkbox:checked").length;
        if (count === 0) {
            document.getElementById("selectAll").checked = false;
        }
    }

    async function bulkUpdateSelected(user) {
        const selected = document.querySelectorAll(".row-checkbox:checked");
        if (selected.length === 0) {
            alert('Please select at least one record to update!');
            return;
        }

        if (!confirm(`Are you sure you want to update ${selected.length} selected records?`)) return;

        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<i class="fa fa-spinner fa-spin"></i> Processing...`;

        let successCount = 0;
        let failCount = 0;

        for (let cb of selected) {
            const row = cb.closest('tr');
            const actionBtn = row.querySelector("td:last-child button");
            try {
                await updateRow(actionBtn, user, false);
                successCount++;
                cb.checked = false;
            } catch (err) {
                failCount++;
                console.error("Failed to update row:", err);
            }
        }

        btn.disabled = false;
        btn.innerHTML = originalText;
        updateBulkBar();

        alert(`Bulk update complete!\nSuccess: ${successCount}\nFailed: ${failCount}`);
    }

    async function createCollectionGroup() {
        const selected = document.querySelectorAll(".row-checkbox:checked");
        if (selected.length === 0) {
            alert('Please select at least one record to create a voucher!');
            return;
        }

        const doId = getSelectedDoId();
        if (doId === false) {
            alert("Error: You can only create a voucher for challans from the SAME DO Number. Please deselect challans from other DOs.");
            return;
        }

        if (!confirm(`Are you sure you want to create a voucher for ${selected.length} selected records?`)) return;

        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<i class="fa fa-spinner fa-spin"></i> Creating...`;

        const ids = Array.from(selected).map(cb => cb.closest('tr').getAttribute('data-id'));

        $.ajax({
            url: "<?= base_url('Admin/create_collection_group') ?>",
            type: "POST",
            data: {
                ids: ids,
                <?= csrf_token() ?>: "<?= csrf_hash() ?>"
            },
            success: function (r) {
                btn.disabled = false;
                btn.innerHTML = originalText;
                if (r.status === 'success') {
                    alert('Voucher created successfully! ID: ' + r.group_code);
                    location.reload();
                } else {
                    alert('Error: ' + r.message);
                }
            },
            error: function (xhr) {
                btn.disabled = false;
                btn.innerHTML = originalText;
                let msg = 'Error creating voucher.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const parsed = JSON.parse(xhr.responseText);
                        if (parsed.message) {
                            msg = parsed.message;
                        }
                    } catch (e) {
                        if (xhr.responseText.length < 500) {
                            msg = xhr.responseText;
                        }
                    }
                }
                console.error('create_collection_group failed:', xhr.status, msg, xhr.responseText);
                alert('Error: ' + msg);
            }
        });
    }

    function updateAllBiltyCommission() {
        let bulkCommission = parseFloat(document.getElementById('bulk_bilty_commission').value);

        if (isNaN(bulkCommission) || bulkCommission < 0) {
            alert('Please enter a valid Bilty Commission amount!');
            return;
        }

        if (!confirm(`Are you sure you want to update Bilty Commission to ₹${bulkCommission.toFixed(2)} for all displayed records?`)) {
            return;
        }

        let updatedCount = 0;
        document.querySelectorAll(".bilty_commission").forEach(function (input) {
            input.value = bulkCommission;
            calculateRow(input);
            updatedCount++;
        });

        alert(`Successfully updated Bilty Commission for ${updatedCount} records!`);
        document.getElementById('bulk_bilty_commission').value = '';
    }

    function calculateDieselAmount(input) {
        let row = input.closest('tr');
        let dieselQty = parseFloat(row.querySelector('.dieselQty').value) || 0;
        let dieselRate = parseFloat(row.getAttribute('data-diesel-rate')) || 0;
        let dieselAmount = dieselQty * dieselRate;
        row.querySelector('.diesel_amount').value = dieselAmount.toFixed(2);
        calculateRow(input);
    }

    function calculateRow(input) {
        let row = input.closest('tr');
        let qty = parseFloat(row.querySelector(".quantity").textContent.replace(/,/g, '')) || 0;
        let rest_val = row.querySelector(".rest_amount").value;
        let rate = parseFloat(row.querySelector(".rate").textContent.replace(/,/g, '')) || 0;
        let shortage_rate = parseFloat(row.getAttribute('data-shortage-rate')) || 0;

        // ✅ Check if received qty is entered
        if (rest_val !== '' && rest_val !== null && parseFloat(rest_val) > 0) {
            let rest = parseFloat(rest_val);

            let min_qty_val = Math.min(qty, rest);
            row.querySelector(".min_qty_col").value = min_qty_val.toFixed(2);

            let shortage = Math.max(0, qty - min_qty_val);
            row.querySelector(".shortage").value = shortage.toFixed(2);

            let freight = min_qty_val * rate;
            row.querySelector(".freight").value = freight.toFixed(2);

            // Shortage calculation
            let actual_shortage = Math.max(0, qty - rest);
            let special_deduction = parseInt(row.getAttribute('data-special-shortage')) || 0;
            let do_shortage_qty = parseFloat(row.getAttribute('data-min-qty')) || 0;

            let shortage_price = 0;
            if (actual_shortage > 0) {
                let chargeable_shortage = 0;
                if (special_deduction === 1) {
                    chargeable_shortage = Math.max(0, actual_shortage - do_shortage_qty);
                } else {
                    chargeable_shortage = actual_shortage;
                }
                let apply_s_rate = (shortage_rate > 0) ? shortage_rate : rate;
                shortage_price = chargeable_shortage * apply_s_rate;
            }
            row.querySelector(".shortage_price").value = shortage_price.toFixed(2);

            let tds_percentage = parseFloat(row.getAttribute('data-tds-percentage')) || 2.00;
            let tds_base_amount = min_qty_val * rate;
            let tds_amount = (tds_base_amount * tds_percentage) / 100;
            row.querySelector(".tds").value = tds_amount.toFixed(2);

            let d_amount = parseFloat(row.querySelector(".diesel_amount").value.replace(/,/g, '')) || 0;
            let cash = parseFloat(row.querySelector(".cash").value.replace(/,/g, '')) || 0;
            let bilty_comm = parseFloat(row.querySelector(".bilty_commission").value.replace(/,/g, '')) || 0;

            let net = freight - shortage_price - d_amount - cash - bilty_comm - tds_amount;
            row.querySelector(".net_amount").value = net.toFixed(2);
        } else {
            // ✅ Not entered - set zeros
            row.querySelector(".min_qty_col").value = '0.00';
            row.querySelector(".shortage").value = '0.00';
            row.querySelector(".freight").value = '0.00';
            row.querySelector(".shortage_price").value = '0.00';
            row.querySelector(".tds").value = '0.00';
            row.querySelector(".net_amount").value = '0.00';
        }
    }

    function updateRow(btn, user, show_alert = true) {
        let row = btn.closest('tr');
        let id = row.getAttribute('data-id');
        let qty = parseFloat(row.querySelector(".quantity").textContent.replace(/,/g, '')) || 0;
        let rest_val = row.querySelector(".rest_amount").value;
        let rate = parseFloat(row.querySelector(".rate").textContent.replace(/,/g, '')) || 0;

        // ✅ Check if received qty is entered before proceeding
        if (rest_val === '' || rest_val === null || parseFloat(rest_val) <= 0) {
            if (show_alert) alert('Please enter Received Qty before saving!');
            return Promise.reject('No received qty');
        }

        let rest = parseFloat(rest_val);
        let min_qty_val = Math.min(qty, rest);
        let shortage = Math.max(0, qty - min_qty_val);
        let freight = min_qty_val * rate;

        // Shortage calculation
        let actual_shortage = Math.max(0, qty - rest);
        let special_deduction = parseInt(row.getAttribute('data-special-shortage')) || 0;
        let do_shortage_qty = parseFloat(row.getAttribute('data-min-qty')) || 0;
        let shortage_rate_from_do = parseFloat(row.getAttribute('data-shortage-rate')) || 0;

        let shortage_price = 0;
        if (actual_shortage > 0) {
            let chargeable_shortage = 0;
            if (special_deduction === 1) {
                chargeable_shortage = Math.max(0, actual_shortage - do_shortage_qty);
            } else {
                chargeable_shortage = actual_shortage;
            }
            let apply_s_rate = (shortage_rate_from_do > 0) ? shortage_rate_from_do : rate;
            shortage_price = chargeable_shortage * apply_s_rate;
        }

        let d_q = parseFloat(row.querySelector(".dieselQty").value) || 0;
        let d_rate = parseFloat(row.getAttribute('data-diesel-rate')) || 0;
        let d_amount = d_q * d_rate;
        let cash = parseFloat(row.querySelector(".cash").value) || 0;
        let bilty_comm = parseFloat(row.querySelector(".bilty_commission").value) || 0;

        let tds_percentage = parseFloat(row.getAttribute('data-tds-percentage')) || 2.00;
        let tds_base_amount = min_qty_val * rate;
        let tds = (tds_base_amount * tds_percentage) / 100;

        let net = freight - shortage_price - d_amount - cash - bilty_comm - tds;
        let t_d = shortage_price - d_amount - cash - bilty_comm - tds;

        row.querySelector(".added_by").value = user;

        return $.ajax({
            url: "<?= base_url('Admin/updateDispatch') ?>",
            type: "POST",
            data: {
                id: id,
                rest_amount: rest,
                shortage: shortage,
                freight: freight,
                dieselQty: d_q,
                dieselPrice: d_rate,
                totaldieselRate: d_amount,
                cash: cash,
                bilty_commission: bilty_comm,
                deposit_by: user,
                total_deduction: t_d,
                net_amount: net,
                tds: tds,
                <?= csrf_token() ?>: "<?= csrf_hash() ?>"
            },
            success: function (r) {
                if (r.status === 'success') {
                    if (r.calculations) {
                        row.querySelector(".shortage").value = r.calculations.shortage || shortage.toFixed(2);
                        row.querySelector(".shortage_price").value = r.calculations.shortage_price;
                        row.querySelector(".freight").value = r.calculations.freight || freight.toFixed(2);
                        row.querySelector(".tds").value = r.calculations.tds || tds.toFixed(2);
                        row.querySelector(".net_amount").value = r.calculations.net_amount;
                    }
                    if (show_alert) alert('Updated successfully');
                } else {
                    if (show_alert) alert('Error: ' + r.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
                if (show_alert) alert('Error updating record. Check console for details.');
            }
        });
    }
    // function calculateRow(input) {
    //     let row = input.closest('tr');
    //     let id = row.getAttribute('data-id');
    //     let qty = parseFloat(row.querySelector(".quantity").textContent.replace(/,/g, '')) || 0;
    //     let rest = parseFloat(row.querySelector(".rest_amount").value) || 0;
    //     let rate = parseFloat(row.querySelector(".rate").textContent.replace(/,/g, '')) || 0;
    //     let shortage_rate = parseFloat(row.getAttribute('data-shortage-rate')) || 0;

    //     let min_qty_val = Math.min(qty, rest);
    //     row.querySelector(".min_qty_col").value = min_qty_val.toFixed(2);

    //     let shortage = Math.max(0, qty - min_qty_val);
    //     row.querySelector(".shortage").value = shortage.toFixed(2);

    //     let freight = min_qty_val * rate;
    //     row.querySelector(".freight").value = freight.toFixed(2);

    //     // Shortage calculation flow
    //     let actual_shortage = Math.max(0, qty - rest);
    //     let special_deduction = parseInt(row.getAttribute('data-special-shortage')) || 0;
    //     let do_shortage_qty = parseFloat(row.getAttribute('data-min-qty')) || 0; // shortage_qty from DO

    //     let shortage_price = 0;
    //     if (actual_shortage > 0) {
    //         let chargeable_shortage = 0;
    //         if (special_deduction === 1) {
    //             chargeable_shortage = Math.max(0, actual_shortage - do_shortage_qty);
    //         } else {
    //             chargeable_shortage = actual_shortage;
    //         }

    //         let apply_s_rate = (shortage_rate > 0) ? shortage_rate : rate;
    //         shortage_price = chargeable_shortage * apply_s_rate;
    //     }

    //     row.querySelector(".shortage_price").value = shortage_price.toFixed(2);

    //     let tds_percentage = parseFloat(row.getAttribute('data-tds-percentage')) || 2.00;
    //     let tds_base_amount = min_qty_val * rate;
    //     let tds_amount = (tds_base_amount * tds_percentage) / 100;
    //     row.querySelector(".tds").value = tds_amount.toFixed(2);

    //     let d_amount = parseFloat(row.querySelector(".diesel_amount").value.replace(/,/g, '')) || 0;
    //     let cash = parseFloat(row.querySelector(".cash").value.replace(/,/g, '')) || 0;
    //     let cashType = (row.getAttribute('data-cash-type') || 'Party').toUpperCase();

    //     let bilty_comm = parseFloat(row.querySelector(".bilty_commission").value.replace(/,/g, '')) || 0;
    //     let tds = tds_amount;

    //     let d_type = (row.getAttribute('data-diesel-payment-type') || 'Party').toUpperCase();
    //     let net = (min_qty_val * rate)
    //       - shortage_price
    //       - d_amount
    //       - cash
    //       - bilty_comm
    //       - tds;

    //     row.querySelector(".net_amount").value = net.toFixed(2);
    // }

    // function updateRow(btn, user, show_alert = true) {
    //     let row = btn.closest('tr');
    //     let id = row.getAttribute('data-id');
    //     let qty = parseFloat(row.querySelector(".quantity").textContent.replace(/,/g, '')) || 0;
    //     let rest = parseFloat(row.querySelector(".rest_amount").value) || 0;
    //     let rate = parseFloat(row.querySelector(".rate").textContent.replace(/,/g, '')) || 0;

    //     let min_qty_val = Math.min(qty, rest);
    //     let shortage = Math.max(0, qty - min_qty_val);
    //     let freight = min_qty_val * rate;

    //     // Shortage calculation flow
    //     let actual_shortage = Math.max(0, qty - rest);
    //     let special_deduction = parseInt(row.getAttribute('data-special-shortage')) || 0;
    //     let do_shortage_qty = parseFloat(row.getAttribute('data-min-qty')) || 0;
    //     let shortage_rate_from_do = parseFloat(row.getAttribute('data-shortage-rate')) || 0;

    //     let shortage_price = 0;
    //     if (actual_shortage > 0) {
    //         let chargeable_shortage = 0;
    //         if (special_deduction === 1) {
    //             chargeable_shortage = Math.max(0, actual_shortage - do_shortage_qty);
    //         } else {
    //             chargeable_shortage = actual_shortage;
    //         }

    //         let apply_s_rate = (shortage_rate_from_do > 0) ? shortage_rate_from_do : rate;
    //         shortage_price = chargeable_shortage * apply_s_rate;
    //     }

    //     let d_q = parseFloat(row.querySelector(".dieselQty").value) || 0;
    //     let d_rate = parseFloat(row.getAttribute('data-diesel-rate')) || 0;
    //     let d_amount = d_q * d_rate;
    //     let cash = parseFloat(row.querySelector(".cash").value) || 0;
    //     let bilty_comm = parseFloat(row.querySelector(".bilty_commission").value) || 0;

    //     let tds_percentage = parseFloat(row.getAttribute('data-tds-percentage')) || 2.00;
    //     let tds_base_amount = min_qty_val * rate;
    //     let tds = (tds_base_amount * tds_percentage) / 100;

    //     let d_type = (row.getAttribute('data-diesel-payment-type') || 'Party').toUpperCase();
    //     let cashType = (row.getAttribute('data-cash-type') || 'Party').toUpperCase();

    //     let net = (min_qty_val * rate)
    //       - shortage_price
    //       - d_amount
    //       - cash
    //       - bilty_comm
    //       - tds;

    //     let t_d = shortage_price - d_amount - cash + bilty_comm + tds;

    //     row.querySelector(".added_by").value = user;

    //     return $.ajax({
    //         url: "<?= base_url('Admin/updateDispatch') ?>",
    //         type: "POST",
    //         data: {
    //             id: id,
    //             rest_amount: rest,
    //             shortage: shortage,
    //             freight: freight,
    //             dieselQty: d_q,
    //             dieselPrice: d_rate,
    //             totaldieselRate: d_amount,
    //             cash: cash,
    //             bilty_commission: bilty_comm,
    //             deposit_by: user,
    //             total_deduction: t_d,
    //             net_amount: net,
    //             tds: tds,
    //             <?= csrf_token() ?>: "<?= csrf_hash() ?>"
    //         },
    //         success: function(r) {
    //             if (r.status === 'success') {
    //                 if (r.calculations) {
    //                     row.querySelector(".shortage").value = r.calculations.shortage || shortage.toFixed(2);
    //                     row.querySelector(".shortage_price").value = r.calculations.shortage_price;
    //                     row.querySelector(".freight").value = r.calculations.freight || freight.toFixed(2);
    //                     row.querySelector(".tds").value = r.calculations.tds || tds.toFixed(2);
    //                     row.querySelector(".net_amount").value = r.calculations.net_amount;
    //                 }
    //                 if (show_alert) alert('Updated successfully');
    //             } else {
    //                 if (show_alert) alert('Error: ' + r.message);
    //             }
    //         },
    //         error: function(xhr, status, error) {
    //             console.error('AJAX Error:', error);
    //             console.error('Response:', xhr.responseText);
    //             if (show_alert) alert('Error updating record. Check console for details.');
    //         }
    //     });
    // }
    function exportExcel() {
        let from_date = $('#from_date').val();
        let to_date = $('#to_date').val();
        let do_no = $('#single').val();
        let chalan_status = $('#chalan_status').val();
        let voucher_id = $('#single2').val();

        let url = "<?= base_url('Admin/exportCollectionExcel') ?>?" +
            "from_date=" + from_date +
            "&to_date=" + to_date +
            "&do_no=" + do_no +
            "&chalan_status=" + chalan_status +
            "&voucher_id=" + (voucher_id || '');

        window.location.href = url;
    }

    function exportPDF() {
        let from_date = $('#from_date').val();
        let to_date = $('#to_date').val();
        let do_no_text = $('#single option:selected').text();
        let title = 'Collection Report';
        if (from_date && to_date) title += ' (' + from_date + ' to ' + to_date + ')';
        if ($('#single').val()) title += ' - DO: ' + do_no_text;

        printTable('myTable', title);
    }

    function printTable(tableId, title) {
        // Clone the table to manipulate it for printing
        let table = document.getElementById(tableId).cloneNode(true);

        // Initialize totals
        let totals = {
            qty: 0, received: 0, min: 0, shortage: 0, freight: 0,
            shortage_price: 0, diesel_qty: 0, diesel_amount: 0,
            cash: 0, bilty: 0, tds: 0, net: 0
        };

        // Remove action column (last column) and search/checkboxes, and calculate totals
        let rows = table.querySelectorAll('tr');
        rows.forEach(row => {
            if (row.cells.length > 0) {
                // If any cells have inputs (like Received Qty), replace with their values
                // Skip hidden inputs to avoid repeated text artifacts (OwnParty...)
                let inputs = row.querySelectorAll('input:not([type="hidden"]), select');
                inputs.forEach(input => {
                    let val = input.value;
                    let span = document.createElement('span');
                    span.textContent = val;
                    input.parentNode.replaceChild(span, input);
                });

                if (row.parentElement.tagName === 'TBODY') {
                    // Indices in collection_vw (assuming initial table structure):
                    // 6: Qty, 8: Received, 9: Min, 10: Shortage, 11: Freight, 12: S.Price, 13: D.Qty, 14: D.Amount, 15: Cash, 16: Bilty, 17: TDS, 18: Net
                    let cells = row.cells;
                    totals.qty += parseFloat(cells[6].innerText.replace(/,/g, '')) || 0;
                    totals.received += parseFloat(cells[8].innerText.replace(/,/g, '')) || 0;
                    totals.min += parseFloat(cells[9].innerText.replace(/,/g, '')) || 0;
                    totals.shortage += parseFloat(cells[10].innerText.replace(/,/g, '')) || 0;
                    totals.freight += parseFloat(cells[11].innerText.replace(/,/g, '')) || 0;
                    totals.shortage_price += parseFloat(cells[12].innerText.replace(/,/g, '')) || 0;
                    totals.diesel_qty += parseFloat(cells[13].innerText.replace(/,/g, '')) || 0;
                    totals.diesel_amount += parseFloat(cells[14].innerText.replace(/,/g, '')) || 0;
                    totals.cash += parseFloat(cells[15].innerText.replace(/,/g, '')) || 0;
                    totals.bilty += parseFloat(cells[16].innerText.replace(/,/g, '')) || 0;
                    totals.tds += parseFloat(cells[17].innerText.replace(/,/g, '')) || 0;
                    totals.net += parseFloat(cells[18].innerText.replace(/,/g, '')) || 0;
                }

                row.deleteCell(-1); // Remove Action

                // If it's the header, remove checkbox from first cell
                if (row.parentElement.tagName === 'THEAD') {
                    let firstCell = row.cells[0];
                    firstCell.innerHTML = firstCell.innerHTML.replace(/<input[^>]*checkbox[^>]*>/i, '').trim();
                } else {
                    // For body rows, remove checkbox if any
                    let firstCell = row.cells[0];
                    let checkbox = firstCell.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.remove();
                }
            }
        });

        // Add Totals Row
        let tfoot = table.createTFoot();
        let footerRow = tfoot.insertRow(0);
        footerRow.style.fontWeight = 'bold';
        footerRow.style.backgroundColor = '#f8f9fa';

        // Fill footer cells
        for (let j = 0; j < 21; j++) {
            let cell = footerRow.insertCell(j);
            cell.style.border = '1px solid #dee2e6';
            cell.style.padding = '2px 4px';

            if (j === 5) cell.innerText = 'TOTAL:';
            else if (j === 6) cell.innerText = totals.qty.toFixed(2);
            else if (j === 8) cell.innerText = totals.received.toFixed(2);
            else if (j === 9) cell.innerText = totals.min.toFixed(2);
            else if (j === 10) cell.innerText = totals.shortage.toFixed(2);
            else if (j === 11) cell.innerText = totals.freight.toFixed(2);
            else if (j === 12) cell.innerText = totals.shortage_price.toFixed(2);
            else if (j === 13) cell.innerText = totals.diesel_qty.toFixed(2);
            else if (j === 14) cell.innerText = totals.diesel_amount.toFixed(2);
            else if (j === 15) cell.innerText = totals.cash.toFixed(2);
            else if (j === 16) cell.innerText = totals.bilty.toFixed(2);
            else if (j === 17) cell.innerText = totals.tds.toFixed(2);
            else if (j === 18) cell.innerText = totals.net.toFixed(2);
        }

        let printWindow = window.open('', '', 'height=700,width=1200');
        printWindow.document.write('<html><head><title>' + title + '</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
        printWindow.document.write('<style>');
        printWindow.document.write('@page { size: landscape; margin: 10mm; }');
        printWindow.document.write('body { font-family: Arial, sans-serif; padding: 10px; color: #000 !important; }');
        printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: auto; color: #000 !important; }');
        printWindow.document.write('th, td { border: 1px solid #dee2e6; padding: 2px 4px; font-size: 8px; text-align: left; word-break: break-word; color: #000 !important; }');
        printWindow.document.write('th, td, span, div, b, strong, .badge { color: #000 !important; -webkit-text-fill-color: #000 !important; opacity: 1 !important; background-image: none !important; }');
        printWindow.document.write('th { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; font-weight: bold; }');
        printWindow.document.write('tr:nth-child(even) { background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; }');
        printWindow.document.write('.badge, .badge-primary, .group-id-badge { background-color: transparent !important; color: #000 !important; border: 1px solid #dee2e6 !important; padding: 1px 2px !important; display: inline-block; box-shadow: none !important; }');
        printWindow.document.write('@media print { .no-print { display: none; } * { -webkit-print-color-adjust: exact; print-color-adjust: exact; color: #000 !important; } }');
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<div class="text-center" style="text-align: center;">');
        printWindow.document.write('<h2>' + title + '</h2>');
        printWindow.document.write('<p>Generated on: ' + new Date().toLocaleString() + '</p>');
        printWindow.document.write('</div>');
        printWindow.document.write(table.outerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();

        setTimeout(function () {
            printWindow.print();
        }, 1000);
    }
</script>
<?php include("footer.php"); ?>