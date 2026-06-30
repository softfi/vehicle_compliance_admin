<?php include("header.php"); ?>
<?php 
	use App\Models\AdminModel;
	$db = db_connect();
	$this->AdminModel = new AdminModel($db);

    $activeFilters = $filters ?? [];
    $filterFromDate   = $activeFilters['from_date'] ?? '';
    $filterToDate     = $activeFilters['to_date'] ?? '';
    $filterLocationId = (int) ($activeFilters['location_id'] ?? 0);
    $filterSupplierId = (int) ($activeFilters['supplier_id'] ?? 0);
    $filterSearch     = $activeFilters['search'] ?? '';
    $totalRecords     = isset($stock_dtls) ? count($stock_dtls) : 0;
?>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
 #myTable thead th {
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
        border-bottom: 1px solid #ddd;
    }
 .purchase-filter-card {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        padding: 16px;
        margin-bottom: 16px;
    }
 .purchase-filter-card label {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 4px;
        display: block;
    }
 .filter-actions {
        display: flex;
        gap: 8px;
        align-items: flex-end;
        height: 100%;
        padding-top: 22px;
    }
</style>

<!-- Page Body Start-->
<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">        
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <?php if(in_array(1.1, $jobAssign)){ ?>
                            <a class="btn btn-sm btn-primary" href="<?= base_url(); ?>/Admin/Purchaseentry">ENTER STOCKS</a>
                        <?php } ?>
                        <a class="btn btn-sm btn-primary" href="<?= base_url(); ?>/Admin/stock_transfer">Stock Transfer</a>
                        <h3>All Purchase Voucher</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Container-fluid starts-->
        <div class="container-fluid default-dashboard">
            <div class="purchase-filter-card">
                <form method="get" action="<?= base_url('admin/Purchase_Voucher'); ?>">
                    <div class="row">
                        <div class="col-md-2 col-sm-6">
                            <label for="from_date">From Date</label>
                            <input type="date" id="from_date" name="from_date" class="uk-input" value="<?= esc($filterFromDate); ?>">
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label for="to_date">To Date</label>
                            <input type="date" id="to_date" name="to_date" class="uk-input" value="<?= esc($filterToDate); ?>">
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label for="location_id">Location</label>
                            <select id="location_id" name="location_id" class="uk-input">
                                <option value="">All Locations</option>
                                <?php foreach ($location as $loc): ?>
                                    <option value="<?= (int) $loc->location_id; ?>" <?= $filterLocationId === (int) $loc->location_id ? 'selected' : ''; ?>>
                                        <?= esc($loc->location_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label for="supplier_id">Supplier</label>
                            <select id="supplier_id" name="supplier_id" class="uk-input">
                                <option value="">All Suppliers</option>
                                <?php foreach ($vendor as $supp): ?>
                                    <option value="<?= (int) $supp->id; ?>" <?= $filterSupplierId === (int) $supp->id ? 'selected' : ''; ?>>
                                        <?= esc($supp->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label for="search">Search</label>
                            <input type="text" id="search" name="search" class="uk-input" value="<?= esc($filterSearch); ?>" placeholder="Invoice, supplier, location...">
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <div class="filter-actions">
                                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                                <a href="<?= base_url('admin/Purchase_Voucher'); ?>" class="btn btn-sm btn-secondary">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-small-bottom">
                <span class="uk-text-meta">Showing <strong><?= $totalRecords; ?></strong> purchase voucher(s)</span>
                <input type="text" id="tableSearch" placeholder="Quick search in table..." class="uk-input" style="width: 240px;">
            </div>

            <div class="uk-grid-small uk-child-width-expand@m" uk-grid>
                <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-top">
                    <div style="max-height: 500px; overflow-y: auto;">
                        <table id="myTable" class="uk-table uk-table-small uk-table-divider" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Sl No</th>
                                    <th>Date</th>
                                    <th>Invoice No</th>
                                    <th>Supplier Name</th>
                                    <th>Total Quantity</th>
                                    <th>Total Amount</th>
                                    <th>Location</th>
                                    <th>Bill</th>
                                    <th>View</th>
                                    <?php if (in_array(1.4, $jobAssign)): ?>
                                        <th>Edit</th>
                                    <?php endif; ?>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($totalRecords === 0): ?>
                                    <tr>
                                        <td colspan="<?= in_array(1.4, $jobAssign) ? 11 : 10; ?>" class="uk-text-center uk-text-muted">
                                            No purchase vouchers found for the selected filters.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                <?php $m = 1; foreach ($stock_dtls as $stock): ?>
                                    <?php
                                        $modalId = 'purchaseModal' . $m;
                                        $isStockTransfer = strpos((string) ($stock->invoice_number ?? ''), 'stock-trans') === 0;
                                        $stockDate = ! empty($stock->date) ? date('Y-m-d', strtotime($stock->date)) : '';
                                        $billPhoto = trim((string) ($stock->bill_photo ?? ''));
                                        $billPhotoUrl = $billPhoto !== '' ? base_url('public/uploads/purchase_bills/' . $billPhoto) : '';
                                    ?>
                                    <tr>
                                        <td><?= $m++; ?></td>
                                        <td><?= ! empty($stock->date) ? date('d-m-Y', strtotime($stock->date)) : '-'; ?></td>
                                        <td><?= esc($stock->invoice_number); ?></td>
                                        <td><?= esc($stock->supplier_name); ?></td>
                                        <td><?= $stock->total_quantity; ?></td>
                                        <td><?= $stock->total_gst_amount; ?></td>
                                        <td><?= esc($stock->location_name); ?></td>
                                        <td>
                                            <?php if ($billPhotoUrl !== ''): ?>
                                                <a href="<?= esc($billPhotoUrl); ?>" target="_blank" class="btn btn-sm btn-outline-primary">View Bill</a>
                                            <?php else: ?>
                                                <span class="uk-text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (in_array(1.2, $jobAssign)): ?>
                                                <a class="btn btn-sm btn-primary" href="#<?= $modalId; ?>" uk-toggle>View</a>
                                            <?php endif; ?>
                                            
                                            <!-- Modal -->
                                            <div id="<?= $modalId; ?>" class="uk-modal-container" uk-modal>
                                                <div class="uk-modal-dialog uk-modal-body">
                                                    <button class="uk-modal-close-default" type="button" uk-close></button>

                                                    <h5>
                                                        Stock Code: <?= esc($stock->stock_code); ?><br>
                                                        Invoice No: <?= esc($stock->invoice_number); ?><br>
                                                        Order Date: <?= ! empty($stock->date) ? date('d-m-Y', strtotime($stock->date)) : '-'; ?>
                                                        <?php if (! empty($stock->remarks)): ?>
                                                            <br>Remarks: <?= esc($stock->remarks); ?>
                                                        <?php endif; ?>
                                                    </h5>

                                                    <?php if ($billPhotoUrl !== ''): ?>
                                                        <div class="uk-margin-small-bottom">
                                                            <a href="<?= esc($billPhotoUrl); ?>" target="_blank"
                                                                class="uk-button uk-button-default uk-button-small uk-margin-small-right">
                                                                View Bill
                                                            </a>
                                                            <a href="<?= esc($billPhotoUrl); ?>" download="<?= esc($billPhoto); ?>"
                                                                class="uk-button uk-button-default uk-button-small">
                                                                Download Bill
                                                            </a>
                                                        </div>
                                                    <?php else: ?>
                                                        <p class="uk-text-muted uk-margin-small-bottom">No bill image uploaded for this purchase.</p>
                                                    <?php endif; ?>

                                                    <a href="<?= base_url('admin/downloadStock/' . $stock->stock_code); ?>"
                                                    class="uk-button uk-button-primary uk-button-small"
                                                    style="margin-bottom:10px;">
                                                        Download Stock
                                                    </a>

                                                    <table class="uk-table uk-table-divider uk-table-small">
                                                        <thead>
                                                            <tr>
                                                                <th>Sl No</th>
                                                                <th>Product Name</th>
                                                                <th>Supplier Name</th>
                                                                <th>Quantity</th>
                                                                <th>Rate</th>
                                                                <th>Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $i = 1;
                                                            $total = 0;
                                                            $single_stock_dtls = $this->AdminModel->singleStock($stock->stock_code);
                                                            foreach ($single_stock_dtls as $stk_dtls):
                                                                if (! $isStockTransfer) {
                                                                    if ((string) ($stk_dtls->invoice_number ?? '') !== (string) ($stock->invoice_number ?? '')) {
                                                                        continue;
                                                                    }
                                                                    $lineDate = ! empty($stk_dtls->date) ? date('Y-m-d', strtotime($stk_dtls->date)) : '';
                                                                    if ($stockDate !== '' && $lineDate !== $stockDate) {
                                                                        continue;
                                                                    }
                                                                }
                                                                $amount = $stk_dtls->quantity * $stk_dtls->rate;
                                                                $total += $amount;
                                                            ?>
                                                                <tr>
                                                                    <td><?= $i++; ?></td>
                                                                    <td><?= esc($stk_dtls->item_name); ?> (<?= esc($stk_dtls->item_id); ?>)</td>
                                                                    <td><?= esc($stk_dtls->name); ?></td>
                                                                    <td><?= $stk_dtls->quantity . ' ' . esc($stk_dtls->unit_name); ?></td>
                                                                    <td><?= number_format($stk_dtls->rate, 2); ?></td>
                                                                    <td><?= number_format($amount, 2); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                        <tfoot>
                                                            <tr>
                                                                <th colspan="5" style="text-align:right;">Total</th>
                                                                <th><?= number_format($total, 2); ?></th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                        <?php if (in_array(1.4, $jobAssign)): ?>
                                            <td>
                                                <?php if ($isStockTransfer): ?>
                                                    <button class="btn btn-sm btn-secondary" disabled>Edit</button>
                                                <?php else: ?>
                                                    <a class="btn btn-success" href="<?php echo base_url(); ?>/Admin/edit_stock/<?php echo $stock->stock_code; ?>">Edit</a>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                        <td>
                                            <?php if (in_array(1.3, $jobAssign)): ?>
                                                <a href="javascript:void(0);" onClick="deleteRecord('<?= $stock->stock_code; ?>');" class="btn btn-sm btn-danger">Delete</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
  
    <form name="frm_deleteBanner" id="frm_deleteBanner" action="<?= base_url(); ?>/admin/delete_stock" method="post">
        <input type="hidden" name="user_id" id="user_id" value="">
    </form>

    <script type="text/javascript">
        function deleteRecord(id) {
            var conf = confirm("Are you sure want to delete this record?");
            if (conf) {
                document.getElementById('user_id').value = id;
                document.getElementById('frm_deleteBanner').submit();
            }
        }

        document.getElementById('tableSearch').addEventListener('input', function () {
            var query = this.value.toLowerCase();
            var rows = document.querySelectorAll('#myTable tbody tr');

            rows.forEach(function (row) {
                if (row.querySelector('td[colspan]')) {
                    return;
                }
                row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
        });
    </script>

<?php include("footer.php"); ?>
