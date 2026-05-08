<?php include("header.php"); ?>

<style>
    .upload-zone {
        border: 2px dashed #7366ff;
        border-radius: 15px;
        padding: 40px 20px;
        text-align: center;
        background: #f8f9ff;
        transition: all 0.3s ease;
        position: relative;
        cursor: pointer;
    }
    .upload-zone:hover {
        background: #f0f1ff;
        border-color: #5141ff;
    }
    .upload-zone i {
        font-size: 50px;
        color: #7366ff;
        margin-bottom: 15px;
    }
    .upload-zone input[type="file"] {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0;
        cursor: pointer;
    }
    .instruction-icon {
        width: 40px;
        height: 40px;
        background: #7366ff1a;
        color: #7366ff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin-right: 15px;
    }
    .format-badge {
        font-size: 0.8rem;
        padding: 5px 12px;
        border-radius: 20px;
        background: #eee;
        margin: 5px;
        display: inline-block;
    }
</style>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>

    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h3><i class="fa fa-cloud-upload mr-2 text-primary"></i>Bulk Attendance Upload</h3>
                        <p class="text-muted mt-1">Upload multiple attendance records via Excel</p>
                    </div>
                </div>
            </div>

            <?php if (session()->getFlashdata('msg')): ?>
                <div class="alert alert-light-success border-left-success outline-2x fade show" role="alert">
                    <i class="fa fa-check-circle mr-2"></i><?= session()->getFlashdata('msg'); ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-light-danger border-left-danger outline-2x fade show" role="alert">
                    <i class="fa fa-times-circle mr-2"></i><?= session()->getFlashdata('error'); ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Upload Section -->
                <div class="col-xl-5 col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white pb-0">
                            <h5>Upload Your File</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= base_url('admin/attendance/bulk-store'); ?>" enctype="multipart/form-data">
                                <?= csrf_field(); ?>

                                <div class="upload-zone mb-4" id="dropzone">
                                    <i class="fa fa-file-excel-o"></i>
                                    <p class="mb-0 font-weight-bold" id="file-name">Click or Drag & Drop Excel File</p>
                                    <small class="text-muted">Only .xlsx or .xls accepted</small>
                                    <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls" required onchange="updateFileName(this)">
                                </div>

                                <div class="form-group mb-4">
                                    <div class="p-3 bg-light rounded d-flex align-items-center" style="border: 1px solid #e0e0e0; transition: border-color 0.3s ease;">
                                        <div class="form-check d-flex align-items-center m-0">
                                            <input class="form-check-input" id="confirm_upload" name="confirm_upload" type="checkbox" required style="width: 20px; height: 20px; cursor: pointer; border-radius: 5px;">
                                            <label for="confirm_upload" class="form-check-label mb-0 text-dark ml-3" style="cursor: pointer; font-weight: 600; font-size: 0.95rem;">
                                                I confirm that the data is correctly formatted
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block btn-lg shadow-sm">
                                    <i class="fa fa-upload mr-2"></i>Process & Upload
                                </button>
                            </form>

                            <div class="mt-4 pt-3 border-top">
                                <p class="text-muted mb-3"><i class="fa fa-info-circle mr-1"></i> Quick Links:</p>
                                <a href="<?= base_url('admin/attendance/download-template'); ?>" class="btn btn-outline-success btn-sm mr-2 mb-2">
                                    <i class="fa fa-download mr-1"></i>Download Sample Template
                                </a>
                                <a href="<?= base_url('admin/attendance'); ?>" class="btn btn-outline-secondary btn-sm mb-2">
                                    <i class="fa fa-list mr-1"></i>Back to Attendance List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instructions Section -->
                <div class="col-xl-7 col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white pb-0">
                            <h5>Preparation Guide</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex mb-4">
                                <div class="instruction-icon"><i class="fa fa-columns"></i></div>
                                <div>
                                    <h6 class="mb-1">Required Columns</h6>
                                    <p class="small text-muted mb-2">Ensure your Excel file has these columns in order:</p>
                                    <span class="format-badge"><b>A:</b> Staff ID</span>
                                    <span class="format-badge"><b>B:</b> Date (YYYY-MM-DD)</span>
                                    <span class="format-badge"><b>C:</b> Status</span>
                                    <span class="format-badge"><b>D:</b> Check-in (HH:MM)</span>
                                    <span class="format-badge"><b>E:</b> Check-out (HH:MM)</span>
                                    <span class="format-badge"><b>F:</b> Notes</span>
                                    <span class="format-badge"><b>G:</b> Leave Type</span>
                                </div>
                            </div>

                            <div class="d-flex mb-4">
                                <div class="instruction-icon"><i class="fa fa-check-square-o"></i></div>
                                <div>
                                    <h6 class="mb-1">Valid Status Values</h6>
                                    <p class="small text-muted mb-2">Status column must contain exactly these values:</p>
                                    <span class="badge badge-success">Present</span>
                                    <span class="badge badge-danger">Absent</span>
                                    <span class="badge badge-warning">Leave</span>
                                    <span class="badge badge-info">Half-day</span>
                                    <span class="badge badge-dark">Sick-leave</span>
                                </div>
                            </div>

                            <div class="d-flex">
                                <div class="instruction-icon"><i class="fa fa-calendar-check-o"></i></div>
                                <div>
                                    <h6 class="mb-1">Example Data Row</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mt-2 small bg-light text-dark">
                                             <thead class="bg-white text-dark">
                                                 <tr>
                                                     <th class="text-dark">Staff ID</th>
                                                     <th class="text-dark">Date</th>
                                                     <th class="text-dark">Status</th>
                                                     <th class="text-dark">Check-in</th>
                                                     <th class="text-dark">Check-out</th>
                                                     <th class="text-dark">Notes</th>
                                                 </tr>
                                             </thead>
                                             <tbody class="text-dark">
                                                 <tr>
                                                     <td class="text-dark">123</td>
                                                     <td class="text-dark">2026-04-14</td>
                                                     <td class="text-dark">Present</td>
                                                     <td class="text-dark">09:00</td>
                                                     <td class="text-dark">18:00</td>
                                                     <td class="text-dark">Bulk record</td>
                                                 </tr>
                                             </tbody>
                                         </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning-light shadow-sm">
                        <div class="media">
                            <i class="fa fa-warning mr-3 mt-1"></i>
                            <div class="media-body">
                                <p class="mb-0 small"><b>Important:</b> Duplicate entries for the same staff on the same date will be automatically skipped to prevent data conflicts.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateFileName(input) {
        var fileName = input.files[0].name;
        document.getElementById('file-name').innerHTML = '<i class="fa fa-check-circle text-success mr-2"></i>' + fileName;
        document.getElementById('dropzone').style.background = '#e7ffeb';
        document.getElementById('dropzone').style.borderColor = '#51bb25';
    }
</script>

<?php include("footer.php"); ?>

