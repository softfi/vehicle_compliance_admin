<?php include("header.php"); ?>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

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

    .page-body-wrapper {
        background: var(--light-bg);
        min-height: 100vh;
    }

    .card-modern {
        background: white;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        margin-bottom: 20px;
        overflow: hidden;
    }

    .card-header-custom {
        background: linear-gradient(135deg, var(--primary-color), #357abd);
        color: white;
        padding: 20px 30px;
        font-weight: 600;
        font-size: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-body-custom {
        padding: 35px;
    }

    .form-section {
        max-width: 900px;
        margin: 0 auto;
    }

    .form-row-custom {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group label {
        font-weight: 600;
        color: var(--secondary-color);
        margin-bottom: 8px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-control {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        padding: 12px 15px;
        transition: all 0.3s ease;
        font-size: 14px;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.15);
        outline: none;
    }

    .form-control:hover {
        border-color: #ced4da;
    }

    .required-field::after {
        content: " *";
        color: var(--danger-color);
    }

    .btn-custom {
        padding: 12px 32px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        font-size: 15px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary-custom {
        background: var(--primary-color);
        color: white;
    }

    .btn-primary-custom:hover {
        background: #357abd;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(74, 144, 226, 0.4);
    }

    .btn-secondary-custom {
        background: #6c757d;
        color: white;
    }

    .btn-secondary-custom:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }

    .page-title h3 {
        color: var(--secondary-color);
        font-weight: 700;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin: 0;
    }

    .breadcrumb-item a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .breadcrumb-item.active {
        color: #6c757d;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-start;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 2px solid #f0f0f0;
    }

    .info-box {
        background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
        border-left: 4px solid var(--primary-color);
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .info-box i {
        font-size: 24px;
        color: var(--primary-color);
    }

    .info-box-text {
        flex: 1;
    }

    .info-box-text strong {
        display: block;
        color: var(--secondary-color);
        margin-bottom: 3px;
    }

    .info-box-text span {
        color: #666;
        font-size: 13px;
    }

    .section-divider {
        border: 0;
        height: 2px;
        background: linear-gradient(to right, transparent, #e0e0e0, transparent);
        margin: 30px 0;
    }

    .section-title {
        color: var(--secondary-color);
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title::before {
        content: '';
        width: 4px;
        height: 24px;
        background: var(--primary-color);
        border-radius: 2px;
    }

    @media (max-width: 768px) {
        .card-body-custom {
            padding: 20px;
        }

        .form-row-custom {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-custom {
            width: 100%;
            justify-content: center;
        }
    }

    /* Loading Animation */
    .btn-custom:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .form-control.error {
        border-color: var(--danger-color);
    }

    .error-message {
        color: var(--danger-color);
        font-size: 12px;
        margin-top: 5px;
        display: none;
    }

    .input-icon {
        position: relative;
    }

    .input-icon i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        pointer-events: none;
    }

    /* Animation */
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

    .card-modern {
        animation: fadeInUp 0.5s ease;
    }
</style>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    
    <div class="page-body">
        <div class="container-fluid">        
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>
                            <i class="fas fa-edit"></i> Edit Driver Assignment
                        </h3>
                    </div>
                    <div class="col-sm-6 p-0">
                        <ol class="breadcrumb float-right">
                            <li class="breadcrumb-item">
                                <a href="index.html"><i class="fas fa-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">Dashboard</li>
                            <li class="breadcrumb-item">
                                <a href="<?= base_url(); ?>/Admin/Driver_Assignment">Driver Assignment</a>
                            </li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid default-dashboard">
            <?php 
            // Extract assignment data
            $das = null;
            foreach($drivers_asignment as $assignment) {
                $das = $assignment;
                break;
            }
            ?>

            <?php if ($das): ?>
            <div class="form-section">
                <div class="card-modern">
                    <div class="card-header-custom">
                        <i class="fas fa-user-edit"></i>
                        Update Driver Assignment Details
                    </div>
                    
                    <div class="card-body-custom">
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i>
                            <div class="info-box-text">
                                <strong>Assignment ID: #<?= $das->id ?></strong>
                                <span>Modify the driver assignment details below and click Update to save changes</span>
                            </div>
                        </div>

                        <form id="editForm" action="<?= base_url(); ?>/Admin/update_driver_asignment" method="post">
                            <input type="hidden" name="id" value="<?= $das->id ?>">
                            
                            <!-- Vehicle and Driver Section -->
                            <div class="section-title">
                                <i class="fas fa-truck"></i> Vehicle & Driver Information
                            </div>
                            
                            <div class="form-row-custom">
                                <div class="form-group">
                                    <label for="vehicle_no" class="required-field">
                                        <i class="fas fa-truck"></i> Vehicle Number
                                    </label>
                                    <select class="form-control" name="vehicle_no" id="vehicle_no" required>
                                        <option value="">Select Vehicle Number</option>
                                        <?php foreach ($vehicles as $vehicle): ?>
                                            <option value="<?= $vehicle->id; ?>" <?= ($das->vehicle_no == $vehicle->id) ? 'selected' : ''; ?>>
                                                <?= $vehicle->vehicle_no; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="error-message">Please select a vehicle</div>
                                </div>

                                <div class="form-group">
                                    <label for="driver" class="required-field">
                                        <i class="fas fa-user-tie"></i> Driver Name
                                    </label>
                                    <select class="form-control" name="driver" id="single1" required>
                                        <option value="">Select Driver</option>
                                        <?php foreach ($drivers as $driver): ?>
                                            <option value="<?= $driver->id; ?>" <?= ($das->driver == $driver->id) ? 'selected' : ''; ?>>
                                                <?= esc($driver->name); ?> (<?= esc($driver->staff_code); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="error-message">Please select a driver</div>
                                </div>
                            </div>

                            <hr class="section-divider">

                            <!-- Opening Details Section -->
                            <div class="section-title">
                                <i class="fas fa-play-circle"></i> Opening Details
                            </div>
                            
                            <div class="form-row-custom">
                                <div class="form-group">
                                    <label for="from_date" class="required-field">
                                        <i class="fas fa-calendar-alt"></i> From Date
                                    </label>
                                    <input type="date" class="form-control" name="from_date" id="from_date" value="<?= $das->from_date ?>" required>
                                    <div class="error-message">Please select a from date</div>
                                </div>

                                <div class="form-group">
                                    <label for="opening_hsd" class="required-field">
                                        <i class="fas fa-gas-pump"></i> Opening HSD (Liters)
                                    </label>
                                    <input type="number" step="0.01" class="form-control" name="opening_hsd" id="opening_hsd" value="<?= $das->opening_hsd ?>" placeholder="0.00" required>
                                    <div class="error-message">Please enter opening HSD</div>
                                </div>

                                <div class="form-group">
                                    <label for="opening_km" class="required-field">
                                        <i class="fas fa-tachometer-alt"></i> Opening KM
                                    </label>
                                    <input type="number" step="0.01" class="form-control" name="opening_km" id="opening_km" value="<?= $das->opening_km ?>" placeholder="0.00" required>
                                    <div class="error-message">Please enter opening KM</div>
                                </div>
                            </div>

                            <hr class="section-divider">

                            <!-- Closing Details Section -->
                            <div class="section-title">
                                <i class="fas fa-stop-circle"></i> Closing Details
                            </div>
                            
                            <div class="form-row-custom">
                                <div class="form-group">
                                    <label for="to_date">
                                        <i class="fas fa-calendar-check"></i> To Date
                                    </label>
                                    <input type="date" class="form-control" name="to_date" id="to_date" value="<?= $das->to_date ?>">
                                </div>

                                <div class="form-group">
                                    <label for="closing_hsd">
                                        <i class="fas fa-gas-pump"></i> Closing HSD (Liters)
                                    </label>
                                    <input type="number" step="0.01" class="form-control" name="closing_hsd" id="closing_hsd" value="<?= $das->closing_hsd ?>" placeholder="0.00">
                                </div>

                                <div class="form-group">
                                    <label for="closing_km">
                                        <i class="fas fa-tachometer-alt"></i> Closing KM
                                    </label>
                                    <input type="number" step="0.01" class="form-control" name="closing_km" id="closing_km" value="<?= $das->closing_km ?>" placeholder="0.00">
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="form-actions">
                                <button type="submit" class="btn btn-custom btn-primary-custom" id="submitBtn">
                                    <i class="fas fa-save"></i> Update Assignment
                                </button>
                                <a href="<?= base_url(); ?>/Admin/Driver_Assignment" class="btn btn-custom btn-secondary-custom">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> 
                    No driver assignment data found!
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include("footer.php"); ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editForm');
    const submitBtn = document.getElementById('submitBtn');

    // Form validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            const errorMsg = field.parentElement.querySelector('.error-message');
            
            if (!field.value.trim()) {
                field.classList.add('error');
                if (errorMsg) errorMsg.style.display = 'block';
                isValid = false;
            } else {
                field.classList.remove('error');
                if (errorMsg) errorMsg.style.display = 'none';
            }
        });

        // Date validation
        const fromDate = document.getElementById('from_date').value;
        const toDate = document.getElementById('to_date').value;
        
        if (toDate && fromDate && new Date(toDate) < new Date(fromDate)) {
            e.preventDefault();
            alert('To Date cannot be earlier than From Date');
            document.getElementById('to_date').classList.add('error');
            isValid = false;
        }

        // Closing KM validation
        const openingKm = parseFloat(document.getElementById('opening_km').value) || 0;
        const closingKm = parseFloat(document.getElementById('closing_km').value) || 0;
        
        if (closingKm && closingKm < openingKm) {
            e.preventDefault();
            alert('Closing KM cannot be less than Opening KM');
            document.getElementById('closing_km').classList.add('error');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
        } else {
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        }
    });

    // Clear error on input
    const formControls = form.querySelectorAll('.form-control');
    formControls.forEach(control => {
        control.addEventListener('input', function() {
            this.classList.remove('error');
            const errorMsg = this.parentElement.querySelector('.error-message');
            if (errorMsg) errorMsg.style.display = 'none';
        });
    });

    // Auto-calculate consumed values (optional enhancement)
    const openingHsd = document.getElementById('opening_hsd');
    const closingHsd = document.getElementById('closing_hsd');
    const openingKm = document.getElementById('opening_km');
    const closingKm = document.getElementById('closing_km');

    function calculateAndValidate() {
        // Add visual feedback if closing values are entered
        if (closingHsd.value && openingHsd.value) {
            const consumed = (parseFloat(closingHsd.value) - parseFloat(openingHsd.value)).toFixed(2);
            closingHsd.title = `HSD Consumed: ${consumed} liters`;
        }

        if (closingKm.value && openingKm.value) {
            const distance = (parseFloat(closingKm.value) - parseFloat(openingKm.value)).toFixed(2);
            closingKm.title = `Distance Traveled: ${distance} km`;
        }
    }

    [openingHsd, closingHsd, openingKm, closingKm].forEach(input => {
        input.addEventListener('input', calculateAndValidate);
    });

    // Prevent form resubmission on page refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
});

// Confirmation before leaving page with unsaved changes
let formChanged = false;
document.getElementById('editForm').addEventListener('change', function() {
    formChanged = true;
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
    }
});

document.getElementById('editForm').addEventListener('submit', function() {
    formChanged = false;
});
</script>