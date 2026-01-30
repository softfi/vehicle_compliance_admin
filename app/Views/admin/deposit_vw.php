<?php include("header.php"); ?>
<?php
$records_per_page = isset($_GET['per_page']) && $_GET['per_page'] === 'all' ? 'all' : (isset($records_per_page) ? $records_per_page : 10);
$current_page = isset($current_page) ? $current_page : 1;
$total_records = isset($vouchers) ? count($vouchers) : 0;
if ($records_per_page === 'all') { $total_pages = 1; $current_page = 1; } else { $total_pages = ceil($total_records / $records_per_page); }
?>
<div class="page-body-wrapper voucher-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="filter-card">
                <h5>📋 Deposit View</h5>
                <form method="get" action="<?php echo base_url(); ?>/Admin/Deposit">
                    <?php $default_from_date = $date['from_date'] ?? date('Y-m-01'); $default_to_date = $date['to_date'] ?? date('Y-m-d'); ?>
                    <input type="hidden" name="per_page" id="hidden_per_page" value="<?= $records_per_page ?>">
                    <div class="row">
                        <div class="col-md-3"><div class="form-group-custom"><label for="from_date">From Date</label><input type="date" id="from_date" name="from_date" class="form-control-custom" value="<?= $default_from_date; ?>" /></div></div>
                        <div class="col-md-3"><div class="form-group-custom"><label for="to_date">To Date</label><input type="date" id="to_date" name="to_date" class="form-control-custom" value="<?= $default_to_date; ?>" /></div></div>
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label for="party">Party</label>
                                <select class="form-control-custom select2-search" name="party" id="party">
                                    <option value="">Select Party</option>
                                    <?php foreach ($vendors as $v_item): ?>
                                        <option value="<?= $v_item->id; ?>" <?= isset($date['party']) && $date['party'] == $v_item->id ? 'selected' : '' ?>><?= $v_item->name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <!-- <div class="col-md-3">
                            <div class="form-group-custom">
                                <label for="voucher_no">Voucher No</label>
                                <select class="form-control-custom select2-search" name="voucher_no" id="voucher_no">
                                    <option value="">Select Voucher</option>
                                    <?php if(isset($voucher_list)): foreach ($voucher_list as $vl): ?>
                                        <option value="<?= $vl->group_code; ?>" <?= isset($date['voucher_no']) && $date['voucher_no'] == $vl->group_code ? 'selected' : '' ?>>
                                            <?= $vl->group_code; ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                        </div> -->
                    </div>
                    <div class="btn-group-custom"><button type="submit" class="btn-custom btn-primary-custom"><i class="fa fa-filter"></i> Apply Filters</button><a href="#" id="download_excel" class="btn-custom btn-success-custom"><i class="fa fa-download"></i> Download Excel</a></div>
                </form>
            </div>
            <div class="table-card">
                <div class="table-controls"><div class="records-per-page"><label for="per_page">Records per page:</label><select id="per_page" class="form-control-custom" onchange="changePerPage()"><option value="10" <?= $records_per_page == 10 ? 'selected' : '' ?>>10</option><option value="25" <?= $records_per_page == 25 ? 'selected' : '' ?>>25</option><option value="50" <?= $records_per_page == 50 ? 'selected' : '' ?>>50</option><option value="100" <?= $records_per_page == 100 ? 'selected' : '' ?>>100</option><option value="all" <?= $records_per_page == 'all' ? 'selected' : '' ?>>Show All</option></select></div></div>
                <div class="row mb-3"><div class="col-md-4"><input type="text" id="tableSearch" class="form-control" placeholder="🔍 Search in table..."></div></div>
                <div class="table-wrapper">
                    <table id="myTable">
                        <thead>
                            <tr>
                                <th>Sl No</th>
                                <th>Party Name</th>
                                <th>Voucher No</th>
                                <th>No of Challan</th>
                                <th>Total Net Amount</th>
                                <th>Deposited by (Drop Down)</th>
                                <th>Deposited on</th>
                                <th>Deposit Place</th>
                                <th>Challan Receipt upload</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($vouchers as $v): ?>
                            <tr data-id="<?= $v->id; ?>">
                                <td><?= $i++; ?></td>
                                <td><?= $v->party_name ?? 'N/A'; ?></td>
                                <td><?= $v->group_code; ?></td>
                                <td><?= $v->challan_count; ?></td>
                                <td><?= number_format($v->total_net_amount, 2); ?></td>
                                <td>
                                    <select class="form-control-custom deposited_by">
                                        <option value="">Select User</option>
                                        <?php foreach ($all_users as $user): ?>
                                            <option value="<?= $user->id; ?>" <?= (isset($v->deposited_by) && $v->deposited_by == $user->id) ? 'selected' : ''; ?>>
                                                <?= $user->full_name; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="date" class="form-control-custom deposit_date" value="<?= $v->deposit_date; ?>"></td>
                                <td><input type="text" class="form-control-custom deposit_place" value="<?= $v->deposit_place; ?>"></td>
                                <td>
                                    <input type="file" class="form-control-custom challan_receipt" accept="image/*" multiple>
                                </td>
                                <td>
                                    <div class="d-flex flex-row gap-2">
                                        <button type="button" class="btn-custom btn-primary-custom" style="white-space: nowrap;" onclick="updateVoucher(this)">Submit</button>
                                        <?php if (!empty($v->receipt_image)): ?>
                                            <button type="button" class="btn btn-sm btn-info" style="white-space: nowrap;" onclick="viewChallans('<?= htmlspecialchars((string)$v->receipt_image); ?>', <?= $v->id; ?>)">🔍 View</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>                            
                </div>
                <div class="records-info">Showing <?= count($vouchers) ?> vouchers</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Viewing Challans -->
