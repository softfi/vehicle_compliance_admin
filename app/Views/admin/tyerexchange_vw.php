<?php include("header.php"); ?>
<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <?php if (!empty($tyer_data)) {
                        $tyer = $tyer_data[0];
                        ?>
                        <div class="card mt-4">
                            <div
                                class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0 text-white"><i class="fas fa-sync-alt me-2"></i>Update Tyre Status</h4>
                                    <small class="text-white-50">Manage lifecycle for tyre:
                                        <strong><?= $tyer->tyer_sl_no ?></strong></small>
                                </div>
                                <span class="badge bg-light text-dark"><?= $tyer->brand_name ?> |
                                    <?= $tyer->tyer_type ?></span>
                            </div>

                            <div class="card-body">
                                <form action="<?php echo base_url(); ?>/Admin/update_tyer_report" method="post">
                                    <input type="hidden" value="<?= $tyer->id ?>" name="tyer_id" />

                                    <div class="row g-4">
                                        <div class="col-md-12">
                                            <label class="form-label"><i class="fas fa-info-circle me-2"></i>Update
                                                Status</label>
                                            <select class="form-control w-100" name="status" id="statusSelect">
                                                <option value="4">For Repair</option>
                                                <option value="3">Move to Scrap Yard</option>
                                                <!-- <option value="7">Sold</option> -->
                                            </select>
                                        </div>

                                        <div class="col-md-12" id="sellingDateSection" style="display: none;">
                                            <label class="form-label"><i class="fas fa-calendar-alt me-2"></i>Selling
                                                Date</label>
                                            <input type="date" name="selling_date" class="form-control"
                                                value="<?= date('Y-m-d') ?>">
                                        </div>

                                        <div class="col-md-12" id="vendorSection">
                                            <label class="form-label"><i class="fas fa-store me-2"></i>Select Vendor</label>
                                            <select name="vendor_id" class="form-control select2-vendor w-100">
                                                <option value="">Select Vendor</option>
                                                <?php foreach ($vendor as $ven) { ?>
                                                    <option value="<?= $ven->id; ?>"><?= $ven->name; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label"><i class="fas fa-comment-alt me-2"></i>Remark</label>
                                            <textarea class="form-control w-100" name="remark" rows="3"
                                                placeholder="Additional notes..."></textarea>
                                        </div>

                                        <div class="col-md-12 mt-3">
                                            <button class="btn btn-primary w-100 py-2">
                                                <i class="fas fa-check-circle me-2"></i>Update Tyre Lifecycle
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
            $('.select2-vendor').select2({
                placeholder: "Search and Select Vendor",
                allowClear: true,
                width: '100%'
            });

            // Handle visibility based on selected status
            $('#statusSelect').change(function () {
                var status = $(this).val();

                // Hide Vendor selection when moving to Scrap Yard
                if (status == '3') {
                    $('#vendorSection').slideUp();
                } else {
                    $('#vendorSection').slideDown();
                }

                // Show Selling Date only when Sold is selected
                if (status == '7') {
                    $('#sellingDateSection').slideDown();
                } else {
                    $('#sellingDateSection').slideUp();
                }
            });

            // Trigger immediately to set correct state on page load
            $('#statusSelect').trigger('change');
        });
    </script>

    <?php include("footer.php"); ?>