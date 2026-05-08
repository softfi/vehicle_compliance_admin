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
    border-radius: 12px;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #e2e8f0;
}

.enhanced-table thead th {
    position: sticky !important;
    top: -1px;
    z-index: 105 !important;
    background: #1e293b !important; /* Elegant dark navy */
    color: #f1f5f9 !important;
    font-weight: 500;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.025em;
    padding: 14px 10px;
    border-bottom: 2px solid #334155;
}

/* Horizontal Sticky for Vehicle Number */
.enhanced-table th:nth-child(2) {
    left: 0;
    z-index: 110 !important;
    background: #334155 !important;
}

.enhanced-table td:nth-child(2) {
    position: sticky !important;
    left: 0;
    z-index: 100 !important;
    background: #f8fafc !important;
    box-shadow: 2px 0 5px rgba(0,0,0,0.05);
    font-weight: 600;
    color: #1e293b;
    border-right: 2px solid #e2e8f0;
}

.enhanced-table tbody tr:hover {
    background-color: #f1f5f9 !important;
}

.enhanced-table tbody td {
    padding: 10px 8px;
    font-size: 0.8rem;
    color: #475569;
    border-bottom: 1px solid #f1f5f9;
}

.tyre-serial {
    background: #f1f5f9;
    color: #334155;
    padding: 2px 8px;
    border-radius: 6px;
    font-family: 'Monaco', 'Consolas', monospace;
    font-size: 0.75rem;
    border: 1px solid #e2e8f0;
}

