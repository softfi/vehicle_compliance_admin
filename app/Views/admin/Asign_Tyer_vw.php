<?php include("header.php"); ?>

<style>
/* Custom CSS for enhanced design */
.page-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 20px 0;
}

.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px 0;
    margin: -20px -15px 30px -15px;
    border-radius: 0 0 25px 25px;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="1" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
    opacity: 0.3;
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 15px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.hero-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    margin-bottom: 0;
}

.enhanced-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    border: none;
    transition: all 0.3s ease;
    overflow: hidden;
}

.enhanced-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 50px rgba(0,0,0,0.15);
}

.upload-section {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 30px;
    border-radius: 20px;
    position: relative;
    overflow: hidden;
}

.upload-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

.upload-content {
    position: relative;
    z-index: 2;
}

.upload-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 20px;
}

.file-upload-wrapper {
    position: relative;
    margin-bottom: 20px;
}

.file-upload-input {
    opacity: 0;
    position: absolute;
    z-index: -1;
}

.file-upload-label {
    display: block;
    padding: 15px 20px;
    background: rgba(255,255,255,0.2);
    border: 2px dashed rgba(255,255,255,0.5);
    border-radius: 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.file-upload-label:hover {
    background: rgba(255,255,255,0.3);
    border-color: rgba(255,255,255,0.8);
    transform: scale(1.02);
}

.btn-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 25px;
    padding: 12px 30px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(102,126,234,0.4);
}

.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102,126,234,0.6);
    color: white;
}

.btn-success-gradient {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    border: none;
    border-radius: 25px;
    padding: 12px 30px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(17,153,142,0.4);
}

.btn-success-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(17,153,142,0.6);
    color: white;
}

.vehicle-image-container {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    border-radius: 20px;
    padding: 30px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.vehicle-image-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 30% 70%, rgba(255,255,255,0.3) 0%, transparent 50%);
}

.vehicle-image {
    position: relative;
    z-index: 2;
    max-height: 400px;
    border-radius: 15px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    transition: transform 0.3s ease;
}

.vehicle-image:hover {
    transform: scale(1.05);
}

.enhanced-table {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.enhanced-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.enhanced-table thead th {
    border: none;
    padding: 15px 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-align: center;
}

.enhanced-table tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f1f3f4;
}

.enhanced-table tbody tr:hover {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    transform: scale(1.01);
}

.enhanced-table tbody td {
    padding: 12px 8px;
    vertical-align: middle;
    text-align: center;
    font-size: 0.85rem;
}

.tyre-serial {
    background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
    padding: 5px 10px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.8rem;
    display: inline-block;
    min-width: 60px;
}

.btn-action {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    border: none;
    border-radius: 20px;
    padding: 8px 16px;
    color: white;
    font-weight: 600;
    font-size: 0.8rem;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(250,112,154,0.4);
    color: white;
    text-decoration: none;
}

.btn-exchange {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    border: none;
    border-radius: 20px;
    padding: 8px 16px;
    color: #333;
    font-weight: 600;
    font-size: 0.8rem;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-exchange:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(168,237,234,0.4);
    color: #333;
    text-decoration: none;
}

.alert-modern {
    border-radius: 15px;
    border: none;
    padding: 15px 20px;
    margin-bottom: 20px;
    font-weight: 500;
}

.alert-success-modern {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
}

.alert-warning-modern {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    color: #856404;
}

.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    padding: 25px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.stats-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: pulse 4s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 0.5; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.1); }
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.stats-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.modal-modern {
    border-radius: 20px;
    overflow: hidden;
    border: none;
}

.modal-modern .uk-modal-body {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    padding: 30px;
}

.form-control-modern {
    border-radius: 15px;
    border: 2px solid #e9ecef;
    padding: 12px 16px;
    transition: all 0.3s ease;
}

.form-control-modern:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
    transform: translateY(-1px);
}

.sample-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: white;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.sample-link:hover {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transform: translateX(5px);
}

/* DataTable controls styling */
.table-controls {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.table-controls .dataTables_length select {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    padding: 8px 12px;
    margin: 0 10px;
}

.table-controls .dataTables_filter input {
    border-radius: 25px;
    border: 2px solid #e9ecef;
    padding: 10px 20px;
    width: 250px;
    transition: all 0.3s ease;
}

.table-controls .dataTables_filter input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
    outline: none;
}

.table-controls label {
font-weight: 600;
color: #495057;
margin-bottom: 0;
display: flex;
align-items: center;
}

