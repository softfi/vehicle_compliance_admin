<?php include("header.php"); ?>
<div class="page-body-wrapper voucher-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">

            <!-- ✅ FILTER UI (EXACT SAME AS DEPOSIT VIEW) -->
            <div class="filter-card">
                <h5>📋 Payment View</h5>
                <form method="get" action="<?= base_url(); ?>/Admin/Payment">
                    <?php 
                        $default_from_date = $filters['from_date'] ?? date('Y-m-01'); 
                        $default_to_date   = $filters['to_date'] ?? date('Y-m-d'); 
                        $records_per_page  = $_GET['per_page'] ?? 10;
                    ?>
                    <input type="hidden" name="per_page" id="hidden_per_page" value="<?= $records_per_page ?>">

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label>From Date</label>
                                <input type="date" name="from_date" class="form-control-custom" value="<?= $default_from_date ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label>To Date</label>
                                <input type="date" name="to_date" class="form-control-custom" value="<?= $default_to_date ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label>Party</label>
                                <select class="form-control-custom select2-search" name="party">
                                    <option value="">Select Party</option>
                                    <?php foreach ($all_party as $p): ?>
                                        <option value="<?= $p->id ?>" <?= ($filters['party'] ?? '') == $p->id ? 'selected' : '' ?>>
                                            <?= $p->name ?>
                                        </option>
                                    <?php endforeach; ?>
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

            <!-- TABLE -->
            <div class="table-card">

                <div class="table-controls">
                    <div class="records-per-page">
                        <label>Records per page:</label>
                        <select id="per_page" class="form-control-custom" onchange="changePerPage()">
                            <option value="10" <?= $records_per_page==10?'selected':'' ?>>10</option>
                            <option value="25" <?= $records_per_page==25?'selected':'' ?>>25</option>
                            <option value="50" <?= $records_per_page==50?'selected':'' ?>>50</option>
                            <option value="100" <?= $records_per_page==100?'selected':'' ?>>100</option>
                            <option value="all" <?= $records_per_page=='all'?'selected':'' ?>>Show All</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="tableSearch" class="form-control" placeholder="🔍 Search in table...">
                    </div>
                </div>

                <div class="table-wrapper">
                    <table id="myTable">
                        <thead>
                            <tr>
                                <th>Sl No</th>
                                <th>Party Name</th>
                                <!-- <th>PO Number</th>
                                <th>DO Number</th> -->
                                <th>No. of Vouchers</th>
                                <th>No. of Challans</th>
                                <th>Total Amount</th>
                                <th>Received Date</th>
                                <th>Received Amount</th>
                                <th>Adjustment Amount</th>
                                <th>Difference</th>
                                <th>Adjustment Remarks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($payment_vouchers as $index => $v): ?>
                            <tr data-id="<?= $v->id ?>">
                                <td><?= $index + 1 ?></td>
                                <td><?= $v->party_name ?? 'N/A' ?></td>
                                <!-- <td><?= $v->po_number ?></td>
                                <td><?= $v->do_numbers ?></td> -->
                                <td class="text-center">
                                    <?= !empty($v->voucher_ids) ? count(explode(',', $v->voucher_ids)) : 0 ?>
                                </td>
                                <td class="text-center">
                                    <?= $v->total_challans ?? 0 ?>
                                </td>

                                <td class="total_net_amount fw-bold" data-amount="<?= $v->total_net_amount ?>">
                                    <?= number_format($v->total_net_amount,2) ?>
                                </td>

                                <td>
                                    <input type="date" class="form-control-custom received_date"
                                           value="<?= $v->received_date ?>">
                                </td>

                                <td>
                                    <input type="number" step="0.01"
                                           class="form-control-custom received_amount"
                                           value="<?= $v->received_amount ?>">
                                </td>
                                <td>
                                    <input type="text" class="form-control-custom adjustment_amount"
                                           value="<?= $v->adjustment_amount ?>">
                                </td>
                                <td class="difference fw-bold text-danger">0.00</td>
                                <td>
                                    <input type="text" class="form-control-custom adjustment_remarks"
                                           value="<?= $v->adjustment_remarks ?>">
                                </td>

                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn-custom btn-primary-custom updateBtn">Submit</button>
                                        <button class="btn btn-sm btn-info" onclick="viewVoucher(<?= $v->id ?>)">View Voucher</button>
                                        <button class="btn btn-sm btn-warning" onclick="viewChallan(<?= $v->id ?>)">View Challan</button>
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

