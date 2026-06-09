<?php include("header.php"); ?>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>

    <div class="page-body">
        <div class="container-fluid">
            <h4>Sold Tyre History</h4>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <div></div> <!-- Left space -->
                <button class="btn btn-primary" onclick="downloadExcel()">Download Excel</button>
            </div>
            <div class="table-responsive">
                <table id="soldTable" class="table table-bordered table-striped" style="width:100%">
                    <thead class="thead-dark" style="position: sticky; top: 0; background-color: #fff; z-index: 2;">
                        <tr>
                            <th>SL No</th>
                            <th>Tyre SL No</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Vendor Name</th>
                            <th>Selling Date</th>
                            <th>Location</th>
                            <th>Remarks</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tyer_list)) : ?>
                            <?php $sr_no = 1; foreach ($tyer_list as $tyer) : ?>
                                <tr>
                                    <td><?= $sr_no++; ?></td>
                                    <td><span class="text-primary font-weight-bold"><?= esc($tyer->tyer_sl_no); ?></span></td>
                                    <td><?= esc($tyer->brand_name); ?></td>
                                    <td><?= esc($tyer->model); ?></td>
                                    <td><?= esc($tyer->vendor_name ?: 'N/A'); ?></td>
                                    <td><?= esc($tyer->selling_date); ?></td>
                                    <td><?= esc($tyer->location_name); ?></td>
                                    <td><small><?= esc($tyer->remark); ?></small></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-info text-white" href="<?= base_url(); ?>admin/tyre_details_vw/<?= $tyer->id ?>" title="View History">
                                            <i class="fas fa-history"></i>
                                        </a>
                                        <?php if(isset($jobAssign) && in_array(38.1, $jobAssign)){ ?>
                                            <a class="btn btn-sm btn-success restore-link" 
                                               href="javascript:void(0);" 
                                               data-href="<?= base_url(); ?>admin/soldTyreBackToStock/<?= $tyer->id ?>" title="Restore to Stock">
                                                <i class="fas fa-undo"></i>
                                            </a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="9" class="text-center">No sold records found.</td>
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

<!-- jQuery, DataTables, and SweetAlert2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {
    $('#soldTable').DataTable({
        "paging": false,  
        "searching": true,
        "ordering": true,
        "info": false,    
        "scrollY": "60vh", 
        "scrollCollapse": true
    });

    // SweetAlert Confirmation for Restore with choice
    $('.restore-link').click(function(e) {
        e.preventDefault();
        const url = $(this).data('href');
        
        Swal.fire({
            title: 'Cancel Sale & Restore Tyre',
            html: 'Choose where you want to restore this tyre:<br><br>',
            icon: 'question',
            input: 'radio',
            inputOptions: {
                'stock': 'Back to Active Stock',
                'scrap': 'Back to Scrap Yard'
            },
            inputValue: 'stock',
            showCancelButton: true,
            confirmButtonColor: '#4b49ac',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Restore Tyre',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) {
                    return 'Please select a destination!'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const destination = result.value;
                window.location.href = url + '?destination=' + destination;
            }
        });
    });
});

function downloadExcel() {
    // Reuse existing excel logic if needed or point to specific report
    alert('Excel Export for Sold History is being prepared.');
}
</script>

<?php include("footer.php"); ?>
