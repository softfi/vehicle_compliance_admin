<?php include("header.php"); ?>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-theme@0.1.0-beta.10/dist/select2-bootstrap.min.css" rel="stylesheet" />

<style>
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .stats-card h4 {
        margin: 0;
        font-size: 14px;
        opacity: 0.9;
    }
    .stats-card h2 {
        margin: 10px 0 0 0;
        font-size: 32px;
        font-weight: bold;
    }
    .form-section {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }
    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #667eea;
    }
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }
    .alert-custom {
        display: none;
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideIn 0.3s ease-out;
    }
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
    }
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9998;
        justify-content: center;
        align-items: center;
    }
    .spinner-border {
        width: 3rem;
        height: 3rem;
    }
    .badge-adjustment {
        font-size: 11px;
        padding: 4px 8px;
    }
    .select2-container {
        width: 100% !important;
    }
    .select2-container--bootstrap .select2-selection {
        border: 1px solid #ced4da;
        border-radius: 4px;
        min-height: 38px;
    }
    .select2-container--bootstrap .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
    }
    .select2-container--bootstrap .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 12px;
    }
    .select2-dropdown {
        border: 1px solid #ced4da;
        border-radius: 4px;
    }
    .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 8px;
    }
</style>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-light" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>

<!-- Alert Notification -->
<div class="alert alert-custom" id="alertNotification" role="alert"></div>

