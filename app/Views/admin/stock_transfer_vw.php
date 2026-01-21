<?php include("header.php");?>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />


      <!-- Page Body Start-->
      <div class="page-body-wrapper">
        <?php include("mainsidebar.php"); ?>
        <div class="page-body">
          <div class="container-fluid">        
            <div class="page-title">
              <div class="row">
                <div class="col-sm-6 p-0">
                  <h3>Stock Transfer </h3>
                </div>
                <div class="col-sm-6 p-0">
                  
                </div>
              </div>
            </div>
          </div>
          <!-- Container-fluid starts-->
          <div class="container-fluid default-dashboard">
            
    <!-- Example Block -->
    <div class="block">
        <!-- Example Title -->
        <div class="block-title">
            <h2><strong>Enter Stocks Transfer</strong></h2>
        </div>
        <?php foreach ($cart_dtls as $cartm) {
        } ?>
        
                <div class="uk-card uk-card-body uk-card-small" style="border:solid 1px #ccc;">
                    <div class="uk-grid-small uk-child-width-expand@m" uk-grid>
                        <div>
                            <lable>From Location</lable>
                            <select name="location" id="location" class="form-control">
                                <option value="">Select Location</option>
                                <?php foreach($location as $loc){?>
                                    <option 
                                    <?php if (!empty($cartm)) {
                                                if ($cartm->location == $loc->location_id) {
                                                    echo "selected";
                                                }
                                            } ?>
                                            value="<?=$loc->location_id?>"><?=$loc->location_name?></option>

                                <?php }?>
                            </select>
                        </div>
                          <div>
                            <lable>To Location</lable>
                            <select name="location1" id="location1" class="form-control">
                                <option value="">Select Location</option>
                                <?php foreach($location as $loc){?>
                                    <option 
                                    <?php if (!empty($cartm)) {
                                                if ($cartm->location == $loc->location_id) {
                                                    echo "selected";
                                                }
                                            } ?>
                                            value="<?=$loc->location_id?>"><?=$loc->location_name?></option>

                                <?php }?>
                            </select>
                        </div>
                        <div>
                            <lable>Date</lable>
                            <input type="date" name="invoicedate" value="<?php if (!empty($cartm)) {
                                                                                echo $cartm->invoicedate;
                                                                            }else{ echo date("Y-m-d");} ?>" class="form-control" />
                        </div>
                        <div>
                            <label>Select Items</label><br>
                            <select class="form-control select2 product" name="product" id="product">
                                
                            </select> 
                        </div>
                        <div class="uk-width-auto">
                            <p></p>
                            <button class="btn btn-primary" id="addButton">Add</button></div>
                      
                        </div>
                    </div>
               
            <p></p>
        </div>

        <div class="table table-responsive uk-margin-top" id="responseContainer">

            <table class="table table-responsive table-striped table-bordered" style="border:solid 1px #ccc;">
                <thead>
                    <tr>
                        <th class="text-center">SI.No.</th>
                        <th class="text-center">Items Name</th>
                        <th class="text-center">QTY</th>
                        <th class="text-center">Unit</th>
                        <th class="text-center">Rate</th>
                        <th class="text-center"> Amount</th>
                        
                        <td>Action</td>
                    </tr>
                </thead>
                <tbody id="TextBoxContainer">
                    <?php
                    $i = 1;
                    $tqty = 0;
                    $Totalamount = 0;
                    foreach ($cart_dtls as $cart) {
                        $tqty += $cart->qty;
                    ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= $cart->item_name; ?></td>
                            <td>
                                <input type="hidden" class="uk-input uk-form-small cart-data" name="cart_id" style="width:50px" value="<?= $cart->cart_id; ?>" />
                                <input type="number" class="uk-input uk-form-small cart-data" name="qty" style="width:50px" value="<?= $cart->qty; ?>" />
                            </td>
                            <td><?= $cart->unit_name; ?></td>
                            <td><input type="text" class="uk-input uk-form-small cart-data" name="rate" style="width:200px" value="<?= $cart->rate; ?>" /></td>
                            <td><?php echo $cart->rate * $cart->qty; ?></td>

                            <td><a href="javascript:void(0);" onClick="deleteRecord('<?= $cart->cart_id; ?>');" uk-icon="icon: trash" class="uk-text-danger"></a></td>
                        </tr>
                    <?php
                       
                    } ?>
                   
                </tbody>
            </table>



        </div>
        <a href="<?php echo base_url(); ?>/Admin/InsertstockTransfer" class="btn btn-primary" type="submit">Submit</a>

        <p></p>
                
                
          </div>
          <!-- Container-fluid Ends-->
        </div>
        
        <form name="frm_deleteBanner" id="frm_deleteBanner" action="<?php echo base_url(); ?>/admin/deletestock_transfer" method="post">
        <input type="hidden" name="cartid" id="cartid" value="">
    </form>

    <script type="text/javascript">
        function deleteRecord(id) {
            $("#operation").val('delete');
            $("#cartid").val(id);
            var conf = confirm("Are you sure want to delete this record");
            if (conf) {
                $("#frm_deleteBanner").submit();
            }
        }
    </script>
        
        <script>
        $("#supplierSelect").select2({
        placeholder: "Select an option",
        allowClear: true
    });
    
     $("#product").select2({
        placeholder: "Select an option",
        allowClear: true
    });
    
        $(document).ready(function() {
            $('#addButton').on('click', function() {
                var location = $('#location').val();
                var location1 = $('#location1').val();
                var invoicedate = $('input[name="invoicedate"]').val();
                var productId = $('#product').val();
                if (invoicedate === ''  || productId === '') {
                    alert('Please enter all required fields.');
                    return; // Stop execution if validation fails
                }

                // Send values to AJAX endpoint
                $.ajax({
                    type: 'POST',
                    url: '<?php echo base_url(); ?>/Admin/frm_addtocartStockTransfer',
                    data: {
                        location_id: location,
                        location_id1: location1,
                        invoicedate: invoicedate,
                        productId: productId,
                    },
                    success: function(response) {
                        // Handle the response and update your HTML
                        // For example, you can update a div with the response
                        $('#responseContainer').html(response);
                    },
                    error: function(error) {
                        console.error('AJAX Error:', error);
                    }
                });
            });
        });
    </script>
    
    
    <script>
        $(document).ready(function() {
            // Listen for changes in any input field with the class 'cart-data'
            $('#TextBoxContainer').on('change', 'input.cart-data', function() {
                // Get the closest row to the changed input field
                var currentRow = $(this).closest('tr');

                // Gather all input values within the current row
                var formData = {
                    cart_id: currentRow.find('.cart-data[name="cart_id"]').val(),
                    qty: currentRow.find('.cart-data[name="qty"]').val(),
                    rate: currentRow.find('.cart-data[name="rate"]').val(),
                   
                };
              
                // Send the data to the server using AJAX
                $.ajax({
                    type: 'POST',
                    url: '<?php echo base_url(); ?>/Admin/frm_updatestockTransfer',
                    data: formData,
                    success: function(response) {
                        // Update the HTML with the response
                        $('#responseContainer').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
            });
        });
        $(document).ready(function () {
            $('#location').on('change', function (e) {
                e.preventDefault();  // ✅ Prevent form submission

                let location_id = $(this).val();
                if(location_id){
                    $.ajax({
                        url: '<?= base_url("admin/getItemsDetails1") ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            location_id: location_id,
                        },
                        success: function(items){
                            let options = '<option value="">Select Model No</option>';
                            $.each(items, function(i, item){
                                options += '<option value="'+item.id+'">'
                                    + item.item_id + ' - ' + item.item_name
                                    + ' (₹' + item.amount + ')'
                                    + ' | Avl: ' + item.available_qty
                                    + '</option>';
                            });
                            $('#product').html(options).trigger('change'); // ✅ updates without reload
                        },
                        error: function(){
                            alert('Error fetching item details.');
                        }
                    });
                } else {
                    $('#product').html('<option value="">Select Model No</option>').trigger('change');
                }
            });
        });
    </script>
    
    
    
        <!-- footer start-->
       <?php include("footer.php");?>
