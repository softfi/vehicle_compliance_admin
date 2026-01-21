<?php include("header.php"); 
$db = db_connect();
$this->db = db_connect();
?>
<!-- Page Body Start-->
<div class="page-body-wrapper" style="background:#ececec;">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Voucher View</h3>
                    </div>
                    <div class="col-sm-6 p-0"></div>
                </div>
            </div>
        </div>

        <!-- Container-fluid starts-->
        <div class="container-fluid default-dashboard">
            <div class="uk-grid-small" uk-grid>
                <div class="uk-width-1-1@m">
                   <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-bottom uk-margin-small">
    <form method="post" action="<?php echo base_url(); ?>/Admin/voucher_commition_entry">
        <?php
        $default_from_date = $date['from_date'] ?? date('Y-m-01');
        $default_to_date = $date['to_date'] ?? date('Y-m-d');
        ?>
        <div class="uk-grid-small uk-child-width-expand" uk-grid>
            <div>
                <label for="from_date">From Date:</label>
                <input type="date" id="from_date" name="from_date" class="uk-input" value="<?= $default_from_date; ?>" />
            </div>
            <div>
                <label for="to_date">To Date:</label>
                <input type="date" id="to_date" name="to_date" class="uk-input" value="<?= $default_to_date; ?>" />
            </div>
            <div>
                <label for="do_no">DO No.</label>
                <select class="form-control" name="do_no" id="do_no">
                    <option value="">Select DO No.</option>
                    <?php foreach ($doregistration as $do): ?>
                        <option value="<?= $do->do_registration_id; ?>"><?= $do->do_no; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="submit_button">.</label>
                <button type="submit" class="uk-button uk-button-primary uk-width-1-1" id="submit_button">Filter</button>
            </div>
            <div>
                <label for="download_button">.</label>
<a href="#" id="download_excel" class="uk-button uk-button-secondary uk-width-1-1">Download Excel</a>
            </div>
        </div>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        function fetchDoNumbers() {
            var from_date = $("#from_date").val();
            var to_date = $("#to_date").val();

            $.ajax({
                url: "<?= base_url('Admin/getDoNumbers1') ?>", // Call your controller method
                type: "POST",
                data: { from_date: from_date, to_date: to_date },
                success: function(response) {
                    $("#do_no").html(response); // Update dropdown
                }
            });
        }

        // Trigger on date change
        $("#from_date, #to_date").on("change", function() {
            fetchDoNumbers();
        });

        // Trigger on page load (optional)
        fetchDoNumbers();
    });
</script>


                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                            <div class="table-responsive custom-scrollbar">
<table class="display" id="row_create" style="width:100%">
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
            <th>Diesel</th>
            <th>Driver Exp</th>
            
            <th>Total Deduction	</th>
            <th>Net Amount	</th>
            <th>Challan Deposited</th>
           <th> Deposited by</th>	
            <th>Deposited date</th>
            <th>Commition</th>

        </tr>
    </thead>
    <tbody>
        <?php $i = 1; foreach ($despatch as $des): ?>
            <tr data-id="<?= $des->despatch_id; ?>" >
                
                <td><?= $i++; ?></td>
                <td><?= date('d-m-Y', strtotime($des->des_date)); ?></td>
                <td> <?= $des->doreg_no; ?></td>
                <td><?= $des->vehicle_number; ?></td>
                <td><?= $des->ref_no; ?></td>
                <td class="quantity"><?= $des->quantity; ?></td>
                <td><?= $des->rest_amount; ?></td>
                <td class="rate"><?= $des->rate; ?></td>
                <td><?= $des->shortage; ?></td>
                <td><?= $des->freight; ?></td>
                <td><?= $des->shortage_price; ?></td>
                <td><?= $des->totaldieselRate; ?></td>
                <td><?= $des->driver_expence; ?></td>
                <td><?= $des->total_deduction; ?></td>
                <td><?= $des->net_amount; ?></td>
                
                <td>
                    <input type="checkbox" value="1" name="deposited" id="deposited" 
                        <?php if($des->deposited == 1) { echo "checked"; } ?> 
                        oninput="updateDispatch(this)" 
                    /> Yes
                </td>

                <td><?= $des->deposit_by; ?></td>
                <td><?= $des->deposit_date; ?></td>
                <td>
    <input type="text" name="commition" class="uk-input commition" 
        value="<?= $des->commition; ?>" 
        oninput="updateCommition(this)" 
    />
</td>

            </tr>
        <?php endforeach; ?>
    </tbody>
</table>                            </div>
                    </div>
                </div>
            </div>
            <!-- Container-fluid Ends-->
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    function updateCommition(input) {
        let row = input.closest("tr"); // Get the current row
        let dispatchId = row.getAttribute("data-id"); // Fetch dispatch ID
        let commition = row.querySelector(".commition").value.trim(); // Get new commission value

        $.ajax({
            url: "<?= base_url('Admin/updateCommition') ?>", 
            type: "POST",
            data: {
                id: dispatchId,
                commition: commition,
                <?= csrf_token() ?>: "<?= csrf_hash() ?>" // Include CSRF token
            },
            success: function(response) {
                if (response.status === 'success') {
                    console.log("Commission updated successfully:", response);
                } else {
                    console.error("Error:", response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error updating commission:", error);
            }
        });
    }
</script>

<script>
    document.getElementById("download_excel").addEventListener("click", function (e) {
        e.preventDefault();

        // Get filter values
        let from_date = document.getElementById("from_date").value;
        let to_date = document.getElementById("to_date").value;
        let do_no = document.getElementById("do_no").value;

        // Redirect to the export function with corrected URL
        window.location.href = "<?= base_url('Admin/export_voucher_commition'); ?>?from_date=" + encodeURIComponent(from_date) + "&to_date=" + encodeURIComponent(to_date) + "&do_no=" + encodeURIComponent(do_no);
    });
</script>
<?php include("footer.php"); ?>
