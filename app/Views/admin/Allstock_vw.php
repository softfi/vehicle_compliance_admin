<?php include("header.php"); ?>
<?php 
	use App\Models\AdminModel;
	$db = db_connect();
	$this->AdminModel = new AdminModel($db);
?>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
 #myTable thead th {
        position: sticky;
        top: 0;
        background: white; /* Or match your table background */
        z-index: 10;
        border-bottom: 1px solid #ddd; /* optional for better visibility */
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
            <input type="text" id="tableSearch" placeholder="Search..." class="uk-input uk-margin-small-bottom" style="width: 200px;">
            <div class="uk-grid-small uk-child-width-expand@m" uk-grid>
                <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-top">
                    <div style="max-height: 400px; overflow-y: auto;">
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
                                    <th>View</th>
                                    <?php if (in_array(1.4, $jobAssign)): ?>
                                        <th>Edit</th>
                                    <?php endif; ?>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $m = 1; foreach ($stock_dtls as $stock): ?>
                                    <tr>
                                        <td><?= $m++; ?></td>
                                        <td><?= date('d-m-Y', strtotime($stock->date)); ?></td>
                                        <td><?= $stock->invoice_number; ?></td>
                                        <td><?= $stock->supplier_name; ?></td>
                                        <td><?= $stock->total_quantity; ?></td>
                                        <td><?= $stock->total_gst_amount; ?></td>
                                        <td><?= $stock->location_name; ?></td>
                                        <td>
                                            <?php if (in_array(1.2, $jobAssign)): ?>
                                                <a class="btn btn-sm btn-primary" href="#modal<?= $stock->stock_code; ?>" uk-toggle>View</a>
                                            <?php endif; ?>
                                            
                                            <!-- Modal -->
                                            <div id="modal<?= $stock->stock_code; ?>" class="uk-modal-container" uk-modal>
                                                <div class="uk-modal-dialog uk-modal-body">
                                                    <button class="uk-modal-close-default" type="button" uk-close></button>

                                                    <h5>
                                                        Invoice No: <?= esc($stock->invoice_number); ?><br>
                                                        Order Date: <?= date('d-m-Y', strtotime($stock->date)); ?>
                                                    </h5>

                                                    <!-- Download Button -->
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
                                                                $amount = $stk_dtls->quantity * $stk_dtls->rate;
                                                                $total += $amount;
                                                            ?>
                                                                <tr>
                                                                    <td><?= $i++; ?></td>

                                                                    <!-- Product Name + Item ID -->
                                                                    <td><?= esc($stk_dtls->item_name); ?> (<?= esc($stk_dtls->item_id); ?>)</td>

                                                                    <!-- Supplier Name ONLY -->
                                                                    <td><?= esc($stk_dtls->name); ?></td>

                                                                    <td><?= $stk_dtls->quantity . ' ' . esc($stk_dtls->unit_name); ?></td>
                                                                    <td><?= number_format($stk_dtls->rate, 2); ?></td>
                                                                    <td><?= number_format($amount, 2); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>

                                                        <!-- Total Row -->
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
                                                <?php if (strpos($stock->invoice_number, 'stock-trans') === 0): ?>
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
            <!-- Container-fluid Ends-->
    </div>
  
    <!-- Delete Form (Only Once) -->
    <form name="frm_deleteBanner" id="frm_deleteBanner" action="<?= base_url(); ?>/admin/delete_stock" method="post">
        <input type="hidden" name="user_id" id="user_id" value="">
    </form>

    <!-- Delete Script -->
    <script type="text/javascript">
        function deleteRecord(id) {
            var conf = confirm("Are you sure want to delete this record?");
            if (conf) {
                document.getElementById('user_id').value = id;
                document.getElementById('frm_deleteBanner').submit();
            }
        }
    </script>
    <form name="frm_deleteBanner" id="frm_deleteBanner" action="<?php echo base_url(); ?>/admin/deleteordercart" method="post">
        <input type="hidden" name="user_id" id="user_id" value="">
    </form>

    <script type="text/javascript">
        function deleteRecord(id) {
            $("#operation").val('delete');
            $("#user_id").val(id);
            var conf = confirm("Are you sure want to delete this record");
            if (conf) {
                $("#frm_deleteBanner").submit();
            }
        }
    </script>
   




<?php include("footer.php"); ?>