.btn-action, .btn-exchange {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: 500;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-action {
    background: #3b82f6;
    color: white;
}

.btn-action:hover {
    background: #2563eb;
    color: white;
    transform: translateY(-1px);
}

.btn-exchange {
    background: #64748b;
    color: white;
}

.btn-exchange:hover {
    background: #475569;
    color: white;
    transform: translateY(-1px);
}

/* Ensure Sl.No column doesn't overlap */
.enhanced-table th:first-child,
.enhanced-table td:first-child {
    background: #fdfdfd;
}

/* Sticky intersections */
.enhanced-table thead th:nth-child(2) {
    top: -1px;
    left: 0;
    z-index: 120 !important;
}

/* Responsive adjustments */
@media (max-width: 1200px) {
    .enhanced-table td, .enhanced-table th {
        font-size: 0.7rem;
    }
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
    max-height: 75vh;
    overflow: auto;
    border-radius: 15px;
}
.enhanced-table thead th {
    position: sticky !important;
    top: -1px;
    z-index: 105 !important;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Horizontal Sticky for Vehicle Number */
.enhanced-table th:nth-child(2) {
    left: 0;
    z-index: 110 !important;
}

.enhanced-table td:nth-child(2) {
    position: sticky !important;
    left: 0;
    z-index: 100 !important;
    background: #fff !important;
    box-shadow: 4px 0 8px rgba(0,0,0,0.05);
    font-weight: 700;
}

/* Ensure Sl.No doesn't overlap weirdly */
.enhanced-table th:first-child,
.enhanced-table td:first-child {
    position: sticky !important;
    left: -50px; /* Push Sl.No out or keep it non-sticky to prioritize Vehicle No */
    z-index: 1;
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

/* Interactive Tyre Labels Styling */
.vehicle-image-container {
    position: relative;
    padding: 0 !important;
    background: #fff !important;
    border: 1px solid #eee;
    overflow: visible !important;
}

.tyre-label-overlay {
    position: absolute;
    background: #000000;
    color: #ffffff;
    padding: 3px 10px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 800;
    pointer-events: auto;
    cursor: pointer;
    white-space: nowrap;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
    transform: translate(-50%, -50%);
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    z-index: 100;
    border: 1px solid rgba(255,255,255,0.2);
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 90px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tyre-label-overlay.empty-slot {
    background: #fff9e6;
    color: #856404;
    border: 1.5px dashed #ffd966;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    opacity: 1;
}

.tyre-label-overlay.empty-slot:hover {
    background: #fff3cd;
    border-color: #ffc107;
    border-style: solid;
}

.tyre-label-overlay.empty-slot .pos-header {
    background: #000000;
    color: #ffffff;
    font-weight: 800;
}

.tyre-label-overlay:hover {
    transform: translate(-50%, -50%) scale(1.15);
    z-index: 101;
    filter: brightness(1.1);
}

.tyre-label-overlay .pos-header {
    font-size: 6px;
    background: #000000;
    color: #ffffff;
    font-weight: 800;
    margin-bottom: 2px;
    padding: 2px 4px;
    border-radius: 2px;
    width: 100%;
    text-align: center;
    text-transform: uppercase;
}

.tyre-label-overlay:empty {
    display: flex;
}

.tyre-label-overlay.active {
    background: linear-gradient(135deg, #FF5252 0%, #D50000 100%);
    transform: translate(-50%, -50%) scale(1.1);
    box-shadow: 0 0 20px rgba(255, 82, 82, 0.4);
}

.vehicle-row {
    cursor: pointer;
}

.vehicle-row.table-active {
    background: rgba(102, 126, 234, 0.1) !important;
}
.btn-upload-trigger {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border: none;
    border-radius: 30px;
    padding: 15px 35px;
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
    box-shadow: 0 10px 20px rgba(245, 87, 108, 0.4);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: inline-flex;
    align-items: center;
    gap: 12px;
}

.btn-upload-trigger:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 15px 30px rgba(245, 87, 108, 0.6);
    color: white;
}

.btn-upload-trigger i {
    font-size: 1.4rem;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
    40% {transform: translateY(-5px);}
    60% {transform: translateY(-3px);}
}

.modal-full-width {
    width: 95vw !important;
    max-width: 1400px !important;
}

.modal-premium-content {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 30px !important;
    border: 1px solid rgba(255, 255, 255, 0.3);
    overflow: hidden;
}

.modal-header-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 30px;
}

.full-diagram-container {
    background: white;
    border-radius: 20px;
    padding: 40px;
    margin-top: 20px;
    box-shadow: inset 0 0 20px rgba(0,0,0,0.05);
}

.side-indicator {
    position: absolute;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 71, 87, 0.3);
    padding: 6px 20px;
    border-radius: 10px;
    color: #2d3436;
    font-weight: 800;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    z-index: 10;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 10px;
}

.side-indicator::before {
    content: '';
    width: 4px;
    height: 20px;
    background: #ff4757;
    border-radius: 2px;
}

.helper-indicator {
    top: 15px;
    left: 20px;
}

.driver-indicator {
    bottom: 15px;
    left: 20px;
}

/* Select2 Premium Styling */
.select2-container--default .select2-selection--single {
    border-radius: 15px !important;
    border: 2px solid #e9ecef !important;
    height: 50px !important;
    padding: 10px 16px !important;
    transition: all 0.3s ease !important;
    background: white !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 26px !important;
    color: #495057 !important;
    font-weight: 500 !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 48px !important;
    right: 15px !important;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.select2-dropdown {
    border-radius: 15px !important;
    border: none !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    overflow: hidden !important;
    z-index: 9999 !important;
}

.select2-search__field {
    border-radius: 10px !important;
    border: 1px solid #e9ecef !important;
    padding: 8px 12px !important;
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
                            <button class="btn-upload-trigger me-3" uk-toggle="target: #modal-upload-interface">
                                <i class="fas fa-cloud-upload-alt"></i>
                                Excel Upload & Diagram
                            </button>
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

                        <!-- Modal Interface for Upload ONLY -->
                        <div id="modal-upload-interface" uk-modal>
                            <div class="uk-modal-dialog modal-premium-content">
                                <button class="uk-modal-close-default" type="button" uk-close></button>
                                <div class="modal-header-gradient">
                                    <h2 class="uk-modal-title" style="color: white; margin: 0;">
                                        <i class="fas fa-cloud-upload-alt me-2"></i>
                                        Excel Upload Interface
                                    </h2>
                                </div>
                                <div class="uk-modal-body p-4">
                                    <div class="upload-section">
                                        <div class="upload-content">
                                            <h3 class="upload-title">
                                                <i class="fas fa-file-excel me-2"></i>
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
                                                <button type="submit" class="btn btn-gradient w-100 mb-3">
                                                    <i class="fas fa-upload me-2"></i>Upload File
                                                </button>
                                            </form>

                                            <!-- Download Button -->
                                            <button id="downloadExcelModal" class="btn btn-success-gradient w-100">
                                                <i class="fas fa-download me-2"></i>Download Current Data
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="uk-modal-footer uk-text-right">
                                    <button class="uk-button uk-button-default uk-modal-close" type="button">Close</button>
                                </div>
                            </div>
                        </div>

                        <!-- Truck Diagram Section (Restored to Main Page) -->
                        <div class="container-fluid mb-4 p-4">
                            <div class="row justify-content-center">
                                <div class="col-lg-10">
                                    <div class="vehicle-image-container card shadow-sm">
                                        <div id="diagramWrapper" style="position: relative;">
                                            <!-- Side Indicators -->
                                            <div class="side-indicator helper-indicator">
                                                <i class="fas fa-user-friends" style="color: #ff4757;"></i>
                                                Driver Side
                                            </div>
                                            <div class="side-indicator driver-indicator">
                                                <i class="fas fa-user-circle" style="color: #ff4757;"></i>    
                                                Helper Side
                                            </div>

                                            <img src="<?= base_url('uploads/TruckDiagram.jpg'); ?>" loading="lazy" decoding="async"
                                                alt="Vehicle Diagram"
                                                class="img-fluid vehicle-image w-100">
                                            
                                            <!-- Dynamic Overlays - Precision Alignment -->
                                            <div id="lbl-front-right" class="tyre-label-overlay" data-pos="Front Right" style="top: 22%; left: 16%;"><span class="pos-header">Front Right</span></div>
                                            <div id="lbl-front-left"  class="tyre-label-overlay" data-pos="Front Left" style="top: 76%; left: 16%;"><span class="pos-header">Front Left</span></div>
                                            
                                            <div id="lbl-rear1-right" class="tyre-label-overlay" data-pos="Rear1 Right" style="top: 32%; left: 28.5%;"><span class="pos-header">Rear 1 Right</span></div>
                                            <div id="lbl-rear1-left"  class="tyre-label-overlay" data-pos="Rear1 Left" style="top: 66%; left: 28.5%;"><span class="pos-header">Rear 1 Left</span></div>
                                            <div id="lbl-rear2-right" class="tyre-label-overlay" data-pos="Rear2 Right" style="top: 20%; left: 28.5%;"><span class="pos-header">Rear 2 Right</span></div>
                                            <div id="lbl-rear2-left"  class="tyre-label-overlay" data-pos="Rear2 Left" style="top: 78%; left: 28.5%;"><span class="pos-header">Rear 2 Left</span></div>
                                            
                                            <div id="lbl-rear3-right" class="tyre-label-overlay" data-pos="Rear3 Right" style="top: 32%; left: 59.5%;"><span class="pos-header">Rear 3 Right</span></div>
                                            <div id="lbl-rear3-left"  class="tyre-label-overlay" data-pos="Rear3 Left" style="top: 66%; left: 59.5%;"><span class="pos-header">Rear 3 Left</span></div>
                                            <div id="lbl-rear4-right" class="tyre-label-overlay" data-pos="Rear4 Right" style="top: 20%; left: 59.5%;"><span class="pos-header">Rear 4 Right</span></div>
                                            <div id="lbl-rear4-left"  class="tyre-label-overlay" data-pos="Rear4 Left" style="top: 78%; left: 59.5%;"><span class="pos-header">Rear 4 Left</span></div>
                                            
                                            <div id="lbl-rear5-right" class="tyre-label-overlay" data-pos="Rear5 Right" style="top: 32%; left: 72.5%;"><span class="pos-header">Rear 5 Right</span></div>
                                            <div id="lbl-rear5-left"  class="tyre-label-overlay" data-pos="Rear5 Left" style="top: 66%; left: 72.5%;"><span class="pos-header">Rear 5 Left</span></div>
                                            <div id="lbl-rear6-right" class="tyre-label-overlay" data-pos="Rear6 Right" style="top: 20%; left: 72.5%;"><span class="pos-header">Rear 6 Right</span></div>
                                            <div id="lbl-rear6-left"  class="tyre-label-overlay" data-pos="Rear6 Left" style="top: 78%; left: 72.5%;"><span class="pos-header">Rear 6 Left</span></div>
                                            
                                            <div id="lbl-rear7-right" class="tyre-label-overlay" data-pos="Rear7 Right" style="top: 32%; left: 85.5%;"><span class="pos-header">Rear 7 Right</span></div>
                                            <div id="lbl-rear7-left"  class="tyre-label-overlay" data-pos="Rear7 Left" style="top: 66%; left: 85.5%;"><span class="pos-header">Rear 7 Left</span></div>
                                            <div id="lbl-rear8-right" class="tyre-label-overlay" data-pos="Rear8 Right" style="top: 20%; left: 85.5%;"><span class="pos-header">Rear 8 Right</span></div>
                                            <div id="lbl-rear8-left"  class="tyre-label-overlay" data-pos="Rear8 Left" style="top: 78%; left: 85.5%;"><span class="pos-header">Rear 8 Left</span></div>
                                        </div>
                                        <div class="p-3 bg-light text-center border-top">
                                            <h5 class="text-muted mb-1" id="selectedVehicleDisplay">Select a vehicle from table</h5>
                                            <small class="text-primary font-weight-bold">Interactive Tyre Layout</small>
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

                                                $pos_ids = [];
                                                foreach ($vehic['tyer_position'] as $position => $serial_no) {
                                                    $positions[$position] = $serial_no;
                                                    $pos_ids[$position] = $vehic['tyer_ids'][$position] ?? '';
                                                }
                                            ?>
                                                <tr class="vehicle-row" 
                                                    data-veh-no="<?= $vehic['vehicle_no']; ?>"
                                                    <?php foreach($positions as $pos => $serial) {
                                                        $p_key = strtolower(str_replace(' ', '-', $pos));
                                                        echo ' data-' . $p_key . '="' . ($serial ?: '') . '"';
                                                        echo ' data-' . $p_key . '-id="' . ($pos_ids[$pos] ?? '') . '"';
                                                    } ?>>
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
                                                                    <h4 class="mb-0">
                                                                        <i class="fas fa-exchange-alt me-2"></i>
                                                                        Exchange Tyre
                                                                    </h4>
                                                                    <div class="mt-2 mb-4">
                                                                        <span class="badge bg-dark px-3 py-2 fs-6">
                                                                            <i class="fas fa-truck me-2"></i>Vehicle: <?= $vehic['vehicle_no']; ?>
                                                                        </span>
                                                                    </div>
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
                                                                        
                                                                        <div class="mb-3">
                                                                            <label class="form-label">
                                                                                <i class="fas fa-calendar-minus me-2"></i>Replacement Date
                                                                            </label>
                                                                            <input type="date" name="replacement_date" class="form-control form-control-modern" value="<?= date('Y-m-d') ?>">
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

<!-- Unified Quick Tyre Action Modal -->
<div id="modal-quick-action" class="uk-flex-top" uk-modal="bg-close: false; esc-close: false;">
    <div class="uk-modal-dialog modal-modern uk-margin-auto-vertical">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <div class="uk-modal-body">
            <h4 class="mb-0" id="modalQuickTitle">
                <i class="fas fa-tools me-2"></i>Tyre Action
            </h4>
            <div id="modalVehNoWrapper" class="mt-2 mb-4">
                <span class="badge bg-dark px-3 py-2 fs-6">
                    <i class="fas fa-truck me-2"></i>Vehicle: <span id="display_veh_no"></span>
                </span>
            </div>
            
            <form id="quickActionForm" method="post" action="">
                <input type="hidden" name="vehicle_id" id="quick_veh_id"/>
                <input type="hidden" name="tyer_position" id="quick_pos_input" />
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center p-2 border rounded bg-light mb-3">
                        <div>
                            <small class="text-muted d-block">Position:</small>
                            <strong id="display_pos_name" class="text-primary"></strong>
                        </div>
                        <div id="actionTypeToggle" class="btn-group btn-group-sm d-none">
                            <input type="radio" class="btn-check" name="actionType" id="typeExchange" value="exchange" checked>
                            <label class="btn btn-outline-primary" for="typeExchange">Replace</label>
                            
                            <input type="radio" class="btn-check" name="actionType" id="typeBackToStock" value="backtostock">
                            <label class="btn btn-outline-primary" for="typeBackToStock">Back to Stock</label>

                            <input type="radio" class="btn-check" name="actionType" id="typeRotate" value="rotate">
                            <label class="btn btn-outline-primary" for="typeRotate">Rotate</label>
                        </div>
                    </div>
                </div>

                <div id="replaceOldTyreInfo" class="mb-3 d-none">
                    <div class="alert alert-info py-2 small mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Current Tyre: <strong id="currentSerialDisplay"></strong>
                    </div>
                </div>

                <!-- STOCK EXCHANGE SECTION -->
                <div id="stockExchangeFields">
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt me-2"></i>Location
                        </label>
                        <select id="quickLocationSelect" name="location" class="form-control form-control-modern">
                            <option value="">Choose location...</option>
                            <?php foreach ($location as $loc) { ?>
                                <option value="<?= $loc->location_id; ?>"><?= $loc->location_name; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div id="quickLocationDetails" class="mb-3"></div>
                </div>

                <!-- DATE SECTION -->
                <div id="replaceOnlyDate" class="d-none">
                    <div class="col-md-12 mb-3">
                        <label class="form-label" id="quickDateLabel">Replacement Date</label>
                        <input type="date" name="replacement_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <!-- INTERNAL ROTATION SECTION -->
                <div id="internalRotationFields" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-sync-alt me-2"></i>Select Tyre to Swap With
                        </label>
                        <select id="internalTyreSelect" name="source_tyre_id" class="form-control form-control-modern">
                            <option value="">Select position to swap...</option>
                        </select>
                        <div class="form-text small">Selecting a tyre will swap it with the current position.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Replacement Date</label>
                        <input type="date" name="replacement_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <!-- ASSIGN FIELDS -->
                <div id="assignFields" class="d-none">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Assign Date</label>
                        <input type="date" name="asign_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <button class="btn btn-gradient w-100" type="submit">
                    <i class="fas fa-check-circle me-2"></i><span id="btnActionText">Submit</span>
                </button>
            </form>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>

<!-- Enhanced Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize Select2 for Quick Location with Modal Support
        $('#quickLocationSelect').select2({
            placeholder: "Choose location...",
            allowClear: true,
            width: '100%',
            dropdownParent: $('#modal-quick-action .uk-modal-dialog')
        });

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
        $('#downloadExcel, #downloadExcelModal').click(function() {
            const $btn = $(this);
            const originalHtml = $btn.html();
            $btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Downloading...');
            
            setTimeout(() => {
                window.location.href = '<?= base_url("Admin/downloadExcelAsign_tyer") ?>';
                $btn.html(originalHtml);
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

        // Row click interaction for interactive diagram
        $('#vehicleTable tbody').on('click', '.vehicle-row', function() {
            const $row = $(this);
            const vehNo = $row.data('veh-no');
            
            // UI Feedback
            $('.vehicle-row').removeClass('table-active');
            $row.addClass('table-active');
            $('#selectedVehicleDisplay').html('<i class="fas fa-truck me-2"></i>' + vehNo);
            window.currentSelectedVehicleNo = vehNo; // Store vehicle number globally

            // Update Diagram Labels
            const positions = [
                'front-right', 'front-left', 
                'rear1-right', 'rear1-left', 'rear2-right', 'rear2-left',
                'rear3-right', 'rear3-left', 'rear4-right', 'rear4-left',
                'rear5-right', 'rear5-left', 'rear6-right', 'rear6-left',
                'rear7-right', 'rear7-left', 'rear8-right', 'rear8-left'
            ];

            positions.forEach(pos => {
                const serial = $row.data(pos);
                const $lbl = $('#lbl-' + pos);
                const header = $lbl.find('.pos-header').prop('outerHTML');
                
                if (serial && serial !== '' && serial !== '—') {
                    $lbl.html(header + serial).fadeIn(300).removeClass('empty-slot').addClass('active');
                    setTimeout(() => $lbl.removeClass('active'), 1000);
                } else {
                    $lbl.html(header + '<span class="small opacity-50">Empty</span>').fadeIn(300).addClass('empty-slot');
                }
            });
            
            // Set active vehicle ID globally
            const vehId = $row.find('.btn-exchange').attr('href').replace('#modal-center', '');
            window.currentSelectedVehicleId = vehId;
        });

        // Quick Action Interaction from Diagram (Delegated for Modal Support)
        $(document).on('click', '.tyre-label-overlay', function() {
            if (!window.currentSelectedVehicleId) {
                UIkit.notification({message: '<i class="fas fa-exclamation-triangle me-2"></i>Please select a vehicle from the table first', status: 'warning'});
                return;
            }

            const $lbl = $(this);
            const posNameAttr = $lbl.data('pos'); // Raw pos name for DB (Rear1 Right)
            const posDisplay = $lbl.find('.pos-header').text();
            
            // Get serial by removing header text
            let currentSerial = $lbl.text().replace(posDisplay, '').trim();
            if (currentSerial.toLowerCase() === 'empty') currentSerial = '';
            
            // Reset Form
            $('#quickActionForm')[0].reset();
            $('#quickLocationDetails').empty();
            $('#quickLocationSelect').val(null).trigger('change');
            $('#quick_veh_id').val(window.currentSelectedVehicleId);
            $('#quick_pos_input').val(posNameAttr);
            $('#display_pos_name').text(posDisplay);
            $('#display_veh_no').text(window.currentSelectedVehicleNo || 'N/A');

            if (currentSerial && currentSerial !== '') {
                // REPLACE / ROTATE MODE
                $('#modalQuickTitle').html('<i class="fas fa-exchange-alt me-2"></i>Replace / Rotate Tyre');
                $('#btnActionText').text('Confirm Replacement');
                $('#quickActionForm').attr('action', '<?= base_url();?>/Admin/exchange_tyer_data');
                $('#replaceOldTyreInfo').removeClass('d-none');
                $('#currentSerialDisplay').text(currentSerial);
                $('#assignFields').addClass('d-none');
                $('#actionTypeToggle').removeClass('d-none');
                
                // Reset to Stock Exchange by default
                $('#typeExchange').prop('checked', true).trigger('change');

                // Populate Internal Tyres for Rotation
                const $rotationSelect = $('#internalTyreSelect');
                $rotationSelect.html('<option value="">Select position to swap...</option>');
                
                // Get current row from which to pull data
                const $activeRow = $('.vehicle-row.table-active');
                const positions = [
                    'front-right', 'front-left', 'rear1-right', 'rear1-left', 'rear2-right', 'rear2-left', 'rear3-right', 'rear3-left', 'rear4-right', 'rear4-left', 'rear5-right', 'rear5-left', 'rear6-right', 'rear6-left', 'rear7-right', 'rear7-left', 'rear8-right', 'rear8-left'
                ];
                
                positions.forEach(p => {
                    const ser = $activeRow.data(p);
                    const dbId = $activeRow.data(p + '-id');
                    const label = $('#lbl-' + p + ' .pos-header').text();
                    
                    // Don't include currently clicked position or empty positions in rotation list
                    if (p !== posNameAttr.toLowerCase().replace(' ','-') && ser && ser !== '' && ser !== '—') {
                        $rotationSelect.append(`<option value="${dbId}">${label} (${ser})</option>`);
                    }
                });
            } else {
                // ASSIGN MODE
                $('#modalQuickTitle').html('<i class="fas fa-plus-circle me-2"></i>Assign New Tyre');
                $('#btnActionText').text('Assign Tyre');
                $('#quickActionForm').attr('action', '<?= base_url();?>/Admin/update_tyer_data');
                $('#replaceOldTyreInfo').addClass('d-none');
                $('#assignFields').removeClass('d-none');
                $('#actionTypeToggle').addClass('d-none');
                $('#stockExchangeFields').removeClass('d-none');
                $('#replaceOnlyDate').addClass('d-none');
                $('#internalRotationFields').addClass('d-none');
            }

            UIkit.modal('#modal-quick-action').show();
        });

        // Toggle between Replace, Back to Stock, and Internal Rotation
        $('input[name="actionType"]').on('change', function() {
            if (this.value === 'rotate') {
                $('#stockExchangeFields').addClass('d-none');
                $('#replaceOnlyDate').addClass('d-none');
                $('#internalRotationFields').removeClass('d-none');
                $('#quickActionForm').attr('action', '<?= base_url();?>/Admin/rotate_tyre_data');
                $('#btnActionText').text('Confirm Swap / Rotate');
                $('#quickDateLabel').text('Rotation Date');
                $('#quickLocationSelect').attr('required', false);
                $('#internalTyreSelect').attr('required', true);
                window.isBackToStock = false;
            } else if (this.value === 'backtostock') {
                $('#stockExchangeFields').removeClass('d-none');
                $('#replaceOnlyDate').removeClass('d-none');
                $('#internalRotationFields').addClass('d-none');
                $('#quickActionForm').attr('action', '<?= base_url();?>/Admin/backToStock_tyer_data');
                $('#btnActionText').text('Confirm Move to Stock');
                $('#quickDateLabel').text('Date');
                $('#quickLocationSelect').attr('required', true);
                $('#internalTyreSelect').attr('required', false);
                window.isBackToStock = true;
                $('#quickLocationDetails').html('<div class="alert alert-warning py-2 small mt-2"><i class="fas fa-info-circle me-1"></i> Tyre will be moved to the selected location inventory.</div>');
            } else {
                $('#stockExchangeFields').removeClass('d-none');
                $('#replaceOnlyDate').removeClass('d-none');
                $('#internalRotationFields').addClass('d-none');
                $('#quickActionForm').attr('action', '<?= base_url();?>/Admin/exchange_tyer_data');
                $('#btnActionText').text('Confirm Replacement');
                $('#quickDateLabel').text('Replacement Date');
                $('#quickLocationSelect').attr('required', true);
                $('#internalTyreSelect').attr('required', false);
                window.isBackToStock = false;
                // Re-trigger location change if already selected to show tyre list
                if ($('#quickLocationSelect').val()) {
                    $('#quickLocationSelect').trigger('change');
                }
            }
        });

        // Dynamic Location Loading for Quick Modal
        $('#quickLocationSelect').on('change', function() {
            const locationId = this.value;
            const $details = $('#quickLocationDetails');

            if (window.isBackToStock) {
                if (locationId) {
                    $details.html('<div class="alert alert-warning py-2 small mt-2"><i class="fas fa-info-circle me-1"></i> Tyre will be moved to the selected location inventory.</div>');
                } else {
                    $details.empty();
                }
                return;
            }

            if (locationId) {
                $details.html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading Tyres...</div>');
                $.ajax({
                    url: '<?= base_url();?>/Admin/gettyer',
                    method: 'POST',
                    data: { location_id: locationId },
                    dataType: 'html'
                })
                .done(function(response){ 
                    $details.html(response); 
                    
                    // Initialize Select2 for the dynamic tyre select
                    $('#quickTyreSelect').select2({
                        placeholder: "--Select Tyre--",
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#modal-quick-action .uk-modal-dialog')
                    });

                    // Set correct name for the tyre select if returned
                    $details.find('select').attr('name', 'tyer_id').attr('required', true);
                })
                .fail(function(){ $details.html('<div class="alert alert-danger">Error loading data</div>'); });
            } else {
                $details.empty();
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