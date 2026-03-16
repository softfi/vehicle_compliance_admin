<?php include("header.php"); ?>
      <!-- Page Body Start-->
      <div class="page-body-wrapper" style="background:#f9f9f9;">
            <?php include("mainsidebar.php"); ?>
                <div class="page-body">
                  <div class="container-fluid">        
                    <div class="page-title">
                      <div class="row">
                        <div class="col-sm-6 p-0">
                          <h3>Driver Salary </h3>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- Container-fluid starts-->
                  <div class="container-fluid default-dashboard">
                  
                 </div>
                 
                  <div class="container-fluid default-dashboard">
            <div class="uk-grid-small" uk-grid>
                <div class="uk-width-1-1@m">
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                       <form id="selectionForm" action="" method="post">
                            <div class="uk-grid-small uk-child-width-expand" uk-grid>
                                <div class="form-group">
                                    <label for="year">Year</label>
                                    <select class="form-control" name="year" id="year">
                                        <?php
$currentYear = date('Y');
for ($y = 2023; $y <= 2040; $y++): ?>
                                            <option value="<?= $y; ?>" <?= $y == $currentYear ? 'selected' : ''; ?>><?= $y; ?></option>
                                        <?php
endfor; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="month">Month</label>
                                    <select class="form-control" name="month" id="month">
                                        <?php
$currentMonth = date('n');
$months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
];
foreach ($months as $index => $month): ?>
                                            <option value="<?= $index + 1; ?>" <?=($index + 1) == $currentMonth ? 'selected' : ''; ?>><?= $month; ?></option>
                                        <?php
endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="location">Location</label>
                                    <select name="location" id="location" class="form-control">
                                        <option value="">Select location</option>
                                        <?php foreach ($location as $loc): ?>
                                            <option value="<?= $loc->location_id; ?>"><?= $loc->location_name; ?></option>
                                        <?php
endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="driver_id">Driver <span class="text-danger">*</span></label>
                                    <select name="driver_id" id="driver_id" class="form-control select2-search" required>
                                        <option value="">Select driver</option>
                                        <?php foreach ($drivers as $driver): ?>
                                            <option value="<?= $driver->id; ?>"><?= $driver->name; ?></option>
                                        <?php
endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>.</label><br>
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                    <button class="btn btn-primary" type="button" onclick="downloadExcel()">Download Excel</button>
                                    <!-- <button class="btn btn-success" type="button" onclick="sendWhatsAppBulk()">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:middle;margin-right:4px"><path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/></svg>
                                        Send WhatsApp
                                    </button> -->
                                </div>
    </div>
                        </form>

                    </div>
                    <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-top">
                        <div id="loader" class="uk-text-center" style="display: none;">
                            <div uk-spinner="ratio: 2"></div>
                            <p>Loading...</p>
                        </div>
                        <!-- Print Slip — above the select-all checkbox/table -->
                        <div style="display:flex; justify-content:flex-start; margin-bottom:8px;">
                            <button class="btn btn-info" type="button" onclick="printSalarySlip()">
                                🖨 Print Slip
                            </button>
                        </div>
                        <div id="results"></div>
                    </div>
                </div>
            </div>
        </div>
                </div>             
               
          <!-- Container-fluid Ends-->
        </div>
        
        
        
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <!-- UIkit Loader -->




<script>
// ─── Select All Checkbox ─────────────────────────────────────────
$(document).on('change', '#selectAllDrivers', function() {
    $('.driver-select-cb').prop('checked', this.checked);
});
// Keep select-all in sync when individual boxes change
$(document).on('change', '.driver-select-cb', function() {
    var total    = $('.driver-select-cb').length;
    var checked  = $('.driver-select-cb:checked').length;
    $('#selectAllDrivers').prop('checked', total === checked);
    $('#selectAllDrivers').prop('indeterminate', checked > 0 && checked < total);
});