<!-- View Voucher Modal -->
<div id="viewVoucherModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Payment Voucher</h5>
                <div class="ms-auto me-2">
                    <button type="button" class="btn btn-sm btn-success" onclick="exportVoucherExcelCurrent()">Excel</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="exportVoucherPDF()">PDF</button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Sl No</th>
                                <th>Party Name</th>
                                <th>Voucher No</th>
                                <th>No of Challan</th>
                                <th>Total Net Amount</th>
                                <th>Deposited by</th>
                                <th>Deposited on</th>
                                <th>Deposit Place</th>
                                <!-- <th>Challan Receipt</th> -->
                            </tr>
                        </thead>
                        <tbody id="voucherDetailsBody">
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Challan Modal -->
<div id="viewChallanModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Challans</h5>
                <div class="ms-auto me-2">
                    <button type="button" class="btn btn-sm btn-success" onclick="exportChallanExcelCurrent()">Excel</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="exportChallanPDF()">PDF</button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="challanTable">
                        <thead>
                            <tr>
                                <th>Sl No</th>
                                <th>Date</th>
                                <th>Voucher ID</th>
                                <th>DO No</th>
                                <th>Vehicle No</th>
                                <th>Ref No</th>
                                <th>Challan No</th>
                                <th>Qty</th>
                                <th>Party Rate</th>
                                <th>Received Qty</th>
                                <th>Min Qty</th>
                                <th>Shortage</th>
                                <th>Shortage Price</th>
                                <th>Diesel Qty</th>
                                <th>Diesel Amount</th>
                                <th>Cash</th>
                                <th>Bilty Comm</th>
                                <th>TDS</th>
                                <th>Net Amount</th>
                                <th>Added By</th>            
                            </tr>
                        </thead>
                        <tbody id="challanDetailsBody">
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentVoucherId = null;
    let currentChallanVoucherId = null;

    function viewVoucher(id) {
        currentVoucherId = id;
        $('#voucherDetailsBody').html('<tr><td colspan="9" class="text-center">Loading...</td></tr>');
        $('#viewVoucherModal').modal('show');

        $.ajax({
            url: "<?= base_url('Admin/getPaymentVoucherDetails') ?>",
            type: "GET",
            data: { id: id },
            success: function(r) {
                if (r.status === 'success') {
                    if (r.data.length === 0) {
                        $('#voucherDetailsBody').html('<tr><td colspan="9" class="text-center">No linked vouchers found</td></tr>');
                        return;
                    }

                    let html = '';
                    r.data.forEach((v, index) => {
                        let receiptBtn = 'No Image';
                        
                        if (v.receipt_image && v.receipt_image !== '[]') {
                             let img = v.receipt_image;
                             // Try to parse if it looks like a JSON array
                             if (v.receipt_image.startsWith('[') || v.receipt_image.startsWith('"')) {
                                 try {
                                     let json = JSON.parse(v.receipt_image);
                                     if (Array.isArray(json)) {
                                         if (json.length > 0) {
                                             img = json[0]; 
                                         } else {
                                             img = null; // Empty array
                                         }
                                     }
                                 } catch(e) {}
                             }
                             
                             if (img) {
                                 receiptBtn = `<a href="<?= base_url('public/assets/uploads/receipts/') ?>/${img}" target="_blank" class="btn btn-sm btn-info">View</a>`;
                             }
                        }

                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${v.party_name || 'N/A'}</td> 
                                <td>${v.group_code || '-'}</td>
                                <td>${v.challan_count || 0}</td>
                                <td>${parseFloat(v.total_net_amount).toFixed(2)}</td>
                                <td>${v.deposited_by_name || '-'}</td>
                                <td>${v.deposit_date || '-'}</td>
                                <td>${v.deposit_place || '-'}</td>
                            </tr>
                        `;
                    });
                    $('#voucherDetailsBody').html(html);
                } else {
                    $('#voucherDetailsBody').html(`<tr><td colspan="9" class="text-center text-danger">${r.message}</td></tr>`);
                }
            },
            error: function() {
                $('#voucherDetailsBody').html('<tr><td colspan="9" class="text-center text-danger">Error loading data</td></tr>');
            }
        });
    }

    function viewChallan(id) {
        currentChallanVoucherId = id;
        $('#challanDetailsBody').html('<tr><td colspan="20" class="text-center">Loading...</td></tr>');
        $('#viewChallanModal').modal('show');

        $.ajax({
            url: "<?= base_url('Admin/getPaymentChallanDetails') ?>",
            type: "GET",
            data: { id: id },
            success: function(r) {
                if (r.status === 'success') {
                    if (r.data.length === 0) {
                        $('#challanDetailsBody').html('<tr><td colspan="20" class="text-center">No Challans found for this Payment</td></tr>');
                        return;
                    }

                    let html = '';
                    let totals = {
                        qty: 0, received: 0, actual_min: 0, shortage: 0, 
                        s_price: 0, d_qty: 0, d_amount: 0, cash: 0, 
                        bilty: 0, tds: 0, net: 0
                    };

                    r.data.forEach((des, index) => {
                        let qty = parseFloat(des.quantity) || 0;
                        let rate = parseFloat(des.rate) || 0;
                        let received = parseFloat(des.rest_amount) || 0;
                        let do_min = parseFloat(des.min_qty) || 0;
                        let s_rate = parseFloat(des.shortage_rate) || 0;
                        let d_qty = parseFloat(des.dieselQty) || 0;
                        let d_rate = parseFloat(des.diesel_rate) || 0;
                        let cash = parseFloat(des.cash) || 0;
                        let bilty = parseFloat(des.bilty_commission) || 0;
                        let tds_p = parseFloat(des.tds_percentage) || 2.00;
                        let special_shortage = parseInt(des.special_shortage) || 0;

                        let actual_min = Math.min(qty, received);
                        let actual_shortage = Math.max(0, qty - received);
                        let freight = actual_min * rate;
                        
                        let s_price = 0;
                        if (actual_shortage > 0) {
                            let chargeable_shortage = 0;
                            if (special_shortage == 1) {
                                chargeable_shortage = Math.max(0, actual_shortage - do_min);
                            } else {
                                chargeable_shortage = actual_shortage;
                            }
                            s_price = chargeable_shortage * (s_rate > 0 ? s_rate : rate);
                        }
                        
                        let shortage = actual_shortage; 
                        let d_amount = d_qty * d_rate;
                        let tds = (actual_min * rate * tds_p) / 100;
                        let net = freight - s_price - d_amount + cash - bilty - tds;

                        // Add to totals
                        totals.qty += qty;
                        totals.received += received;
                        totals.actual_min += actual_min;
                        totals.shortage += shortage;
                        totals.s_price += s_price;
                        totals.d_qty += d_qty;
                        totals.d_amount += d_amount;
                        totals.cash += cash;
                        totals.bilty += bilty;
                        totals.tds += tds;
                        totals.net += net;

                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${des.des_date ? new Date(des.des_date).toLocaleDateString('en-GB') : '-'}</td>
                                <td>${des.group_code || '-'}</td>
                                <td>${des.doreg_no || '-'}</td>
                                <td>${des.vehicle_number || '-'}</td>
                                <td>${des.ref_no || '-'}</td>
                                <td>${des.challan_no || '-'}</td>
                                <td>${qty.toFixed(2)}</td>
                                <td>${rate.toFixed(2)}</td>
                                <td>${received.toFixed(2)}</td>
                                <td>${actual_min.toFixed(2)}</td>
                                <td>${shortage.toFixed(2)}</td>
                                <td>${s_price.toFixed(2)}</td>
                                <td>${d_qty}</td>
                                <td>${d_amount.toFixed(2)}</td>
                                <td>${cash.toFixed(2)}</td>
                                <td>${bilty.toFixed(2)}</td>
                                <td>${tds.toFixed(2)}</td>
                                <td class="fw-bold text-success">${net.toFixed(2)}</td>
                                <td>${des.made_by || '-'}</td>          
                            </tr>
                        `;
                    });

                    // Append Totals row
                    html += `
                        <tr class="fw-bold bg-light">
                            <td colspan="7" class="text-end">TOTAL:</td>
                            <td>${totals.qty.toFixed(2)}</td>
                            <td>-</td>
                            <td>${totals.received.toFixed(2)}</td>
                            <td>${totals.actual_min.toFixed(2)}</td>
                            <td>${totals.shortage.toFixed(2)}</td>
                            <td>${totals.s_price.toFixed(2)}</td>
                            <td>${totals.d_qty.toFixed(2)}</td>
                            <td>${totals.d_amount.toFixed(2)}</td>
                            <td>${totals.cash.toFixed(2)}</td>
                            <td>${totals.bilty.toFixed(2)}</td>
                            <td>${totals.tds.toFixed(2)}</td>
                            <td class="text-success">${totals.net.toFixed(2)}</td>
                            <td>-</td>
                        </tr>
                    `;

                    $('#challanDetailsBody').html(html);
                } else {
                    $('#challanDetailsBody').html(`<tr><td colspan="20" class="text-center text-danger">${r.message}</td></tr>`);
                }
            },
            error: function() {
                $('#challanDetailsBody').html('<tr><td colspan="20" class="text-center text-danger">Error loading data</td></tr>');
            }
        });
    }
