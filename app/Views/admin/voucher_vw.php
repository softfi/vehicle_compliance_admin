<?php include("header.php"); ?>
<style>
    .voucher-wrapper {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 20px 0;
    }
    
    .page-header {
        background: white;
        padding: 5px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 24px;
        margin-top: 20px;
    }
    
    .filter-card {
        background: white;
        padding: 28px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }
    
    .filter-card h5 {
        font-size: 16px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .form-group-custom {
        margin-bottom: 16px;
    }
    
    .form-group-custom label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #475569;
        margin-bottom: 8px;
    }
    
    .form-control-custom {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .form-control-custom:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .btn-group-custom {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }
    
    .btn-custom {
        padding: 10px 24px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }
    
    .btn-primary-custom {
        background: #3b82f6;
        color: white;
    }
    
    .btn-primary-custom:hover {
        background: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    .btn-success-custom {
        background: #10b981;
        color: white;
    }
    
    .btn-success-custom:hover {
        background: #059669;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    
    .table-card {
        background: white;
        padding: 28px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }
    
    .table-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 16px;
    }
     
    .records-per-page {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .records-per-page label {
        font-size: 13px;
        font-weight: 500;
        color: #475569;
        white-space: nowrap;
    }
    
    .records-per-page select {
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 14px;
    }
    
    .search-box {
        flex: 1;
        display: flex;
        justify-content: flex-end;
    }
    
    .search-box input {
        max-width: 280px;
    }
    
    .records-info {
        background: #f8fafc;
        padding: 12px 20px;
        border-radius: 6px;
        text-align: center;
        color: #64748b;
        font-size: 14px;
        margin-bottom: 16px;
    }
    
    .table-wrapper {
        max-height: 500px;
        overflow-y: auto;
        overflow-x: auto;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }
    
    #myTable {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }
    
    #myTable thead th {
        position: sticky;
        top: 0;
        background: #f8fafc;
        color: #334155;
        font-weight: 600;
        padding: 12px 10px;
        text-align: left;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        z-index: 10;
    }
    
    #myTable tbody tr {
        transition: background 0.2s;
        border-bottom: 1px solid #f1f5f9;
    }
    
    #myTable tbody tr:hover {
        background: #f8fafc;
    }
    
    #myTable tbody td {
        padding: 10px;
        color: #475569;
        vertical-align: middle;
        white-space: nowrap;
    }
    
    #myTable input[type="text"],
    #myTable input[type="number"],
    #myTable input[type="date"] {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 13px;
    }
    
    #myTable input[type="text"]:focus,
    #myTable input[type="number"]:focus,
    #myTable input[type="date"]:focus {
        outline: none;
        border-color: #3b82f6;
    }
    
    #myTable button {
        padding: 6px 16px;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    #myTable button:hover {
        background: #2563eb;
        transform: translateY(-1px);
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 24px 0 0 0;
        gap: 6px;
        flex-wrap: wrap;
    }
    
    .pagination a, .pagination span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        text-decoration: none;
        border: 1px solid #e2e8f0;
        color: #475569;
        background: white;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .pagination a:hover {
        background-color: #f8fafc;
        border-color: #3b82f6;
        color: #3b82f6;
    }
    
    .pagination .current {
        background-color: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }
    
    .pagination .disabled {
        color: #cbd5e1;
        background-color: #fff;
        border-color: #e2e8f0;
        cursor: not-allowed;
    }
    
    @media (max-width: 768px) {
        .table-controls {
            flex-direction: column;
            align-items: stretch;
        }
        
        .search-box {
            justify-content: flex-start;
        }
        
        .search-box input {
            max-width: 100%;
        }
        
        .btn-group-custom {
            flex-direction: column;
        }
    }
</style>

