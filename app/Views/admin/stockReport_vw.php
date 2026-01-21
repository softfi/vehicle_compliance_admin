<?php include("header.php");?>
<style>
    .stock-report-wrapper {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 20px 0;
    }
    
    .page-header {
        background: white;
        padding: 5px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 30px;
        margin-top: 20px;
    }
    
    .filter-card {
        background: white;
        padding: 28px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 24px;
    }
    
    .filter-card h5 {
        font-size: 16px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .form-group {
        margin-bottom: 16px;
    }
    
    .form-group label {
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
    }
    
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 16px;
    }
    
    .table-filters {
        display: flex;
        gap: 16px;
        align-items: flex-end;
        flex-wrap: wrap;
    }
    
    .filter-item {
        display: flex;
        flex-direction: column;
    }
    
    .filter-item label {
        font-size: 13px;
        font-weight: 500;
        color: #475569;
        margin-bottom: 6px;
    }
    
    .filter-select, .filter-input {
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 14px;
        min-width: 180px;
    }
    
    .filter-input {
        min-width: 280px;
    }
    
    .stock-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 14px;
    }
    
    .stock-table thead th {
        background: #f8fafc;
        color: #334155;
        font-weight: 600;
        padding: 14px 12px;
        text-align: left;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stock-table tbody tr {
        transition: background 0.2s;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .stock-table tbody tr:hover {
        background: #f8fafc;
    }
    
    .stock-table tbody td {
        padding: 14px 12px;
        color: #475569;
        vertical-align: middle;
    }
    
    .stock-table tfoot th {
        background: #f8fafc;
        color: #334155;
        font-weight: 600;
        padding: 14px 12px;
        border-top: 2px solid #e2e8f0;
        font-size: 13px;
    }
    
    .badge-item {
        display: inline-block;
        padding: 4px 10px;
        background: #eff6ff;
        color: #1e40af;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .text-danger-custom {
        color: #dc2626;
        font-weight: 600;
    }
    
    .text-success-custom {
        color: #059669;
        font-weight: 600;
    }
    
    .table-responsive-custom {
        overflow-x: auto;
        margin-top: 16px;
    }
    
    /* Hide DataTables default search */
    /* .dataTables_filter {
        display: none !important;
    } */
    
    @media (max-width: 768px) {
        .table-header {
            flex-direction: column;
            align-items: stretch;
        }
        
        .table-filters {
            flex-direction: column;
        }
        
        .filter-select, .filter-input {
            width: 100%;
        }
        
        .btn-group-custom {
            flex-direction: column;
        }
    }
</style>

<!-- Page Body Start-->
<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body stock-report-wrapper">
        <div class="container-fluid">

            <!-- Filter Card -->
            <div class="filter-card">
                <h5>📊 Stock Report</h5>
                <form action="<?php echo base_url(); ?>/Admin/Stock_Report" method="post" id="stockFilterForm">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="from_date">From Date</label>
                                <input class="form-control-custom" value="<?=$date['from_date'];?>" type="date" id="from_date" name="from_date" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="to_date">To Date</label>
                                <input class="form-control-custom" type="date" id="to_date" value="<?=$date['to_date'];?>" name="to_date" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="location">Location</label>
                                <select class="form-control-custom" id="location" name="location" required>
                                    <option value="">Select Location</option>
                                    <?php foreach ($locations as $location) { ?>
                                        <option value="<?= $location->location_id ?>" <?php if ($location->location_id==$date['location']){echo "selected";} ?>><?= $location->location_name ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="btn-group-custom">
                        <button class="btn-custom btn-primary-custom" type="submit">
                            <i class="fa fa-filter"></i> Apply Filters
                        </button>
                        <button class="btn-custom btn-success-custom" type="button" onclick="downloadExcel()">
                            <i class="fa fa-download"></i> Download Excel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table Card -->
            <div class="table-card">
                <div class="table-header">
                    <h5 style="margin: 0; font-size: 16px; font-weight: 600; color: #334155;">Stock Details</h5>
                    <div class="table-filters">
                        <div class="filter-item">
                            <label for="inlineLocationFilter">Quick Location Filter</label>
                            <select class="filter-select" id="inlineLocationFilter">
                                <option value="">All Locations</option>
                                <?php foreach ($locations as $location) { ?>
                                    <option value="<?= $location->location_id ?>"><?= $location->location_name ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive-custom">
                    <table class="stock-table" id="row_create">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Sl.#</th>
                                <th>Item Name</th>
                                <th>Unit</th>
                                <th>Amount (₹)</th>
                                <th>Opening Stock</th>
                                <th>Purchase Stock</th>
                                <th>transfer Stock</th>
                                <th>Consumed</th>
                                <th>Available Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            foreach ($stock_dtls as $stock) {
                            ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td>
                                        <strong><?= $stock->item_name; ?></strong>
                                        <br><span class="badge-item"><?= $stock->item_code; ?></span>
                                    </td>
                                    <td><?= $stock->unit_short_name; ?></td>
                                    <td><?= number_format($stock->amount, 2); ?></td>
                                    <td><?= $stock->opening_stock; ?></td>
                                    <td class="text-success-custom"><?= $stock->purchase_stock; ?></td>
                                    <td class="text-success-custom"><?= $stock->transfer_stock; ?></td>
                                    <td class="text-danger-custom"><?= $stock->consumed_stock; ?></td>
                                    <td><strong><?= $stock->available_stock; ?></strong></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Sl.#</th>
                                <th>Item Name</th>
                                <th>Unit</th>
                                <th>Amount (₹)</th>
                                <th>Opening Stock</th>
                                <th>Purchase Stock</th>
                                <th>transfer Stock</th>
                                <th>Consumed</th>
                                <th>Available Stock</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function downloadExcel() {
        var from_date = document.getElementById('from_date').value;
        var to_date = document.getElementById('to_date').value;
        var location = document.getElementById('location').value;

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url(); ?>/Admin/export_stock_report_to_excel';

        var inputFromDate = document.createElement('input');
        inputFromDate.type = 'hidden';
        inputFromDate.name = 'from_date';
        inputFromDate.value = from_date;

        var inputToDate = document.createElement('input');
        inputToDate.type = 'hidden';
        inputToDate.name = 'to_date';
        inputToDate.value = to_date;

        var inputLocation = document.createElement('input');
        inputLocation.type = 'hidden';
        inputLocation.name = 'location';
        inputLocation.value = location;

        form.appendChild(inputFromDate);
        form.appendChild(inputToDate);
        form.appendChild(inputLocation);
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    // Stock table item search filter
    (function(){
        const itemSearch = document.getElementById('itemSearch');
        const stockRows = Array.from(document.querySelectorAll('#row_create tbody tr'));
        
        function filterItems() {
            const q = (itemSearch.value || '').trim().toLowerCase();
            stockRows.forEach(tr => {
                const text = tr.innerText.toLowerCase();
                tr.style.display = q ? (text.includes(q) ? '' : 'none') : '';
            });
        }

        if (itemSearch) itemSearch.addEventListener('input', filterItems);

        // Inline location filter
        const inlineLoc = document.getElementById('inlineLocationFilter');
        const mainForm = document.getElementById('stockFilterForm');
        const mainLoc = document.getElementById('location');
        const fromInput = document.getElementById('from_date');
        const toInput = document.getElementById('to_date');
        
        if (inlineLoc && mainLoc) {
            inlineLoc.value = mainLoc.value;
            inlineLoc.addEventListener('change', function(){
                mainLoc.value = inlineLoc.value;

                if (fromInput) fromInput.value = '2020-01-01';
                if (toInput) {
                    const d = new Date();
                    const yyyy = d.getFullYear();
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const dd = String(d.getDate()).padStart(2, '0');
                    toInput.value = `${yyyy}-${mm}-${dd}`;
                }

                if (mainForm) mainForm.submit();
            });
        }
    })();
</script>

<!-- footer start-->
<?php include("footer.php");?>