/* Custom filter box for global search */
.custom-filter-box {
background: #f8f9fa;
padding: 15px;
border-radius: 15px;
margin-bottom: 15px;
box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.custom-filter-box .form-control {
border-radius: 25px;
border: 2px solid #e9ecef;
padding: 10px 16px;
max-width: 320px;
}
.custom-filter-box .btn {
border-radius: 25px;
}

/* Sticky table header inside scroll container */
.table-responsive.custom-scroll {
max-height: 500px;
overflow-y: auto;
}
.enhanced-table thead th {
position: sticky;
top: 0;
z-index: 2;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .enhanced-table {
        font-size: 0.7rem;
    }
    
    .enhanced-table thead th,
    .enhanced-table tbody td {
        padding: 8px 4px;
    }
    
    .upload-section {
        padding: 20px;
    }
    
    .table-controls .dataTables_filter input {
        width: 200px;
    }
    .custom-filter-box{
        background:#f8f9fa;
        padding:15px;
        border-radius:15px;
        margin-bottom:20px;
        box-shadow:0 2px 10px rgba(0,0,0,0.05);
    }
}
</style>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body page-gradient">
        <div class="container-fluid">
            <!-- Hero Section -->
            <div class="hero-section">
                <div class="container-fluid hero-content">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="hero-title">Vehicle Tyre Management</h1>
                            <p class="hero-subtitle">Efficiently manage and track tyre assignments across your fleet</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="stats-card d-inline-block">
                                <div class="stats-number"><?= count($vehicle) ?></div>
                                <div class="stats-label">Total Vehicles</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid default-dashboard">
                <div class="col-sm-12">
                    <div class="enhanced-card">
                        <?php if (session()->getFlashdata('msg')): ?>
                            <div class="alert alert-success-modern">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= session()->getFlashdata('msg') ?>
                            </div>
                        <?php endif; ?>

                        <div class="card-body p-0">
                            <?php if(session()->getFlashdata('msg1')): ?>
                                <div class="alert alert-warning-modern">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <?= session()->getFlashdata('msg1') ?>
                                </div>
                            <?php endif; ?>

                            <!-- Upload and Image Section -->
                            <div class="container-fluid mb-4 p-4">
                                <div class="row align-items-start g-4">
                                    <!-- LEFT SIDE (Form & Buttons) -->
                                    <div class="col-lg-6">
                                        <div class="upload-section">
                                            <div class="upload-content">
                                                <h3 class="upload-title">
                                                    <i class="fas fa-cloud-upload-alt me-2"></i>
                                                    Assign Tyre Excel Upload
                                                </h3>

                                                <p class="mb-3">
                                                    <i class="fas fa-download me-2"></i>
                                                    Download sample Excel:
                                                    <a href="<?= base_url('sampleexcel/tyerasign.xlsx'); ?>" class="sample-link">
                                                        Click here <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                </p>

                                                <!-- Upload Form -->
                                                <form action="<?= base_url('admin/upload_tyer_excel') ?>" method="post" enctype="multipart/form-data">
                                                    <div class="file-upload-wrapper">
                                                        <input type="file"
                                                            class="file-upload-input"
                                                            name="file"
                                                            id="file"
                                                            accept=".xlsx, .xls, .csv"
                                                            required>
                                                        <label for="file" class="file-upload-label">
                                                            <i class="fas fa-file-upload fa-2x mb-2"></i><br>
                                                            Click to select Excel or CSV file
                                                        </label>
                                                    </div>
                                                    <button type="submit" class="btn btn-gradient me-3">
                                                        <i class="fas fa-upload me-2"></i>Upload File
                                                    </button>
                                                </form>

                                                <!-- Download Button -->
                                                <button id="downloadExcel" class="btn btn-success-gradient">
                                                    <i class="fas fa-download me-2"></i>Download Excel
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- RIGHT SIDE (Image) -->
                                    <div class="col-lg-6">
                                        <div class="vehicle-image-container">
                                            <img src="<?= base_url('uploads/TruckDiagram.jpg'); ?>" loading="lazy" decoding="async"
                                                alt="Vehicle Diagram"
                                                class="img-fluid vehicle-image">
                                            <div class="mt-3">
                                                <h5 class="text-muted">Vehicle Tyre Layout</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Enhanced Vehicle Table -->
                            <div class="px-4 pb-4">
                                <!-- Table Controls Section -->
                                <div class="mb-4">
                                    <h4 class="mb-3">
                                        <i class="fas fa-table me-2"></i>
                                        Vehicle Tyre Assignment Table
                                    </h4>
                                </div>

                                <div class="custom-filter-box d-flex flex-wrap align-items-center gap-2">
                                    <input id="vehicleSearchInput" type="text" class="form-control me-2" placeholder="Search vehicles, positions or serials">
                                    <button id="vehicleSearchBtn" class="btn btn-gradient me-2">
                                        <i class="fas fa-search me-1"></i>Search
                                    </button>
                                    <button id="vehicleClearBtn" class="btn btn-exchange">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </button>
                                </div>
                                
                                <div class="table-responsive custom-scroll">
                                    <table id="vehicleTable" class="table enhanced-table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Sl.No</th>
                                                <th>Vehicle Number</th>
                                                <th>Front Right</th>
                                                <th>Front Left</th>
                                                <th>Rear1 Right</th>
                                                <th>Rear1 Left</th>
                                                <th>Rear2 Right</th>
                                                <th>Rear2 Left</th>
                                                <th>Rear3 Right</th>
                                                <th>Rear3 Left</th>
                                                <th>Rear4 Right</th>
                                                <th>Rear4 Left</th>
                                                <th>Rear5 Right</th>
                                                <th>Rear5 Left</th>
                                                <th>Rear6 Right</th>
                                                <th>Rear6 Left</th>
                                                <th>Rear7 Right</th>
                                                <th>Rear7 Left</th>
                                                <th>Rear8 Right</th>
                                                <th>Rear8 Left</th>
                                                <th>Action</th>
                                                <th>Exchange Tyre</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $sr_no = 1;
                                            foreach ($vehicle as $vehic) { 
                                                $positions = [
                                                    'Front Right' => '',
                                                    'Front Left'  => '',
                                                    'Rear1 Right' => '',
                                                    'Rear1 Left'  => '',
                                                    'Rear2 Right' => '',
                                                    'Rear2 Left'  => '',
                                                    'Rear3 Right' => '',
                                                    'Rear3 Left'  => '',
                                                    'Rear4 Right' => '',
                                                    'Rear4 Left'  => '',
                                                    'Rear5 Right' => '',
                                                    'Rear5 Left'  => '',
                                                    'Rear6 Right' => '',
                                                    'Rear6 Left'  => '',
                                                    'Rear7 Right' => '',
                                                    'Rear7 Left'  => '',
                                                    'Rear8 Right' => '',
                                                    'Rear8 Left'  => '',
                                                ];

                                                foreach ($vehic['tyer_position'] as $position => $serial_no) {
                                                    $positions[$position] = $serial_no;
                                                }
                                            ?>
                                                <tr>
                                                    <td><strong><?= $sr_no++; ?></strong></td>
                                                    <td><strong class="text-primary"><?= $vehic['vehicle_no']; ?></strong></td>
                                                    <?php foreach (['Front Right', 'Front Left', 'Rear1 Right', 'Rear1 Left', 'Rear2 Right', 'Rear2 Left', 'Rear3 Right', 'Rear3 Left', 'Rear4 Right', 'Rear4 Left', 'Rear5 Right', 'Rear5 Left', 'Rear6 Right', 'Rear6 Left', 'Rear7 Right', 'Rear7 Left', 'Rear8 Right', 'Rear8 Left'] as $pos): ?>
                                                        <td>
                                                            <?php if ($positions[$pos]): ?>
                                                                <span class="tyre-serial"><?= $positions[$pos]; ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">—</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                    <td>
                                                        <?php if(in_array(25.1,$jobAssign)){ ?>
                                                            <a class="btn-action" href="<?= site_url('admin/Asign_Tyers/'.$vehic['id']); ?>">
                                                                <i class="fas fa-cogs me-1"></i>Assign Tyre
                                                            </a>
                                                        <?php } ?>
                                                    </td>
                                                    <td>
                                                        <?php if(in_array(25.2,$jobAssign)){ ?>
                                                            <a class="btn-exchange" href="#modal-center<?= $vehic['id']; ?>" uk-toggle>
                                                                <i class="fas fa-exchange-alt me-1"></i>Exchange
                                                            </a>
                                                        <?php } ?>
                                                        <div id="modal-center<?= $vehic['id']; ?>" class="uk-flex-top" uk-modal>
                                                            <div class="uk-modal-dialog modal-modern uk-margin-auto-vertical">
                                                                <button class="uk-modal-close-default" type="button" uk-close></button>
                                                                <div class="uk-modal-body">
                                                                    <h4 class="mb-4">
                                                                        <i class="fas fa-exchange-alt me-2"></i>
                                                                        Exchange Tyre for <?= $vehic['vehicle_no']; ?>
                                                                    </h4>
                                                                    <form method="post" action="<?= base_url();?>/Admin/exchange_tyer_data">
                                                                        <input type="hidden" name="vehicle_id" value="<?= $vehic['id']; ?>"/>
                                                                        
                                                                        <div class="mb-3">
                                                                            <label class="form-label">
                                                                                <i class="fas fa-map-marker-alt me-2"></i>Location
                                                                            </label>
                                                                            <select id="locationSelect<?= $vehic['id'] ?>" name="location" class="form-control form-control-modern">
                                                                                <option value="">Select location</option>
                                                                                <?php foreach ($location as $loc) { ?>
                                                                                    <option value="<?= $loc->location_id; ?>"><?= $loc->location_name; ?></option>
                                                                                <?php } ?>
                                                                            </select>
                                                                        </div>
                                                                        
                                                                        <div id="locationDetails<?= $vehic['id'] ?>" class="mb-3"></div>
                                                                        
                                                                        <div class="mb-4"> 
                                                                            <label class="form-label">
                                                                                <i class="fas fa-tire me-2"></i>Tyre Position
                                                                            </label>
                                                                            <select class="form-control form-control-modern" name="tyer_position">
                                                                                <?php
                                                                                $all_positions = [
                                                                                    "Front Right","Front Left",
                                                                                    "Rear1 Right","Rear1 Left",
                                                                                    "Rear2 Right","Rear2 Left",
                                                                                    "Rear3 Right","Rear3 Left",
                                                                                    "Rear4 Right","Rear4 Left",
                                                                                    "Rear5 Right","Rear5 Left",
                                                                                    "Rear6 Right","Rear6 Left",
                                                                                    "Rear7 Right","Rear7 Left",
                                                                                    "Rear8 Right","Rear8 Left"
                                                                                ];
                                                                                foreach ($all_positions as $pos) {
                                                                                    echo "<option value='$pos'>$pos</option>";
                                                                                }
                                                                                ?>
                                                                            </select>                 
                                                                        </div>
                                                                        
                                                                        <button class="btn btn-gradient w-100" type="submit">
                                                                            <i class="fas fa-check me-2"></i>Submit Exchange
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>

<!-- Enhanced Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script>
    $(document).ready(function() {
        // Enhanced file upload interaction
        $('#file').change(function() {
            const fileName = $(this)[0].files[0]?.name;
            if (fileName) {
                $('.file-upload-label').html(`
                    <i class="fas fa-file-check fa-2x mb-2"></i><br>
                    Selected: ${fileName}
                `);
            }
        });

        // Download Excel with animation
        $('#downloadExcel').click(function() {
            $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Downloading...');
            
            setTimeout(() => {
                window.location.href = '<?= base_url("Admin/downloadExcelAsign_tyer") ?>';
                $(this).html('<i class="fas fa-download me-2"></i>Download Excel');
            }, 500);
        });

        // Enhanced AJAX for dynamic location (delegated for performance)
        $(document).on('change', 'select[id^="locationSelect"]', function() {
            const locationId = this.value;
            const vehicId = this.id.replace('locationSelect', '');
            const $details = $('#locationDetails' + vehicId);

            if (locationId) {
                $details.html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
                $.ajax({
                    url: '<?= base_url();?>/Admin/gettyer',
                    method: 'POST',
                    data: { location_id: locationId },
                    dataType: 'html'
                })
                .done(function(response){ $details.html(response); })
                .fail(function(){ $details.html('<div class="alert alert-danger">Error loading data</div>'); });
            } else {
                $details.empty();
            }
        });

        // Initialize DataTable with paging and performance options
        const table = $('#vehicleTable').DataTable({
            dom: 'lrtip',
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1],[10, 25, 50, 100, "All"]],
            deferRender: true,
            responsive: true,
            order: [],
            language: {
                searchPlaceholder: "Search..."
            }
        });

        // Custom search handlers
        $('#vehicleSearchBtn').on('click', function(){
            const term = $('#vehicleSearchInput').val();
            table.search(term).draw();
        });
        // Trigger on Enter key
        $('#vehicleSearchInput').on('keydown', function(e){
            if(e.key === 'Enter'){ e.preventDefault(); $('#vehicleSearchBtn').click(); }
        });
        // Clear search
        $('#vehicleClearBtn').on('click', function(){
            $('#vehicleSearchInput').val('');
            table.search('').draw();
        });

        // Add smooth animations
        $('.enhanced-table tbody tr').hover(
            function() {
                $(this).addClass('table-row-hover');
            },
            function() {
                $(this).removeClass('table-row-hover');
            }
        );

        // Enhanced alert auto-hide
        $('.alert-modern').delay(5000).fadeOut(300);
    });

</script>