<!-- Page Body Start -->
<div class="page-body-wrapper" style="background:#f9f9f9;">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Salary Adjustment Management</h3>
                        <p class="text-muted">Manage driver salary adjustments efficiently</p>
                    </div>
                    <div class="col-sm-6 p-0 text-right">
                        <button class="btn btn-info" onclick="refreshData()">
                            <i class="fa fa-refresh"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h4>Total Adjustments</h4>
                        <h2 id="totalAdjustments"><?= count($allamount ?? []) ?></h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <h4>This Month</h4>
                        <h2 id="monthAdjustments">
                            <?php 
                            $currentMonth = date('Y-m');
                            $monthCount = 0;
                            foreach ($allamount ?? [] as $amt) {
                                if (date('Y-m', strtotime($amt->from_date)) == $currentMonth) {
                                    $monthCount++;
                                }
                            }
                            echo $monthCount;
                            ?>
                        </h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <h4>Total Amount</h4>
                        <h2 id="totalAmount">
                            ₹<?= number_format(array_sum(array_column($allamount ?? [], 'amount')), 2) ?>
                        </h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <h4>Active Drivers</h4>
                        <h2 id="activeDrivers"><?= count($drivers ?? []) ?></h2>
                    </div>
                </div>
            </div>

            <!-- Add New Adjustment Form -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fa fa-plus-circle"></i> Add New Salary Adjustment
                </h4>
                <form id="adjustmentForm" action="<?= base_url(); ?>/admin/add_adjust_salary" method="POST">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="driver_select">Driver <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="driver" id="driver_select" required>
                                    <option value="">Select Driver</option>
                                    <?php foreach ($drivers ?? [] as $driver) { 
                                        if ($driver->user_type == 'DRIVER') { ?>
                                            <option value="<?= htmlspecialchars($driver->id, ENT_QUOTES, 'UTF-8'); ?>" 
                                                    data-code="<?= htmlspecialchars($driver->staff_code, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?= htmlspecialchars($driver->name, ENT_QUOTES, 'UTF-8'); ?> 
                                                (<?= htmlspecialchars($driver->staff_code, ENT_QUOTES, 'UTF-8'); ?>)
                                            </option>
                                        <?php } 
                                    } ?>
                                </select>
                                <small class="form-text text-muted">Select the driver for adjustment</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="vehicle_select">Vehicle <span class="text-danger">*</span></label>
                                <select class="form-control select2-vehicle" name="vehicle" id="single" required>
                                    <option value="">Select Vehicle</option>
                                    <?php foreach ($vehicles ?? [] as $vehicle): ?>
                                        <option value="<?= htmlspecialchars($vehicle->id, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars($vehicle->vehicle_no, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Type to search vehicle number</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="adjustment_date">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="adjustment_date" name="from_date" 
                                       max="<?= date('Y-m-d'); ?>" required>
                                <small class="form-text text-muted">Adjustment date</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="amount">Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="amount" name="amount" 
                                       placeholder="Enter Amount" required>
                                <small class="form-text text-muted">Enter adjustment amount</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="remark">Remark</label>
                                <input type="text" class="form-control" id="remark" name="remark" 
                                       placeholder="Enter remark" maxlength="255">
                                <small class="form-text text-muted">Optional remark</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Submit Adjustment
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fa fa-refresh"></i> Reset Form
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Bulk Upload Section -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fa fa-upload"></i> Bulk Upload via Excel
                </h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> 
                            <a href="<?= base_url(); ?>/sampleexcel/adjust_salary.xlsx" class="alert-link">
                                Download sample Excel template
                            </a> to ensure correct format.
                        </div>
                        <form action="<?= base_url(); ?>/Admin/upload_adjust_salary" method="post" 
                              enctype="multipart/form-data" id="excelUploadForm">
                            <div class="form-group">
                                <label for="excel_file">Select Excel File <span class="text-danger">*</span></label>
                                <input type="file" name="file" id="excel_file" class="form-control" 
                                       accept=".csv, .xlsx, .xls" required>
                                <small class="form-text text-muted">
                                    Supported formats: CSV, XLSX, XLS (Max 5MB)
                                </small>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-cloud-upload"></i> Upload Excel
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fa fa-filter"></i> Filter Records
                </h4>
                <form id="filterForm" method="post">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_year">Year</label>
                                <select class="form-control" name="year" id="filter_year">
                                    <?php 
                                    $currentYear = date('Y');
                                    for ($y = 2023; $y <= 2040; $y++): ?>
                                        <option value="<?= $y; ?>" <?= $y == $currentYear ? 'selected' : ''; ?>>
                                            <?= $y; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_month">Month</label>
                                <select class="form-control" name="month" id="filter_month">
                                    <?php
                                    $currentMonth = date('n');
                                    $months = [
                                        "January", "February", "March", "April", "May", "June",
                                        "July", "August", "September", "October", "November", "December"
                                    ];
                                    foreach ($months as $index => $month): ?>
                                        <option value="<?= $index + 1; ?>" 
                                                <?= ($index + 1) == $currentMonth ? 'selected' : ''; ?>>
                                            <?= $month; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-primary btn-block" onclick="applyFilter()">
                                        <i class="fa fa-search"></i> Apply Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Data Table -->
            <div class="form-section">
                <h4 class="section-title">
                    <i class="fa fa-table"></i> Salary Adjustments
                    <span class="badge badge-primary badge-pill float-right" id="recordCount">
                        <?= count($allamount ?? []) ?> Records
                    </span>
                </h4>
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="adjustmentTable">
                        <thead class="thead-dark">
                            <tr>
                                <th width="5%">Sl.No</th>
                                <th width="15%">Driver Name</th>
                                <th width="12%">Vehicle No</th>
                                <th width="12%">Amount</th>
                                <th width="10%">Date</th>
                                <th width="15%">Remark</th>
                                <th width="9%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            $total = 0;
                            foreach ($allamount ?? [] as $amount):
                                $total += $amount->amount;
                            ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td><?= htmlspecialchars($amount->driver_name ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                    
                                    <td><?= htmlspecialchars($amount->vehicle_no ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <strong>₹<?= number_format($amount->amount ?? 0, 2); ?></strong>
                                    </td>
                                    <td><?= date('d-M-Y', strtotime($amount->from_date ?? 'now')); ?></td>
                                    <td>
                                        <small><?= htmlspecialchars($amount->remark ?? '-', ENT_QUOTES, 'UTF-8'); ?></small>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a class="btn btn-success btn-sm" 
                                               href="<?= base_url(); ?>/Admin/edit_adjust_salary/<?= htmlspecialchars($amount->id, ENT_QUOTES, 'UTF-8'); ?>"
                                               title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <button class="btn btn-danger btn-sm" 
                                                    onclick="confirmDelete(<?= htmlspecialchars($amount->id, ENT_QUOTES, 'UTF-8'); ?>)"
                                                    title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <th colspan="5" class="text-right">Total Amount:</th>
                                <th colspan="4"><strong>₹<?= number_format($total ?? 0, 2); ?></strong></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 for Driver dropdown
    $('.select2').select2({
        theme: 'bootstrap',
        placeholder: function() {
            return $(this).data('placeholder') || 'Select an option';
        },
        allowClear: true
    });

    // Custom matcher for better vehicle search
    function matchCustom(params, data) {
        // If there are no search terms, return all data
        if ($.trim(params.term) === '') {
            return data;
        }

        // Skip if there is no 'text' property
        if (typeof data.text === 'undefined') {
            return null;
        }

        // Search in vehicle number (case insensitive)
        if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
            return data;
        }

        // Return null if the term should not be displayed
        return null;
    }

    // Initialize DataTable
    const table = $('#adjustmentTable').DataTable({
        pageLength: 25,
        order: [[6, 'desc']], // Sort by date descending
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        language: {
            search: "Search records:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            emptyTable: "No salary adjustments found"
        }
    });

    // Form validation and submission
    $('#adjustmentForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return false;
        }

        showLoading(true);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                showLoading(false);
                if (response.success) {
                    showAlert('Success! Salary adjustment added successfully.', 'success');
                    $('#adjustmentForm')[0].reset();
                    $('.select2, .select2-vehicle').val(null).trigger('change');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert('Error: ' + response.message, 'danger');
                }
            },
            error: function() {
                showLoading(false);
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });
    });

    // Excel upload validation
    $('#excelUploadForm').on('submit', function(e) {
        const fileInput = $('#excel_file')[0];
        if (fileInput.files.length > 0) {
            const fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
            if (fileSize > 5) {
                e.preventDefault();
                showAlert('File size exceeds 5MB limit!', 'warning');
                return false;
            }
        }
        showLoading(true);
    });

    // Dynamic driver loading based on year/month
    $('#filter_year, #filter_month').on('change', function() {
        loadDriversByPeriod();
    });
});

