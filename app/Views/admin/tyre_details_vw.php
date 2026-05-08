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
                            <i class="fas fa-history text-primary"></i> Tyre History
                        </h3>
                        <p class="text-muted small mb-0">Complete tracking of all tyre events and movements</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <!-- <button class="btn btn-success btn-sm" onclick="exportToExcel()">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button> -->
                        <button class="btn btn-info btn-sm" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button class="btn btn-primary btn-sm" id="toggleFilters">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tyre Info Card -->
            <?php if (!empty($tyre)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">🔍 Tyre: <?= esc($tyre->tyer_sl_no) ?></h5>
                        <?php if (!empty($tyre->replaced_from_serial)): ?>
                            <small class="text-warning d-block">
                                <i class="fas fa-arrow-left"></i> Replaced From: 
                                <a href="<?= base_url('Admin/tyre_details_vw/'.$tyre->replaced_from_id) ?>" class="text-warning text-decoration-none fw-bold">
                                    <?= esc($tyre->replaced_from_serial) ?>
                                </a>
                            </small>
                        <?php endif; ?>
                        <?php if (!empty($tyre->replaced_to_serial)): ?>
                            <small class="text-info d-block">
                                <i class="fas fa-arrow-right"></i> Replaced To: 
                                <a href="<?= base_url('Admin/tyre_details_vw/'.$tyre->replaced_to_id) ?>" class="text-info text-decoration-none fw-bold">
                                    <?= esc($tyre->replaced_to_serial) ?>
                                </a>
                            </small>
                        <?php endif; ?>
                    </div>
                    <span class="badge bg-light text-dark"><?= esc($tyre->brand_name) ?></span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <small class="text-muted d-block">Brand</small>
                            <p class="fw-bold mb-0 text-dark"><?= esc($tyre->brand_name) ?></p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Type / Model</small>
                            <p class="mb-0"><?= esc($tyre->tyer_type) ?> | <?= esc($tyre->model) ?></p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Current Status</small>
                            <p class="mb-0">
                                <?php if ($tyre->status == 1): ?>
                                    <span class="badge bg-success">✅ In Stock</span>
                                <?php elseif ($tyre->status == 2): ?>
                                    <span class="badge bg-primary">🚚 Assigned</span>
                                <?php elseif ($tyre->status == 3): ?>
                                    <span class="badge bg-danger">🛠️ In Scrap Yard</span>
                                <?php elseif ($tyre->status == 4): ?>
                                    <span class="badge bg-warning text-dark">🔧 Under Repair</span>
                                <?php elseif ($tyre->status == 7): ?>
                                    <span class="badge bg-dark">💰 Sold</span>
                                <?php elseif ($tyre->status == 10): ?>
                                    <span class="badge bg-info text-dark">📦 Returned (Claimed)</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Location</small>
                            <p class="mb-0 fw-bold"><?= esc($tyre->location_name ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Purchased From</small>
                            <p class="mb-0"><?= esc($tyre->vendor_name ?? '—') ?></p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Purchase Date</small>
                            <p class="mb-0"><?= !empty($tyre->date) ? date('d-M-Y', strtotime($tyre->date)) : '—' ?></p>
                        </div>
                        <?php if(!empty($tyre->vehicle_no)): ?>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Assigned To</small>
                            <p class="mb-0 text-primary fw-bold"><i class="fas fa-truck"></i> <?= esc($tyre->vehicle_no) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
                            
            <!-- Advanced Filters Panel -->
            <div class="card shadow-sm mb-4" id="filterPanel" style="display: none;">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-sliders-h"></i> Advanced Filters</h6>
                </div>
                <div class="card-body">
                    <form id="filterForm" method="GET">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small">Search</label>
                                <input type="text" class="form-control form-control-sm" id="searchInput" 
                                       placeholder="Serial, Vehicle, Remarks..." 
                                       value="<?= esc($_GET['search'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Event Type</label>
                                <select class="form-select form-select-sm" name="event_type" id="eventTypeFilter">
                                    <option value="">All Events</option>
                                    <option value="1">Purchased</option>
                                    <option value="2">Transferred</option>
                                    <option value="3">Assigned</option>
                                    <option value="4">Exchanged</option>
                                    <option value="5">Sent for Repair</option>
                                    <option value="6">Back to Stock</option>
                                    <option value="7">Sold</option>
                                    <option value="8">Rotation</option>
                                    <option value="9">Moved to Scrap</option>
                                </select>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="resetFilters()">
                                    <i class="fas fa-redo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main Data Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-table"></i> History Records</h6>
                        <div>
                            <span class="badge bg-secondary" id="recordCount">
                                Showing <?= count($history ?? []) ?> records
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <?php if (!empty($history)): ?>
                            <table class="table table-hover table-striped align-middle mb-0" id="historyTable">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th width="50">#</th>
                                        <th width="150">Tyre Serial</th>
                                        <th width="120">Brand</th>
                                        <th width="120">Assign Vehicle</th>
                                        <th width="120">Position</th>
                                        <th width="150">Event Type</th>
                                        <th width="120">Event Date</th>
                                        <th width="150">Vendor</th>
                                        <th width="120">Location</th>
                                        <th width="120">From</th>
                                        <th width="120">To</th>
                                        <th width="200">Remarks</th>
                                        <th width="150">Created At</th>
                                        <th width="60">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $eventTypes = [
                                        1 => ['label' => 'Purchased',       'class' => 'success',   'icon' => 'fa-shopping-cart'],
                                        2 => ['label' => 'Transferred',     'class' => 'info',      'icon' => 'fa-exchange-alt'],
                                        3 => ['label' => 'Assigned',        'class' => 'primary',   'icon' => 'fa-link'],
                                        4 => ['label' => 'Exchanged',       'class' => 'warning',   'icon' => 'fa-sync'],
                                        5 => ['label' => 'Sent for Repair', 'class' => 'warning',   'icon' => 'fa-wrench'],
                                        6 => ['label' => 'Back to Stock',   'class' => 'secondary', 'icon' => 'fa-undo'],
                                        7 => ['label' => 'Sold',            'class' => 'success',   'icon' => 'fa-money-bill'],
                                        8 => ['label' => 'Rotation',        'class' => 'dark',      'icon' => 'fa-rotate'],
                                        9 => ['label' => 'Moved to Scrap',  'class' => 'danger',    'icon' => 'fa-trash'],
                                        10 => ['label' => 'Exchange Requested', 'class' => 'warning', 'icon' => 'fa-sync-alt'],
                                        11 => ['label' => 'Exchange Completed', 'class' => 'success', 'icon' => 'fa-check-double'],
                                    ];
                                    $i = 1; 
                                    $current_tyre_serial = $tyre->tyer_sl_no ?? '';
                                    foreach ($history as $row): 
                                        $event = $eventTypes[$row->event_type] ?? ['label' => 'Unknown', 'class' => 'secondary', 'icon' => 'fa-question'];
                                        $is_old_tyre = ($row->tyer_sl_no != $current_tyre_serial);
                                    ?>
                                        <tr data-event-type="<?= $row->event_type ?>" 
                                            data-vehicle-id="<?= $row->vehicle_id ?? '' ?>"
                                            data-date="<?= $row->event_date ?>"
                                            class="<?= $is_old_tyre ? 'bg-light' : '' ?>">
                                            <td><span class="text-muted"><?= $i++; ?></span></td>
                                            <td>
                                                <strong class="<?= $is_old_tyre ? 'text-muted' : 'text-primary' ?>">
                                                    <?= esc($row->tyer_sl_no ?? '—') ?>
                                                </strong>
                                                <?php if ($is_old_tyre): ?>
                                                    <br><span class="badge bg-secondary" style="font-size: 0.65rem;">OLD TYRE</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($row->brand_name ?? '—') ?></td>
                                            <td>
                                                <?php if (!empty($row->vehicle_no)): ?>
                                                    <span class="badge bg-dark"><?= esc($row->vehicle_no) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row->tyre_position)): ?>
                                                    <span class="badge bg-secondary"><?= esc($row->tyre_position) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $event['class'] ?> badge-event">
                                                    <i class="fas <?= $event['icon'] ?>"></i> <?= $event['label'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?= date('d-M-Y', strtotime($row->event_date)) ?></small>
                                            </td>
                                            <td>
                                                <small><?= esc($row->vendor_name ?? '—') ?></small>
                                            </td>
                                            <td>
                                                <small><?= esc($row->location_name ?? '—') ?></small>
                                            </td>
                                            <td>
                                                <?php if (!empty($row->from_location)): ?>
                                                    <span class="badge bg-light text-dark small">
                                                        <?= esc($row->from_location) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row->to_location)): ?>
                                                    <span class="badge bg-light text-dark small">
                                                        <?= esc($row->to_location) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted" title="<?= esc($row->remarks ?? '') ?>">
                                                    <?= esc($row->remarks ? (strlen($row->remarks) > 40 ? substr($row->remarks, 0, 40) . '...' : $row->remarks) : '—') ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d-M-Y', strtotime($row->created_at)) ?>
                                                    <br><?= date('h:i A', strtotime($row->created_at)) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info" 
                                                        onclick="viewDetails(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No tyre history found</h5>
                                <p class="text-muted small">Records will appear here once events are logged</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Event Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15) !important;
}
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}
.badge-event {
    font-size: 0.8rem;
    padding: 6px 10px;
    font-weight: 500;
}
.table thead.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}
@media print {
    #toggleFilters, .btn, .stat-card { display: none !important; }
    .card { border: 1px solid #ddd !important; box-shadow: none !important; }
}
</style>

<script>
// Toggle Filter Panel
document.getElementById('toggleFilters').addEventListener('click', function() {
    const panel = document.getElementById('filterPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
});

// Real-time Search Filter
document.getElementById('searchInput')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const table = document.getElementById('historyTable');
    const rows = table?.getElementsByTagName('tbody')[0]?.getElementsByTagName('tr');
    let visibleCount = 0;

    if (rows) {
        for (let row of rows) {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        }
        document.getElementById('recordCount').textContent = `Showing ${visibleCount} records`;
    }
});

