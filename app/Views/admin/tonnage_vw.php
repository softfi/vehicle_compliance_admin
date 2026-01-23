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

    .range-row {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 5px;
        background: #f9f9f9;
    }

    .range-row-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .range-row-number {
        font-weight: bold;
        color: #333;
    }

    .remove-range-btn {
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
    }

    .remove-range-btn:hover {
        background: #c82333;
    }

    #rangesContainer {
        margin-top: 15px;
    }

</style>

<div class="page-body-wrapper" style="background:#ececec;">
<?php include("mainsidebar.php"); ?>

<div class="page-body">
<div class="container-fluid">

<h3 class="mb-3">Tonnage Formula</h3>

<!-- Success/Error Messages -->
<?php if(session()->getFlashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('success') ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('error') ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- FORM -->
<div class="uk-card uk-card-body uk-card-default uk-card-small mb-3" id="tonnage_form_card">
    <form id="tonnageForm"
          action="<?= base_url('Admin/insert_set_with_ranges') ?>"
          method="post">

        <input type="hidden" name="id" id="tonnage_id">
        <input type="hidden" name="set_id" id="set_id">

        <!-- Set Name Input -->
        <div class="uk-grid-small uk-flex-middle" uk-grid>
            <div class="uk-width-1-3@m">
                <label>Set Name <span class="text-danger">*</span></label>
                <input type="text"
                       name="set_name"
                       id="set_name"
                       class="uk-input"
                       placeholder="e.g. Set A, Set B"
                       required>
            </div>
        </div>

        <!-- Add Range Button - Above Ranges Container -->
        <div style="margin-top: 15px; text-align: right;">
            <button type="button"
                    id="addRangeBtn"
                    class="btn btn-success"
                    onclick="addRangeRow()">
                + Add Range
            </button>
        </div>

        <!-- Ranges Container -->
        <div id="rangesContainer" style="margin-top: 15px;">
            <!-- Range rows will be added here dynamically -->
        </div>

        <!-- Submit Buttons -->
        <div class="uk-grid-small uk-flex-middle" uk-grid style="margin-top: 20px;">
            <div class="uk-width-1-2@m">
                <button type="submit"
                        id="submitBtn"
                        class="btn btn-primary">
                    Submit Set with Ranges
                </button>
                <button type="button"
                        onclick="resetForm()"
                        class="btn btn-secondary">
                    Reset
                </button>
            </div>
        </div>
    </form>
</div>

<!-- SETS LIST -->
<div class="uk-card uk-card-body uk-card-default uk-card-small">

    <h4 class="mb-3">Sets List</h4>

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
                    <th>Set Name</th>
                    <th>Range Count</th>
                    <th>View</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>

            <tbody>
            <?php 
            if(isset($sets_with_count) && count($sets_with_count) > 0) { 
                $i = 1; 
                foreach ($sets_with_count as $set) { ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= $set->set_name ?? '-'; ?></td>
                    <td><?= $set->range_count ?? 0; ?> Range(s)</td>
                    <td>
                        <button type="button"
                                class="btn btn-info btn-sm"
                                onclick="viewSetRanges('<?= $set->id ?>')">
                            View
                        </button>
                    </td>
                    <td>
                        <button type="button"
                                class="btn btn-warning btn-sm"
                                onclick="editSet('<?= $set->id ?>')">
                            Edit
                        </button>
                    </td>
                    <td>
                        <button type="button"
                                class="btn btn-danger btn-sm"
                                onclick="deleteSet('<?= $set->id ?>')">
                            Delete
                        </button>
                    </td>
                </tr>
            <?php } 
            } else { ?>
                <tr>
                    <td colspan="6" class="text-center">No sets found. Create a new set with ranges.</td>
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
<form id="frm_deleteSet"
      action="<?= base_url('admin/delete_set') ?>"
      method="post">
    <input type="hidden" name="id" id="delete_set_id">
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    let rangeCounter = 0;

    // Initialize - Add first range row
    $(document).ready(function() {
        addRangeRow();
    });

    // Add Range Row
    function addRangeRow() {
        rangeCounter++;
        var rangeHtml = `
            <div class="range-row" id="range_row_${rangeCounter}">
                <div class="range-row-header">
                    <span class="range-row-number">Range ${rangeCounter}</span>
                    <button type="button" class="remove-range-btn" onclick="removeRangeRow(${rangeCounter})">Remove</button>
                </div>
                <div class="uk-grid-small uk-flex-middle" uk-grid>
                    <div class="uk-width-1-4@m">
                        <label>Min (Range) <span class="text-danger">*</span></label>
                        <input type="number"
                               step="0.01"
                               name="ranges[${rangeCounter}][min]"
                               class="uk-input range-min"
                               placeholder="e.g. 0"
                               required>
                    </div>
                    <div class="uk-width-1-4@m">
                        <label>Max (Range) - Inclusive</label>
                        <input type="number"
                               step="0.01"
                               name="ranges[${rangeCounter}][max]"
                               class="uk-input range-max"
                               placeholder="Leave empty for unlimited">
                        
                    </div>
                    <div class="uk-width-1-4@m">
                        <label>Penalty (%)</label>
                        <input type="number"
                               step="0.01"
                               name="ranges[${rangeCounter}][penalty_value]"
                               class="uk-input range-penalty"
                               placeholder="0"
                               value="0">
                    </div>
                </div>
            </div>
        `;
        $("#rangesContainer").append(rangeHtml);
    }

    // Remove Range Row
    function removeRangeRow(counter) {
        if($("#rangesContainer .range-row").length > 1) {
            $("#range_row_" + counter).remove();
            updateRangeNumbers();
        } else {
            alert("At least one range is required!");
        }
    }

    // Update Range Numbers
    function updateRangeNumbers() {
        $("#rangesContainer .range-row").each(function(index) {
            $(this).find(".range-row-number").text("Range " + (index + 1));
        });
    }

    // Search
    $("#searchInput").on("keyup", function () {
        let val = $(this).val().toLowerCase();
        $("#myTable tbody tr").filter(function () {
            $(this).toggle(
                $(this).text().toLowerCase().indexOf(val) > -1
            );
        });
    });

    // Form Validation and Submit
    $("#tonnageForm").on("submit", function(e) {
        // Validate set name
        if(!$("#set_name").val().trim()) {
            e.preventDefault();
            alert("Please enter Set Name!");
            return false;
        }

        // Validate at least one range with min value
        var hasValidRange = false;
        var validRangesCount = 0;
        $("#rangesContainer .range-min").each(function() {
            if($(this).val() !== '' && $(this).val() !== null) {
                hasValidRange = true;
                validRangesCount++;
            }
        });

        if(!hasValidRange) {
            e.preventDefault();
            alert("Please add at least one range with Min value!");
            return false;
        }

        // Re-index ranges array to avoid gaps (important for PHP array processing)
        // Also remove empty range rows to avoid confusion
        var newIndex = 1;
        $("#rangesContainer .range-row").each(function() {
            var minInput = $(this).find(".range-min");
            var maxInput = $(this).find(".range-max");
            var penaltyInput = $(this).find(".range-penalty");
            
            // Only re-index if range has min value
            if(minInput.val() !== '' && minInput.val() !== null) {
                minInput.attr('name', 'ranges[' + newIndex + '][min]');
                maxInput.attr('name', 'ranges[' + newIndex + '][max]');
                penaltyInput.attr('name', 'ranges[' + newIndex + '][penalty_value]');
                newIndex++;
            } else {
                // Remove empty range rows before submit
                $(this).remove();
            }
        });
    });

    // Reset Form
    function resetForm() {
        $("#tonnageForm")[0].reset();
        $("#tonnage_id").val('');
        $("#set_id").val('');
        $("#rangesContainer").empty();
        rangeCounter = 0;
        addRangeRow();
        $("#tonnageForm").attr("action", "<?= base_url('Admin/insert_set_with_ranges') ?>");
        $("#submitBtn").text("Submit Set with Ranges");
        // Clear URL parameters if any
        if(window.location.href.indexOf('?') > -1) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }

    // View Set Ranges
    function viewSetRanges(set_id) {
        window.location.href = "<?= base_url('admin/view_tonnage_set') ?>?set_id=" + set_id;
    }

    // Edit Set with all ranges
    function editSet(set_id) {
        $.post(
            "<?= base_url('Admin/edit_set_with_ranges') ?>",
            { set_id: set_id },
            function (res) {
                if(res.status === 'success') {
                    // Set the set name and id
                    $("#set_name").val(res.set.set_name);
                    $("#set_id").val(res.set.id);
                    
                    // Clear ranges
                    $("#rangesContainer").empty();
                    rangeCounter = 0;
                    
                    // Add all ranges
                    if(res.ranges && res.ranges.length > 0) {
                        res.ranges.forEach(function(range) {
                            rangeCounter++;
                            var maxVal = (range.max === null || range.max === '' || range.max === 0) ? '' : range.max;
                            var rangeHtml = `
                                <div class="range-row" id="range_row_${rangeCounter}">
                                    <div class="range-row-header">
                                        <span class="range-row-number">Range ${rangeCounter}</span>
                                        <button type="button" class="remove-range-btn" onclick="removeRangeRow(${rangeCounter})">Remove</button>
                                    </div>
                                    <div class="uk-grid-small uk-flex-middle" uk-grid>
                                        <div class="uk-width-1-4@m">
                                            <label>Min (Range) <span class="text-danger">*</span></label>
                                            <input type="number"
                                                   step="0.01"
                                                   name="ranges[${rangeCounter}][min]"
                                                   class="uk-input range-min"
                                                   placeholder="e.g. 0"
                                                   value="${range.min || ''}"
                                                   required>
                                        </div>
                                        <div class="uk-width-1-4@m">
                                            <label>Max (Range) - Inclusive</label>
                                            <input type="number"
                                                   step="0.01"
                                                   name="ranges[${rangeCounter}][max]"
                                                   class="uk-input range-max"
                                                   placeholder="Leave empty for unlimited"
                                                   value="${maxVal}">
                                        </div>
                                        <div class="uk-width-1-4@m">
                                            <label>Penalty (%)</label>
                                            <input type="number"
                                                   step="0.01"
                                                   name="ranges[${rangeCounter}][penalty_value]"
                                                   class="uk-input range-penalty"
                                                   placeholder="0"
                                                   value="${range.penalty_value || '0'}">
                                        </div>
                                    </div>
                                </div>
                            `;
                            $("#rangesContainer").append(rangeHtml);
                        });
                    } else {
                        // If no ranges, add one empty range
                        addRangeRow();
                    }
                    
                    // Update form action and button text
                    $("#tonnageForm").attr("action", "<?= base_url('Admin/update_set_with_ranges') ?>");
                    $("#submitBtn").text("Update Set with Ranges");
                    
                    // Scroll to form
                    window.scrollTo(0, 0);
                } else {
                    alert("Error loading set data: " + (res.message || 'Unknown error'));
                }
            },
            "json"
        ).fail(function() {
            alert("Error loading set data. Please try again.");
        });
    }

    // Delete Set
    function deleteSet(set_id) {
        $("#delete_set_id").val(set_id);
        UIkit.modal('#delete-confirmation-modal').show();
    }
    
    // Confirm Delete Action
    function confirmDelete() {
        $("#frm_deleteSet").submit();
    }
</script>

<!-- Delete Confirmation Modal -->
<div id="delete-confirmation-modal" uk-modal>
    <div class="uk-modal-dialog uk-modal-body">
        <h2 class="uk-modal-title">Confirmation</h2>
        <p>Are you sure you want to delete this Set? All associated ranges will also be deleted.</p>
        <p class="uk-text-right">
            <button class="uk-button uk-button-default uk-modal-close" type="button">Cancel</button>
            <button class="uk-button uk-button-danger" type="button" onclick="confirmDelete()">Delete</button>
        </p>
    </div>
</div>

<?php include("footer.php"); ?>
