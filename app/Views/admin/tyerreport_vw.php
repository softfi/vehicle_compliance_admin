<?php include("header.php"); ?>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>

    <div class="page-body">
        <div class="container-fluid">
            <h4>Tyre Inventory Report</h4>

            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body p-3">
                    <form method="post" action="<?= base_url() ?>/Admin/tyer_report">
                        <div class="row align-items-end g-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Location Filter</label>
                                <select id="location_id" name="location_id" class="form-control form-control-sm">
                                    <option value="">All Locations</option>
                                    <?php foreach ($location as $loc) { ?>
                                        <option value="<?= $loc->location_id; ?>" <?= (isset($selected_location) && $selected_location == $loc->location_id) ? 'selected' : '' ?>>
                                            <?= $loc->location_name; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-4" style="display: none;">
                                <label class="form-label small fw-bold">Status Filter</label>
                                <select id="status" name="status" class="form-control form-control-sm">
                                    <option value="">All Status (Excl. Stock)</option>
                                    <option value="2" <?= (isset($selected_status) && $selected_status == 2) ? 'selected' : '' ?>>Assigned</option>
                                    <option value="10" <?= (isset($selected_status) && $selected_status == 10) ? 'selected' : '' ?>>Exchange Requested</option>
                                    <option value="11" <?= (isset($selected_status) && $selected_status == 11) ? 'selected' : '' ?>>Exchange Completed</option>
                                    <option value="4" <?= (isset($selected_status) && $selected_status == 4) ? 'selected' : '' ?>>Under Repair</option>
                                    <option value="3" <?= (isset($selected_status) && $selected_status == 3) ? 'selected' : '' ?>>Scrap Yard</option>
                                    <option value="7" <?= (isset($selected_status) && $selected_status == 7) ? 'selected' : '' ?>>Sold</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <?php if (in_array(26.1, $jobAssign)) { ?>
                                    <button class="btn btn-sm btn-dark w-100">Filter Records</button>
                                <?php } ?>
                            </div>
                            <div class="col-md-2 text-end">
                                <button type="button" onclick="downloadExcel()" class="btn btn-sm btn-success">
                                    <i class="fas fa-file-excel me-1"></i> Export Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tyre_inventory_table" class="table table-bordered table-striped" style="width:100%">
                    <thead class="thead-dark" style="position: sticky; top: 0; background-color: #fff; z-index: 2;">
                        <tr>
                            <th>Sl No</th>
                            <th>Serial Number</th>
                            <th>Brand / Model</th>
                            <th>Bill No</th>
                            <th>Location</th>
                            <th>Condition</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tyer_data)): ?>
                            <?php $i = 1;
                            foreach ($tyer_data as $tyer): ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td><span class="text-primary fw-bold"><?= $tyer->tyer_sl_no ?></span></td>
                                    <td>
                                        <div class="fw-bold"><?= $tyer->brand_name ?></div>
                                        <small class="text-muted"><?= $tyer->tyer_type ?> | <?= $tyer->model ?></small>
                                    </td>
                                    <td><?= $tyer->bill_no ?></td>
                                    <td><?= $tyer->location_name ?></td>
                                    <td>
                                        <?php if ($tyer->tyre_condition == 'Old'): ?>
                                            <span class="badge bg-warning text-dark">Old</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">New</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($tyer->status == 1): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> In Stock</span>
                                        <?php elseif ($tyer->status == 2): ?>
                                            <span class="badge bg-primary"><i class="fas fa-link"></i> Assigned</span>
                                        <?php elseif ($tyer->status == 10): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-sync-alt"></i> Exchange
                                                Requested</span>
                                        <?php elseif ($tyer->status == 11): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-double"></i> Exchange
                                                Completed</span>
                                        <?php elseif ($tyer->status == 3): ?>
                                            <span class="badge bg-danger"><i class="fas fa-trash-alt"></i> Scrap Yard</span>
                                        <?php elseif ($tyer->status == 4): ?>
                                            <span class="badge bg-info text-dark"><i class="fas fa-wrench"></i> Under Repair</span>
                                        <?php elseif ($tyer->status == 7): ?>
                                            <span class="badge bg-dark"><i class="fas fa-money-bill"></i> Sold</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a class="btn btn-sm btn-outline-info"
                                                href="<?= base_url(); ?>/admin/tyre_details_vw/<?= $tyer->id ?>"
                                                title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($tyer->status != 3 && $tyer->status != 7): ?>
                                                <a class="btn btn-sm btn-outline-primary"
                                                    href="<?= base_url(); ?>/admin/tyer_exchange/<?= $tyer->id ?>" title="Update">
                                                    <i class="fas fa-cog"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No active tyres found in inventory.</td>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#tyre_inventory_table').DataTable({
            "paging": false,
            "searching": true,
            "ordering": true,
            "info": false,
            "scrollY": "60vh",
            "scrollCollapse": true,
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search inventory..."
            }
        });
    });

    function downloadExcel() {
        var locationId = document.getElementById('location_id').value;
        var statusElement = document.getElementById('status');
        var status = statusElement ? statusElement.value : '';

        $.ajax({
            url: '<?= base_url(); ?>/admin/expert_excel',
            type: 'POST',
            data: {
                location_id: locationId,
                status: status
            },
            xhrFields: { responseType: 'blob' },
            success: function (response, status, xhr) {
                var blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'tyre_report.xlsx';
                link.click();
            }
        });
    }
</script>

<?php include("footer.php"); ?>