// ─── Bulk WhatsApp Send via API ──────────────────────────────────────────────
function sendWhatsAppBulk() {
    var drivers = [];
    var noPhone = [];

    $('.driver-select-cb:checked').each(function() {
        var mobile   = $(this).data('mobile');
        var name     = $(this).data('name');
        var staffId  = $(this).data('staff-id');
        var year     = $(this).data('year');
        var month    = $(this).data('month');
        var fromDate = $(this).data('from-date');
        var toDate   = $(this).data('to-date');

        if (!mobile || mobile.toString().trim() === '') {
            noPhone.push(name);
            return;
        }
        drivers.push({ staff_id: staffId, year: year, month: month, from_date: fromDate, to_date: toDate });
    });

    if (drivers.length === 0 && noPhone.length === 0) {
        alert('Please select at least one driver.');
        return;
    }
    if (noPhone.length > 0) {
        alert('⚠️ The following drivers have no mobile number and will be skipped:\n' + noPhone.join('\n'));
    }
    if (drivers.length === 0) return;

    if (!confirm('Send WhatsApp salary slip to ' + drivers.length + ' driver(s) via API?')) return;

    // Show progress overlay
    var $overlay = $('<div id="wa-sending-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;\
background:rgba(0,0,0,0.5);z-index:99999;display:flex;align-items:center;justify-content:center;">\
<div style="background:#fff;padding:30px 40px;border-radius:12px;text-align:center;box-shadow:0 8px 32px rgba(0,0,0,0.3);">\
<div uk-spinner="ratio: 2" style="color:#25D366"></div>\
<p style="margin-top:15px;font-size:16px;font-weight:600;">Sending WhatsApp messages...</p>\
<p style="color:#888;font-size:13px;">Please wait, do not close this window.</p></div></div>');
    $('body').append($overlay);

    $.ajax({
        url: '<?php echo base_url(); ?>admin/send_salary_whatsapp_bulk',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ drivers: drivers }),
        success: function(res) {
            $('#wa-sending-overlay').remove();

            // If API returned an error (e.g. credentials not configured), show alert
            if (!res.success && !res.details) {
                alert('❌ ' + (res.message || 'WhatsApp sending failed. Please check your API credentials in .env file.'));
                return;
            }

            var sentCount   = res.sent   ?? 0;
            var failedCount = res.failed ?? 0;

            // Build result report
            var html = '<div style="max-height:300px;overflow-y:auto;">';
            html += '<table class="table table-sm table-bordered" style="font-size:13px;">';
            html += '<thead><tr><th>Driver</th><th>Mobile</th><th>Status</th><th>Note</th></tr></thead><tbody>';
            if (res.details && res.details.length > 0) {
                res.details.forEach(function(d) {
                    var badge = d.status === 'sent'
                        ? '<span style="color:green;font-weight:600;">✓ Sent</span>'
                        : '<span style="color:red;font-weight:600;">✗ ' + (d.status === 'skipped' ? 'Skipped' : 'Failed') + '</span>';
                    html += '<tr><td>' + d.name + '</td><td>' + (d.mobile || '-') + '</td><td>' + badge + '</td><td>' + (d.reason || '') + '</td></tr>';
                });
            }
            html += '</tbody></table></div>';
            html += '<p style="margin-top:10px;font-size:14px;font-weight:600;">'
                  + '✓ Sent: <span style="color:green">' + sentCount + '</span> &nbsp;|&nbsp; '
                  + '✗ Failed/Skipped: <span style="color:red">' + failedCount + '</span></p>';

            // Show UIkit modal with result
            var modalHtml = '<div id="waResultModal" uk-modal>\
                <div class="uk-modal-dialog">\
                <button class="uk-modal-close-default" type="button" uk-close></button>\
                <div class="uk-modal-header"><h2 class="uk-modal-title">WhatsApp Send Results</h2></div>\
                <div class="uk-modal-body">' + html + '</div></div></div>';
            $('body').append(modalHtml);
            UIkit.modal('#waResultModal').show();
            $('#waResultModal').on('hidden', function(){ $(this).remove(); });
        },
        error: function(xhr) {
            $('#wa-sending-overlay').remove();
            var msg = 'An error occurred while sending.';
            try { var j = JSON.parse(xhr.responseText); msg = j.message || msg; } catch(e){}
            alert('❌ ' + msg);
        }
    });
}

$(document).ready(function() {
    $('#selectionForm').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        // Driver is required — block if not selected
        if (!$('#driver_id').val()) {
            alert('Please select a driver first.');
            return;
        }

        // Show loader before making AJAX request
        $('#loader').show();
        $('#results').html(''); // Clear previous results

        $.ajax({
            url: '<?php echo base_url(); ?>/admin/getdriver_salary_details', // Replace with actual URL
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Hide loader after data is loaded
                $('#loader').hide();
                // Display the result
                $('#results').html(response);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error: ' + status + error);
                $('#loader').hide(); // Hide loader even on error
            }
        });
    });
});
</script>

<script>
    function downloadExcel() {
        // Get the selected values from the dropdowns
        var year = document.getElementById('year').value;
        var month = document.getElementById('month').value;
        var location = document.getElementById('location').value;
        var driver_id = document.getElementById('driver_id').value;

        // Create a form element
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url(); ?>/admin/getdriver_salary_details_excel';

        // Create input elements for form data
        var inputYear = document.createElement('input');
        inputYear.type = 'hidden';
        inputYear.name = 'year';
        inputYear.value = year;

        var inputMonth = document.createElement('input');
        inputMonth.type = 'hidden';
        inputMonth.name = 'month';
        inputMonth.value = month;

        var inputLocation = document.createElement('input');
        inputLocation.type = 'hidden';
        inputLocation.name = 'location';
        inputLocation.value = location;

        var inputDriver = document.createElement('input');
        inputDriver.type = 'hidden';
        inputDriver.name = 'driver_id';
        inputDriver.value = driver_id;

        // Append inputs to the form
        form.appendChild(inputYear);
        form.appendChild(inputMonth);
        form.appendChild(inputLocation);
        form.appendChild(inputDriver);

        // Append form to the body
        document.body.appendChild(form);

        // Submit the form
        form.submit();

        // Remove the form from the document
        document.body.removeChild(form);
    }

    function printSalarySlip() {
        var year = document.getElementById('year').value;
        var month = document.getElementById('month').value;
        var driver_id = document.getElementById('driver_id').value;
        if (!driver_id) {
            alert("Please select a driver first.");
            return;
        }
        window.open('<?= base_url(); ?>/admin/salary_slip?staff_id=' + driver_id + '&year=' + year + '&month=' + month, '_blank');
    }
