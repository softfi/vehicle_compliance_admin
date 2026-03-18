<?php include("header.php"); ?>

<style>
    :root {
        --primary-color: #4a90e2;
        --secondary-color: #2c3e50;
        --success-color: #27ae60;
        --danger-color: #e74c3c;
        --warning-color: #f39c12;
        --light-bg: #f8f9fa;
        --card-shadow: 0 2px 8px rgba(0,0,0,0.1);
        --hover-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .page-body { padding-top: 20px; }

    .card-modern {
        background: white;
        border-radius: 8px;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }

    .card-modern:hover {
        box-shadow: var(--hover-shadow);
    }

    .card-header-custom {
        background: linear-gradient(135deg, var(--primary-color), #357abd);
        color: white;
        padding: 15px 20px;
        border-radius: 8px 8px 0 0;
        font-weight: 600;
    }

    .card-body-custom {
        padding: 25px;
    }

    .form-group label {
        font-weight: 600;
        color: var(--secondary-color);
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-control {
        border-radius: 6px;
        border: 1px solid #dce4ec;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.15);
    }

    .btn-custom {
        padding: 10px 24px;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-primary-custom {
        background: var(--primary-color);
        color: white;
    }

    .btn-primary-custom:hover {
        background: #357abd;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(74, 144, 226, 0.3);
    }

    .btn-danger-custom {
        background: var(--danger-color);
        color: white;
    }

    .btn-danger-custom:hover {
        background: #c0392b;
        transform: translateY(-2px);
    }

    .btn-success-custom {
        background: var(--success-color);
        color: white;
    }

    .btn-success-custom:hover {
        background: #229954;
        transform: translateY(-2px);
    }

    .table-container {
        max-height: 500px;
        overflow-y: auto;
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .table-modern {
        margin-bottom: 0;
        width: 100%;
    }

    .table-modern thead th {
        position: sticky;
        top: 0;
        background: linear-gradient(135deg, #2c3e50, #34495e);
        color: white;
        z-index: 10;
        border: none;
        padding: 15px;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table-modern tbody tr {
        transition: background-color 0.2s ease;
    }

    .table-modern tbody tr:hover {
        background-color: #f8f9fa;
    }

    .table-modern tbody td {
        padding: 12px 15px;
        vertical-align: middle;
        border-bottom: 1px solid #e9ecef;
    }

    /* New Search Box Styles */
    .search-container {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: var(--card-shadow);
        margin-bottom: 20px;
    }

    .search-input-wrapper {
        position: relative;
        display: block;
        width: 100%;
    }

    .search-input-wrapper input {
        width: 100%;
        height: 50px;
        padding: 12px 20px 12px 50px;
        border: 2px solid #e0e0e0;
        border-radius: 25px;
        font-size: 16px;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
    }

    .search-input-wrapper input:focus {
        outline: none;
        border-color: var(--primary-color);
        background-color: white;
        box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.1);
    }

    .search-input-wrapper .search-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        font-size: 18px;
        pointer-events: none;
    }

    .search-input-wrapper .clear-search {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        background: var(--danger-color);
        color: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .search-input-wrapper .clear-search:hover {
        background: #c0392b;
        transform: translateY(-50%) scale(1.1);
    }

    .search-input-wrapper .clear-search.active {
        display: flex;
    }

    .search-results-info {
        margin-top: 10px;
        padding: 10px 15px;
        background: #e7f3ff;
        border-radius: 6px;
        color: #004085;
        font-size: 14px;
        display: none;
    }

    .search-results-info.active {
        display: block;
    }

    .filter-section {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: var(--card-shadow);
        margin-bottom: 20px;
    }

    .alert-custom {
        border-radius: 8px;
        border: none;
        padding: 15px 20px;
        margin-bottom: 20px;
        animation: slideDown 0.4s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .page-title h3 {
        color: var(--secondary-color);
        font-weight: 700;
        margin-bottom: 0;
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin: 0;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-sm-custom {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 4px;
        white-space: nowrap;
    }

    .grid-container {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 20px;
    }

    @media (max-width: 1024px) {
        .grid-container {
            grid-template-columns: 1fr;
        }
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        align-items: end;
    }

    /* Custom Scrollbar */
    .table-container::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .table-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .table-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .table-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Hide row class */
    .hide-row {
        display: none !important;
    }

    /* No results message */
    .no-results-message {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
        display: none;
    }

    .no-results-message.active {
        display: block;
    }

    .no-results-message i {
        font-size: 60px;
        margin-bottom: 20px;
        color: #dee2e6;
    }

    .no-results-message h5 {
        color: #495057;
        margin-bottom: 10px;
    }

    .badge {
        font-size: 11px;
        padding: 4px 8px;
    }
</style>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    
    <div class="page-body">
        <div class="container-fluid">        
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3><i class="fas fa-user-tie"></i> Assign Driver</h3>
                    </div>
                    <div class="col-sm-6 p-0">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.html"><i class="fas fa-home"></i></a></li>
                            <li class="breadcrumb-item">Dashboard</li>
                            <li class="breadcrumb-item active">Assign Driver</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid default-dashboard">
            <?php if(session()->getFlashdata('msg')): ?>
                <div class="alert alert-warning alert-custom alert-dismissible fade show">
                    <i class="fas fa-info-circle"></i> <?= session()->getFlashdata('msg') ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <div class="grid-container">
                <!-- Form Section -->
                <div>
                    <div class="card-modern">
                        <div class="card-header-custom">
                            <i class="fas fa-plus-circle"></i> New Driver Assignment
                        </div>
                        <div class="card-body-custom">
                            <form action="<?= base_url(); ?>/Admin/insert_driver_asignment" method="post" id="assignmentForm">
                                <div class="form-group">
                                    <label for="vehicle_no"><i class="fas fa-truck"></i> Vehicle Number</label>
                                    <select class="form-control" name="vehicle_no" id="vehicle_no" required>
                                        <option value="">Select Vehicle Number</option>
                                        <?php foreach ($vehicles as $vehicle): ?>
                                            <option value="<?= $vehicle->id; ?>"><?= $vehicle->vehicle_no; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="driver"><i class="fas fa-user"></i> Driver</label>
                                    <select class="form-control" name="driver" id="single1" required>
                                        <option value="">Select Driver</option>
                                        <?php foreach ($drivers as $driver): ?>
                                            <?php if ($driver->user_type === 'DRIVER'): ?>
                                                <option value="<?= $driver->id; ?>">
                                                    <?= esc($driver->name); ?> (<?= esc($driver->staff_code); ?>)
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="from_date"><i class="fas fa-calendar-alt"></i> From Date</label>
                                    <input type="date" class="form-control" name="from_date" id="from_date" required>
                                </div>

                                <div class="form-group">
                                    <label for="opening_hsd"><i class="fas fa-gas-pump"></i> Opening HSD</label>
                                    <input type="number" step="0.01" class="form-control" name="opening_hsd" id="opening_hsd" placeholder="0.00" required>
                                </div>

                                <div class="form-group">
                                    <label for="opening_km"><i class="fas fa-tachometer-alt"></i> Opening KM</label>
                                    <input type="number" step="0.01" class="form-control" name="opening_km" id="opening_km" placeholder="0.00" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="to_date"><i class="fas fa-calendar-check"></i> To Date</label>
                                    <input type="date" class="form-control" name="to_date" id="to_date">
                                </div>
                        
                                <div class="form-group">
                                    <label for="closing_hsd"><i class="fas fa-gas-pump"></i> Closing HSD</label>
                                    <input type="number" step="0.01" class="form-control" name="closing_hsd" id="closing_hsd" placeholder="0.00">
                                </div>
                        
                                <div class="form-group">
                                    <label for="closing_km"><i class="fas fa-tachometer-alt"></i> Closing KM</label>
                                    <input type="number" step="0.01" class="form-control" name="closing_km" id="closing_km" placeholder="0.00">
                                </div>

                                <?php if(in_array(8.1, $jobAssign)): ?>
                                    <button type="submit" class="btn btn-custom btn-primary-custom btn-block">
                                        <i class="fas fa-save"></i> Submit Assignment
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div>
                    <div class="filter-section">
                        <form id="filterForm" action="<?= base_url(); ?>/Admin/Driver_Assignment" method="post">
                            <div class="filter-grid">
                                <?php
                                    $default_from_date = $date['from_date'] ?? date('Y-m-01');
                                    $default_to_date = $date['to_date'] ?? date('Y-m-d');
                                ?>
                                <div class="form-group mb-0">
                                    <label for="filter_from_date"><i class="fas fa-calendar-alt"></i> From Date</label>
                                    <input type="date" id="filter_from_date" name="from_date" class="form-control" value="<?= $default_from_date; ?>">
                                </div>
                                <div class="form-group mb-0">
                                    <label for="filter_to_date"><i class="fas fa-calendar-check"></i> To Date</label>
                                    <input type="date" id="filter_to_date" name="to_date" class="form-control" value="<?= $default_to_date; ?>">
                                </div>
                                <div class="form-group mb-0">
                                    <?php if(in_array(8.2, $jobAssign)): ?>
                                        <button type="submit" class="btn btn-custom btn-primary-custom">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group mb-0">
                                    <button class="btn btn-custom btn-success-custom" type="button" onclick="downloadExcel()">
                                        <i class="fas fa-file-excel"></i> Download Excel
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- NEW SEARCH BOX -->
                    <div class="search-container">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input 
                                type="text" 
                                id="searchInput" 
                                placeholder="Search by Vehicle Number, Driver Name, or Date..."
                                autocomplete="off"
                            >
                            <button type="button" class="clear-search" id="clearSearch">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="search-results-info" id="searchInfo">
                            <i class="fas fa-info-circle"></i> <span id="resultText"></span>
                        </div>
                    </div>

                    <div class="card-modern">
                        <div class="card-body-custom">
                            <div class="no-results-message" id="noResults">
                                <i class="fas fa-search-minus"></i>
                                <h5>No Results Found</h5>
                                <p>Try adjusting your search terms</p>
                            </div>
                            
                            <div class="table-container" id="tableContainer">
                                <table id="myTable" class="table table-modern table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Vehicle Number</th>
                                            <th>Driver Name</th>
                                            <th>From Date</th>
                                            <th>Opening HSD</th>
                                            <th>Opening KM</th>
                                            <th>To Date</th>
                                            <th>Closing HSD</th>
                                            <th>Closing KM</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody">
                                        <?php foreach ($drivers_asignment as $record): ?>
                                            <tr class="table-row">
                                                <td><strong>#<?= $record->id; ?></strong></td>
                                                <td><i class="fas fa-truck text-primary"></i> <?= $record->vehicle_number; ?></td>
                                                <td>
                                                    <i class="fas fa-user text-secondary"></i> 
                                                    <?= htmlspecialchars($record->driver_name); ?> 
                                                    <span class="badge badge-secondary"><?= htmlspecialchars($record->driver_code); ?></span>
                                                </td>
                                                <td><?= date('d M Y', strtotime($record->from_date)); ?></td>
                                                <td><?= number_format($record->opening_hsd, 2); ?></td>
                                                <td><?= number_format($record->opening_km, 2); ?></td>
                                                <td><?= $record->to_date ? date('d M Y', strtotime($record->to_date)) : '-'; ?></td>
                                                <td><?= $record->closing_hsd ? number_format($record->closing_hsd, 2) : '-'; ?></td>
                                                <td><?= $record->closing_km ? number_format($record->closing_km, 2) : '-'; ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <?php if(in_array(8.3, $jobAssign)): ?>
                                                            <a class="btn btn-custom btn-primary-custom btn-sm-custom" href="<?= base_url(); ?>/Admin/Edit_Driver_Assignment/<?= $record->id; ?>">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if(in_array(8.4, $jobAssign)): ?>
                                                            <button class="btn btn-custom btn-danger-custom btn-sm-custom" onclick="deleteRecord(<?= $record->id; ?>)">
                                                                <i class="fas fa-trash-alt"></i> Delete
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include("footer.php"); ?>
</div>

<script>
// Advanced Table Search Functionality
(function() {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');
    const tableBody = document.getElementById('tableBody');
    const tableRows = document.querySelectorAll('.table-row');
    const noResults = document.getElementById('noResults');
    const tableContainer = document.getElementById('tableContainer');
    const searchInfo = document.getElementById('searchInfo');
    const resultText = document.getElementById('resultText');
    
    let totalRows = tableRows.length;
    
    // Search function
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;
        
        if (searchTerm === '') {
            // Show all rows
            tableRows.forEach(row => {
                row.classList.remove('hide-row');
            });
            clearBtn.classList.remove('active');
            searchInfo.classList.remove('active');
            noResults.classList.remove('active');
            tableContainer.style.display = 'block';
            return;
        }
        
        // Show clear button
        clearBtn.classList.add('active');
        
        // Filter rows
        tableRows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            if (rowText.includes(searchTerm)) {
                row.classList.remove('hide-row');
                visibleCount++;
            } else {
                row.classList.add('hide-row');
            }
        });
        
        // Update UI based on results
        if (visibleCount === 0) {
            noResults.classList.add('active');
            tableContainer.style.display = 'none';
            searchInfo.classList.remove('active');
        } else {
            noResults.classList.remove('active');
            tableContainer.style.display = 'block';
            searchInfo.classList.add('active');
            resultText.textContent = `Showing ${visibleCount} of ${totalRows} records`;
        }
    }
    
    // Event listeners
    searchInput.addEventListener('input', performSearch);
    
    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Escape') {
            clearSearch();
        }
    });
    
    clearBtn.addEventListener('click', clearSearch);
    
    function clearSearch() {
        searchInput.value = '';
        performSearch();
        searchInput.focus();
    }
    
    // Focus search on Ctrl/Cmd + F
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            searchInput.focus();
        }
    });
})();

// Delete record with confirmation
function deleteRecord(id) {
    if (confirm('Are you sure you want to delete this driver assignment? This action cannot be undone.')) {
        window.location.href = '<?= base_url(); ?>/Admin/delete_driver_asignment/' + id;
    }
}

// Download Excel
function downloadExcel() {
    const form = document.getElementById('filterForm');
    const originalAction = form.action;
    form.action = "<?= base_url('Admin/downloadExcel'); ?>";
    form.submit();
    form.action = originalAction;
}

// Form validation enhancement
document.getElementById('assignmentForm')?.addEventListener('submit', function(e) {
    const fromDate = document.getElementById('from_date').value;
    const toDate = document.getElementById('to_date').value;
    
    if (toDate && new Date(toDate) < new Date(fromDate)) {
        e.preventDefault();
        alert('To Date cannot be earlier than From Date');
        return false;
    }
});

// Auto-dismiss alerts
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert-custom');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s ease';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);
</script>