<?php include("header.php"); ?>

<style>
    :root {
        --primary-color: #4a90e2;
        --secondary-color: #2c3e50;
        --success-color: #27ae60;
        --light-bg: #f8f9fa;
        --card-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .page-body { padding-top: 20px; }
    .card-modern { background: white; border-radius: 8px; box-shadow: var(--card-shadow); margin-bottom: 20px; border: none; }
    .card-header-custom { background: linear-gradient(135deg, var(--primary-color), #357abd); color: white; padding: 15px 20px; border-radius: 8px 8px 0 0; font-weight: 600; }
    .card-body-custom { padding: 25px; }
    .btn-custom { padding: 10px 24px; border-radius: 6px; font-weight: 600; transition: all 0.3s ease; border: none; }
    .item-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px; }
    .item-checkbox { display: flex; align-items: center; background: #fff; padding: 8px; border: 1px solid #ddd; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-size: 13px; margin-bottom: 0; }
    .item-checkbox:hover { background: #f0f7ff; border-color: var(--primary-color); }
    .item-checkbox input { margin-right: 8px; width: 16px; height: 16px; }
    .table-container { max-height: 500px; overflow-y: auto; }
    .filter-box { background: #fff; padding: 15px; border-radius: 8px; box-shadow: var(--card-shadow); margin-bottom: 15px; }
</style>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    
    <div class="page-body">
        <div class="container-fluid">        
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3><i class="fas fa-boxes"></i> Material Issue</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <!-- Form Section -->
                <div class="col-md-4">
                    <div class="card-modern">
                        <div class="card-header-custom">Issue New Material</div>
                        <div class="card-body-custom">
                            <form action="<?= base_url('admin/save_material_issue'); ?>" method="post">
                                <div class="form-group">
                                    <label>Select Driver</label>
                                    <select name="driver_id" id="driver_data" class="form-control select2-search" required>
                                        <option value="">Select Driver</option>
                                        <?php foreach($drivers as $d): ?>
                                            <option value="<?= $d->id; ?>"><?= $d->name; ?> (<?= $d->staff_code; ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Issued Date</label>
                                    <input type="date" name="issued_date" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                                </div>
                                
                                <label class="mt-3"><strong>Select Particulars to Issue</strong></label>
                                <div class="item-grid">
                                    <?php 
                                    $items = ["Stepny", "Jack", "Jack Rod", "Wheel Pana", "Pechkush", "Hammer", "Fire Extingusher", "Tirpal", "Safety Shoes", "Safety Jacket (2)", "Safety Helmet"];
                                    foreach($items as $item): ?>
                                        <label class="item-checkbox">
                                            <input type="checkbox" name="items[]" value="<?= $item; ?>">
                                            <?= $item; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block mt-4">Issue Material</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- History Section -->
                <div class="col-md-8">
                    <div class="filter-box">
                        <form action="<?= base_url('admin/material_issue'); ?>" method="get" class="d-flex align-items-end" style="gap: 15px;">
                            <div style="flex-grow: 1;">
                                <label class="mb-2">Filter by Driver</label>
                                <select name="filter_driver" class="form-control select2-search">
                                    <option value="">All Drivers</option>
                                    <?php foreach($drivers as $d): ?>
                                        <option value="<?= $d->id; ?>" <?= (isset($_GET['filter_driver']) && $_GET['filter_driver'] == $d->id) ? 'selected' : ''; ?>><?= $d->name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="width: 150px;">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>
                    </div>

                    <div class="card-modern">
                        <div class="card-header-custom">Material Issue History</div>
                        <div class="card-body-custom">
                            <div class="table-container">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Driver</th>
                                            <th style="width: 40%;">Items Issued</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(!empty($issues)): foreach($issues as $row): ?>
                                            <tr>
                                                <td><?= $row->driver_name; ?></td>
                                                <td><small><?= $row->item_name; ?></small></td>
                                                <td><?= date('d-M-Y', strtotime($row->issued_date)); ?></td>
                                                <td>
                                                    <span class="badge badge-<?= $row->status == 'Active' ? 'success' : 'warning'; ?>">
                                                        <?= $row->status; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info" onclick="editIssue(<?= htmlspecialchars(json_encode($row)); ?>)">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; else: ?>
                                            <tr><td colspan="5" class="text-center">No records found</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="<?= base_url('admin/update_material_issue'); ?>" method="post">
                    <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Edit Issued Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
                </div>
                    <div class="modal-body">
                        <input type="hidden" name="issue_id" id="edit_id">
                        <div class="form-group">
                            <label>Driver</label>
                            <input type="text" id="edit_driver_name" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>Issued Date</label>
                            <input type="date" name="issued_date" id="edit_date" class="form-control" required>
                        </div>
                        <label><strong>Items</strong></label>
                        <div class="item-grid">
                            <?php foreach($items as $item): ?>
                                <label class="item-checkbox">
                                    <input type="checkbox" name="items[]" value="<?= $item; ?>" class="edit-item-check">
                                    <?= $item; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include("footer.php"); ?>
</div>

<script>
function editIssue(data) {
    $('#edit_id').val(data.id);
    $('#edit_driver_name').val(data.driver_name);
    $('#edit_date').val(data.issued_date);
    
    // Reset and check items
    $('.edit-item-check').prop('checked', false);
    let currentItems = data.item_name.split(', ');
    currentItems.forEach(item => {
        $(`.edit-item-check[value="${item}"]`).prop('checked', true);
    });
    
    $('#editModal').modal('show');
}
</script>