</script>

<style>
    /* Full-Screen Loader */
#loader {
    position: fixed; /* Full screen */
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8); /* Light overlay */
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999; /* High priority */
}

/* Optional: Loader Text Styling */
#loader p {
    font-size: 18px;
    font-weight: bold;
    color: #333;
    margin-top: 10px;
}

</style>
<!-- Payment Modal (UIkit) -->
<div id="paymentModal" uk-modal>
    <div class="uk-modal-dialog">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <div class="uk-modal-header">
            <h2 class="uk-modal-title">Process Salary Payment</h2>
        </div>
        <div class="uk-modal-body">
            <form id="salaryPaymentForm" class="uk-form-stacked">
                <input type="hidden" name="staff_id" id="modal_staff_id">
                <input type="hidden" name="opening_balance" id="modal_opening_balance_hidden">
                <input type="hidden" name="year" id="modal_year">
                <input type="hidden" name="month" id="modal_month">
                
                <div class="uk-margin">
                    <label class="uk-form-label">Driver Name</label>
                    <div class="uk-form-controls">
                        <input type="text" class="uk-input" id="modal_driver_name" readonly>
                    </div>
                </div>
                
                <div class="uk-margin">
                    <label class="uk-form-label">Calculated Net Salary</label>
                    <div class="uk-form-controls">
                        <input type="number" step="0.01" class="uk-input" name="calculated_net" id="modal_net_salary" readonly>
                    </div>
                </div>
                
                <div class="uk-margin">
                    <label class="uk-form-label">Paid Amount <span class="text-danger">*</span></label>
                    <div class="uk-form-controls">
                        <input type="number" step="0.01" class="uk-input" name="paid_amount" id="modal_paid_amount" required>
                    </div>
                </div>
                
                <div id="balance_preview" class="uk-alert uk-alert-primary uk-margin-small-top" style="display:none;">
                    Next Month Opening Balance: <strong id="next_month_balance">0.00</strong>
                </div>
            </form>
        </div>
        <div class="uk-modal-footer uk-text-right">
            <button class="uk-button uk-button-default uk-modal-close" type="button">Cancel</button>
            <button class="uk-button uk-button-primary" type="button" onclick="submitSalaryPayment()">Submit Payment</button>
        </div>
    </div>
</div>

<script>
function openPaymentModal(staffId, staffName, netSalary, openingBalance) {
    document.getElementById('modal_staff_id').value = staffId;
    document.getElementById('modal_opening_balance_hidden').value = openingBalance;
    document.getElementById('modal_driver_name').value = staffName;
    document.getElementById('modal_net_salary').value = netSalary;
    document.getElementById('modal_paid_amount').value = netSalary; // Default to full payment
    document.getElementById('modal_year').value = document.getElementById('year').value;
    document.getElementById('modal_month').value = document.getElementById('month').value;
    
    updateBalancePreview();
    UIkit.modal('#paymentModal').show();
}

// Update balance preview on input change
document.getElementById('modal_paid_amount').addEventListener('input', function() {
    updateBalancePreview();
});

function updateBalancePreview() {
    var net = parseFloat(document.getElementById('modal_net_salary').value) || 0;
    var paid = parseFloat(document.getElementById('modal_paid_amount').value) || 0;
    var balance = (net - paid).toFixed(2);
    
    document.getElementById('next_month_balance').innerText = balance;
    document.getElementById('balance_preview').style.display = 'block';
}

function submitSalaryPayment() {
    var paid = document.getElementById('modal_paid_amount').value;
    if (!paid || paid === "") {
        alert('Please enter a valid paid amount.');
        return;
    }

    if (parseFloat(paid) <= 0) {
        if (!confirm('Are you sure you want to process 0 or negative payment?')) {
            return;
        }
    }

    $('#loader').show();
    $.ajax({
        url: '<?php echo base_url(); ?>/admin/process_salary_payment',
        type: 'POST',
        data: $('#salaryPaymentForm').serialize(),
        success: function(response) {
            $('#loader').hide();
            UIkit.modal('#paymentModal').hide();
            if (response.success) {
                alert(response.message);
                $('#selectionForm').submit(); // Refresh results
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            $('#loader').hide();
            alert('An error occurred while processing the payment.');
        }
    });
}
</script>

        <!-- footer start-->
       <?php include("footer.php"); ?>