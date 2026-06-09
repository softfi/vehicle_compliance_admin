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
.btn-action-premium {
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.75rem;
}
</style>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid p-0">        
            <div class="premium-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="text-white mb-1 fw-bold">Tyre Purchase Management</h2>
                        <p class="text-white-50 mb-0">Manage incoming tyre bills and inventory transfers</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group">
                            <?php if(in_array(24.1,$jobAssign)){ ?>
                                <a class="btn btn-success rounded-pill px-4 me-2 font-weight-bold" href="<?php echo base_url();?>/admin/addtyerbill">
                                    <i class="fas fa-plus-circle me-2"></i>Enter Stock
                                </a>
                            <?php }?>
                            <a class="btn btn-info text-white rounded-pill px-4 me-2 font-weight-bold" href="<?php echo base_url();?>/admin/tyreTransfer">
                                <i class="fas fa-exchange-alt me-2"></i>Transfer
                            </a>
                            <button onclick="downloadExcel()" class="btn btn-light rounded-pill px-4 font-weight-bold shadow-sm">
                                <i class="fas fa-file-excel text-success me-2"></i>Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px; background: #d4edda; color: #155724;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fa-lg"></i>
                        <div>
                            <strong>Success!</strong> <?= session()->getFlashdata('success') ?>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px; background: #f8d7da; color: #721c24;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-3 fa-lg"></i>
                        <div>
                            <strong>Error!</strong> <?= session()->getFlashdata('error') ?>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="premium-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 premium-table" id="purchaseTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Bill Info</th>
                                <th>Party / Vendor</th>
                                <th>Brand & Model</th>
                                <th>Qty</th>
                                <th>Amount</th>
                                <th>Location</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            foreach ($tyer_data as $tyer): ?>
                            <tr>
                                <td><strong><?= $i++; ?></strong></td>
                                <td>
                                    <div class="fw-bold">#<?= esc($tyer->bill_no) ?></div>
                                    <small class="text-muted"><?= esc($tyer->date) ?></small>
                                </td>
                                <td><?= esc($tyer->name) ?></td>
                                <td>
                                    <div class="fw-bold"><?= esc($tyer->brand_name) ?></div>
                                    <small class="text-muted"><?= esc($tyer->model) ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border px-3"><?= $tyer->qty; ?></span></td>
                                <td class="fw-bold text-success">₹<?= number_format($tyer->price, 2) ?></td>
                                <td><span class="badge bg-info-soft text-info rounded-pill px-3"><?= esc($tyer->location_name) ?></span></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <?php if(in_array(24.2,$jobAssign)){ ?>
                                            <button class="btn btn-outline-primary btn-action-premium view-details" 
                                                    data-bill-no="<?= $tyer->bill_no ?>" 
                                                    data-location="<?= $tyer->location_id ?>"
                                                    title="View Serials">
                                                <i class="fas fa-list"></i>
                                            </button>
                                        <?php }?>
                                        <?php if(in_array(24.3,$jobAssign)){ ?>
                                            <a class="btn btn-outline-warning btn-action-premium" href="<?= base_url('admin/edit_tyer/'.$tyer->id) ?>" title="Edit Bill">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a class="btn btn-outline-danger btn-action-premium" href="<?= base_url('admin/delete_tyer/'.$tyer->id) ?>" 
                                               onclick="return confirm('Delete this bill and associated tyres?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php }?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-info-soft { background-color: rgba(54, 162, 235, 0.1); }
</style>


    <!-- Off-Canvas Panel Start-->
    <div id="offcanvas" uk-offcanvas="overlay: true; flip: true">
        <div class="uk-offcanvas-bar uk-margin-remove uk-padding-remove">
            <button class="uk-offcanvas-close" type="button" uk-close></button>
            <div class="uk-card uk-card-body uk-card-default uk-card-small">
            <h5>Tyre Details</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>Serial No</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody id="tyre-details">
                    <!-- Details will be loaded here -->
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <!-- Off-Canvas Panel End-->

<script>
    $(document).ready(function () {
        if ($.fn.DataTable.isDataTable('#purchaseTable')) {
            $('#purchaseTable').DataTable().destroy();
        }
        
        $('#purchaseTable').DataTable({
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search bills..."
            }
        });

        $('.view-details').on('click', function () {
            var billNo = $(this).data('bill-no');
            var location = $(this).data('location');

            // Show loading state
            $('#tyre-details').html('<tr><td colspan="2" class="text-center"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>');
            
            // Open the offcanvas early to show loading
            if (window.UIkit) {
                UIkit.offcanvas('#offcanvas').show();
            }

            $.ajax({
                url: '<?php echo base_url("admin/getTyerDetailsByBillNo"); ?>',
                type: 'POST',
                data: { 
                    bill_no: billNo,
                    location: location
                },
                dataType: 'json',
                success: function (details) {
                    var detailsHtml = '';
                    if (details && details.length > 0) {
                        $.each(details, function (index, detail) {
                            detailsHtml += '<tr><td>' + (detail.tyer_sl_no || 'N/A') + '</td><td>' + (detail.tyer_type || 'N/A') + '</td></tr>';
                        });
                    } else {
                        detailsHtml = '<tr><td colspan="2" class="text-center text-muted">No serials found for this bill.</td></tr>';
                    }
                    $('#tyre-details').html(detailsHtml);
                },
                error: function () {
                    $('#tyre-details').html('<tr><td colspan="2" class="text-center text-danger">Failed to fetch details.</td></tr>');
                }
            });
        });
    });
</script>
<script>
    function downloadExcel() {
        var location = 1;
        if (location == 1) {
            $.ajax({
                url: '<?= base_url(); ?>/admin/expert_excel_tyre_management',
                type: 'POST',
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response, status, xhr) {
                    var filename = "";
                    var disposition = xhr.getResponseHeader('Content-Disposition');
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        var matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                    }
                    var blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename || 'export.xlsx';
                    link.click();
                },
                error: function(xhr, status, error) {
                    alert("An error occurred: " + error);
                }
            });
        } else {
            alert("Error downloading the Excel file.");
        }
    }
</script>

<?php include("footer.php"); ?>
