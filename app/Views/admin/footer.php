 <footer class="footer">
          <div class="container-fluid">
            <div class="row">
              <div class="col-md-6 p-0 footer-copyright">
                <p class="mb-0">Copyright 2023 © Dunzo theme by pixelstrap.</p>
              </div>
              <div class="col-md-6 p-0">
                <p class="heart mb-0">Hand crafted &amp; made with
                  <svg class="footer-icon">
                    <use href="https://admin.pixelstrap.net/dunzo/<?php echo base_url();?>/assets/admin/svg/icon-sprite.svg#heart"></use>
                  </svg>
                </p>
              </div>
            </div>
          </div>
        </footer>
      </div>
    </div>
    
  <!--start Select 2  -->
     <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!-- Select2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    
    


     <script>
    $("#single").select2({
        placeholder: "Select an option",
        allowClear: true
    });
    
    
    
    $("#single1").select2({
        placeholder: "Select an option",
        allowClear: true
    });
    
    $("#single2").select2({
        placeholder: "Select an option",
        allowClear: true
    });
    
     $("#driver_data").select2({
        placeholder: "Select an option",
        allowClear: true
    });
    
    $("#vehicle").select2({
        placeholder: "Select an option",
        allowClear: true
    });
    
    $("#vehicle_no").select2({
        placeholder: "Select an option",
        allowClear: true
    });
    
    $("#multiple").select2({
        placeholder: "Select an option",
        allowClear: true
    });
    
     $("#staffFilter").select2({
        placeholder: "Select an option",
        allowClear: true
    });
    
    // Event listener for #vehicle
    $("#vehicle").on('change', function() {
        vehicleId=$(this).val();
         if (vehicleId) {
                $.ajax({
                    url: '<?php echo base_url("Admin/getVehicleDetails"); ?>',
                    type: 'POST',
                    data: { vehicle_id: vehicleId },
                    success: function (response) {
                        $('#vehicle-details').html(response);
                    },
                    error: function () {
                        alert('Failed to fetch vehicle details.');
                    }
                });
            } else {
                $('#vehicle-details').html('');
            }
    });
    
     $("#vehicle_no").on('change', function() {
        vehicleId=$(this).val();
        
        
         if (vehicleId) {
                $.ajax({
                    url: '<?php echo base_url("Admin/openinghsddtl"); ?>',
                    type: 'POST',
                    data: { vehicle_id: vehicleId },
                    success: function (response) {
                        $('#openinghsd').html(response);
                    },
                    error: function () {
                        alert('Failed to fetch vehicle details.');
                    }
                });
            } else {
                $('#openinghsd').html('');
            }
    });
</script>

    
    
    
    <style>
        .select2-container--default .select2-selection--single .select2-selection__arrow {
  height: 24px !important;
  position: absolute;
  top: 1px;
  right: 1px;
  width: 20px;
  top: 10px!important;
}

