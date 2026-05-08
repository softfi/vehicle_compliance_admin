<?php include("header.php"); ?>

<style>
    :root {
        --primary-color: #4a90e2;
        --secondary-color: #2c3e50;
        --danger-color: #e74c3c;
        --light-bg: #f8f9fa;
        --card-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .page-body { padding-top: 20px; }
    .card-modern { background: white; border-radius: 8px; box-shadow: var(--card-shadow); margin-bottom: 20px; border: none; }
    .card-header-custom { background: linear-gradient(135deg, var(--danger-color), #c0392b); color: white; padding: 15px 20px; border-radius: 8px 8px 0 0; font-weight: 600; }
    .card-body-custom { padding: 25px; }
    .preview-img { width: 100%; height: 150px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; margin-top: 10px; display: none; }
    .table-container { max-height: 400px; overflow-y: auto; }
</style>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    
    <div class="page-body">
        <div class="container-fluid">        
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3><i class="fas fa-sync-alt"></i> Re-Issue Material</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <!-- Re-Issue Form -->
                <div class="col-md-5">
                    <div class="card-modern">
                        <div class="card-header-custom">Replacement Form</div>
                        <div class="card-body-custom">
                            <form action="<?= base_url('admin/save_re_issue'); ?>" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label>Select Driver</label>
                                    <select name="driver_id" id="driver_id" class="form-control select2-search" required onchange="fetchMaterials(this.value)">
                                        <option value="">Select Driver</option>
                                        <?php foreach($drivers as $d): ?>
                                            <option value="<?= $d->id; ?>"><?= $d->name; ?> (<?= $d->staff_code; ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Assigned Vehicle</label>
                                    <input type="text" id="assigned_vehicle" class="form-control" readonly placeholder="Auto-fetched">
                                </div>

                                <div class="form-group">
                                    <label>Select Item to Replace</label>
                                    <select name="item_id" id="item_id" class="form-control" required onchange="updateItemName(this)">
                                        <option value="">Select Item</option>
                                    </select>
                                    <input type="hidden" name="item_name" id="item_name">
                                </div>

                                <div class="row">
                                    <div class="col-md-6 text-center">
                                        <label><strong>Old Item Pic</strong></label>
                                        <input type="file" name="old_item_pic" class="form-control-file" required onchange="previewFile(this, 'old_preview')">
                                        <img id="old_preview" class="preview-img">
                                    </div>
                                    <div class="col-md-6 text-center">
                                        <label><strong>New Item Pic</strong></label>
                                        <input type="file" name="new_item_pic" class="form-control-file" required onchange="previewFile(this, 'new_preview')">
                                        <img id="new_preview" class="preview-img">
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label>Re-Issue Date</label>
                                    <input type="date" name="reissue_date" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label>Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="2" placeholder="Reason for replacement..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-danger btn-block mt-4">Record Re-Issue</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- History Section -->
                <div class="col-md-7">
                    <div class="card-modern">
                        <div class="card-header-custom" style="background: var(--secondary-color)">Re-Issue History</div>
                        <div class="card-body-custom">
                            <div class="table-container">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Driver</th>
                                            <th>Item</th>
                                            <th>Old Pic</th>
                                            <th>New Pic</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(!empty($reissues)): foreach($reissues as $row): ?>
                                            <tr>
                                                <td><?= $row->driver_name; ?></td>
                                                <td><?= $row->item_name; ?></td>
                                                <td>
                                                    <a href="<?= base_url('uploads/material/'.$row->old_item_pic); ?>" target="_blank">View Old</a>
                                                </td>
                                                <td>
                                                    <a href="<?= base_url('uploads/material/'.$row->new_item_pic); ?>" target="_blank">View New</a>
                                                </td>
                                                <td><?= date('d-M-Y', strtotime($row->reissue_date)); ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" onclick='editReissue(<?= json_encode($row); ?>)'>
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; else: ?>
                                            <tr><td colspan="6" class="text-center">No records found</td></tr>
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
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white">Edit Re-Issue Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= base_url('admin/update_re_issue'); ?>" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="form-group">
                            <label>Driver: <span id="edit_driver_display" class="font-weight-bold"></span></label>
                        </div>
                        <div class="form-group">
                            <label>Item: <span id="edit_item_display" class="font-weight-bold"></span></label>
                        </div>

                        <div class="form-group">
                            <label>Re-Issue Date</label>
                            <input type="date" name="reissue_date" id="edit_date" class="form-control" required>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <label>New Old Pic (Optional)</label>
                                <input type="file" name="old_item_pic" class="form-control-file">
                            </div>
                            <div class="col-6">
                                <label>New Pic (Optional)</label>
                                <input type="file" name="new_item_pic" class="form-control-file">
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label>Remarks</label>
                            <textarea name="remarks" id="edit_remarks" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include("footer.php"); ?>
</div>

<script>
function fetchMaterials(driverId) {
    if (!driverId) {
        $('#assigned_vehicle').val('');
        return;
    }

    // Fetch assigned vehicle
    $.ajax({
        url: '<?= base_url('Admin/get_driver_vehicle'); ?>',
        type: 'POST',
        data: { driver_id: driverId },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                $('#assigned_vehicle').val(res.vehicle_no);
            } else {
                $('#assigned_vehicle').val('No vehicle assigned');
            }
        }
    });

    $.ajax({
        url: '<?= base_url('admin/get_driver_active_materials'); ?>',
        method: 'POST',
        data: { driver_id: driverId },
        success: function(data) {
            let options = '<option value="">Select Item</option>';
            data.forEach(item => {
                options += `<option value="${item.id}">${item.item_name}</option>`;
            });
            $('#item_id').html(options);
        }
    });
}

function updateItemName(select) {
    let name = select.options[select.selectedIndex].text;
    $('#item_name').val(name);
}

function previewFile(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    const reader = new FileReader();

    reader.addEventListener("load", function () {
        preview.src = reader.result;
        preview.style.display = 'block';
    }, false);

    if (file) {
        reader.readAsDataURL(file);
    }
}

function editReissue(data) {
    $('#edit_id').val(data.id);
    $('#edit_driver_display').text(data.driver_name);
    $('#edit_item_display').text(data.item_name);
    $('#edit_date').val(data.reissue_date);
    $('#edit_remarks').val(data.remarks);
    $('#editModal').modal('show');
}
</script>
