<?php include("header.php"); ?>
<!-- Page Body Start-->
<div class="page-body-wrapper" style="background:#f9f9f9;">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Driver Report</h3>
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid starts-->
        <div class="container-fluid default-dashboard">
            <div class="uk-grid-small" uk-grid>
                <div class="uk-width-1-1@m">
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                        <form id="selectionForm" action="" method="post">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="driver">Driver</label>
                                        <select class="form-control" name="driver" id="driver" required>
                                            <option value="">Select Driver</option>
                                            <?php foreach ($drivers as $driver): ?>
                                                <option value="<?= $driver->id; ?>"><?= $driver->name; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="from_date">From Date</label>
                                        <input type="date" class="form-control" name="from_date" id="from_date" required value="<?= date('Y-m-01'); ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="to_date">To Date</label>
                                        <input type="date" class="form-control" name="to_date" id="to_date" required value="<?= date('Y-m-d'); ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label><br>
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="uk-card uk-card-body uk-card-default uk-card-small uk-margin-top">
                        <div id="results">
                            <p class="text-center text-muted">Select filters and click Submit to view the report.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid Ends-->
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        $('#selectionForm').on('submit', function(e) {
            e.preventDefault();
            $('#results').html('<p class="text-center">Loading...</p>');

            $.ajax({
                url: '<?php echo base_url(); ?>/admin/get_driver_report_details',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#results').html(response);
                },
                error: function(xhr, status, error) {
                    $('#results').html('<p class="text-danger text-center">' + xhr.responseText + '</p>');
                }
            });
        });
    });

    function downloadExcel() {
        var driver = $('#driver').val();
        var from_date = $('#from_date').val();
        var to_date = $('#to_date').val();

        if (!driver || !from_date || !to_date) {
            alert('Please select Driver and Date Range first.');
            return;
        }

        var form = $('<form>', {
            'method': 'POST',
            'action': '<?php echo base_url(); ?>/admin/get_driver_report_excel'
        }).append($('<input>', {
            'name': 'driver',
            'value': driver,
            'type': 'hidden'
        })).append($('<input>', {
            'name': 'from_date',
            'value': from_date,
            'type': 'hidden'
        })).append($('<input>', {
            'name': 'to_date',
            'value': to_date,
            'type': 'hidden'
        }));

        $('body').append(form);
        form.submit();
        form.remove();
    }
</script>

<!-- footer start-->
<?php include("footer.php"); ?>
