<?php include("header.php"); ?>
<!-- Page Body Start-->
<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">        
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Tyre Management</h3>
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid starts-->
        <div class="container-fluid default-dashboard">
            
            
            <div class="uk-card uk-card-body uk-card-default uk-card-small">
                <form method="post" action="<?=base_url()?>/Admin/tyer_report">
            <div class="uk-grid">
                 <div class="uk-width-1-4">
              
                <select id="location_id" name="location_id" class="form-control">
                    <option value="">Select location</option>
                    <?php foreach ($location as $loc) { ?>
                        <option value="<?= $loc->location_id; ?>"><?= $loc->location_name; ?></option>
                    <?php } ?>
                </select>
            </div>
                <div class="uk-width-1-4">
                    <?php if(in_array(26.1,$jobAssign)){ ?>
                    <button class="btn btn-primary">Filter</button>
                    <?php }?>
                </div>

                <a class="btn btn-primary" type="button" onclick="downloadExcel()">Download Excel</a>
                
            </div>
            </div>
            </form>
                <div class="table-responsive custom-scrollbar">
                    <table class="display" id="row_create" style="width:100%">
                        <thead>
                            <tr>
                                <th>Sl no</th>
                                <th>Bill no</th>
                                <th>Seriel  No</th>
                                <th>Location</th>
                                <th>Brand Name</th>
                                <th>Tyer Type</th>
                                <th>Model</th>
                                <th>Tyre Condition</th>
                                <th>Action</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            foreach ($tyer_data as $tyer): ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= $tyer->bill_no ?></td>
                                <td><?= $tyer->tyer_sl_no ?></td>
                                <td><?= $tyer->location_name ?></td>
                                <td><?= $tyer->brand_name ?></td>
                                <td><?= $tyer->tyer_type ?></td>
                                <td><?= $tyer->model ?></td>
                                <td>
                                    <?php if ($tyer->tyre_condition == 'Old'): ?>
                                        <span class="uk-label uk-label-warning">Old</span>
                                    <?php else: ?>
                                        <span class="uk-label uk-label-success">New</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a class="btn btn-primary" href="<?= base_url(); ?>/admin/tyer_exchange/<?= $tyer->id ?>" > Exchange/repair/trash </a>
                                    <a class="btn btn-primary" href="<?= base_url(); ?>/admin/tyre_details_vw/<?= $tyer->id ?>" >Tyre details</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Container-fluid Ends-->
    </div>
    <!-- footer start-->

   
<script>
    function downloadExcel() {
        // Get the selected location ID
        var locationId = document.getElementById('location_id').value;

        if (locationId) {
            // Send the location_id via AJAX
            $.ajax({
                url: '<?= base_url(); ?>/admin/expert_excel',  // Your controller URL
                type: 'POST',
                data: { location_id: locationId },
                xhrFields: {
                    responseType: 'blob'  // Important for file download
                },
                success: function(response, status, xhr) {
                    // Get the filename from the response header if available
                    var filename = "";
                    var disposition = xhr.getResponseHeader('Content-Disposition');
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        var matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                    }

                    // Create a new Blob object using the response data
                    var blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });

                    // Create a link element, use it to download the file
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename || 'export.xlsx';
                    link.click();
                },
                error: function(xhr, status, error) {
                    alert("An error occurred: " + error);
                }
            });
        } else {
            alert("Please select a location before downloading the Excel file.");
        }
    }
</script>
















<?php include("footer.php"); ?>