// Validate form
function validateForm() {
    const driver = $('#driver_select').val();
    const vehicle = $('#vehicle_select').val();
    const date = $('#adjustment_date').val();
    const amount = $('#amount').val();

    if (!driver || !vehicle || !date || !amount) {
        showAlert('Please fill all required fields!', 'warning');
        return false;
    }

    if (parseFloat(amount) <= 0) {
        showAlert('Amount must be greater than zero!', 'warning');
        return false;
    }

    return true;
}

// Load drivers based on selected period
function loadDriversByPeriod() {
    const year = $('#filter_year').val();
    const month = $('#filter_month').val();

    if (year && month) {
        showLoading(true);
        $.ajax({
            url: '<?= base_url("admin/getDriverAssignments") ?>',
            type: 'POST',
            data: { year: year, month: month },
            dataType: 'json',
            success: function(response) {
                showLoading(false);
                if (response.success) {
                    updateDriverDropdown(response.drivers_assignment);
                } else {
                    showAlert(response.message, 'warning');
                }
            },
            error: function() {
                showLoading(false);
                showAlert('Failed to load drivers for selected period.', 'danger');
            }
        });
    }
}

// Update driver dropdown
function updateDriverDropdown(drivers) {
    const $select = $('#driver_select');
    $select.empty().append('<option value="">Select Driver</option>');
    
    $.each(drivers, function(index, driver) {
        $select.append(
            `<option value="${driver.driver}" data-code="${driver.driver_code}">
                ${driver.driver_name} (${driver.driver_code})
            </option>`
        );
    });
    
    $select.trigger('change');
}

// Apply filter
function applyFilter() {
    showLoading(true);
    $('#filterForm').attr('action', '<?= base_url(); ?>/admin/adjust_salary').submit();
}

// Confirm delete
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this salary adjustment? This action cannot be undone.')) {
        showLoading(true);
        window.location.href = '<?= base_url(); ?>/Admin/delete_adjust_salary/' + id;
    }
}

// Show alert notification
function showAlert(message, type) {
    const alertDiv = $('#alertNotification');
    alertDiv.removeClass().addClass(`alert alert-${type} alert-custom alert-dismissible fade show`);
    alertDiv.html(`
        ${message}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    `);
    alertDiv.show();
    
    setTimeout(() => alertDiv.fadeOut(), 5000);
}

// Show/hide loading overlay
function showLoading(show) {
    if (show) {
        $('#loadingOverlay').css('display', 'flex');
    } else {
        $('#loadingOverlay').hide();
    }
}

// Refresh data
function refreshData() {
    showLoading(true);
    location.reload();
}

// Set default date to today
$('#adjustment_date').val(new Date().toISOString().split('T')[0]);
</script>

<?php include("footer.php"); ?>