<?php include("header.php"); ?>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>

    <div class="page-body">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Scrap Yard (Waiting for Sale)</h4>
                <div>
                    <button class="btn btn-success shadow-sm" onclick="downloadExcel()">
                        <i class="fa fa-file-excel-o mr-1"></i> Export to Excel
                    </button>
                </div>
            </div>

            <!-- Bulk Action Panel -->
            <div class="card mb-3 border-primary" id="bulkPanel" style="display: none; background-color: #f7f9fc; border-radius: 8px; border: 1px solid #4b49ac;">
                <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="font-weight-bold text-primary" style="font-size: 0.95rem;">
                            <i class="fa fa-check-square-o mr-2"></i><span id="selectedCount">0</span> tyres selected
                        </span>
                    </div>
                    <div class="d-flex" style="gap: 10px;">
                        <button type="button" class="btn btn-primary btn-sm" id="bulkSellBtn">
                            <i class="fas fa-hand-holding-usd"></i> Sell Selected
                        </button>
                        <button type="button" class="btn btn-success btn-sm" id="bulkRestoreBtn">
                            <i class="fas fa-undo"></i> Restore Selected
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tyerTable" class="table table-bordered table-striped" style="width:100%">
                    <thead class="thead-dark" style="position: sticky; top: 0; background-color: #fff; z-index: 2;">
                        <tr>
                            <th width="30px"><input type="checkbox" id="selectAll" style="transform: scale(1.2); cursor: pointer;"></th>
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
                                    <td><input type="checkbox" class="tyre-checkbox" value="<?= $tyer->id ?>" data-serial="<?= $tyer->tyer_sl_no ?>" style="transform: scale(1.2); cursor: pointer;"></td>
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
                                <td colspan="7" class="text-center py-4">Scrap yard is empty.</td>
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
                        <label class="form-label fw-bold">Tyre Serial Number(s)</label>
                        <textarea id="modal_serial" class="form-control" readonly rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Buyer / Vendor</label>
                        <select name="vendor_id" class="form-control select2-vendor" required>
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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.select2-container--default .select2-selection--single {
    height: 38px !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 6px !important;
    padding: 4px 8px !important;
    display: flex;
    align-items: center;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
}
.select2-container {
    z-index: 99999 !important;
}
</style>

<script>
$(document).ready(function () {
    // Initialize Select2 with dropdownParent to fix search field focus inside bootstrap modal
    $('.select2-vendor').select2({
        dropdownParent: $('#sellModal'),
        placeholder: "Select Buyer",
        allowClear: true,
        width: '100%'
    });

    $('#tyerTable').DataTable({
        "paging": false,  
        "searching": true,
        "ordering": true,
        "info": false,    
        "scrollY": "60vh", 
        "scrollCollapse": true,
        "columnDefs": [
            { "orderable": false, "targets": [0, 6] }
        ]
    });

    // Select all logic
    $('#selectAll').change(function() {
        $('.tyre-checkbox').prop('checked', $(this).prop('checked')).trigger('change');
    });

    // Checkbox change logic
    $(document).on('change', '.tyre-checkbox', function() {
        const checkedCount = $('.tyre-checkbox:checked').length;
        $('#selectedCount').text(checkedCount);
        
        if (checkedCount > 0) {
            $('#bulkPanel').slideDown(200);
        } else {
            $('#bulkPanel').slideUp(200);
            $('#selectAll').prop('checked', false);
        }
    });

    // Single Sell button click
    $('.sell-btn').click(function() {
        const id = $(this).data('id');
        const serial = $(this).data('serial');
        $('#modal_tyer_id').val(id);
        $('#modal_serial').val(serial);
        $('#sellModal .modal-title').text('Sell Scrap Tyre');
        const sellModal = new bootstrap.Modal(document.getElementById('sellModal'));
        sellModal.show();
    });

    // Bulk Sell button click
    $('#bulkSellBtn').click(function() {
        const selectedIds = [];
        const selectedSerials = [];
        $('.tyre-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
            selectedSerials.push($(this).data('serial'));
        });
        
        $('#modal_tyer_id').val(selectedIds.join(','));
        $('#modal_serial').val(selectedSerials.join(', '));
        $('#sellModal .modal-title').text('Sell Scrap Tyres (' + selectedIds.length + ' Selected)');
        
        const sellModal = new bootstrap.Modal(document.getElementById('sellModal'));
        sellModal.show();
    });

    // Bulk Restore button click
    $('#bulkRestoreBtn').click(function() {
        const selectedIds = [];
        $('.tyre-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });
        
        Swal.fire({
            title: 'Restore Selected Tyres?',
            text: `Are you sure you want to move ${selectedIds.length} selected tyres back to active stock?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Restore them!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Restoring...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: '<?= base_url('Admin/bulkScrapTyreBackToStock') ?>',
                    type: 'POST',
                    data: { tyer_ids: selectedIds },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while processing the request.'
                        });
                    }
                });
            }
        });
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
