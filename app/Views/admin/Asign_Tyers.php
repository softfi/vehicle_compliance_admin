<?php include("header.php"); ?>
<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row mb-4">
                    <div class="col-sm-12">
                        <h3>Assign Vehicle</h3>
                    </div>
                </div>
            </div>

            <div class="container-fluid default-dashboard">
                <div class="row align-items-start">
                    <!-- ✅ Left Side: Form -->
                    <div class="col-md-6 mb-4">
                        <div class="form-card">
                            <div class="form-header">
                                <h4 class="mb-0 text-white fw-bold">
                                    <i class="fa fa-edit me-2"></i>Assign Tyre to Vehicle
                                </h4>
                                <small class="text-white-50">Fill in the details below</small>
                            </div>
                            <div class="form-body">
                                <form method="post" action="<?= base_url(); ?>/Admin/update_tyer_data">
                                    <input type="hidden" id="vehicle_id" class="form-control" name="vehicle_id" value="<?= $lvehicle_id ?>" />

                                    <div class="mb-4">
                                        <label class="form-label-custom">
                                            <i class="fa fa-map-marker-alt me-2"></i>Location
                                        </label>
                                        <select id="locationSelect" name="location" class="form-control-modern">
                                            <option value="">Select location</option>
                                            <?php foreach ($location as $loc) { ?>
                                                <option value="<?= $loc->location_id; ?>"><?= $loc->location_name; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label-custom">
                                            <i class="fa fa-tire me-2"></i>Select Tyer
                                        </label>
                                        <select class="form-control-modern tyerSelect" name="tyer_id" id="single1">
                                            <option value="">Select Tyer</option>
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label-custom">
                                            <i class="fa fa-map-pin me-2"></i>Tyer Position
                                        </label>
                                        <select class="form-control-modern" name="tyer_position">
                                            <?php foreach ($available_positions as $pos): ?>
                                                <option value="<?= esc($pos) ?>"><?= esc($pos) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label-custom">
                                            <i class="fa fa-calendar-alt me-2"></i>Assign Date
                                        </label>
                                        <input type="date" id="asign_date" class="form-control-modern" name="asign_date" required />
                                    </div>

                                    <button class="btn-gradient-submit" type="submit">
                                        <i class="fa fa-check-circle me-2"></i>Submit Assignment
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ Right Side: Truck Image -->
                    <div class="col-md-6 mb-4">
                        <div class="image-card">
                            <div class="image-header">
                                <h4 class="mb-0 text-white fw-bold">
                                    <i class="fa fa-truck me-2"></i>Vehicle Diagram
                                </h4>
                                <small class="text-white-50">Tyre position reference</small>
                            </div>
                            <div class="image-body">
                                <img src="<?= base_url('uploads/TruckDiagram.jpg'); ?>" 
                                    alt="Vehicle Banner"
                                    class="truck-image">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 mt-4">
            <div class="card shadow-lg border-0" style="border-radius: 15px; overflow: hidden;">
                <!-- Gradient Header -->
                <div class="card-header-custom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="header-title-section">
                            <h4 class="mb-0 text-white fw-bold">
                                <i class="fa fa-list-alt me-2"></i>Assigned Tyres Reference
                            </h4>
                            <small class="text-white-50">Total Records: <span id="totalRecords"><?= !empty($tyer_data) ? count($tyer_data) : 0 ?></span></small>
                        </div>
                        
                        <!-- Enhanced Search Bar -->
                        <div class="search-box-container">
                            <div class="search-box-wrapper">
                                <i class="fa fa-search search-icon"></i>
                                <input type="text" 
                                       id="tableSearch" 
                                       class="search-input" 
                                       placeholder="Search by serial no, position, brand...">
                                <span class="search-border"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table modern-table mb-0" id="tyresTable">
                            <thead>
                                <tr>
                                    <th><i class="fa fa-hashtag me-1"></i> Sl No</th>
                                    <th><i class="fa fa-barcode me-1"></i> Tyer Serial No</th>
                                    <th><i class="fa fa-map-pin me-1"></i> Position</th>
                                    <th><i class="fa fa-calendar me-1"></i> Assign Date</th>
                                    <th><i class="fa fa-tag me-1"></i> Type</th>
                                    <th><i class="fa fa-certificate me-1"></i> Brand</th>
                                    <th><i class="fa fa-map-marker me-1"></i> Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($tyer_data)) : ?>
                                    <?php $i = 1; foreach ($tyer_data as $row) : ?>
                                        <tr class="table-row-hover">
                                            <td><span class="badge bg-gradient-primary"><?= $i++; ?></span></td>
                                            <td class="fw-semibold text-primary"><?= esc($row->tyer_sl_no) ?></td>
                                            <td><span class="position-badge"><?= esc($row->tyer_position) ?></span></td>
                                            <td><?= esc($row->asign_date) ?></td>
                                            <td><?= esc($row->tyer_type) ?></td>
                                            <td><span class="brand-tag"><?= esc($row->brand_name) ?></span></td>
                                            <td><i class="fa fa-map-marker-alt text-danger me-1"></i><?= esc($row->location_name) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr class="no-results">
                                        <td colspan="7" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                                                <p class="text-muted mb-0">No tyres assigned yet.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="noResults" class="no-results-found" style="display: none;">
                        <i class="fa fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No matching records found</h5>
                        <p class="text-muted small">Try adjusting your search terms</p>
                    </div>
                </div>
                
                <!-- Footer with stats -->
                <div class="card-footer-custom">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fa fa-eye me-1"></i>Showing <span id="visibleRecords"><?= !empty($tyer_data) ? count($tyer_data) : 0 ?></span> of <span id="totalRecordsFooter"><?= !empty($tyer_data) ? count($tyer_data) : 0 ?></span> records
                        </small>
                        <small class="text-muted">
                            <i class="fa fa-clock me-1"></i>Last updated: <?= date('M d, Y') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Location change handler
    $('#locationSelect').change(function() {
        var locationId = $(this).val();
        $('.tyerSelect').empty().append('<option value="">Select Tyer</option>');

        if (locationId) {
            $.ajax({
                url: '<?= base_url(); ?>/Admin/gettyerData',
                method: 'POST',
                data: { location_id: locationId },
                dataType: 'json',
                success: function(response) {
                    $('.tyerSelect').empty().append('<option value="">Select Tyer</option>');
                    $.each(response, function(index, tire) {
                        $('.tyerSelect').append('<option value="' + tire.id + '">' + tire.tyer_sl_no + '</option>');
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }
    });

    // Enhanced table search with animations
    $('#tableSearch').on('keyup', function() {
        var searchText = $(this).val().toLowerCase().trim();
        var $tableRows = $('#tyresTable tbody tr').not('.no-results');
        var visibleCount = 0;

        if (searchText === '') {
            $tableRows.show();
            $('#noResults').hide();
            visibleCount = $tableRows.length;
            $('#visibleRecords').text(visibleCount);
            return;
        }

        $tableRows.each(function() {
            var rowText = $(this).text().toLowerCase();
            if (rowText.indexOf(searchText) > -1) {
                $(this).fadeIn(200);
                visibleCount++;
            } else {
                $(this).fadeOut(200);
            }
        });

        // Update visible count
        $('#visibleRecords').text(visibleCount);

        // Show/hide no results message
        if (visibleCount === 0) {
            $('#noResults').fadeIn(300);
        } else {
            $('#noResults').fadeOut(300);
        }
    });

    // Enhanced focus effects
    $('#tableSearch').on('focus', function() {
        $(this).parent().addClass('search-focused');
    }).on('blur', function() {
        $(this).parent().removeClass('search-focused');
    });
});
</script>

<style>
/* Modern Card Styling */
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Gradient Header */
.card-header-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1.5rem;
    border-bottom: 3px solid rgba(255,255,255,0.2);
}

/* Search Box Container */
.search-box-container {
    min-width: 300px;
}

.search-box-wrapper {
    position: relative;
    width: 100%;
}

.search-input {
    width: 100%;
    padding: 12px 20px 12px 45px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50px;
    background: rgba(255,255,255,0.95);
    font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.search-input:focus {
    outline: none;
    border-color: #fff;
    background: #fff;
    box-shadow: 0 6px 25px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.search-icon {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #667eea;
    font-size: 16px;
    transition: all 0.3s ease;
}

.search-focused .search-icon {
    color: #764ba2;
    transform: translateY(-50%) scale(1.1);
}

.search-input::placeholder {
    color: #a0a0a0;
}

/* Modern Table Styling */
.modern-table {
    border-collapse: separate;
    border-spacing: 0;
}

.modern-table thead {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.modern-table thead th {
    padding: 18px 15px;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.5px;
    color: #495057;
    border: none;
    border-bottom: 3px solid #667eea;
}

.modern-table tbody td {
    padding: 16px 15px;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
    transition: all 0.3s ease;
}

.table-row-hover {
    transition: all 0.3s ease;
    cursor: pointer;
}

.table-row-hover:hover {
    background: linear-gradient(90deg, #f8f9ff 0%, #fff 100%);
    transform: scale(1.01);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);
}

/* Badge Styling */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.position-badge {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
}

.brand-tag {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
}

/* Footer Styling */
.card-footer-custom {
    background: #f8f9fa;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e9ecef;
}

/* No Results Animation */
.no-results-found {
    text-align: center;
    padding: 60px 20px;
    animation: fadeInUp 0.5s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Empty State */
.empty-state {
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Responsive Design */
@media (max-width: 768px) {
    .card-header-custom {
        padding: 1rem;
    }
    
    .search-box-container {
        min-width: 100%;
        margin-top: 1rem;
    }
    
    .header-title-section {
        width: 100%;
    }
}

/* Smooth Scrollbar */
.table-responsive::-webkit-scrollbar {
    height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
}

/* Icon animations */
.modern-table thead th i {
    opacity: 0.7;
    transition: all 0.3s ease;
}

.modern-table thead th:hover i {
    opacity: 1;
    transform: scale(1.1);
}

/* ========== FORM STYLING ========== */
.form-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.form-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 50px rgba(102, 126, 234, 0.2);
}

.form-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1.5rem;
    border-bottom: 3px solid rgba(255,255,255,0.2);
}

.form-body {
    padding: 2rem;
}

.form-label-custom {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
    display: block;
    font-size: 14px;
}

.form-label-custom i {
    color: #667eea;
}

.form-control-modern {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e1e8ed;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.form-control-modern:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    transform: translateY(-2px);
}

.form-control-modern:hover {
    border-color: #764ba2;
    background: white;
}

.btn-gradient-submit {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-gradient-submit:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
}

.btn-gradient-submit:active {
    transform: translateY(-1px);
}

/* ========== IMAGE CARD STYLING ========== */
.image-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
}

.image-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 50px rgba(102, 126, 234, 0.2);
}

.image-header {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    padding: 1.5rem;
    border-bottom: 3px solid rgba(255,255,255,0.2);
}

.image-body {
    padding: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.truck-image {
    max-width: 100%;
    max-height: 400px;
    object-fit: contain;
    border-radius: 10px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    transition: transform 0.3s ease;
}

.truck-image:hover {
    transform: scale(1.05);
}

/* Form Input Animation */
@keyframes inputFocus {
    0% {
        box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4);
    }
    100% {
        box-shadow: 0 0 0 8px rgba(102, 126, 234, 0);
    }
}

.form-control-modern:focus {
    animation: inputFocus 0.6s ease-out;
}
</style>