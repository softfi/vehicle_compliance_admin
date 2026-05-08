<?php include("header.php"); ?>
<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>

    <div class="page-body">
        <div class="container-fluid py-4">
            <!-- Page Header -->
            <div class="page-title mb-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="mb-0">
                            <i class="fas fa-exchange-alt text-primary"></i> Tyre Exchange History
                        </h3>
                        <p class="text-muted small mb-0">Track all tyre swaps and replacements across the fleet</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-info btn-sm" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dashboard Stats Summary -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card border-0 shadow-sm bg-gradient-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 opacity-75">Total Exchanges</h6>
                                    <h3 class="mb-0 fw-bold"><?= count($history ?? []) ?></h3>
                                </div>
                                <div class="stat-icon bg-white-transparent">
                                    <i class="fas fa-sync-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Panel -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-filter text-primary me-2"></i> Report Filters</h6>
                </div>
                <div class="card-body">
                    <form id="filterForm" method="GET">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Search Keywords</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Search Serial, Remarks..." 
                                           value="<?= esc($_GET['search'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Vehicle</label>
                                <select class="form-select form-select-sm select2-vehicle" name="vehicle_id">
                                    <option value="">All Vehicles</option>
                                    <?php foreach ($vehicles as $v): ?>
                                        <option value="<?= $v->id ?>" <?= ($_GET['vehicle_id'] ?? '') == $v->id ? 'selected' : '' ?>>
                                            <?= esc($v->vehicle_no) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    <i class="fas fa-sync me-1"></i> Apply
                                </button>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <a href="<?= base_url('admin/tyre_exchange_report') ?>" class="btn btn-sm btn-outline-secondary w-100">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main Data Table -->
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="exchangeTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Exchange Date</th>
                                    <th>Vehicle</th>
                                    <th>Position</th>
                                    <th>Removed Tyre (Old)</th>
                                    <th>Installed Tyre (New)</th>
                                    <th>Remarks</th>
                                    <th class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($history)): ?>
                                    <?php $i = 1; foreach ($history as $row): ?>
                                        <tr>
                                            <td class="ps-4 text-muted small"><?= $i++ ?></td>
                                            <td>
                                                <div class="fw-bold"><?= date('d M, Y', strtotime($row->exchange_date)) ?></div>
                                                <small class="text-muted"><?= date('h:i A', strtotime($row->created_at)) ?></small>
                                            </td>
                                            <td>
                                                <?php if (!empty($row->vehicle_no)): ?>
                                                    <span class="badge bg-dark px-3 py-2">
                                                        <i class="fas fa-truck me-1"></i> <?= esc($row->vehicle_no) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary px-3 py-2">
                                                        <i class="fas fa-warehouse me-1"></i> Stock
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?= esc($row->tyre_position) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        <span class="badge bg-danger-soft text-danger rounded-circle p-2">
                                                            <i class="fas fa-arrow-down"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-danger"><?= esc($row->old_serial ?: 'N/A') ?></div>
                                                        <small class="text-muted"><?= esc($row->old_brand ?: '—') ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        <span class="badge bg-success-soft text-success rounded-circle p-2">
                                                            <i class="fas fa-arrow-up"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-success"><?= esc($row->new_serial ?: 'N/A') ?></div>
                                                        <small class="text-muted"><?= esc($row->new_brand ?: '—') ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted d-block text-wrap" style="max-width: 200px;">
                                                    <?= esc($row->remarks ?: 'No remarks') ?>
                                                </small>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <?php if($row->to_tyre_id): ?>
                                                    <a href="<?= base_url('admin/tyre_details_vw/'.$row->to_tyre_id) ?>" 
                                                       class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                       title="View Lifecycle">
                                                        <i class="fas fa-history"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="py-4">
                                                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No exchange history records found</h5>
                                                <p class="text-muted small">Perform a tyre exchange on a vehicle to see records here</p>
                                            </div>
                                        </td>
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

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
}
.stat-card {
    border-radius: 12px;
    transition: transform 0.2s;
}
.stat-card:hover {
    transform: translateY(-3px);
}
.bg-white-transparent {
    background: rgba(255,255,255,0.2);
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}
.bg-danger-soft { background-color: rgba(231, 74, 59, 0.1); }
.bg-success-soft { background-color: rgba(28, 200, 138, 0.1); }
.badge-soft { font-weight: 600; padding: 0.5em 0.8em; }

@media print {
    #filterForm, .sidebar, .btn-outline-primary { display: none !important; }
    .card { border: 1px solid #eee !important; box-shadow: none !important; }
}
</style>

<?php include("footer.php"); ?>

<script>
$(document).ready(function() {
    $('.select2-vehicle').select2({
        placeholder: "Select Vehicle",
        allowClear: true,
        width: '100%'
    });
});
</script>
