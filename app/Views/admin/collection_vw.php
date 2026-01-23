<?php include("header.php"); ?>
<style>
    .voucher-wrapper { background: #f8f9fa; min-height: 100vh; padding: 20px 0; }
    .page-header { background: white; padding: 5px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 24px; margin-top: 20px; }
    .filter-card { background: white; padding: 28px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 20px; }
    .filter-card h5 { font-size: 16px; font-weight: 600; color: #334155; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #e2e8f0; }
    .form-group-custom { margin-bottom: 16px; }
    .form-group-custom label { display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 8px; }
    .form-control-custom { width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; transition: all 0.2s; }
    .form-control-custom:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .btn-group-custom { display: flex; gap: 12px; margin-top: 24px; }
    .btn-custom { padding: 10px 24px; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-block; text-align: center; }
    .btn-primary-custom { background: #3b82f6; color: white; }
    .btn-primary-custom:hover { background: #2563eb; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
    .btn-success-custom { background: #10b981; color: white; }
    .btn-success-custom:hover { background: #059669; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
    .table-card { background: white; padding: 28px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 20px; }
    .table-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 16px; }
    .records-per-page { display: flex; align-items: center; gap: 10px; }
    .records-per-page label { font-size: 13px; font-weight: 500; color: #475569; white-space: nowrap; }
    .records-per-page select { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; }
    .search-box { flex: 1; display: flex; justify-content: flex-end; }
    .search-box input { max-width: 280px; }
    .records-info { background: #f8fafc; padding: 12px 20px; border-radius: 6px; text-align: center; color: #64748b; font-size: 14px; margin-bottom: 16px; }
    .table-wrapper { max-height: 500px; overflow-y: auto; overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px; }
    #myTable { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; }
    #myTable thead th { position: sticky; top: 0; background: #f8fafc; color: #334155; font-weight: 600; padding: 12px 10px; text-align: left; border-bottom: 2px solid #e2e8f0; white-space: nowrap; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; z-index: 10; }
    #myTable tbody tr { transition: background 0.2s; border-bottom: 1px solid #f1f5f9; }
    #myTable tbody tr:hover { background: #f8fafc; }
    #myTable tbody td { padding: 10px; color: #475569; vertical-align: middle; white-space: nowrap; }
    #myTable input[type="text"], #myTable input[type="number"], #myTable input[type="date"] { width: 100%; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; }
    #myTable input[type="text"]:focus, #myTable input[type="number"]:focus, #myTable input[type="date"]:focus { outline: none; border-color: #3b82f6; }
    #myTable button { padding: 6px 16px; background: #3b82f6; color: white; border: none; border-radius: 4px; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.2s; }
    #myTable button:hover { background: #2563eb; transform: translateY(-1px); }
    .pagination { display: flex; justify-content: center; align-items: center; margin: 24px 0 0 0; gap: 6px; flex-wrap: wrap; }
    .pagination a, .pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; padding: 0 12px; text-decoration: none; border: 1px solid #e2e8f0; color: #475569; background: white; border-radius: 6px; font-size: 14px; font-weight: 500; transition: all 0.2s; }
    .pagination a:hover { background-color: #f8fafc; border-color: #3b82f6; color: #3b82f6; }
    .pagination .current { background-color: #3b82f6; color: white; border-color: #3b82f6; }
    .pagination .disabled { color: #cbd5e1; background-color: #fff; border-color: #e2e8f0; cursor: not-allowed; }
</style>
<?php
$records_per_page = isset($_GET['per_page']) && $_GET['per_page'] === 'all' ? 'all' : (isset($records_per_page) ? $records_per_page : 10);
$current_page = isset($current_page) ? $current_page : 1;
$total_records = isset($total_count) ? $total_count : count($despatch);
if ($records_per_page === 'all') { $total_pages = 1; $current_page = 1; } else { $total_pages = ceil($total_records / $records_per_page); }
?>
<div class="page-body-wrapper voucher-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="filter-card">
                <h5>📋 Collection View</h5>
                <form method="get" action="<?php echo base_url(); ?>/Admin/Collection">
                    <?php $default_from_date = $date['from_date'] ?? date('Y-m-01'); $default_to_date = $date['to_date'] ?? date('Y-m-d'); ?>
                    <input type="hidden" name="per_page" id="hidden_per_page" value="<?= $records_per_page ?>">
                    <div class="row">
                        <div class="col-md-3"><div class="form-group-custom"><label for="from_date">From Date</label><input type="date" id="from_date" name="from_date" class="form-control-custom" value="<?= $default_from_date; ?>" onchange="updateDoRegistrations()"/></div></div>
                        <div class="col-md-3"><div class="form-group-custom"><label for="to_date">To Date</label><input type="date" id="to_date" name="to_date" class="form-control-custom" value="<?= $default_to_date; ?>" onchange="updateDoRegistrations()"/></div></div>
                        <div class="col-md-3"><div class="form-group-custom"><label for="do_no">DO No.</label><select class="form-control-custom" name="do_no" id="single"><option value="">Select DO No.</option><?php foreach ($doregistration as $do): ?><option value="<?= $do->do_registration_id; ?>" <?= isset($date['do_no']) && $date['do_no'] == $do->do_registration_id ? 'selected' : '' ?>><?= $do->do_no; ?></option><?php endforeach; ?></select></div></div>
                        <div class="col-md-3"><div class="form-group-custom"><label for="chalan_status">Chalan Status</label><select class="form-control-custom" name="chalan_status" id="chalan_status"><option value="" <?= !isset($date['chalan_status']) || $date['chalan_status'] === '' ? 'selected' : '' ?>>All</option><option value="1" <?= isset($date['chalan_status']) && $date['chalan_status'] == '1' ? 'selected' : '' ?>>Received</option><option value="2" <?= isset($date['chalan_status']) && $date['chalan_status'] == '2' ? 'selected' : '' ?>>Not Received</option></select></div></div>
                        <div class="col-md-3"><div class="form-group-custom"><label for="payment_status">Payment Status</label><select class="form-control-custom" name="payment_status" id="payment_status"><option value="" <?= !isset($date['payment_status']) || $date['payment_status'] === '' ? 'selected' : '' ?>>All</option><option value="1" <?= isset($date['payment_status']) && $date['payment_status'] == '1' ? 'selected' : '' ?>>Paid</option><option value="0" <?= isset($date['payment_status']) && $date['payment_status'] == '0' ? 'selected' : '' ?>>Unpaid</option></select></div></div>
                        <div class="col-md-3"><div class="form-group-custom"><label for="deposited_status">Deposited Status</label><select class="form-control-custom" name="deposited_status" id="deposited_status"><option value="" <?= !isset($date['deposited_status']) || $date['deposited_status'] === '' ? 'selected' : '' ?>>All</option><option value="1" <?= isset($date['deposited_status']) && $date['deposited_status'] == '1' ? 'selected' : '' ?>>Deposited</option><option value="0" <?= isset($date['deposited_status']) && $date['deposited_status'] == '0' ? 'selected' : '' ?>>Not Deposited</option></select></div></div>
                    </div>
                    <div class="btn-group-custom"><button type="submit" class="btn-custom btn-primary-custom"><i class="fa fa-filter"></i> Apply Filters</button><a href="#" id="download_excel" class="btn-custom btn-success-custom"><i class="fa fa-download"></i> Download Excel</a></div>
                </form>
            </div>
            <div class="table-card">
                <div class="table-controls"><div class="records-per-page"><label for="per_page">Records per page:</label><select id="per_page" class="form-control-custom" onchange="changePerPage()"><option value="10" <?= $records_per_page == 10 ? 'selected' : '' ?>>10</option><option value="25" <?= $records_per_page == 25 ? 'selected' : '' ?>>25</option><option value="50" <?= $records_per_page == 50 ? 'selected' : '' ?>>50</option><option value="100" <?= $records_per_page == 100 ? 'selected' : '' ?>>100</option><option value="all" <?= $records_per_page == 'all' ? 'selected' : '' ?>>Show All</option></select></div></div>
                <div class="row mb-3"><div class="col-md-4"><input type="text" id="tableSearch" class="form-control" placeholder="🔍 Search in table..."></div></div>
                <div class="table-wrapper">
                    <table id="myTable">
                        <thead>
                            <tr><th>Sl No</th><th>Date</th><th>DO No</th><th>Vehicle No</th><th>Challan No</th><th>Challan Qty.</th><th>Recive Qty.</th><th>Rate</th><th>Shortage</th><th>Freight</th><th>Price</th><th>Diesel</th><th>Driver Exp</th><th>Deduction</th><th>Net Amount</th><th>Deposited</th><th>By</th><th>Date</th><th>Tds</th><th>Other</th><th>Status</th><th>Rec. Date</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php $i = ($records_per_page === 'all') ? 1 : ($current_page - 1) * $records_per_page + 1; foreach ($despatch as $des): ?>
                            <tr data-id="<?= $des->despatch_id; ?>">
                                <td><?= $i++; ?></td><td><?= date('d-m-Y', strtotime($des->des_date)); ?></td><td><?= $des->doreg_no; ?></td><td><?= $des->vehicle_number; ?></td><td><?= $des->ref_no; ?></td><td class="quantity"><?= $des->quantity; ?></td><td><input type="text" class="uk-input rest_amount" value="<?= $des->rest_amount; ?>"></td><td class="rate"><?= $des->rate; ?></td><td><input type="text" class="uk-input shortage" value="<?= $des->shortage; ?>" readonly></td><td><input type="text" class="uk-input freight" value="<?= $des->freight; ?>" readonly></td><td><input type="text" class="uk-input shortage_price" value="<?= $des->shortage_price; ?>" readonly></td>
                                <td><input type="text" class="uk-input dieselPrice" value="<?= $des->dieselPrice; ?>" style="width:40px;"> x <input type="text" class="uk-input dieselQty" value="<?= $des->dieselQty; ?>" style="width:40px;"> = <input type="number" class="uk-input totalDieselRate" value="<?= $des->totaldieselRate; ?>" style="width:60px;" readonly></td>
                                <td><input type="text" class="uk-input driver_expence" value="<?= $des->driver_expence; ?>"></td><td><input type="text" class="uk-input total_deduction" value="<?= $des->total_deduction; ?>" readonly></td><td><input type="text" class="uk-input net_amount" value="<?= $des->net_amount; ?>" readonly></td><td><input type="checkbox" value="1" class="deposited_checkbox" <?= ($des->deposited == 1) ? 'checked' : ''; ?> /></td><td><input type="text" class="uk-input deposit_by" value="<?= $des->deposit_by; ?>" readonly></td><td><input type="date" class="uk-input deposit_date" value="<?= $des->deposit_date; ?>"></td><td><input type="number" class="uk-input tds" value="<?= $des->tds; ?>"></td><td><input type="number" class="uk-input other_deduction" value="<?= $des->other_deduction; ?>"></td><td><label><input type="radio" name="p_<?= $des->despatch_id ?>" value="1" <?= ($des->payment_status == 1) ? 'checked' : ''; ?>> P</label><label><input type="radio" name="p_<?= $des->despatch_id ?>" value="0" <?= ($des->payment_status == 0) ? 'checked' : ''; ?>> U</label></td><td><input type="date" class="uk-input received_date" value="<?= $des->received_date; ?>"></td>
                                <td><button type="button" onclick="updateRow(this, '<?= isset($singleuser[0]->full_name) ? htmlspecialchars($singleuser[0]->full_name, ENT_QUOTES) : ''; ?>')">Add</button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>                            
                </div>
                <div class="records-info"><?php if ($records_per_page === 'all'): ?>Showing all <?= $total_records ?> records<?php else: ?>Showing <?= min(($current_page - 1) * $records_per_page + 1, $total_records) ?> to <?= min($current_page * $records_per_page, $total_records) ?> of <?= $total_records ?> records<?php endif; ?></div>
                <?php if ($records_per_page !== 'all' && $total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($current_page > 1): ?> <a href="?page=<?= $current_page-1 ?>&per_page=<?= $records_per_page ?>">&laquo; Prev</a> <?php endif; ?>
                    <?php for ($i=max(1,$current_page-2); $i<=min($total_pages,$current_page+2); $i++): ?> <a href="?page=<?= $i ?>&per_page=<?= $records_per_page ?>" class="<?= $i==$current_page?'current':'' ?>"><?= $i ?></a> <?php endfor; ?>
                    <?php if ($current_page < $total_pages): ?> <a href="?page=<?= $current_page+1 ?>&per_page=<?= $records_per_page ?>">Next &raquo;</a> <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('tableSearch').addEventListener('input', function() {
        const term = this.value.toLowerCase(); const rows = document.querySelectorAll('#myTable tbody tr');
        rows.forEach(row => { row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none'; });
    });
    function updateDoRegistrations() {
        var from = $("#from_date").val(); var to = $("#to_date").val();
        if (from && to) $.ajax({ url: "<?= base_url('Admin/getDoNumbers') ?>", type: "POST", data: { from_date: from, to_date: to, <?= csrf_token() ?>: "<?= csrf_hash() ?>" }, success: function(r) { $("#single").html(r); } });
    }
    function changePerPage() { var p = document.getElementById('per_page').value; var url = new URL(window.location.href); url.searchParams.set('per_page', p); url.searchParams.set('page', '1'); window.location.href = url.toString(); }
    function calculateTotal(row) {
        let p = parseFloat(row.querySelector('.dieselPrice').value) || 0; let q = parseFloat(row.querySelector('.dieselQty').value) || 0;
        row.querySelector('.totalDieselRate').value = (p * q).toFixed(2);
    }
    document.getElementById("global_diesel_price").addEventListener("input", function () {
        let p = parseFloat(this.value) || 0; document.querySelectorAll(".dieselPrice").forEach(i => { i.value = p; calculateTotal(i.closest('tr')); });
    });
    function updateRow(btn, user) {
        let row = btn.closest('tr'); let id = row.getAttribute('data-id');
        let qty = parseFloat(row.querySelector(".quantity").textContent) || 0; let rest = parseFloat(row.querySelector(".rest_amount").value) || 0; let rate = parseFloat(row.querySelector(".rate").textContent) || 0;
        let shortage = Math.max(0, qty - rest); row.querySelector(".shortage").value = shortage;
        let freight = rest * rate; row.querySelector(".freight").value = freight;
        let dep = row.querySelector(".deposited_checkbox").checked ? 1 : 0; row.querySelector(".deposit_by").value = user;
        let d_date = new Date().toISOString().split('T')[0]; row.querySelector(".deposit_date").value = d_date;
        let d_exp = parseFloat(row.querySelector(".driver_expence").value) || 0;
        let d_p = parseFloat(row.querySelector(".dieselPrice").value) || 0; let d_q = parseFloat(row.querySelector(".dieselQty").value) || 0;
        let d_t = d_p * d_q; row.querySelector(".totalDieselRate").value = d_t.toFixed(2);
        let s_p = parseFloat(row.querySelector(".shortage_price").value) || 0; let tds = parseFloat(row.querySelector(".tds").value) || 0; let oth = parseFloat(row.querySelector(".other_deduction").value) || 0;
        let t_d = s_p + d_exp + d_t + tds + oth; row.querySelector(".total_deduction").value = t_d.toFixed(2);
        let net = freight - t_d; row.querySelector(".net_amount").value = net.toFixed(2);
        let p_s = row.querySelector('input[type="radio"]:checked')?.value || 0; let r_date = row.querySelector(".received_date").value;
        $.ajax({ url: "<?= base_url('Admin/updateDispatch') ?>", type: "POST", data: { id: id, rest_amount: rest, shortage: shortage, freight: freight, dieselQty: d_q, dieselPrice: d_p, totaldieselRate: d_t, driver_expence: d_exp, deposited: dep, deposit_by: user, deposit_date: d_date, total_deduction: t_d, net_amount: net, tds: tds, otherDeduction: oth, paymentStatus: p_s, received_date: r_date, <?= csrf_token() ?>: "<?= csrf_hash() ?>" }, success: function(r) { alert(r.status === 'success' ? 'Updated' : 'Error: ' + r.message); } });
    }
</script>
<?php include("footer.php"); ?>
