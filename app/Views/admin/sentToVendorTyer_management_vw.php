<?php include("header.php"); ?>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>

    <div class="page-body">
        <div class="container-fluid py-4">
            <div class="page-title mb-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="mb-0">
                            <i class="fas fa-sync-alt text-warning"></i> Tyres: Exchange Requested
                        </h3>
                        <p class="text-muted small mb-0">Tyres currently waiting for exchange completion</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-list"></i> Pending Exchanges</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="pendingClaimsTable" class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Tyre Serial</th>
                                    <th>Brand</th>
                                    <th>Model</th>
                                    <th>Bill No</th>
                                    <th>Request Date</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($tyer_list)) : ?>
                                    <?php $i = 1; foreach ($tyer_list as $tyer) : ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td><strong class="text-primary"><?= esc($tyer->tyer_sl_no); ?></strong></td>
                                            <td><?= esc($tyer->brand_name); ?></td>
                                            <td><?= esc($tyer->model); ?></td>
                                            <td><?= esc($tyer->bill_no); ?></td>
                                            <td><?= date('d-M-Y', strtotime($tyer->date)); ?></td>
                                            <td><?= esc($tyer->location_name); ?></td>
                                            <td>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-clock"></i> Exchange Requested
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= base_url(); ?>admin/tyre_details_vw/<?= $tyer->id ?>" class="btn btn-sm btn-info text-white" title="View History">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                                <a href="<?= base_url(); ?>admin/vendor_exchange/<?= $tyer->id ?>" class="btn btn-sm btn-success" title="Complete Exchange">
                                                    <i class="fas fa-check-circle"></i> Complete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">No pending claims found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    $('#pendingClaimsTable').DataTable({
        "paging": true,
        "searching": true,
        "ordering": true,
        "info": true
    });
});
</script>

<?php include("footer.php"); ?>