.select2-container--open .select2-dropdown--below {
  border-top: none;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
  margin: -20px 0 0 0;
}

    </style>
    
    
    <!--End Select 2  --> 
    
    
    
    
    
    
    
    
    <!-- latest jquery-->
    <script src="<?php echo base_url();?>/assets/admin/js/jquery.min.js"></script>
    <!-- Bootstrap js-->
    <script src="<?php echo base_url();?>/assets/admin/js/bootstrap/bootstrap.bundle.min.js"></script>
    <!-- feather icon js-->
    <script src="<?php echo base_url();?>/assets/admin/js/icons/feather-icon/feather.min.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/icons/feather-icon/feather-icon.js"></script>
    <!-- scrollbar js-->
    <script src="<?php echo base_url();?>/assets/admin/js/scrollbar/simplebar.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/scrollbar/custom.js"></script>
    <!-- Sidebar jquery-->
    <script src="<?php echo base_url();?>/assets/admin/js/config.js"></script>
    <!-- Plugins JS start-->
    <script src="<?php echo base_url();?>/assets/admin/js/sidebar-menu.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/sidebar-pin.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/slick/slick.min.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/slick/slick.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/header-slick.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/chart/morris-chart/raphael.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/chart/morris-chart/morris.js"> </script>
    <script src="<?php echo base_url();?>/assets/admin/js/chart/morris-chart/prettify.min.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/chart/apex-chart/apex-chart.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/chart/apex-chart/stock-prices.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/chart/apex-chart/moment.min.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/notify/bootstrap-notify.min.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/dashboard/default.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/notify/index.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/datatable/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/datatable/datatables/datatable.custom.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/datatable/datatables/datatable.custom1.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/owlcarousel/owl.carousel.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/owlcarousel/owl-custom.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/typeahead/handlebars.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/typeahead/typeahead.bundle.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/typeahead/typeahead.custom.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/typeahead-search/handlebars.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/typeahead-search/typeahead-custom.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/height-equal.js"></script>
    <!-- Plugins JS Ends-->
    <!-- Theme js-->
    <script src="<?php echo base_url();?>/assets/admin/js/script.js"></script>
    <!--<script src="<?php echo base_url();?>/assets/admin/js/theme-customizer/customizer.js"></script>-->
    <!-- Plugin used-->
    
    <style>
    table tr th{white-space: nowrap;}
    table tr td{white-space: nowrap;}
</style>

<style>
  /* Critical CSS for perfect header alignment */
  #row_create {
    width: 100% !important;
  }
  
  #row_create thead th {
    position: relative;
    white-space: nowrap;
    background-color: white !important; /* Prevents transparent headers */
  }
  
  .dataTables_scrollHeadInner {
    width: 100% !important;
  }
  
  .dataTables_scrollHeadInner table {
    width: 100% !important;
    margin-left: 0 !important;
  }
  
  /* Fix for Firefox rendering */
  @-moz-document url-prefix() {
    .dataTables_scrollBody {
      overflow-x: visible !important;
    }
  }
</style>

<script>
    $(document).ready(function() {
  const tableId = '#row_create';

  // Destroy existing DataTable instance
  if ($.fn.DataTable.isDataTable(tableId)) {
    $(tableId).DataTable().destroy();
  }

  // Initialize DataTable
  var table = $(tableId).DataTable({
    pageLength: 100,
    scrollY: "400px",
    scrollX: true,
    scrollCollapse: true,
    paging: true,
    autoWidth: false, // Disable auto width calculation
    dom: '<"top"lf>rt<"bottom"ip>',
    fixedHeader: {
      header: true,
      headerOffset: 0
    },
    columnDefs: [
      { 
        targets: "_all", 
        width: null, // Remove any default width assignment
        createdCell: function (td, cellData, rowData, row, col) {
          // Remove inline styles from both th and td elements
          $(td).removeAttr('style');
        }
      }
    ],
    headerCallback: function(thead) {
      // Remove inline styles from all th elements in header
      $(thead).find('th').removeAttr('style');
    },
    initComplete: function() {
      // Re-adjust column widths and headers
      this.api().columns.adjust();
      this.api().fixedHeader.adjust();

      // Recheck after render
      setTimeout(() => {
        this.api().columns.adjust();
        this.api().fixedHeader.adjust();
      }, 100);
    },
    drawCallback: function() {
      // Cleanup inline styles again on redraw
      $(tableId + ' thead th').removeAttr('style');
      $(tableId + ' tbody td').removeAttr('style');
      this.api().columns.adjust();
      this.api().fixedHeader.adjust();
    }
  });

  // Adjust table on window resize
  $(window).on('resize', function() {
    table.columns.adjust();
    table.fixedHeader.adjust();
  });

  // Watch for DOM changes in the table and adjust layout
  new MutationObserver(function() {
    table.columns.adjust();
    table.fixedHeader.adjust();
  }).observe(document.getElementById('row_create'), {
    subtree: true,
    childList: true
  });
});
</script>

  </body>

<!-- Mirrored from admin.pixelstrap.net/dunzo/template/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 23 May 2024 06:23:17 GMT -->
</html>