<?php
// Pagination configuration
$records_per_page = isset($_GET['per_page']) && $_GET['per_page'] === 'all' ? 'all' : (isset($records_per_page) ? $records_per_page : 10);
$current_page = isset($current_page) ? $current_page : 1;
$total_records = isset($total_count) ? $total_count : count($despatch);

// Handle 'all' records case
if ($records_per_page === 'all') {
    $total_pages = 1;
    $current_page = 1;
} else {
    $total_pages = ceil($total_records / $records_per_page);
}
?>

<!-- Page Body Start-->
<div class="page-body-wrapper voucher-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <!-- Filter Card -->
            <div class="filter-card">
                <h5>📋 Voucher View</h5>
                <form method="get" action="<?php echo base_url(); ?>/Admin/voucher_entry">
                    <?php
                    $default_from_date = $date['from_date'] ?? date('Y-m-01');
                    $default_to_date = $date['to_date'] ?? date('Y-m-d');
                    ?>
                    <input type="hidden" name="per_page" id="hidden_per_page" value="<?= $records_per_page ?>">
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label for="from_date">From Date</label>
                                <input type="date" id="from_date" name="from_date" class="form-control-custom" value="<?= $default_from_date; ?>" onchange="updateDoRegistrations()"/>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label for="to_date">To Date</label>
                                <input type="date" id="to_date" name="to_date" class="form-control-custom" value="<?= $default_to_date; ?>" onchange="updateDoRegistrations()"/>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label for="do_no">DO No.</label>
                                <select class="form-control-custom" name="do_no" id="single">
                                    <option value="">Select DO No.</option>
                                    <?php foreach ($doregistration as $do): ?>
                                        <option value="<?= $do->do_registration_id; ?>" <?= isset($date['do_no']) && $date['do_no'] == $do->do_registration_id ? 'selected' : '' ?>><?= $do->do_no; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label for="chalan_status">Chalan Status</label>
                                <select class="form-control-custom" name="chalan_status" id="chalan_status">
                                    <option value="" <?= !isset($date['chalan_status']) || $date['chalan_status'] === '' ? 'selected' : '' ?>>All</option>
                                    <option value="1" <?= isset($date['chalan_status']) && $date['chalan_status'] == '1' ? 'selected' : '' ?>>Received</option>
                                    <option value="2" <?= isset($date['chalan_status']) && $date['chalan_status'] == '2' ? 'selected' : '' ?>>Not Received</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label for="payment_status">Payment Status</label>
                                <select class="form-control-custom" name="payment_status" id="payment_status">
                                    <option value="" <?= !isset($date['payment_status']) || $date['payment_status'] === '' ? 'selected' : '' ?>>All</option>
                                    <option value="1" <?= isset($date['payment_status']) && $date['payment_status'] == '1' ? 'selected' : '' ?>>Paid</option>
                                    <option value="0" <?= isset($date['payment_status']) && $date['payment_status'] == '0' ? 'selected' : '' ?>>Unpaid</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label for="deposited_status">Deposited Status</label>
                                <select class="form-control-custom" name="deposited_status" id="deposited_status">
                                    <option value="" <?= !isset($date['deposited_status']) || $date['deposited_status'] === '' ? 'selected' : '' ?>>All</option>
                                    <option value="1" <?= isset($date['deposited_status']) && $date['deposited_status'] == '1' ? 'selected' : '' ?>>Deposited</option>
                                    <option value="0" <?= isset($date['deposited_status']) && $date['deposited_status'] == '0' ? 'selected' : '' ?>>Not Deposited</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="btn-group-custom">
                        <button type="submit" class="btn-custom btn-primary-custom">
                            <i class="fa fa-filter"></i> Apply Filters
                        </button>
                        <a href="#" id="download_excel" class="btn-custom btn-success-custom">
                            <i class="fa fa-download"></i> Download Excel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table Card -->
            <div class="table-card">
                <div class="table-controls">
                    <div class="records-per-page">
                        <label for="per_page">Records per page:</label>
                        <select id="per_page" class="form-control-custom" onchange="changePerPage()">
                             <option value="10" <?= $records_per_page == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $records_per_page == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $records_per_page == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $records_per_page == 100 ? 'selected' : '' ?>>100</option>
                            <option value="all" <?= $records_per_page == 'all' ? 'selected' : '' ?>>Show All</option>
                        </select>
                    </div>
                    <!-- <div class="search-box">
                        <input type="text" id="tableSearch" class="form-control-custom" placeholder="🔍 Search in table...">
                    </div> -->
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
                                <th>Date</th>
                                <th>DO No</th>
                                <th>Vehicle No</th>
                                <th>Challan No</th>
                                <th>Challan Qty.</th>
                                <th>Recive Qty.</th>
                                <th>Rate</th>
                                <th>Shortage</th>
                                <th>Freight</th>
                                <th>Shortage price</th>
                                <th>
                                    Diesel
                                    <input type="number" id="global_diesel_price" class="uk-input" placeholder="Price" style="width:80px;" />
                                </th>
                                <th>Driver Exp</th>
                                <th>Total Deduction</th>
                                <th>Net Amount</th>
                                <th>Challan Deposited</th>
                                <th>Deposited by</th>
                                <th>Deposited date</th>
                                <th>Tds Amount</th>
                                <th>Other Deduction</th>
                                <th>Payment Status</th>
                                <th>Received Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($records_per_page === 'all') {
                                $i = 1;
                            } else {
                                $i = ($current_page - 1) * $records_per_page + 1;
                            }
                            foreach ($despatch as $des): 
                            ?>
                            <tr data-id="<?= $des->despatch_id; ?>">
                                <td><?= $i++; ?>-<?= $des->despatch_id; ?></td>
                                <td><?= date('d-m-Y', strtotime($des->des_date)); ?></td>
                                <td><?= $des->doreg_no; ?></td>
                                <td><?= $des->vehicle_number; ?></td>
                                <td><?= $des->ref_no; ?></td>
                                <td class="quantity"><?= $des->quantity; ?></td>
                                <td><input type="text" name="rest_amount" class="uk-input rest_amount" value="<?= $des->rest_amount; ?>"></td>
                                <td class="rate"><?= $des->rate; ?></td>
                                <td><input type="text" name="shortage" class="uk-input shortage" value="<?= $des->shortage; ?>" readonly></td>
                                <td><input type="text" name="freight" class="uk-input freight" value="<?= $des->freight; ?>" readonly></td>
                                <td><input type="text" name="shortage" class="uk-input shortage_price" value="<?= $des->shortage_price; ?>" readonly></td>
                                <td>
                                    <input type="text" class="uk-input dieselPrice" placeholder="price" value="<?= $des->dieselPrice; ?>" style="width:50px;" oninput="calculateDieselTotal(this)">
                                    x
                                    <input type="text" class="uk-input dieselQty" placeholder="qty" value="<?= $des->dieselQty; ?>" style="width:50px;" oninput="calculateDieselTotal(this)">
                                    =
                                    <input type="number" class="uk-input totalDieselRate" value="<?= $des->totaldieselRate; ?>" placeholder="total" style="width:100px;" readonly>
                                </td>
                                <td><input type="text" class="uk-input driver_expence" value="<?= $des->driver_expence; ?>" placeholder="driver_expence"></td>
                                <td><input type="text" name="total_deduction" class="uk-input total_deduction" value="<?= $des->total_deduction; ?>" readonly></td>
                                <td><input type="text" name="net_amount" class="uk-input net_amount" value="<?= $des->net_amount; ?>" readonly></td>
                                <td>
                                    <input type="checkbox" value="1" name="deposited" class="deposited_checkbox" <?= ($des->deposited == 1) ? 'checked' : ''; ?> />
                                </td>
                                <td><input type="text" name="deposit_by" class="uk-input deposit_by" value="<?= $des->deposit_by; ?>" readonly></td>
                                <td><input type="date" name="deposit_date" class="uk-input deposit_date" value="<?= $des->deposit_date; ?>"></td>
                                <td><input type="number" name="tds" class="uk-input tds" value="<?= $des->tds; ?>"></td>
                                <td><input type="number" name="other_deduction" class="uk-input other_deduction" value="<?= $des->other_deduction; ?>"></td>
                                <td>
                                    <label><input type="radio" name="payment_status_<?= $des->despatch_id ?>" value="1" <?= ($des->payment_status == 1) ? 'checked' : ''; ?>> Paid</label>
                                    <label><input type="radio" name="payment_status_<?= $des->despatch_id ?>" value="0" <?= ($des->payment_status == 0) ? 'checked' : ''; ?>> Unpaid</label>
                                </td>
                                <td><input type="date" name="received_date" class="uk-input received_date" value="<?= $des->received_date; ?>"></td>
                                <?php if(in_array(36.1,$jobAssign)){ ?>
                                <td>
                                    <button type="button" onclick="updateRow(this, '<?= isset($singleuser[0]->full_name) ? htmlspecialchars($singleuser[0]->full_name, ENT_QUOTES) : ''; ?>')">
                                        Add
                                    </button>
                                </td>
                                <?php } ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>                            
                </div>
                
                <div class="records-info">
                    <?php if ($records_per_page === 'all'): ?>
                        Showing all <span id="total_records"><?= $total_records ?></span> records
                    <?php else: ?>
                        Showing <span id="showing_from"><?= min(($current_page - 1) * $records_per_page + 1, $total_records) ?></span> to 
                        <span id="showing_to"><?= min($current_page * $records_per_page, $total_records) ?></span> of 
                        <span id="total_records"><?= $total_records ?></span> records
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($records_per_page !== 'all' && $total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    $current_params = [
                        'from_date' => $date['from_date'] ?? '',
                        'to_date' => $date['to_date'] ?? '',
                        'do_no' => $date['do_no'] ?? '',
                        'chalan_status' => $date['chalan_status'] ?? '',
                        'payment_status' => $date['payment_status'] ?? '',
                        'deposited_status' => $date['deposited_status'] ?? '',
                        'per_page' => $records_per_page
                    ];
                    
                    if ($current_page > 1): 
                        $prev_params = $current_params;
                        $prev_params['page'] = $current_page - 1;
                        $prev_url = '?' . http_build_query($prev_params);
                    ?>
                        <a href="<?= $prev_url ?>">&laquo; Previous</a>
                    <?php else: ?>
                        <span class="disabled">&laquo; Previous</span>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    if ($start_page > 1): ?>
                        <?php 
                        $first_params = $current_params;
                        $first_params['page'] = 1;
                        $first_url = '?' . http_build_query($first_params);
                        ?>
                        <a href="<?= $first_url ?>">1</a>
                        <?php if ($start_page > 2): ?>
                            <span>...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <?php 
                            $page_params = $current_params;
                            $page_params['page'] = $i;
                            $page_url = '?' . http_build_query($page_params);
                            ?>
                            <a href="<?= $page_url ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <span>...</span>
                        <?php endif; ?>
                        <?php 
                        $last_params = $current_params;
                        $last_params['page'] = $total_pages;
                        $last_url = '?' . http_build_query($last_params);
                        ?>
                        <a href="<?= $last_url ?>"><?= $total_pages ?></a>
                    <?php endif; ?>
                    
                    <?php
                    if ($current_page < $total_pages): 
                        $next_params = $current_params;
                        $next_params['page'] = $current_page + 1;
                        $next_url = '?' . http_build_query($next_params);
                    ?>
                        <a href="<?= $next_url ?>">Next &raquo;</a>
                    <?php else: ?>
                        <span class="disabled">Next &raquo;</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Search functionality
    document.getElementById('tableSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#myTable tbody tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Update showing info
        if (searchTerm) {
            document.getElementById('showing_from').textContent = visibleCount > 0 ? '1' : '0';
            document.getElementById('showing_to').textContent = visibleCount;
            document.getElementById('total_records').textContent = visibleCount;
        } else {
            <?php if ($records_per_page === 'all'): ?>
                document.getElementById('total_records').textContent = '<?= $total_records ?>';
            <?php else: ?>
                document.getElementById('showing_from').textContent = '<?= min(($current_page - 1) * $records_per_page + 1, $total_records) ?>';
                document.getElementById('showing_to').textContent = '<?= min($current_page * $records_per_page, $total_records) ?>';
                document.getElementById('total_records').textContent = '<?= $total_records ?>';
            <?php endif; ?>
        }
    });

    function updateDoRegistrations() {
        var from_date = $("#from_date").val();
        var to_date = $("#to_date").val();

        if (from_date && to_date) {
            $.ajax({
                url: "<?= base_url('Admin/getDoNumbers') ?>",
                type: "POST",
                data: {
                    from_date: from_date,
                    to_date: to_date,
                    <?= csrf_token() ?>: "<?= csrf_hash() ?>"
                },
                success: function(response) {
                    $("#single").html(response);
                },
                error: function() {
                    alert("Failed to fetch DO numbers.");
                }
            });
        }
    }
    
    function changePerPage() {
        var perPage = document.getElementById('per_page').value;
        document.getElementById('hidden_per_page').value = perPage;

        var currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('per_page', perPage);
        currentUrl.searchParams.set('page', '1');
        window.location.href = currentUrl.toString();
    }

    function calculateDieselTotal(input) {
        let row = input.closest('tr');
        let dieselPrice = parseFloat(row.querySelector('.dieselPrice').value) || 0;
        let dieselQty = parseFloat(row.querySelector('.dieselQty').value) || 0;
        let totalRate = dieselPrice * dieselQty;
        
        row.querySelector('.totalDieselRate').value = totalRate.toFixed(2);
    }
    
    document.getElementById("global_diesel_price").addEventListener("input", function () {
        let globalPrice = parseFloat(this.value) || 0;
        document.querySelectorAll(".dieselPrice").forEach(function (input) {
            input.value = globalPrice;
            calculateDieselTotal(input);
        });
    });

    $(document).ready(function() {
        function fetchDoNumbers() {
            var from_date = $("#from_date").val();
            var to_date = $("#to_date").val();

            $.ajax({
                url: "<?= base_url('Admin/getDoNumbers') ?>",
                type: "POST",
                data: { 
                    from_date: from_date, 
                    to_date: to_date,
                    <?= csrf_token() ?>: "<?= csrf_hash() ?>"
                },
                success: function(response) {
                    $("#single").html(response);
                }
            });
        }

        $("#from_date, #to_date").on("change", function() {
            fetchDoNumbers();
        });

        fetchDoNumbers();
    });


    function updateDispatch1(input) {
        let row = input.closest("tr");
        let dispatchId = row.getAttribute("data-id");
        let challanNo = row.querySelector(".challan_no").value;

        $.ajax({
            url: "<?= base_url('Admin/updateDispatch1') ?>",
            type: "POST",
            data: {
                id: dispatchId,
                challan_no: challanNo,
                <?= csrf_token() ?>: "<?= csrf_hash() ?>"
            },
            success: function (response) {
                console.log("Updated successfully:", response);
            },
            error: function (xhr, status, error) {
                console.error("Error updating dispatch:", error);
            }
        });
    }

    function updateRow(button, loginUserName) {
        let row = button.closest('tr');
        let dispatchId = row.getAttribute('data-id');
        let quantity = parseFloat(row.querySelector(".quantity").textContent) || 0;
        let restAmount = parseFloat(row.querySelector(".rest_amount").value) || 0;
        let rate = parseFloat(row.querySelector(".rate").textContent) || 0;

        let shortage = quantity - restAmount;
        if(shortage < 0){
            shortage = 0;
        }
        row.querySelector(".shortage").value = shortage;

        let freight = restAmount * rate;
        row.querySelector(".freight").value = freight;

        let depositedCheckbox = row.querySelector(".deposited_checkbox");
        let deposited = depositedCheckbox.checked ? 1 : 0;
        let deposit_by = loginUserName;
        row.querySelector(".deposit_by").value = deposit_by;

        let deposit_date = new Date().toISOString().split('T')[0];
        row.querySelector(".deposit_date").value = deposit_date;

        let driver_expence = parseFloat(row.querySelector(".driver_expence").value) || 0;
        let dieselPrice = parseFloat(row.querySelector(".dieselPrice").value) || 0;
        let dieselQty = parseFloat(row.querySelector(".dieselQty").value) || 0;

        let totalRate = dieselPrice * dieselQty;
        row.querySelector(".totalDieselRate").value = totalRate.toFixed(2);

        let shortage_price = parseFloat(row.querySelector(".shortage_price").value) || 0;
        let tdsAmount = parseFloat(row.querySelector(".tds").value) || 0;
        let otherDeduction = parseFloat(row.querySelector(".other_deduction").value) || 0;

        let totalDeduction = shortage_price + driver_expence + totalRate + tdsAmount + otherDeduction;
        row.querySelector(".total_deduction").value = totalDeduction.toFixed(2);

        let netAmount = freight - totalDeduction;
        row.querySelector(".net_amount").value = netAmount.toFixed(2);

        let paymentStatusInput = row.querySelector('input[type="radio"][name^="payment_status_"]:checked');
        let paymentStatus = paymentStatusInput ? parseInt(paymentStatusInput.value) : 0;

        let received_date = row.querySelector(".received_date").value.trim();

        $.ajax({
            url: "<?= base_url('Admin/updateDispatch') ?>",
            type: "POST",
            data: {
                id: dispatchId,
                rest_amount: restAmount,
                shortage: shortage,
                freight: freight,
                dieselQty: dieselQty,
                dieselPrice: dieselPrice,
                totaldieselRate: totalRate,
                driver_expence: driver_expence,
                deposited: deposited,
                deposit_by: deposit_by,
                deposit_date: deposit_date,
                total_deduction: totalDeduction,
                net_amount: netAmount,
                tds: tdsAmount,
                otherDeduction: otherDeduction,
                paymentStatus: paymentStatus,
                received_date: received_date,
                <?= csrf_token() ?>: "<?= csrf_hash() ?>"
            },
            success: function(response) {
                if (response.status === 'success') {
                    alert('Row updated successfully');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('AJAX error while updating');
            }
        });
    }
</script>
<script>
    document.getElementById("download_excel").addEventListener("click", function (e) {
        e.preventDefault();

        // Get all filter values
        let from_date = document.getElementById("from_date").value;
        let to_date = document.getElementById("to_date").value;
        let single = document.getElementById("single").value;
        let chalan_status = document.getElementById("chalan_status").value;
        let payment_status = document.getElementById("payment_status").value;
        let deposited_status = document.getElementById("deposited_status").value;

        // Build URL parameters
        let params = new URLSearchParams();
        if (from_date) params.append('from_date', from_date);
        if (to_date) params.append('to_date', to_date);
        if (single) params.append('do_no', single);
        if (chalan_status) params.append('chalan_status', chalan_status);
        if (payment_status) params.append('payment_status', payment_status);
        if (deposited_status) params.append('deposited_status', deposited_status);

        // Redirect to the export function with all parameters
        window.location.href = "<?= base_url('Admin/export_voucher_excel'); ?>?" + params.toString();
    });
</script>

<?php include("footer.php"); ?>