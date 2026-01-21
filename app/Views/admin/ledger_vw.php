<?php include("header.php"); ?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    select.custom-select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        padding-right: 30px;
        cursor: pointer;
    }
    .input-group .fa-caret-down {
        font-size: 14px;
        color: #555;
    }
</style>
<div class="page-body-wrapper" style="background:#f9f9f9;">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            
            <div class="page-title">
                <h4 class="mb-3">Ledger Creation</h4>
            </div>

            <!-- Form Start -->
            <div class="card p-4 mb-4">
                <form method="post" action="<?= base_url('admin/insertLedger'); ?>">
                    <div class="row">
                        <!-- Financial Year -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Financial Year</label>
                                <select name="fy_id" class="form-control" required>
                                    <option value="">Select Financial Year</option>
                                    <?php foreach($financial_years as $fy): ?>
                                        <?php 
                                            $startYear = date("Y", strtotime($fy->from_date));
                                            $endYear   = date("y", strtotime($fy->to_date));
                                            $fy_name   = $startYear . "-" . $endYear;
                                        ?>
                                        <option value="<?= $fy->fy_id; ?>">
                                            <?= $fy_name . " (" . date("d-m-Y", strtotime($fy->from_date)) . " to " . date("d-m-Y", strtotime($fy->to_date)) . ")"; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Group -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Group</label>
                                <select name="group_id" class="form-control" required>
                                    <option value="">Select Group</option>
                                    <?php foreach($groups as $g): ?>
                                        <option value="<?= $g->group_id; ?>"><?= $g->group_name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Ledger Name -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ledger Name</label>
                                <input type="text" name="ledger_name" class="form-control" placeholder="Enter Ledger Name" required>
                            </div>
                        </div>

                        <!-- Balance + CR/DR -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Balance</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="balance" class="form-control" placeholder="Enter Balance" required>
                                    <div class="input-group" style="max-width:150px;">
                                        <select name="transaction_type" class="form-control custom-select">
                                            <option value="1">CR</option>
                                            <option value="2">DR</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Add Ledger</button>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Form End -->
            <!-- Ledger Table Start -->
            <div class="card p-3">
                <h5>List of Ledger</h5>
                <table class="table table-bordered table-striped mt-3">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Group</th>
                            <th>Ledger Name</th>
                            <th>Balance</th>
                            <th>Transaction Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($ledgers)): ?>
                            <?php $i=1; foreach($ledgers as $ledger): ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td><?= $ledger->group_name; ?></td>
                                    <td><?= $ledger->ledger_name; ?></td>
                                    <td><?= number_format($ledger->balance,2); ?></td>
                                    <td><?= ($ledger->transaction_type == 1) ? 'Credit' : 'Debit'; ?></td>
                                    <td>
                                        <a href="<?= base_url('admin/deleteLedger/'.$ledger->ledger_id); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">No ledger found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Ledger Table End -->
        </div>
    </div>
</div>
<?php include("footer.php"); ?>
