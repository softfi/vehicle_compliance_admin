<?php include("header.php"); ?>
<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <?php if (!empty($tyer)) { ?>
                        <div class="card mt-4 border-warning">
                            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0"><i class="fas fa-handshake me-2"></i>Vendor Warranty Claim</h4>
                                    <small>Return defective tyre and receive replacement</small>
                                </div>
                                <span class="badge bg-dark"><?= $tyer->tyer_sl_no ?></span>
                            </div>

                            <div class="card-body">
                                <form action="<?php echo base_url(); ?>/Admin/process_vendor_exchange" method="post">
                                    <input type="hidden" value="<?= $tyer->id ?>" name="old_tyre_id" />

                                    <div class="row g-4">
                                        <!-- Old Tyre Info (Read Only) -->
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small">Defective Tyre Serial</label>
                                            <input type="text" class="form-control bg-light text-dark fw-bold" value="<?= $tyer->tyer_sl_no ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small">Current Brand/Model</label>
                                            <input type="text" class="form-control bg-light text-dark fw-bold" value="<?= $tyer->brand_name ?> | <?= $tyer->model ?>" readonly>
                                        </div>

                                        <hr class="my-4">
                                        
                                        <div class="col-md-12">
                                            <h6 class="text-primary mb-3">Replacement Details</h6>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">New Tyre Serial Number <span class="text-danger">*</span></label>
                                            <input type="text" name="new_serial" class="form-control" placeholder="Enter New Serial No." required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">New Brand <span class="text-danger">*</span></label>
                                            <select name="brand_id" class="form-control select2-brand" required>
                                                <option value="">Select Brand</option>
                                                <?php foreach ($brands as $b) { ?>
                                                    <option value="<?= $b->brand_name; ?>" <?= $b->brand_name == $tyer->brand_name ? 'selected' : '' ?>><?= $b->brand_name; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">New Model</label>
                                            <input type="text" name="new_model" class="form-control" value="<?= $tyer->model ?>" placeholder="Enter Model Name">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Replacement Date</label>
                                            <input type="date" name="exchange_date" class="form-control" value="<?= date('Y-m-d') ?>">
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label">Reason for Claim / Remarks</label>
                                            <textarea class="form-control" name="remark" rows="3" placeholder="Explain the defect..."></textarea>
                                        </div>

                                        <div class="col-md-12 mt-4">
                                            <div class="alert alert-info small">
                                                <i class="fas fa-info-circle me-2"></i>
                                                This action will mark the old tyre as 'Returned' and create a new record for the replacement tyre in your stock.
                                            </div>
                                            <button type="submit" class="btn btn-warning w-100 py-2 fw-bold">
                                                <i class="fas fa-check-circle me-2"></i>Process Vendor Exchange
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="alert alert-danger mt-4">Tyre data not found.</div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Select2 CSS & JS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.select2-brand').select2({
                placeholder: "Search Brand",
                allowClear: true,
                width: '100%'
            });
        });
    </script>

    <?php include("footer.php"); ?>
