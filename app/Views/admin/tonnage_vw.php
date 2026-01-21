<?php include("header.php"); ?>

<style>
    #myTable thead th {
        position: sticky;
        top: 0;
        background: #fff;
    }

    #searchInput {
        width: 500px;
    }
</style>

<div class="page-body-wrapper" style="background:#ececec;">
<?php include("mainsidebar.php"); ?>

<div class="page-body">
<div class="container-fluid">

<h3 class="mb-3">Tonnage</h3>

<!-- FORM -->
<div class="uk-card uk-card-body uk-card-default uk-card-small mb-3">
    <form id="tonnageForm"
          action="<?= base_url('Admin/insert_tonnage') ?>"
          method="post">

        <input type="hidden" name="id" id="tonnage_id">

        <div class="uk-grid-small uk-flex-middle" uk-grid>

            <div class="uk-width-1-3@m">
                <label>Weight</label>
                <input type="text"
                       name="weight"
                       id="weight"
                       class="uk-input"
                       placeholder="e.g. <35MT"
                       required>
            </div>

            <div class="uk-width-1-3@m">
                <label>Price</label>
                <input type="number"
                       step="any"
                       name="price"
                       id="price"
                       class="uk-input"
                       required>
            </div>

            <div class="uk-width-1-3@m" style="padding-top:25px;">
                <button type="submit"
                        id="submitBtn"
                        class="btn btn-primary">
                    Submit
                </button>
            </div>

        </div>
    </form>
</div>

<!-- LIST -->
<div class="uk-card uk-card-body uk-card-default uk-card-small">

    <input type="text"
           id="searchInput"
           class="form-control mb-2"
           placeholder="Search">

    <div style="max-height:400px; overflow:auto;">
        <table id="myTable"
               class="uk-table uk-table-small uk-table-divider">

            <thead>
                <tr>
                    <th>#</th>
                    <th>Weight</th>
                    <th>Price</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>

            <tbody>
            <?php $i = 1; foreach ($tonnage as $row) { ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= $row->weight; ?></td>
                    <td><?= $row->price; ?></td>
                    <td>
                        <button type="button"
                                class="btn btn-warning btn-sm"
                                onclick="edit_tonnage('<?= $row->id ?>')">
                            Edit
                        </button>
                    </td>
                    <td>
                        <button type="button"
                                class="btn btn-danger btn-sm"
                                onclick="deleteRecord('<?= $row->id ?>')">
                            Delete
                        </button>
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

<!-- DELETE FORM -->
<form id="frm_deleteTonnage"
      action="<?= base_url('admin/delete_tonnage') ?>"
      method="post">
    <input type="hidden" name="id" id="delete_id">
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // Search
    $("#searchInput").on("keyup", function () {
        let val = $(this).val().toLowerCase();
        $("#myTable tbody tr").filter(function () {
            $(this).toggle(
                $(this).text().toLowerCase().indexOf(val) > -1
            );
        });
    });

    // Edit
    function edit_tonnage(id) {
        $.post(
            "<?= base_url('Admin/edit_tonnage') ?>",
            { id: id },
            function (res) {
                $("#tonnage_id").val(res.id);
                $("#weight").val(res.weight);
                $("#price").val(res.price);

                $("#tonnageForm").attr(
                    "action",
                    "<?= base_url('Admin/update_tonnage') ?>"
                );

                $("#submitBtn").text("Update");
                window.scrollTo(0, 0);
            },
            "json"
        );
    }

    // Delete
    function deleteRecord(id) {
        $("#delete_id").val(id);
        UIkit.modal('#delete-confirmation-modal').show();
    }
    
    // Confirm Delete Action
    function confirmDelete() {
        $("#frm_deleteTonnage").submit();
    }
</script>

<!-- Delete Confirmation Modal -->
<div id="delete-confirmation-modal" uk-modal>
    <div class="uk-modal-dialog uk-modal-body">
        <h2 class="uk-modal-title">Confirmation</h2>
        <p>Are you sure you want to delete this record?</p>
        <p class="uk-text-right">
            <button class="uk-button uk-button-default uk-modal-close" type="button">Cancel</button>
            <button class="uk-button uk-button-danger" type="button" onclick="confirmDelete()">Delete</button>
        </p>
    </div>
</div>

<?php include("footer.php"); ?>
