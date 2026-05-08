<?php include("header.php"); ?>
<!-- Page Body Start-->
<div class="page-body-wrapper" style="background:#ececec;">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Vehicle Types</h3>
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid starts-->
        <div class="container-fluid default-dashboard">
            <?php if (session()->getFlashdata('msg')) : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= session()->getFlashdata('msg') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="uk-grid-small" uk-grid>
                <div class="uk-width-1-3@m">
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                        <h4 class="uk-card-title">Add New Vehicle Type</h4>
                        <form action="<?php echo base_url(); ?>/Admin/insert_vehicle_type" method="post">
                            <div class="uk-margin-bottom">
                                <label>Type Name</label>
                                <input type="text" name="type_name" placeholder="e.g. Truck, Loader, Trailer" class="uk-input" required />
                            </div>

                            <div class="uk-margin-bottom">
                                <label>Description</label>
                                <textarea name="description" placeholder="Optional description" class="uk-textarea" rows="3"></textarea>
                            </div>

                            <div class="uk-margin-bottom">
                                <label>Status</label>
                                <select name="status" class="uk-select">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="uk-margin-bottom">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="uk-width-2-3@m">
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                        <table class="display" id="row_create" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Sl no</th>
                                    <th>Type Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                foreach ($types as $type) { ?>
                                    <tr>
                                        <td><?= $i++; ?></td>
                                        <td><?= htmlspecialchars($type->type_name) ?></td>
                                        <td><?= htmlspecialchars($type->description) ?></td>
                                        <td>
                                            <span class="badge <?= $type->status == 'Active' ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $type->status ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-warning edit-type-btn" data-id="<?= $type->id ?>" uk-toggle="target: #edit-modal">Edit</button>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('Admin/delete_vehicle_type/' . $type->id) ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this type?');">Delete</a>
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
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="uk-flex-top" uk-modal>
    <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <h2 class="uk-modal-title">Edit Vehicle Type</h2>
        <form action="<?php echo base_url(); ?>/Admin/update_vehicle_type" method="post">
            <input type="hidden" name="id" id="edit_id">
            <div class="uk-margin-bottom">
                <label>Type Name</label>
                <input type="text" name="type_name" id="edit_type_name" class="uk-input" required />
            </div>
            <div class="uk-margin-bottom">
                <label>Description</label>
                <textarea name="description" id="edit_description" class="uk-textarea" rows="3"></textarea>
            </div>
            <div class="uk-margin-bottom">
                <label>Status</label>
                <select name="status" id="edit_status" class="uk-select">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="uk-margin-bottom text-end">
                <button type="submit" class="btn btn-primary">Update Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.edit-type-btn').on('click', function() {
        var id = $(this).data('id');
        $.ajax({
            url: '<?= base_url('Admin/edit_vehicle_type') ?>',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success') {
                    $('#edit_id').val(response.data.id);
                    $('#edit_type_name').val(response.data.type_name);
                    $('#edit_description').val(response.data.description);
                    $('#edit_status').val(response.data.status);
                }
            }
        });
    });
});
</script>

<?php include("footer.php"); ?>