<div id="challanModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Challan Receipts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sl No</th>
                                <th>Challan Image Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="challanTableBody">
                            <!-- Rows will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentVoucherId = null;

    // Search functionality
    document.getElementById('tableSearch').addEventListener('input', function() {
        const term = this.value.toLowerCase();
        const rows = document.querySelectorAll('#myTable tbody tr');
        rows.forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    });
    
    // View Challans Function
    function viewChallans(imageData, voucherId) {
        currentVoucherId = voucherId;
        renderGalleryTable(imageData);
        $('#challanModal').modal('show');
    }

    function renderGalleryTable(imageData) {
        let images = [];
        try {
            if (imageData) {
                images = JSON.parse(imageData);
                if (!Array.isArray(images)) images = [imageData];
            }
        } catch (e) {
            images = [imageData];
        }

        let tableBody = $('#challanTableBody');
        tableBody.empty();
        
        if (images.length === 0) {
            tableBody.append('<tr><td colspan="3" class="text-center">No images found</td></tr>');
            return;
        }

        images.forEach((img, index) => {
            tableBody.append(`
                <tr>
                    <td>${index + 1}</td>
                    <td>${img}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('public/assets/uploads/receipts/'); ?>/${img}" target="_blank" class="btn btn-sm btn-primary">View</a>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteChallan('${img}', this)">Delete</button>
                        </div>
                    </td>
                </tr>
            `);
        });
    }

    function deleteChallan(imageName, btn) {
        if (!confirm('Are you sure you want to delete this image?')) return;

        $.ajax({
            url: "<?= base_url('Admin/deleteChallanImage') ?>",
            type: "POST",
            data: {
                voucher_id: currentVoucherId,
                image_name: imageName,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            success: function(r) {
                if (r.status === 'success') {
                    alert('Image deleted successfully');
                    // Update the row in the main table to reflect the new JSON data
                    let mainTableRow = $(`tr[data-id="${currentVoucherId}"]`);
                    let viewBtn = mainTableRow.find('button[onclick^="viewChallans"]');
                    
                    if (r.new_data) {
                        viewBtn.attr('onclick', `viewChallans('${r.new_data.replace(/'/g, "\\'")}', ${currentVoucherId})`);
                        renderGalleryTable(r.new_data);
                    } else {
                        // If no images left, hide the View button in the main table and modal
                        viewBtn.remove();
                        $('#challanModal').modal('hide');
                    }
                } else {
                    alert('Error: ' + r.message);
                }
            },
            error: function() {
                alert('Error deleting image.');
            }
        });
    }

    // Change records per page
    function changePerPage() {
        var p = document.getElementById('per_page').value;
        var url = new URL(window.location.href);
        url.searchParams.set('per_page', p);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }
    
    // Update Voucher Function
    function updateVoucher(btn) {
        let row = btn.closest('tr');
        let id = row.getAttribute('data-id');
        let deposited_by = row.querySelector('.deposited_by').value;
        let deposit_date = row.querySelector('.deposit_date').value;
        let deposit_place = row.querySelector('.deposit_place').value;
        let fileInput = row.querySelector('.challan_receipt');
        
        let formData = new FormData();
        formData.append('voucher_id', id);
        formData.append('deposited_by', deposited_by);
        formData.append('deposit_date', deposit_date);
        formData.append('deposit_place', deposit_place);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        if (fileInput.files.length > 0) {
            for (let i = 0; i < fileInput.files.length; i++) {
                formData.append('challan_receipt[]', fileInput.files[i]);
            }
        }

        $.ajax({
            url: "<?= base_url('Admin/updateVoucherDeposit') ?>",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(r) {
                if (r.status === 'success') {
                    alert('Updated successfully');
                    location.reload(); // Reload to show new images
                } else {
                    alert('Error: ' + r.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('Error updating record.');
            }
        });
    }
</script>
<style>
    .modal-lg { max-width: 80% !important; }
    #challanGallery img { cursor: pointer; transition: transform 0.2s; }
    #challanGallery img:hover { transform: scale(1.05); }
</style>
<?php include("footer.php"); ?>
