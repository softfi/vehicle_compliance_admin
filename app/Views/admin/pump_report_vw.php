<!-- Page Body Start -->
<?php include("header.php");?>
  <!-- Page Body Start-->
      <div class="page-body-wrapper uk-background-muted" style="background:#fcfcfc">
            <?php include("mainsidebar.php"); ?>
            
         <div class="page-body">
                  <div class="container-fluid">        
                    <div class="page-title">
                      <div class="row">
                        <div class="col-sm-6 p-0">
                          <h3>Pump Ledger</h3>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Container-fluid starts-->    
   <div class="container-fluid default-dashboard">
            <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-bottom">
                <form action="<?= base_url(); ?>/Admin/pump_report" method="post" class="">
                    <div class="uk-grid-small" uk-grid>
                        <div class="uk-width-1-4@s">
                            <label for="from_date">From Date</label>
                            <input class="uk-input" type="date" id="from_date" name="from_date" value="<?= $filter_data['from_date'] ?>" required>
                        </div>
                        <div class="uk-width-1-4@s">
                            <label for="to_date">To Date</label>
                            <input class="uk-input" type="date" id="to_date" name="to_date" value="<?= $filter_data['to_date'] ?>" required>
                        </div>
                            <div class="uk-width-1-4@s">
                            <label>Select Pump</label>
                            <select name="pump_id" class="uk-input select2-search" id="single" required>
                                <option value="">Select Pump</option>
                                <?php foreach ($Allvendor as $pump) { ?>
                                    <option value="<?= $pump->id ?>" <?= $filter_data['pump_id'] == $pump->id ? "selected" : "" ?>><?= $pump->name ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="uk-width-auto@s uk-flex uk-flex-middle">
                            <?php if(in_array(42.1,$jobAssign)){ ?>
                            <button class="uk-button uk-button-primary" type="submit">Submit</button>
                            <?php } ?>
                        </div>
                        <div class="uk-width-expand@s uk-flex uk-flex-middle uk-flex-right">
                            <button id="downloadExcel" class="uk-button uk-button-secondary" type="button">Download Excel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Section -->
        <div class="container-fluid">
            <div class="uk-grid uk-child-width-1-2@m uk-grid-small uk-grid-match" uk-grid>
                <div>
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                        <h4>Pump Credit Payment</h4>
                        <div class="table-responsive custom-scrollbar">
                                                    <table class="uk-table uk-table-striped uk-table-small">
    <thead>
        <tr>
            <th>Sl No</th>
            <th>Pump Name</th>
            <th>Date</th>
            <th>Bank Name</th>
            <th>Location</th>
            <th>Bank/Cash</th>
            <th>UTR No.</th>
            <th>Amount</th>
            <th>Opening Balance</th>
            <th>File</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 1;
        $total_credit = 0;
        $total_opn_bal = 0;

        if (!empty($AllPaymentVoucherCredit)) {
            foreach ($AllPaymentVoucherCredit as $credit) {
                $total_credit += $credit->amount;
                $total_opn_bal += $credit->bal;
                ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= esc($credit->vendor_name); ?></td>
                    <td>
                        <?= date('d-m-Y', strtotime($credit->pay_date)); ?>
                    </td>
                    <td><?= esc($credit->bank_name); ?></td>
                    <td><?= esc($credit->location_name); ?></td>
                    <td>
                        <?= $credit->bank_cash == 1 ? 'Cash' : ($credit->bank_cash == 2 ? 'UPI' : ''); ?>
                    </td>
                    <td><?= esc($credit->utr_no); ?></td>
                    <td><?= number_format($credit->amount, 2); ?></td>
                    <td><?= number_format($credit->bal, 2); ?></td>
                    <td>
                        <?php if (!empty($credit->upload_file) && file_exists(FCPATH . $credit->upload_file)) : ?>
                            <a href="<?= base_url($credit->upload_file); ?>" target="_blank">View File</a>
                        <?php else : ?>
                            No File
                        <?php endif; ?>
                    </td>
                </tr>
            <?php }
        } else { ?>
            <tr>
                <td colspan="9">No records found.</td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="7" class="uk-text-right">Total Credit</th>
            <th><?= number_format($total_credit, 2); ?></th>
            <th>Total opn Bal=<?= number_format($total_opn_bal, 2); ?></th>
        </tr>
    </tfoot>
</table>
                        </div>
                    </div>
                </div>
          <div>
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                        <h4>Pump Debit Payment</h4>
                        <div class="table-responsive custom-scrollbar">
                                                    <table class="uk-table uk-table-striped uk-table-small">
    <thead>
        <tr>
            <th>Sl No</th>
            <th>Pump Name</th>
            <th>Date</th>
            <th>Bank Name</th>
            <th>Location</th>
            <th>Bank/Cash</th>
            <th>UTR No.</th>
            <th>Amount</th>
            <th>File</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 1;
        $total_debit = 0;

        if (!empty($AllPaymentVoucherDebit)) {
            foreach ($AllPaymentVoucherDebit as $debit) {
                $total_debit += $debit->amount;
                ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= esc($debit->vendor_name); ?></td>
                    <td>
                        <?= date('d-m-Y', strtotime($debit->pay_date)); ?>
                    </td>
                    <td><?= esc($debit->bank_name); ?></td>
                    <td><?= esc($debit->location_name); ?></td>
                    <td>
                        <?= $debit->bank_cash == 1 ? 'Cash' : ($debit->bank_cash == 2 ? 'UPI' : ''); ?>
                    </td>
                    <td><?= esc($debit->utr_no); ?></td>
                    <td><?= number_format($debit->amount, 2); ?></td>
                    <td>
                        <?php if (!empty($debit->upload_file) && file_exists(FCPATH . $debit->upload_file)) : ?>
                            <a href="<?= base_url($debit->upload_file); ?>" target="_blank">View File</a>
                        <?php else : ?>
                            No File
                        <?php endif; ?>
                    </td>
                </tr>
            <?php }
        } else { ?>
            <tr>
                <td colspan="9">No records found.</td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="7" class="uk-text-right">Total debit</th>
            <th><?= number_format($total_debit, 2); ?></th>
            <th></th>
        </tr>
    </tfoot>
</table>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="uk-card uk-card-body uk-card-secondary uk-card-small">
                        <h4>Total Profit/Loss</h4>
                        <div class="table-responsive custom-scrollbar">
                          <table class="uk-table uk-table-striped uk-table-hover uk-table-responsive">
    <tr>
        <th>Total Opening Balance</th>
        <td><?= number_format($total_opn_bal, 2); ?></td>
    </tr>
    <tr>
        <th>Total Credit</th>
        <td><?= number_format($total_credit, 2); ?></td>
    </tr>
    <tr>
        <th>Total Debit</th>
        <td><?= number_format($total_debit, 2); ?></td>
    </tr>
    <tr>
        <th>Total Profit/Loss</th>
        <td>
            <?= number_format($total_opn_bal+$total_credit - $total_debit, 2); ?>
        </td>
    </tr>
</table>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* Select2 Searchable Dropdown Styling */
            .select2-container--default .select2-selection--single {
                height: 38px !important;
                padding: 5px;
                border: 1px solid #e5e5e5;
                border-radius: 4px;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 26px !important;
                color: #333;
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px !important;
            }
            .select2-search { width: 100% !important; }
        </style>

        <script>
            $(document).ready(function () {
                // Initialize Select2 for pump selection
                if ($('.select2-search').length > 0) {
                    $('.select2-search').select2({
                        placeholder: "Select Pump",
                        allowClear: true,
                        width: '100%'
                    });
                }

                $('#downloadExcel').click(function () {
                    const fromDate = $('#from_date').val();
                    const toDate = $('#to_date').val();
                    const pumpId = $('#single').val();

                    if (!fromDate || !toDate || !pumpId) {
                        alert('Please select From Date, To Date and Pump.');
                        return;
                    }

                    const exportUrl = '<?= base_url(); ?>/Admin/exportpump_paymentExcel?from_date='
                        + encodeURIComponent(fromDate) + '&to_date='
                        + encodeURIComponent(toDate) + '&pump_id='
                        + encodeURIComponent(pumpId);

                    window.location.href = exportUrl;
                });
            });
        </script>
    </div>
</div>
        <?php include("footer.php"); ?>
