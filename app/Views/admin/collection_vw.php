<?php include("header.php"); ?>
<style>
    #myTable { min-width: 2500px; }
    #myTable thead th:nth-child(1), #myTable tbody td:nth-child(1) { min-width: 60px; width: 60px; }
    #myTable thead th:nth-child(2), #myTable tbody td:nth-child(2) { min-width: 100px; width: 100px; }
    #myTable thead th:nth-child(3), #myTable tbody td:nth-child(3) { min-width: 90px; width: 90px; }
    #myTable thead th:nth-child(4), #myTable tbody td:nth-child(4) { min-width: 110px; width: 110px; }
    #myTable thead th:nth-child(5), #myTable tbody td:nth-child(5) { min-width: 100px; width: 100px; }
    #myTable thead th:nth-child(6), #myTable tbody td:nth-child(6) { min-width: 100px; width: 100px; }
    #myTable thead th:nth-child(7), #myTable tbody td:nth-child(7) { min-width: 80px; width: 80px; }
    #myTable thead th:nth-child(8), #myTable tbody td:nth-child(8) { min-width: 80px; width: 80px; }
    #myTable thead th:nth-child(9), #myTable tbody td:nth-child(9) { min-width: 100px; width: 100px; }
    #myTable thead th:nth-child(10), #myTable tbody td:nth-child(10) { min-width: 100px; width: 100px; }
    #myTable thead th:nth-child(11), #myTable tbody td:nth-child(11) { display: none; }
    #myTable thead th:nth-child(12), #myTable tbody td:nth-child(12) { display: none; }
    #myTable thead th:nth-child(13), #myTable tbody td:nth-child(13) { min-width: 110px; width: 110px; }
    #myTable thead th:nth-child(14), #myTable tbody td:nth-child(14) { min-width: 100px; width: 100px; }
    #myTable thead th:nth-child(15), #myTable tbody td:nth-child(15) { min-width: 110px; width: 110px; }
    #myTable thead th:nth-child(16), #myTable tbody td:nth-child(16) { min-width: 90px; width: 90px; }
    #myTable thead th:nth-child(17), #myTable tbody td:nth-child(17) { min-width: 100px; width: 100px; }
    #myTable thead th:nth-child(18), #myTable tbody td:nth-child(18) { min-width: 80px; width: 80px; }
    #myTable thead th:nth-child(19), #myTable tbody td:nth-child(19) { min-width: 90px; width: 90px; }
    #myTable thead th:nth-child(20), #myTable tbody td:nth-child(20) { min-width: 100px; width: 100px; }
    #myTable thead th:nth-child(21), #myTable tbody td:nth-child(21) { min-width: 150px; width: 150px; } /* Group ID - Increased width */
    #myTable thead th:nth-child(22), #myTable tbody td:nth-child(22) { min-width: 80px; width: 80px; } /* Action column */
    
    .row-checkbox { width: 16px; height: 16px; cursor: pointer; margin-right: 5px; vertical-align: middle; }
    .bilty-inline-wrapper { display: flex; gap: 8px; align-items: flex-end; }
    .bilty-inline-wrapper input { flex: 1; }
    .bilty-inline-wrapper button { white-space: nowrap; padding: 10px 16px; }
    .bilty-inline-wrapper small { display: block; color: #64748b; font-size: 11px; margin-top: 4px; }
    
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
                        <div class="col-md-3"><div class="form-group-custom"><label for="deposited_status">Added Status</label><select class="form-control-custom" name="deposited_status" id="deposited_status"><option value="" <?= !isset($date['deposited_status']) || $date['deposited_status'] === '' ? 'selected' : '' ?>>All</option><option value="1" <?= isset($date['deposited_status']) && $date['deposited_status'] == '1' ? 'selected' : '' ?>>Added</option><option value="0" <?= isset($date['deposited_status']) && $date['deposited_status'] == '0' ? 'selected' : '' ?>>Not Added</option></select></div></div>
                    </div>
                    <div class="btn-group-custom">
                        <button type="submit" class="btn-custom btn-primary-custom"><i class="fa fa-filter"></i> Apply Filters</button>
                        <a href="#" id="download_excel" class="btn-custom btn-success-custom"><i class="fa fa-download"></i> Download Excel</a>
                    </div>
                </form>
            </div>

            <!-- Action Controls Bar -->
            <div class="action-controls-bar" style="display: flex; align-items: center; flex-wrap: wrap; gap: 12px; padding: 15px 20px; background: #f8fafc; border-radius: 8px; margin-bottom: 20px;">
                <!-- Bulk Actions Group -->
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" class="btn-custom btn-success-custom" style="padding: 8px 16px; display: flex; align-items: center; gap: 8px;" onclick="bulkUpdateSelected('<?= isset($singleuser[0]->full_name) ? htmlspecialchars($singleuser[0]->full_name, ENT_QUOTES) : ''; ?>')">
                        <i class="fa fa-refresh"></i> Save Changes
                    </button>

                    <div class="btn-group" role="group" aria-label="Group Tools" style="display: flex; gap: 5px;">
                        <button type="button" class="btn-custom btn-primary-custom" style="padding: 8px 16px; display: flex; align-items: center; gap: 8px;" onclick="createCollectionGroup()">
                            <i class="fa fa-plus-circle"></i> Create
                        </button>
                        <!-- <button type="button" class="btn-custom" style="background-color: #17a2b8; color: white; padding: 8px 16px; display: flex; align-items: center; gap: 8px;" onclick="openAddToGroupModal()">
                            <i class="fa fa-folder-open"></i> Add
                        </button>
                        <button type="button" class="btn-custom" style="background-color: #dc3545; color: white; padding: 8px 16px; display: flex; align-items: center; gap: 8px;" onclick="removeFromGroup()">
                            <i class="fa fa-unlink"></i> Ungroup
                        </button> -->
                    </div>
                </div>
                
                <!-- Bilty Bulk Controls -->
                <div class="bilty-bulk-controls" style="display: flex; align-items: center; gap: 8px; padding-left: 20px; border-left: 1px solid #e2e8f0;">
                    <input type="number" id="bulk_bilty_commission" step="0.01" class="form-control-custom" placeholder="Bilty Bulk" style="width: 100px;" />
                    <button type="button" class="btn-custom btn-primary-custom" style="padding: 8px 16px;" onclick="updateAllBiltyCommission()">
                        <i class="fa fa-refresh"></i> Apply Bilty
                    </button>
                </div>
                
                <!-- Search Box -->
                <div class="search-box" style="flex: 1; max-width: 300px; margin-left: auto;">
                    <input type="text" id="tableSearch" class="form-control-custom" placeholder="🔍 Search in table...">
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
                        <th>Group ID</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $i = ($records_per_page === 'all') ? 1 : ($current_page - 1) * $records_per_page + 1; 
                            foreach ($despatch as $des): 
                                // Base values
                                $qty = $des->quantity;
                                $rate = $des->rate;
                                $received = $des->rest_amount ?? 0;
                                $do_min = $des->min_qty ?? 0;
                                $s_rate = $des->shortage_rate ?? 0;
                                $d_qty = $des->dieselQty ?? 0;
                                $d_rate = $des->diesel_rate ?? 0;
                                $cash = $des->cash ?? 0;
                                $bilty = $des->bilty_commission ?? 0;
                                $tds_p = $des->tds_percentage ?? 2.00;

                                // Calculations
                                $actual_min = min($qty, $received);
                                $shortage = max(0, $qty - $actual_min);
                                $freight = $actual_min * $rate;
                                $s_price = ($shortage > 0) ? ($shortage * ($s_rate > 0 ? $s_rate : $rate)) : 0;
                                $d_amount = $d_qty * $d_rate;
                                $d_type = !empty($des->diesel_payment_type) ? $des->diesel_payment_type : 'Party';
                                $tds = ($actual_min * $rate * $tds_p) / 100;
                                $net = $freight - $s_price + (strcasecmp($d_type, 'Own') == 0 ? $d_amount : -$d_amount) - $cash + $bilty + $tds;
                        ?>
                        <tr data-id="<?= $des->despatch_id; ?>" data-min-qty="<?= $do_min; ?>" data-shortage-rate="<?= $s_rate; ?>" data-diesel-rate="<?= $d_rate; ?>" data-tds-percentage="<?= $tds_p; ?>" data-diesel-payment-type="<?= $d_type; ?>">
                            <td style="min-width: 60px;"><input type="checkbox" class="row-checkbox" onchange="updateBulkBar()"> <?= $i++; ?></td>
                            <td style="min-width: 100px;"><?= date('d-m-Y', strtotime($des->des_date)); ?></td>
                            <td style="min-width: 90px;"><?= $des->doreg_no; ?></td>
                            <td style="min-width: 110px;"><?= $des->vehicle_number; ?></td>
                            <td style="min-width: 100px;"><?= $des->ref_no ?? '-'; ?></td>
                            <td style="min-width: 100px;"><?= $des->challan_no ?? '-'; ?></td>
                            <td class="quantity" style="min-width: 80px; font-weight: 600;"><?= number_format($qty, 2); ?></td>
                            <td class="rate" style="min-width: 80px; font-weight: 600;"><?= number_format($rate, 2); ?></td>
                            <td style="min-width: 100px;"><input type="number" step="0.01" class="uk-input rest_amount" value="<?= $received > 0 ? $received : ''; ?>" oninput="calculateRow(this)" style="width: 90px;"></td>
                            <td style="min-width: 100px;"><input type="text" class="uk-input min_qty_col" value="<?= number_format($actual_min, 2); ?>" readonly style="width: 90px;"></td>
                            <td style="min-width: 90px;"><input type="text" class="uk-input shortage" value="<?= number_format($shortage, 2); ?>" readonly style="width: 80px;"></td>
                            <td style="min-width: 100px;"><input type="text" class="uk-input freight" value="<?= number_format($freight, 2); ?>" readonly style="width: 90px;"></td>
                            <td style="min-width: 110px;"><input type="text" class="uk-input shortage_price" value="<?= number_format($s_price, 2); ?>" readonly style="width: 100px;"></td>
                            <td style="min-width: 100px;"><input type="number" step="0.01" class="uk-input dieselQty" value="<?= $d_qty; ?>" oninput="calculateDieselAmount(this)" style="width: 90px;"></td>
                            <td style="min-width: 110px;"><input type="text" class="uk-input diesel_amount" value="<?= number_format($d_amount, 2); ?>" readonly style="width: 100px;"></td>
                            <td style="min-width: 90px;"><input type="number" step="0.01" class="uk-input cash" value="<?= $cash; ?>" oninput="calculateRow(this)" style="width: 80px;"></td>
                            <td style="min-width: 100px;"><input type="number" step="0.01" class="uk-input bilty_commission" value="<?= $bilty; ?>" oninput="calculateRow(this)" style="width: 90px;"></td>
                            <td style="min-width: 80px;"><input type="text" class="uk-input tds" value="<?= number_format($tds, 2); ?>" readonly style="width: 70px;"></td>
                            <td style="min-width: 110px;"><input type="text" class="uk-input net_amount" value="<?= number_format($net, 2); ?>" readonly style="width: 100px; font-weight: 600; color: #059669;"></td>
                            <td style="min-width: 100px;"><input type="text" class="uk-input added_by" value="<?= $des->deposit_by ?? ''; ?>" readonly style="width: 90px;"></td>
                            <input type="hidden" class="diesel_payment_type_val" value="<?= $d_type; ?>">
                            <td style="min-width: 150px;"><span class="badge badge-primary group-id-badge" title="<?= $des->group_code ?? '-'; ?>"><?= $des->group_code ?? '-'; ?></span></td>
                            <td style="min-width: 80px; text-align: center;"><button type="button" onclick="updateRow(this, '<?= isset($singleuser[0]->full_name) ? htmlspecialchars($singleuser[0]->full_name, ENT_QUOTES) : ''; ?>')">Add</button></td>
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
<!-- Add to Group Modal -->
<div id="addToGroupModal" class="modal" tabindex="-1" role="dialog" style="display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
  <div class="modal-dialog" role="document" style="margin: 10% auto; max-width: 500px;">
    <div class="modal-content" style="background-color: #fefefe; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <div class="modal-header" style="padding: 15px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
        <h5 class="modal-title" style="margin: 0; font-size: 1.25rem;">Select Existing Group</h5>
        <button type="button" class="close" onclick="closeAddToGroupModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
      </div>
      <div class="modal-body" style="padding: 20px;">
        <p>Select a group to add the selected records to:</p>
        <select id="groupSelect" class="form-control-custom" style="width: 100%;">
            <option value="">Loading groups...</option>
        </select>
      </div>
      <div class="modal-footer" style="padding: 15px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" class="btn-custom" onclick="addToGroup()" style="background-color: #3b82f6; color: white;">Add to Group</button>
        <button type="button" class="btn-custom" onclick="closeAddToGroupModal()" style="background-color: #6c757d; color: white;">Cancel</button>
      </div>
    </div>
  </div>
</div>

<script>
    // ... existing search script ...
    document.getElementById('tableSearch').addEventListener('input', function() {
        const term = this.value.toLowerCase(); const rows = document.querySelectorAll('#myTable tbody tr');
        rows.forEach(row => { row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none'; });
    });

    // Modal Functions
    function openAddToGroupModal() {
        const selected = document.querySelectorAll(".row-checkbox:checked");
        if (selected.length === 0) {
            alert('Please select records to add to a group!');
            return;
        }
        
        fetchGroups();
        document.getElementById('addToGroupModal').style.display = 'block';
    }

    function closeAddToGroupModal() {
        document.getElementById('addToGroupModal').style.display = 'none';
    }

    // Close modal if clicked outside
    window.onclick = function(event) {
        if (event.target == document.getElementById('addToGroupModal')) {
            closeAddToGroupModal();
        }
    }

    function fetchGroups() {
        const select = document.getElementById('groupSelect');
        select.innerHTML = '<option value="">Loading...</option>';
        
        $.ajax({
            url: "<?= base_url('Admin/get_active_groups') ?>",
            type: "GET",
            success: function(r) {
                if(r.status === 'success') {
                    if(r.groups.length > 0) {
                        let html = '<option value="">-- Select Group --</option>';
                        r.groups.forEach(g => {
                            html += `<option value="${g.id}">${g.group_code} (${new Date(g.created_at).toLocaleDateString()})</option>`;
                        });
                        select.innerHTML = html;
                    } else {
                        select.innerHTML = '<option value="">No active groups found</option>';
                    }
                } else {
                    select.innerHTML = '<option value="">Error loading groups</option>';
                }
            },
            error: function() {
                select.innerHTML = '<option value="">Error loading groups</option>';
            }
        });
    }

    function addToGroup() {
        const selected = document.querySelectorAll(".row-checkbox:checked");
        const groupId = document.getElementById('groupSelect').value;
        
        if (!groupId) {
            alert('Please select a group!');
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
            success: function(r) {
                if (r.status === 'success') {
                    alert(r.message);
                    location.reload();
                } else {
                    alert('Error: ' + r.message);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            },
            error: function(err) {
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
            alert('Please select records to ungroup!');
            return;
        }

        if (!confirm(`Are you sure you want to remove ${selected.length} records from their groups?`)) return;

        const ids = Array.from(selected).map(cb => cb.closest('tr').getAttribute('data-id'));
        
        $.ajax({
            url: "<?= base_url('Admin/manage_collection_group') ?>",
            type: "POST",
            data: {
                ids: ids,
                action: 'remove',
                <?= csrf_token() ?>: "<?= csrf_hash() ?>"
            },
            success: function(r) {
                if (r.status === 'success') {
                    alert(r.message);
                    location.reload();
                } else {
                    alert('Error: ' + r.message);
                }
            },
            error: function(err) {
                console.error(err);
                alert('An error occurred');
            }
        });
    }

    function updateDoRegistrations() {
        var from = $("#from_date").val(); var to = $("#to_date").val();
        if (from && to) $.ajax({ url: "<?= base_url('Admin/getDoNumbers') ?>", type: "POST", data: { from_date: from, to_date: to, <?= csrf_token() ?>: "<?= csrf_hash() ?>" }, success: function(r) { $("#single").html(r); } });
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
            alert('Please select at least one record to create a group!');
            return;
        }

        if (!confirm(`Are you sure you want to create a group for ${selected.length} selected records?`)) return;

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
            success: function(r) {
                btn.disabled = false;
                btn.innerHTML = originalText;
                if (r.status === 'success') {
                    alert('Group created successfully! ID: ' + r.group_code);
                    location.reload();
                } else {
                    alert('Error: ' + r.message);
                }
            },
            error: function(xhr, status, error) {
                btn.disabled = false;
                btn.innerHTML = originalText;
                console.error('AJAX Error:', error);
                alert('Error creating group. Check console for details.');
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
        let id = row.getAttribute('data-id');
        let qty = parseFloat(row.querySelector(".quantity").textContent.replace(/,/g, '')) || 0;
        let rest = parseFloat(row.querySelector(".rest_amount").value) || 0;
        let rate = parseFloat(row.querySelector(".rate").textContent.replace(/,/g, '')) || 0;
        let shortage_rate = parseFloat(row.getAttribute('data-shortage-rate')) || 0;
        
        let min_qty_val = Math.min(qty, rest);
        row.querySelector(".min_qty_col").value = min_qty_val.toFixed(2);
        
        let shortage = Math.max(0, qty - min_qty_val);
        row.querySelector(".shortage").value = shortage.toFixed(2);
        
        let freight = min_qty_val * rate;
        row.querySelector(".freight").value = freight.toFixed(2);
        
        let shortage_price = 0;
        if (shortage > 0) {
            if (shortage_rate > 0) {
                shortage_price = shortage * shortage_rate;
            } else {
                shortage_price = shortage * rate;
            }
        }
        row.querySelector(".shortage_price").value = shortage_price.toFixed(2);
        
        let tds_percentage = parseFloat(row.getAttribute('data-tds-percentage')) || 2.00;
        let tds_base_amount = min_qty_val * rate;
        let tds_amount = (tds_base_amount * tds_percentage) / 100;
        row.querySelector(".tds").value = tds_amount.toFixed(2);
        
        let d_amount = parseFloat(row.querySelector(".diesel_amount").value.replace(/,/g, '')) || 0;
        let cash = parseFloat(row.querySelector(".cash").value.replace(/,/g, '')) || 0;
        let bilty_comm = parseFloat(row.querySelector(".bilty_commission").value.replace(/,/g, '')) || 0;
        let tds = tds_amount;

        let d_type = (row.getAttribute('data-diesel-payment-type') || 'Party').toUpperCase();
        let net = (min_qty_val * rate) - shortage_price + (d_type === 'OWN' ? d_amount : -d_amount) - cash + bilty_comm + tds;
        row.querySelector(".net_amount").value = net.toFixed(2);
    }
    
    function updateRow(btn, user, show_alert = true) {
        let row = btn.closest('tr');
        let id = row.getAttribute('data-id');
        let qty = parseFloat(row.querySelector(".quantity").textContent.replace(/,/g, '')) || 0;
        let rest = parseFloat(row.querySelector(".rest_amount").value) || 0;
        let rate = parseFloat(row.querySelector(".rate").textContent.replace(/,/g, '')) || 0;
        
        let min_qty_val = Math.min(qty, rest);
        let shortage = Math.max(0, qty - min_qty_val);
        let freight = min_qty_val * rate;
        let shortage_price = parseFloat(row.querySelector(".shortage_price").value) || 0;
        let d_q = parseFloat(row.querySelector(".dieselQty").value) || 0;
        let d_rate = parseFloat(row.getAttribute('data-diesel-rate')) || 0;
        let d_amount = d_q * d_rate;
        let cash = parseFloat(row.querySelector(".cash").value) || 0;
        let bilty_comm = parseFloat(row.querySelector(".bilty_commission").value) || 0;
        
        let tds_percentage = parseFloat(row.getAttribute('data-tds-percentage')) || 2.00;
        let tds_base_amount = min_qty_val * rate;
        let tds = (tds_base_amount * tds_percentage) / 100;
        
        let d_type = (row.getAttribute('data-diesel-payment-type') || 'Party').toUpperCase();
        let net = (min_qty_val * rate) - shortage_price + (d_type === 'OWN' ? d_amount : -d_amount) - cash + bilty_comm + tds;
        
        let t_d = shortage_price + (d_type === 'OWN' ? -d_amount : d_amount) + cash + bilty_comm + tds;
        
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
            success: function(r) {
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
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
                if (show_alert) alert('Error updating record. Check console for details.');
            }
        });
    }
</script>
<?php include("footer.php"); ?>