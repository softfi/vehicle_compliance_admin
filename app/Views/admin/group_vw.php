<?php include("header.php"); ?>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>

<!-- Page Body Start-->
<div class="page-body-wrapper" style="background:#f9f9f9;">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-12">
                        <h4 class="mb-3">Group Management</h4>
                    </div>
                </div>
            </div>

            <!-- Group Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="<?= base_url(); ?>admin/insertGroup" method="post" class="form-inline" id="groupForm">
                        <input type="hidden" name="group_id" id="group_id" value="">
                        <div class="form-group mr-3">
                            <label for="group_name" class="mr-2">Group Name</label>
                            <input type="text" name="group_name" id="group_name" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary" id="saveBtn">Add Group</button>
                    </form>
                </div>
            </div>

            <!-- Groups Table -->
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">Existing Groups</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Sl No</th>
                                    <th>Group Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($groups)): ?>
                                    <?php $i=1; foreach ($groups as $g): ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= $g->group_name ?></td>
                                            <td>
                                                <!-- Status Toggle Button -->
                                                <?php if ($g->status == 1): ?>
                                                    <a href="<?= base_url('admin/toggleGroupStatus/'.$g->group_id) ?>" class="badge badge-success">Active</a>
                                                <?php else: ?>
                                                    <a href="<?= base_url('admin/toggleGroupStatus/'.$g->group_id) ?>" class="badge badge-danger">Inactive</a>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <!-- Action Buttons -->
                                                <button type="button" class="btn btn-primary btn-sm edit-group" data-id="<?= $g->group_id ?>" data-name="<?= $g->group_name ?>" title="Edit">
                                                    <i class="fa fa-pencil"></i>
                                                </button>
                                                <a href="<?= base_url('admin/deleteGroup/'.$g->group_id) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure want to delete this group?')" title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center">No groups found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- Page Body End-->

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var editBtns = document.querySelectorAll(".edit-group");
        editBtns.forEach(function(btn) {
            btn.addEventListener("click", function() {
                var groupId = this.getAttribute("data-id");
                var groupName = this.getAttribute("data-name");
                
                document.getElementById("group_id").value = groupId;
                document.getElementById("group_name").value = groupName;
                document.getElementById("saveBtn").innerText = "Update Group";
            });
        });
    });
</script>

<?php include("footer.php"); ?>
