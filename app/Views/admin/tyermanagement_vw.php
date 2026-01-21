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
            <div class=""uk-grid>
            <div class="uk-width-1-2">
                <?php if(in_array(24.1,$jobAssign)){ ?>
                <a class="btn btn-primary" type="button" href="<?php echo base_url();?>/admin/addtyerbill">Enter Tyre Stock</a>
                <?php }?>
                <a class="btn btn-primary" type="button" href="<?php echo base_url();?>/admin/tyreTransfer">Tyre Transfer</a>
            </div> 
            <a class="btn btn-primary" type="button" onclick="downloadExcel()">Download Excel</a>
            
            </div>
            <div class="uk-card uk-card-body uk-card-default uk-card-small">
                <div class="table-responsive custom-scrollbar">
                    <table class="display" id="row_create" style="width:100%">
                        <thead>
                            <tr>
                                <th>Sl no</th>
                                <th>Bill no</th>
                                <th>Total Amount</th>
                                <th>Location</th>
                                <th>Party Name</th>
                                <th>Bill Date</th>
                                <th>Brand Name</th>
                                <th>Qty</th>
                                <th>Model</th>
                                <th>View </th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            foreach ($tyer_data as $tyer): ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= $tyer->bill_no ?></td>
                                <td><?= $tyer->price ?></td>
                                <td><?= $tyer->location_name ?></td>
                                <td><?= $tyer->name ?></td>
                                <td><?= $tyer->date ?></td>
                                <td><?= $tyer->brand_name ?></td>
                                <td><?= $tyer->qty; ?></td>
                                <td><?= $tyer->model ?></td>
                                <td>
                                    <?php if(in_array(24.2,$jobAssign)){ ?>
                                        <button class="btn btn-info view-details" 
                                                data-bill-no="<?= $tyer->bill_no ?>" 
                                                data-location="<?= $tyer->location_id ?>">
                                            View
                                        </button>
                                    <?php }?>
                                </td>
                                
                                <td>
                                    <?php if(in_array(24.3,$jobAssign)){ ?>
                                    <a class="btn btn-info " href="<?= base_url('admin/edit_tyer/'.$tyer->id) ?>">Edit</a>
                                    <?php }?>
                                </td>
                                
                                
                                <td>
                                    <?php if(in_array(24.3,$jobAssign)){ ?>
                                    <a class="btn btn-danger" href="<?= base_url('admin/delete_tyer/'.$tyer->id) ?>" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                                    <?php }?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Sl no</th>
                                <th>Bill no</th>
                                <th>Total Amount</th>
                                <th>Location</th>
                                <th>Brand Name</th>
                                <th>Qty</th>
                                <th>Model</th>
                                <th>View </th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <!-- Container-fluid Ends-->
    </div>
    <!-- footer start-->

    <!-- Off-Canvas Panel Start-->
    <div id="offcanvas" uk-offcanvas="overlay: true; flip: true">
        <div class="uk-offcanvas-bar uk-margin-remove uk-padding-remove">
            <button class="uk-offcanvas-close" type="button" uk-close></button>
            <div class="uk-card uk-card-body uk-card-default uk-card-small">
            <h5>Tyre Details</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>Serial No</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody id="tyre-details">
                    <!-- Details will be loaded here -->
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <!-- Off-Canvas Panel End-->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/uikit@3.6.18/dist/js/uikit.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/uikit@3.6.18/dist/js/uikit-icons.min.js"></script>

<script>
    $(document).ready(function () {
        $('.view-details').on('click', function () {

            var billNo = $(this).data('bill-no');
            var location = $(this).data('location');  // ✅ get location from button

            console.log("Bill NO:", billNo);
            console.log("Location:", location);

            $.ajax({
                url: '<?php echo base_url("admin/getTyerDetailsByBillNo"); ?>',
                type: 'POST',
                data: { 
                    bill_no: billNo,
                    location: location   // ✅ send location to server
                },
                success: function (data) {

                    var details = JSON.parse(data);
                    var detailsHtml = '';

                    $.each(details, function (index, detail) {
                        detailsHtml += '<tr><td>' + detail.tyer_sl_no + '</td><td>' + detail.tyer_type + '</td></tr>';
                    });

                    $('#tyre-details').html(detailsHtml);

                    // Open the offcanvas
                    UIkit.offcanvas('#offcanvas').show();
                },
                error: function () {
                    alert('Failed to fetch details. Please try again.');
                }
            });
        });
    });
</script>
<script>
    function downloadExcel() {
        var location = 1;
        if (location == 1) {
            $.ajax({
                url: '<?= base_url(); ?>/admin/expert_excel_tyre_management',
                type: 'POST',
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response, status, xhr) {
                    var filename = "";
                    var disposition = xhr.getResponseHeader('Content-Disposition');
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        var matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                    }
                    var blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
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
            alert("Error downloading the Excel file.");
        }
    }
</script>

<?php include("footer.php"); ?>