// Event Type Filter
document.getElementById('eventTypeFilter')?.addEventListener('change', function() {
    const eventType = this.value;
    const rows = document.querySelectorAll('#historyTable tbody tr');
    let visibleCount = 0;

    rows.forEach(row => {
        if (!eventType || row.dataset.eventType == eventType) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    document.getElementById('recordCount').textContent = `Showing ${visibleCount} records`;
});

// Reset Filters
function resetFilters() {
    document.getElementById('filterForm').reset();
    document.getElementById('searchInput').value = '';
    const rows = document.querySelectorAll('#historyTable tbody tr');
    rows.forEach(row => row.style.display = '');
    document.getElementById('recordCount').textContent = `Showing ${rows.length} records`;
}

// View Details Modal
function viewDetails(data) {
    const eventTypes = {
        1: 'Purchased', 2: 'Transferred', 3: 'Assigned', 4: 'Exchanged',
        5: 'Sent for Repair', 6: 'Back to Stock', 7: 'Sold', 8: 'Rotation', 9: 'Moved to Scrap', 10: 'Exchange Requested', 11: 'Exchange Completed'
    };

    const content = `
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Tyre Serial Number</label>
                <p class="form-control-plaintext">${data.tyer_sl_no || '—'}</p>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Brand</label>
                <p class="form-control-plaintext">${data.brand_name || '—'}</p>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Vehicle Number</label>
                <p class="form-control-plaintext">${data.vehicle_no || '—'}</p>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Event Type</label>
                <p class="form-control-plaintext">${eventTypes[data.event_type] || 'Unknown'}</p>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Event Date</label>
                <p class="form-control-plaintext">${new Date(data.event_date).toLocaleDateString()}</p>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Vendor</label>
                <p class="form-control-plaintext">${data.vendor_name || '—'}</p>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Location</label>
                <p class="form-control-plaintext">${data.location_name || '—'}</p>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Transfer From</label>
                <p class="form-control-plaintext">${data.from_location || '—'}</p>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Transfer To</label>
                <p class="form-control-plaintext">${data.to_location || '—'}</p>
            </div>
            <div class="col-12">
                <label class="form-label fw-bold small text-muted">Remarks</label>
                <p class="form-control-plaintext">${data.remarks || '—'}</p>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">Created At</label>
                <p class="form-control-plaintext">${new Date(data.created_at).toLocaleString()}</p>
            </div>
        </div>
    `;

    document.getElementById('modalContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}

// Export to Excel
function exportToExcel() {
    const table = document.getElementById('historyTable');
    const wb = XLSX.utils.table_to_book(table, {sheet: "Tyre History"});
    XLSX.writeFile(wb, `tyre_history_${new Date().toISOString().slice(0,10)}.xlsx`);
}

// Add XLSX library for Excel export
const script = document.createElement('script');
script.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
document.head.appendChild(script);
</script>

<?php include("footer.php"); ?>