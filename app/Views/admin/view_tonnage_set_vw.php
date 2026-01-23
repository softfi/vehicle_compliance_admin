<?php include("header.php"); ?>

<style>
    #myTable thead th {
        position: sticky;
        top: 0;
        background: #fff;
    }
</style>

<div class="page-body-wrapper" style="background:#ececec;">
<?php include("mainsidebar.php"); ?>

<div class="page-body">
<div class="container-fluid">

<h3 class="mb-3">View Tonnage Set: <?= $set->set_name ?? 'N/A' ?></h3>

<!-- Back Button -->
<div class="mb-3">
    <a href="<?= base_url('admin/tonnage') ?>" class="btn btn-secondary">
        ← Back to Sets List
    </a>
</div>

<!-- Set Information -->
<div class="uk-card uk-card-body uk-card-default uk-card-small mb-3">
    <h4 class="mb-3">Set Information</h4>
    <table class="uk-table uk-table-small">
        <tr>
            <td><strong>Set Name:</strong></td>
            <td><?= $set->set_name ?? '-'; ?></td>
        </tr>
        <tr>
            <td><strong>Total Ranges:</strong></td>
            <td><?= count($tonnage ?? []); ?> Range(s)</td>
        </tr>
    </table>
</div>

<!-- Ranges List -->
<div class="uk-card uk-card-body uk-card-default uk-card-small">
    <h4 class="mb-3">Ranges List</h4>
    <div style="max-height:500px; overflow:auto;">
        <table id="myTable" class="uk-table uk-table-small uk-table-divider">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Range</th>
                    <th>Penalty (%)</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            if(isset($tonnage) && count($tonnage) > 0) { 
                $i = 1;
                foreach ($tonnage as $row) { ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td>
                        <?php 
                        if($row->max === null || $row->max === '' || $row->max === 0) {
                            echo ($row->min ?? 0) . '+';
                        } else {
                            echo ($row->min ?? 0) . ' to ' . $row->max;
                        }
                        ?>
                    </td>
                    <td><?= $row->penalty_value ?? '0'; ?>%</td>
                </tr>
            <?php } 
            } else { ?>
                <tr>
                    <td colspan="3" class="text-center">No ranges found for this set.</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</div>
</div>
</div>

<?php include("footer.php"); ?>