</script>
                    </table>
                </div>

                <div class="records-info">
                    Showing <?= count($payment_vouchers) ?> records
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>

<script>
$(document).ready(function () {

    $('.select2-search').select2({ 
        width: '100%',
        placeholder: "Select an option",
        allowClear: true
    });

    function calculateRow(row){
        let total = parseFloat(row.find('.total_net_amount').attr('data-amount')) || 0;
        let received = parseFloat(row.find('.received_amount').val()) || 0;
        let adjustment = parseFloat(row.find('.adjustment_amount').val()) || 0;
        let diff = total - received - adjustment;
        row.find('.difference').text(diff.toFixed(2));
    }

    $('#myTable tbody tr').each(function(){
        calculateRow($(this));
    });

    $(document).on('input','.received_amount, .adjustment_amount',function(){
        calculateRow($(this).closest('tr'));
    });

    $('.updateBtn').click(function(){
        let row=$(this).closest('tr');
        $.post("<?= base_url('Admin/updateVoucherPayment') ?>",{
            id:row.data('id'),
            received_date:row.find('.received_date').val(),
            received_amount:row.find('.received_amount').val(),
            adjustment_amount:row.find('.adjustment_amount').val(),
            adjustment_remarks:row.find('.adjustment_remarks').val(),
            "<?= csrf_token() ?>":"<?= csrf_hash() ?>"
        },function(res){
            if(res.status==='success'){
                alert('Updated successfully');
                calculateRow(row);
            }else{
                alert(res.message);
            }
        },'json');
    });

    $('#tableSearch').on('keyup',function(){
        let v=$(this).val().toLowerCase();
        $('#myTable tbody tr').filter(function(){
            $(this).toggle($(this).text().toLowerCase().indexOf(v)>-1)
        });
    });
});

    function exportVoucherExcelCurrent() {
        if (currentVoucherId) {
            window.location.href = "<?= base_url('Admin/exportPaymentVoucherExcel') ?>?id=" + currentVoucherId;
        }
    }

    function exportChallanExcelCurrent() {
        if (currentChallanVoucherId) {
            window.location.href = "<?= base_url('Admin/exportPaymentChallanExcel') ?>?id=" + currentChallanVoucherId;
        }
    }

    function exportVoucherPDF() {
        printModalContent('viewVoucherModal', 'Voucher details');
    }

    function exportChallanPDF() {
        printModalContent('viewChallanModal', 'Challan Details');
    }

    function printModalContent(modalId, title) {
        let content = $(`#${modalId} .modal-body`).html();
        let printWindow = window.open('', '', 'height=600,width=900');
        printWindow.document.write('<html><head><title>' + title + '</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
        printWindow.document.write('<style>table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid black; padding: 5px; font-size: 11px; } .no-print { display: none; } </style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<h4 class="text-center mb-4">' + title + '</h4>');
        printWindow.document.write(content);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        setTimeout(function() {
            printWindow.print();
        }, 500);
    }

    function exportExcel() {
        let from_date = $('input[name="from_date"]').val();
        let to_date = $('input[name="to_date"]').val();
        let party = $('select[name="party"]').val();

        let url = "<?= base_url('Admin/exportPaymentExcel') ?>?" + 
                  "from_date=" + from_date + 
                  "&to_date=" + to_date + 
                  "&party=" + (party || '');
        
        window.location.href = url;
    }

    function exportPDF() {
        let from_date = $('input[name="from_date"]').val();
        let to_date = $('input[name="to_date"]').val();
        let party_text = $('select[name="party"] option:selected').text();
        let title = 'Payment Report';
        if (from_date && to_date) title += ' (' + from_date + ' to ' + to_date + ')';
        if ($('select[name="party"]').val()) title += ' - Party: ' + party_text;

        printTable('myTable', title);
    }

    function printTable(tableId, title) {
        let table = document.getElementById(tableId).cloneNode(true);
        // Initialize totals
        let totals = {
            vouchers: 0,
            challans: 0,
            total_amount: 0,
            received_amount: 0,
            adjustment_amount: 0,
            difference: 0
        };

        let rows = table.querySelectorAll('tr');
        rows.forEach(row => {
            if (row.cells.length > 0) {
                let inputs = row.querySelectorAll('input:not([type="hidden"]), select');
                inputs.forEach(input => {
                    let val = input.value;
                    let span = document.createElement('span');
                    span.textContent = val || '-';
                    input.parentNode.replaceChild(span, input);
                });

                if (row.parentElement.tagName === 'TBODY') {
                    let cells = row.cells;
                    // Indices: 2: Vouchers, 3: Challans, 4: Total, 6: Received, 7: Adjustment, 8: Difference
                    totals.vouchers += parseFloat(cells[2].innerText.replace(/,/g, '')) || 0;
                    totals.challans += parseFloat(cells[3].innerText.replace(/,/g, '')) || 0;
                    totals.total_amount += parseFloat(cells[4].innerText.replace(/,/g, '')) || 0;
                    totals.received_amount += parseFloat(cells[6].innerText.replace(/,/g, '')) || 0;
                    totals.adjustment_amount += parseFloat(cells[7].innerText.replace(/,/g, '')) || 0;
                    totals.difference += parseFloat(cells[8].innerText.replace(/,/g, '')) || 0;
                }

                row.deleteCell(-1); // Remove Action
            }
        });

        // Add Totals Row
        let tfoot = table.createTFoot();
        let footerRow = tfoot.insertRow(0);
        footerRow.style.fontWeight = 'bold';
        footerRow.style.backgroundColor = '#f8f9fa';

        for (let j = 0; j < 10; j++) {
            let cell = footerRow.insertCell(j);
            cell.style.border = '1px solid #dee2e6';
            cell.style.padding = '2px 4px';
            
            if (j === 1) cell.innerText = 'TOTAL:';
            else if (j === 2) cell.innerText = totals.vouchers;
            else if (j === 3) cell.innerText = totals.challans;
            else if (j === 4) cell.innerText = totals.total_amount.toFixed(2);
            else if (j === 6) cell.innerText = totals.received_amount.toFixed(2);
            else if (j === 7) cell.innerText = totals.adjustment_amount.toFixed(2);
            else if (j === 8) cell.innerText = totals.difference.toFixed(2);
        }

        let printWindow = window.open('', '', 'height=700,width=1200');
        printWindow.document.write('<html><head><title>' + title + '</title>');
        printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
        printWindow.document.write('<style>');
        printWindow.document.write('@page { size: landscape; margin: 10mm; }');
        printWindow.document.write('body { font-family: Arial, sans-serif; padding: 10px; }');
        printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: auto; }');
        printWindow.document.write('th, td { border: 1px solid #dee2e6; padding: 2px 4px; font-size: 10px; text-align: left; word-break: break-word; color: #000 !important; }');
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
