<?php include("header.php"); ?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<div class="page-body-wrapper" style="background:#f9f9f9;">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">

            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6">
                        <h4>Financial Year Management</h4>
                    </div>
                </div>
            </div>

            <!-- Add Financial Year Form -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <form action="<?= base_url('admin/insertFinancialYear'); ?>" method="post" class="form-inline">

                        <div class="form-group mr-3">
                            <label for="from_date" class="mr-2 font-weight-bold">From Date</label>
                            <input type="date" class="form-control" name="from_date" id="from_date" required>
                        </div>

                        <div class="form-group mr-3">
                            <label for="to_date" class="mr-2 font-weight-bold">To Date</label>
                            <input type="date" class="form-control" name="to_date" id="to_date" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Add</button>
                    </form>
                </div>
            </div>

            <!-- Financial Year Table -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Financial Year List</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Sl No</th>
                                    <th>Financial Year</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($years)): ?>
                                    <?php $i=1; foreach ($years as $y): ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td>
                                                <?php 
                                                    // Extract year from from_date and to_date
                                                    $fromYear = date("Y", strtotime($y->from_date));
                                                    $toYear   = date("y", strtotime($y->to_date)); // 2 digit year
                                                    echo $fromYear . "-" . $toYear; 
                                                ?>
                                            </td>
                                            
                                            <td>
                                                <a href="<?= base_url('admin/toggleFinancialYearStatus/'.$y->fy_id); ?>"
                                                   class="badge <?= $y->status ? 'badge-success' : 'badge-danger'; ?>">
                                                   <?= $y->status ? 'Active' : 'Inactive'; ?>
                                                </a>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('admin/editFinancialYear/'.$y->fy_id); ?>" 
                                                   class="btn btn-sm btn-info">Edit</a>
                                                <a href="<?= base_url('admin/deleteFinancialYear/'.$y->fy_id); ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Are you sure want to delete this?');">
                                                   Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No Financial Year Found</td>
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

<?php include("footer.php"); ?>
