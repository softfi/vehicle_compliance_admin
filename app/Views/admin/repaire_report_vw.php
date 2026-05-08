<?php include("header.php"); ?>
<style>
.premium-header {
    background: linear-gradient(135deg, #434343 0%, #000000 100%);
    color: white;
    padding: 30px;
    border-radius: 0 0 25px 25px;
    margin-bottom: 30px;
}
.premium-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    padding: 20px;
    margin-bottom: 30px;
    border: 1px solid #f0f0f0;
}
.premium-table thead {
    background: #f8f9fa;
}
.premium-table thead th {
    border: none;
    font-weight: 700;
    font-size: 0.8rem;
    color: #555;
    padding: 15px;
}
.badge-status {
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.75rem;
}
.btn-action-premium {
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.75rem;
    margin-right: 5px;
}
</style>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid p-0">        
            <div class="premium-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="text-white mb-1 fw-bold">Tyre Repair Report</h2>
                        <p class="text-white-50 mb-0">Monitor tyres currently undergoing maintenance and repair</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="premium-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 premium-table" id="repairTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tyre Serial</th>
                                <th>Bill Info</th>
                                <th>Status</th>
                                <th>Repair Vendor</th>
                                <th>Remarks</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            foreach ($tyer_data as $report) {
                                $statusLabel = ($report->status == 4) ? 'Under Repair' : 'Unknown';
                            ?>
                                <tr>
                                    <td><strong><?= $i++ ?></strong></td>
                                    <td>
                                        <span class="text-primary fw-bold"><?= esc($report->tyer_sl_no) ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>#<?= esc($report->bill_no) ?></span>
                                            <small class="text-muted"><?= esc($report->date ?: '—') ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-status bg-warning text-dark">
                                            <i class="fas fa-tools me-1"></i> <?= $statusLabel ?>
                                        </span>
                                    </td>
                                    <td><?= esc($report->exchange_vendorname ?: '—') ?></td>
                                    <td>
                                        <small class="text-muted"><?= esc($report->remark ?: '—') ?></small>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= base_url(); ?>/admin/tyre_details_vw/<?= $report->id ?>" 
                                           class="btn btn-outline-info btn-action-premium" title="View History">
                                            <i class="fas fa-history me-1"></i> History
                                        </a>
                                        <a class="btn btn-outline-success btn-action-premium editBtn" 
                                           href="#modal-sections" uk-toggle
                                           data-tyer-id="<?= $report->id ?>"
                                           data-tyer-sl-no="<?= $report->tyer_sl_no ?>"
                                           data-vendor="<?= $report->ex_ven_id ?>"
                                           data-location="<?= $report->location_id ?>"
                                           data-date="<?= $report->date ?>">
                                           <i class="fas fa-undo me-1"></i> Back to Stock
                                        </a>
                                    </td>
                                </tr>   
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="modal-sections" class="uk-flex-top" uk-modal>
    <div class="uk-modal-dialog uk-margin-auto-vertical modal-modern">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <div class="uk-modal-body">
            <h4 class="mb-4 fw-bold text-primary">
                <i class="fas fa-boxes me-2"></i>Move Back to Stock
            </h4>
            <form id="editForm" method="post" action="<?php echo base_url();?>/Admin/update_tyer_repair">
                <input type="hidden" name="id" id="tyer_id">
                <input type="hidden" name="tyer_sl_no" id="tyer_sl_no">

                <div class="mb-3">
                    <label class="form-label small fw-bold">Repair Vendor</label>
                    <select class="form-select bg-light" id="vendor_select" disabled style="color: #000 !important; opacity: 1;">
                        <option value="">Select Vendor</option>
                        <?php foreach($vendor as $v) { ?>
                            <option value="<?= $v->id ?>"><?= $v->name ?></option>
                        <?php } ?>
                    </select>
                    <!-- Hidden field to actually submit the vendor ID since disabled selects aren't posted -->
                    <input type="hidden" name="vendor" id="hidden_vendor">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Return Location</label>
                    <select class="form-select" name="location" id="location" required>
                        <option value="">Select Location</option>
                        <?php foreach($location as $loc) { ?>
                            <option value="<?= $loc->location_id ?>"><?= $loc->location_name ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">Stock Entry Date</label>
                    <input type="date" class="form-control" name="date" id="date" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <button class="btn btn-light uk-modal-close" type="button">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" id="saveBtn">
                        Confirm & Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#repairTable').DataTable({
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search repairs..."
        }
    });

    $(document).on('click', '.editBtn', function() {
        let tyerId = $(this).data('tyer-id');
        let tyerSlNo = $(this).data('tyer-sl-no');
        let vendor = $(this).data('vendor');
        let location = $(this).data('location');
        let date = $(this).data('date');
        
        $('#tyer_id').val(tyerId);
        $('#tyer_sl_no').val(tyerSlNo);
        
        // Auto-select and lock the vendor
        $('#vendor_select').val(vendor);
        $('#hidden_vendor').val(vendor);
        
        $('#location').val(location);
        $('#date').val(date || '<?= date('Y-m-d') ?>');
    });
});
</script>

<?php include("footer.php"); ?>
; ?>
