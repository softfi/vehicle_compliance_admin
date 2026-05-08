<?php include("header.php"); ?>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>

    <div class="page-body">
        <div class="container-fluid">
            <h4>Tyre List (Vehicle Not Assigned)</h4>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <div></div> <!-- Left space -->
                <a class="btn btn-primary" type="button" onclick="downloadExcel()">Download Excel</a>
            </div>
            <div class="table-responsive">
                <table id="tyerTable" class="table table-bordered table-striped" style="width:100%">
                    <thead class="thead-dark" style="position: sticky; top: 0; background-color: #fff; z-index: 2;">
                        <tr>
                            <th>SL No</th>
                            <th>Tyre SL No</th>
                            <th>Tyre Type</th>
                            <th>Tyer Condition</th>
                            <th>Tyer Position</th>
                            <th>Tyre Model</th>
                            <th>Bill No.</th>
                            <th>Purchase Date</th>
                            <th>Location Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tyer_list)) : ?>
                            <?php $sr_no = 1; foreach ($tyer_list as $tyer) : ?>
                                <tr>
                                    <td><?= $sr_no++; ?></td>
                                    <td><?= esc($tyer->tyer_sl_no); ?></td>
                                    <td><?= esc($tyer->brand_name); ?></td>

                                    <!-- New / Old -->
                                    <td>
                                        <?= $tyer->tyre_condition == 'Old'
                                            ? '<span class="uk-label uk-label-warning">Old</span>'
                                            : '<span class="uk-label uk-label-success">New</span>'; ?>
                                    </td>

                                    <td><?= esc($tyer->tyer_type); ?></td>
                                    <td><?= esc($tyer->model); ?></td>
                                    <td><?= esc($tyer->bill_no); ?></td>
                                    <td><?= esc($tyer->date); ?></td>
                                    <td><?= esc($tyer->location_name); ?></td>
                                    <td>
                                        <a class="btn btn-sm btn-info text-white" href="<?= base_url(); ?>admin/tyre_details_vw/<?= $tyer->id ?>" title="View History">
                                            <i class="fas fa-history"></i>
                                        </a>
                                        <a class="btn btn-sm btn-primary" href="<?= base_url(); ?>admin/tyer_exchange/<?= $tyer->id ?>" title="Update Status (Repair/Scrap/Sold)">
                                            <i class="fas fa-sync-alt"></i> Status
                                        </a>
                                        <button class="btn btn-sm btn-warning send-vendor-btn" 
                                                data-id="<?= $tyer->id ?>" 
                                                data-serial="<?= $tyer->tyer_sl_no ?>" title="Request Tyre Exchange">
                                            <i class="fas fa-sync-alt"></i> Exchange
                                        </button>
                                        <button class="btn btn-sm btn-danger scrap-btn" 
                                                data-id="<?= $tyer->id ?>" 
                                                data-serial="<?= $tyer->tyer_sl_no ?>" title="Quick Scrap">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="10" class="text-center">No tyres found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

    $('.send-vendor-btn').click(function() {
        const id = $(this).data('id');
        const serial = $(this).data('serial');
        const row = $(this).closest('tr');

        Swal.fire({
            title: 'Request Exchange?',
            text: `Tyre Serial: ${serial}. Mark this tyre as Exchange Requested?`,
            input: 'textarea',
            inputPlaceholder: 'Add any remarks here...',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Request Exchange',
            showLoaderOnConfirm: true,
            preConfirm: (remark) => {
                return $.ajax({
                    url: '<?= base_url(); ?>admin/sent_to_vendor',
                    type: 'POST',
                    data: { tyer_id: id, remark: remark },
                    dataType: 'json'
                }).then(response => {
                    if (response.status !== 'success') {
                        throw new Error(response.message || 'Error occurred');
                    }
                    return response;
                }).catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Requested!', 'Tyre status updated to Exchange Requested.', 'success');
                row.fadeOut(500, function() { $(this).remove(); });
            }
        });
    });

    $('.scrap-btn').click(function() {
        const id = $(this).data('id');
        const serial = $(this).data('serial');
        const row = $(this).closest('tr');

        Swal.fire({
            title: 'Move to Scrap Yard?',
            text: `Tyre Serial: ${serial}. Please provide a reason:`,
            input: 'textarea',
            inputPlaceholder: 'Type your reason here...',
            inputAttributes: {
                'aria-label': 'Type your reason here'
            },
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Confirm Scrap',
            showLoaderOnConfirm: true,
            preConfirm: (remark) => {
                if (!remark) {
                    Swal.showValidationMessage('Reason is required!');
                    return false;
                }
                return $.ajax({
                    url: '<?= base_url(); ?>admin/moveStockToScrap',
                    type: 'POST',
                    data: { tyer_id: id, remark: remark },
                    dataType: 'json'
                }).then(response => {
                    if (response.status !== 'success') {
                        throw new Error(response.message || 'Error occurred');
                    }
                    return response;
                }).catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Success!', 'Tyre moved to scrap yard.', 'success');
                row.fadeOut(500, function() { $(this).remove(); });
            }
        });
    });
});

function downloadExcel() {
    $.ajax({
        url: '<?= base_url(); ?>admin/export_excel_Stocktyre_management',
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
                if (matches != null && matches[1]) {
                    filename = matches[1].replace(/['"]/g, '');
                }
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
}
</script>

<?php include("footer.php"); ?>
