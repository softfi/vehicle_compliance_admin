<?php include("header.php"); ?>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>

    <div class="page-body">
        <div class="container-fluid">
            <h4>Scrap Yard (Waiting for Sale)</h4>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <div></div>
                <button class="btn btn-success" onclick="downloadExcel()">Export to Excel</button>
            </div>
            <div class="table-responsive">
                <table id="tyerTable" class="table table-bordered table-striped" style="width:100%">
                    <thead class="thead-dark" style="position: sticky; top: 0; background-color: #fff; z-index: 2;">
                        <tr>
                            <th>SL No</th>
                            <th>Tyre Serial</th>
                            <th>Brand / Type</th>
                            <th>Purchase Bill</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tyer_list)) : ?>
                            <?php $sr_no = 1; foreach ($tyer_list as $tyer) : ?>
                                <tr>
                                    <td><?= $sr_no++; ?></td>
                                    <td><span class="text-primary font-weight-bold"><?= $tyer->tyer_sl_no; ?></span></td>
                                    <td><?= $tyer->brand_name; ?> <br><small class="text-muted"><?= $tyer->tyer_type; ?></small></td>
                                    <td>#<?= $tyer->bill_no; ?> <br><small class="text-muted"><?= $tyer->date; ?></small></td>
                                    <td><?= $tyer->location_name; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary sell-btn" 
                                                data-id="<?= $tyer->id ?>" 
                                                data-serial="<?= $tyer->tyer_sl_no ?>">
                                            <i class="fas fa-hand-holding-usd"></i> Sell
                                        </button>
                                        <a class="btn btn-sm btn-info text-white" href="<?= base_url(); ?>admin/tyre_details_vw/<?= $tyer->id ?>">
                                            <i class="fas fa-history"></i>
                                        </a>
                                        <?php if(isset($jobAssign) && in_array(38.1, $jobAssign)){ ?>
                                            <a class="btn btn-sm btn-success restore-link" 
                                               href="javascript:void(0);" 
                                               data-href="<?= base_url(); ?>admin/scrapTyreBackToStock/<?= $tyer->id ?>">
                                                <i class="fas fa-undo"></i>
                                            </a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">Scrap yard is empty.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Sell Tyre Modal -->
<div class="modal fade" id="sellModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">Sell Scrap Tyre</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('Admin/process_tyre_sale') ?>" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="tyer_id" id="modal_tyer_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tyre Serial Number</label>
                        <input type="text" id="modal_serial" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Buyer / Vendor</label>
                        <select name="vendor_id" class="form-control" required>
                            <option value="">Select Buyer</option>
                            <?php foreach($vendors as $v): ?>
                                <option value="<?= $v->id ?>"><?= $v->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Selling Date</label>
                        <input type="date" name="selling_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold">Remarks</label>
                        <textarea name="remark" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm Sale</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {
    $('#tyerTable').DataTable({
        "paging": false,  
        "searching": true,
        "ordering": true,
        "info": false,    
        "scrollY": "60vh", 
        "scrollCollapse": true
    });

    $('.sell-btn').click(function() {
        const id = $(this).data('id');
        const serial = $(this).data('serial');
        $('#modal_tyer_id').val(id);
        $('#modal_serial').val(serial);
        const sellModal = new bootstrap.Modal(document.getElementById('sellModal'));
        sellModal.show();
    });

    $('.restore-link').click(function(e) {
        e.preventDefault();
        const url = $(this).data('href');
        
        Swal.fire({
            title: 'Restore to Stock?',
            text: "Are you sure you want to move this tyre back to active stock?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Restore it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
});

function downloadExcel() {
    window.location.href = '<?= base_url(); ?>admin/export_excel_Stocktyre_management';
}
</script>

<?php include("footer.php"); ?>
