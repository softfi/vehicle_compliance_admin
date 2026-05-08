<?php include("header.php"); ?>
<!-- Page Body Start-->
<div class="page-body-wrapper" style="background:#f9f9f9;">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Staff Salary </h3>
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid starts-->
        <style>
            .select2-container--default .select2-selection--single {
                height: 38px !important;
                border: 1px solid #ced4da !important;
                padding-top: 5px;
            }
            .select2-container {
                display: block !important;
            }
        </style>
        <div class="container-fluid default-dashboard">
            <div class="uk-grid-small" uk-grid>
                <div class="uk-width-1-1@m">
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                       <form id="selectionForm" action="" method="post">
    <div class="uk-grid-small uk-child-width-expand" uk-grid>
        <div class="form-group">
            <label for="year">Year</label>
            <select class="form-control select2-search" name="year" id="year">
                <?php 
                $currentYear = date('Y');
                for ($y = 2023; $y <= 2040; $y++): ?>
                    <option value="<?= $y; ?>" <?= $y == $currentYear ? 'selected' : ''; ?>><?= $y; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="month">Month</label>
            <select class="form-control select2-search" name="month" id="month">
                <?php
                $currentMonth = date('n');
                $months = [
                    "January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December"
                ];
                foreach ($months as $index => $month): ?>
                    <option value="<?= $index + 1; ?>" <?= ($index + 1) == $currentMonth ? 'selected' : ''; ?>><?= $month; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="location">Location</label>
            <select name="location" id="location" class="form-control select2-search">
                <option value="">Select location</option>
                <?php foreach ($location as $loc): ?>
                    <option value="<?= $loc->location_id; ?>"><?= $loc->location_name; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>.</label><br>
            <button type="submit" class="btn btn-primary">Submit</button>
             <button class="btn btn-primary" type="button" onclick="downloadExcel()">Download Excel</button>

        </div>
    </div>
</form>

                    </div>
                    <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-top">
                        <div id="results"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid Ends-->
    </div>
</div>


<script>
    $(document).ready(function() {
        $('#location, #year, #month').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });

        $('#selectionForm').on('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            $.ajax({
                url: '<?php echo base_url(); ?>/admin/getstaff_salary_details', // Replace with the actual URL to your PHP AJAX handler
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    // Display the result in the results div
                    $('#results').html(response);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ' + status + error);
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

        // Create a form element
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url(); ?>/admin/getstaff_salary_details_excel';

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

        // Append inputs to the form
        form.appendChild(inputYear);
        form.appendChild(inputMonth);
        form.appendChild(inputLocation);

        // Append form to the body
        document.body.appendChild(form);

        // Submit the form
        form.submit();

        // Remove the form from the document
        document.body.removeChild(form);
    }
</script>

<button class="btn btn-primary" type="button" onclick="downloadExcel()">Download Excel</button>


<!-- footer start-->
<?php include("footer.php"); ?>
