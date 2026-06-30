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
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label for="status">Status</label>
                                <select class="form-control-custom" name="status" id="status">
                                    <option value="">All</option>
                                    <option value="deposited" <?= isset($date['status']) && $date['status'] == 'deposited' ? 'selected' : '' ?>>Deposited</option>
                                    <option value="not_deposited" <?= isset($date['status']) && $date['status'] == 'not_deposited' ? 'selected' : '' ?>>Not Deposited</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="btn-group-custom">
                        <button type="submit" class="btn-custom btn-primary-custom"><i class="fa fa-filter"></i> Apply Filters</button>
                        <button type="button" onclick="exportExcel()" class="btn-custom btn-success-custom"><i class="fa fa-file-excel-o"></i> Export Excel</button>
                        <button type="button" onclick="exportPDF()" class="btn-custom btn-danger-custom" style="background-color: #dc3545; color: white;"><i class="fa fa-file-pdf-o"></i> Export PDF</button>
                    </div>
                </form>
            </div>
            <div class="table-card">
                <div class="row mb-3 align-items-center">
                    <!-- Left: Search -->
                    <div class="col-md-6">
                        <input type="text"
                            id="tableSearch"
                            class="form-control"
                            placeholder="🔍 Search in table...">
                    </div>

                    <!-- Right: Records per page -->
                    <div class="col-md-6 d-flex justify-content-end align-items-center gap-2">
                        <label for="per_page" class="mb-0">Records per page:</label>
                        <select id="per_page"
                                class="form-control"
                                onchange="changePerPage()"
                                style="width:120px">
                            <option value="10" <?= $records_per_page == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $records_per_page == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $records_per_page == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $records_per_page == 100 ? 'selected' : '' ?>>100</option>
                            <option value="all" <?= $records_per_page == 'all' ? 'selected' : '' ?>>Show All</option>
                        </select>
                    </div>
                </div>


                <!-- Second Row: Buttons -->
                <div class="row mb-3" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="col-md-6">
                        <button type="button" class="btn-custom btn-success-custom" onclick="bulkUpdateVouchers()">Save Selected</button>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn-custom btn-primary-custom" onclick="addToPayment()">Add to Payment</button>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table id="myTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
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
                            <?php $inPayment = ! empty($v->in_payment); ?>
                            <tr data-id="<?= $v->id; ?>" class="<?= $inPayment ? 'voucher-in-payment' : '' ?>">
                                <td>
                                    <input type="checkbox"
                                           class="voucher-checkbox"
                                           value="<?= $v->id; ?>"
                                           <?= $inPayment ? 'disabled title="Already added to payment"' : '' ?>>
                                </td>
                                <td><?= $i++; ?></td>
                                <td><?= $v->party_name ?? 'N/A'; ?></td>
                                <td>
                                    <?= $v->group_code; ?>
                                    <?php if ($inPayment): ?>
                                        <span class="badge bg-secondary ms-1">In Payment</span>
                                    <?php endif; ?>
                                </td>
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

    function bulkUpdateVouchers() {
        const selected = [];
        document.querySelectorAll('.voucher-checkbox:checked').forEach(cb => {
            let row = cb.closest('tr');
            selected.push({
                voucher_id: cb.value,
                deposited_by: row.querySelector('.deposited_by').value,
                deposit_date: row.querySelector('.deposit_date').value,
                deposit_place: row.querySelector('.deposit_place').value
            });
        });

        if (selected.length === 0) {
            alert('Please select at least one voucher.');
            return;
        }

        if (!confirm(`Are you sure you want to update ${selected.length} vouchers?`)) return;

        $.ajax({
            url: "<?= base_url('Admin/bulkUpdateVoucherDeposit') ?>",
            type: "POST",
            data: {
                vouchers: selected,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            success: function(r) {
                if (r.status === 'success') {
                    alert('Bulk update successful');
                    location.reload();
                } else {
                    alert('Error: ' + r.message);
                }
            },
            error: function() {
                alert('Error processing request.');
            }
        });
    }

    // Select All functionality (skip vouchers already in payment)
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.voucher-checkbox:not(:disabled)');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    // Add to Payment Function
    function addToPayment() {
        const selected = [];
        document.querySelectorAll('.voucher-checkbox:checked:not(:disabled)').forEach(cb => {
            selected.push(cb.value);
        });

        if (selected.length === 0) {
            alert('Please select at least one voucher that is not already in payment.');
            return;
        }

        if (!confirm('Are you sure you want to add selected vouchers to payment?')) return;

        const btn = document.querySelector('button[onclick="addToPayment()"]');
        if (btn) btn.disabled = true;

        $.ajax({
            url: "<?= base_url('Admin/addToPayment') ?>",
            type: "POST",
            dataType: 'json',
            data: {
                voucher_ids: selected,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            success: function(r) {
                if (btn) btn.disabled = false;
                if (r.status === 'success') {
                    alert('Successfully added to payment! PO Number: ' + (r.po_number || 'N/A'));
                    location.reload();
                    return;
                }

                alert('Error: ' + (r.message || 'Failed to add vouchers to payment.'));
                if (r.duplicate_voucher_ids && r.duplicate_voucher_ids.length) {
                    location.reload();
                }
            },
            error: function(xhr) {
                if (btn) btn.disabled = false;
                let message = 'Error processing request.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                alert(message);
            }
        });
    }
    function exportExcel() {
        let from_date = $('#from_date').val();
        let to_date = $('#to_date').val();
        let party = $('#party').val();
        let status = $('#status').val();

        let url = "<?= base_url('Admin/exportDepositExcel') ?>?" + 
                  "from_date=" + from_date + 
                  "&to_date=" + to_date + 
                  "&party=" + (party || '') + 
                  "&status=" + (status || '');
        
        window.location.href = url;
    }

    function exportPDF() {
        let from_date = $('#from_date').val();
        let to_date = $('#to_date').val();
        let party_text = $('#party option:selected').text();
        let title = 'Deposit Report';
        if (from_date && to_date) title += ' (' + from_date + ' to ' + to_date + ')';
        if ($('#party').val()) title += ' - Party: ' + party_text;

        printTable('myTable', title);
    }

    function printTable(tableId, title) {
        let table = document.getElementById(tableId).cloneNode(true);
        // Initialize totals
        let totals = {
            challans: 0,
            amount: 0
        };

        let rows = table.querySelectorAll('tr');
        rows.forEach(row => {
            if (row.cells.length > 0) {
                let inputs = row.querySelectorAll('input:not([type="hidden"]), select');
                inputs.forEach(input => {
                    let val = '';
                    if (input.tagName === 'SELECT') {
                        val = input.options[input.selectedIndex].text;
                        if (val === 'Select User') val = '-';
                    } else {
                        val = input.value;
                    }
                    let span = document.createElement('span');
                    span.textContent = val || '-';
                    input.parentNode.replaceChild(span, input);
                });

                if (row.parentElement.tagName === 'TBODY') {
                    let cells = row.cells;
                    // Indices (before deletion): 4: No of Challan, 5: Total Net Amount
                    totals.challans += parseFloat(cells[4].innerText.replace(/,/g, '')) || 0;
                    totals.amount += parseFloat(cells[5].innerText.replace(/,/g, '')) || 0;
                }

                row.deleteCell(-1); // Remove Action
                row.deleteCell(9);  // Remove Challan Receipt upload column (was index 9)
                
                if (row.parentElement.tagName === 'THEAD') {
                    row.deleteCell(0); // Remove Select All checkbox header
                } else {
                    row.deleteCell(0); // Remove Checkbox col
                }
            }
        });

        // Add Totals Row
        let tfoot = table.createTFoot();
        let footerRow = tfoot.insertRow(0);
        footerRow.style.fontWeight = 'bold';
        footerRow.style.backgroundColor = '#f8f9fa';

        for (let j = 0; j < 8; j++) {
            let cell = footerRow.insertCell(j);
            cell.style.border = '1px solid #dee2e6';
            cell.style.padding = '2px 4px';
            
            if (j === 2) cell.innerText = 'TOTAL:';
            else if (j === 3) cell.innerText = totals.challans;
            else if (j === 4) cell.innerText = totals.amount.toFixed(2);
        }

        let printWindow = window.open('', '', 'height=700,width=1200');
        printWindow.document.write('<html><head><title>' + title + '</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
        printWindow.document.write('<style>');
        printWindow.document.write('@page { size: landscape; margin: 10mm; }');
        printWindow.document.write('body { font-family: Arial, sans-serif; padding: 10px; }');
        printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: auto; }');
        printWindow.document.write('th, td { border: 1px solid #dee2e6; padding: 2px 4px; font-size: 9px; text-align: left; word-break: break-word; color: #000 !important; }');
        printWindow.document.write('th, td, span, div, b, strong { color: #000 !important; -webkit-text-fill-color: #000 !important; opacity: 1 !important; }');
        printWindow.document.write('th { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; font-weight: bold; }');
        printWindow.document.write('tr:nth-child(even) { background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; }');
        printWindow.document.write('.badge { background-color: transparent !important; color: #000 !important; border: 1px solid #dee2e6 !important; padding: 1px 2px !important; display: inline-block; }');
        printWindow.document.write('@media print { .no-print { display: none; } * { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }');
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<div class="text-center" style="text-align: center;">');
        printWindow.document.write('<h2>' + title + '</h2>');
        printWindow.document.write('<p>Generated on: ' + new Date().toLocaleString() + '</p>');
        printWindow.document.write('</div>');
        printWindow.document.write(table.outerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        
        setTimeout(function() {
            printWindow.print();
        }, 1000);
    }
</script>
<style>
    .modal-lg { max-width: 80% !important; }
    #challanGallery img { cursor: pointer; transition: transform 0.2s; }
    #challanGallery img:hover { transform: scale(1.05); }
    tr.voucher-in-payment { background-color: #f8f9fa; }
    tr.voucher-in-payment .voucher-checkbox:disabled { cursor: not-allowed; }
</style>
<?php include("footer.php"); ?>
