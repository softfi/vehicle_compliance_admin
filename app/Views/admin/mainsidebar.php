<!-- Page Sidebar Start-->
 <?php foreach($singleuser as $user) ?>
 <?php if($user->roles!=''){ ?>
<?php $jobAssign = explode(',',$user->roles);   ?>  
<?php }else{ ?>
<?php $jobAssign = explode(',',0,0.0);   ?>
<?php } ?>

        <div class="sidebar-wrapper" data-layout="fill-svg">
          <div>
            <div class="logo-wrapper"><a href=""><img class="img-fluid" src="<?php echo base_url();?>/assets/admin/images/logo/logo.png" alt="" style="width: 30%;"></a>
              <div class="toggle-sidebar">
                <svg class="sidebar-toggle"> 
                  <use href="<?php echo base_url();?>/assets/admin/svg/icon-sprite.svg#toggle-icon"></use>
                </svg>
              </div>
            </div>
            <div class="logo-icon-wrapper"><a href="#"><img class="img-fluid" src="<?php echo base_url();?>/assets/admin/images/logo/logo-icon.png" alt=""></a></div>
            <nav class="sidebar-main">
              <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
              <div id="sidebar-menu">
                <ul class="sidebar-links uk-list" id="simple-bar">
                  <li class="back-btn"><a href="index.html"><img class="img-fluid" src="<?php echo base_url();?>/assets/admin/images/logo/logo-icon.png" alt=""></a>
                    <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
                  </li>
                  <li class="pin-title sidebar-main-title">
                    <div> 
                      <h6>Pinned</h6>
                    </div>
                  </li>
                  <li class="sidebar-main-title">
                    <div>
                      <h6 class="lan-1">General</h6>
                    </div>
                  </li>
                    <?php if(in_array(28,$jobAssign)){ ?>
                    <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav " href="<?php echo base_url();?>/admin/dashboard"><span>Dashboard</span></a></li>
                    <?php }?>
                    <?php if(in_array(27,$jobAssign)){ ?>
                    <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav " href="<?php echo base_url();?>/admin/subadmin"><span>Sub Admin</span></a></li>
                    <?php } ?>
                    <?php if(in_array(1,$jobAssign)){ ?>
                    <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav " href="<?php echo base_url();?>/admin/Purchase_Voucher"><span>Purchase</span></a></li>
                    <?php } ?>
                    <?php if(in_array(2,$jobAssign)){ ?>
                    <!-- <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav " href="<?php echo base_url();?>/admin/do_registration"><span>Do Registration</span></a></li>  -->
                    <li class="sidebar-list">
                      <a class="sidebar-link sidebar-title" href="#">
                          <span>Do Registration</span>
                      </a>
                      <ul class="sidebar-submenu">
                          <li>
                              <a href="<?php echo base_url(); ?>/admin/do_registration">
                                  Do Registration
                              </a>
                          </li>
                          <li>
                              <a href="<?php echo base_url(); ?>/admin/tonnage">
                                  Tonnage
                              </a>
                          </li>
                      </ul>
                    </li>
                    <?php } ?>
                    <?php if(in_array(3,$jobAssign)){ ?>
                    <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav " href="<?php echo base_url();?>/admin/despatch_entry"><span>Despatch Entry</span></a></li> 
                    <?php } ?>
                    <?php if(in_array(36,$jobAssign)){ ?>
                    <li class="sidebar-list">
                      <a class="sidebar-link sidebar-title" href="#">
                        <span>Voucher Entry</span>
                      </a>
                      <ul class="sidebar-submenu">
                        <!-- <li><a href="<?php echo base_url();?>/admin/voucher_entry">Voucher Entry</a></li> -->
                        <li><a href="<?php echo base_url(); ?>/admin/Collection">Collection</a></li>
                        <li><a href="<?php echo base_url(); ?>/admin/Deposit">Deposit</a></li>
                        <li><a href="<?php echo base_url(); ?>/admin/Payment">Payment</a></li>
                      </ul>
                    </li>
                    <?php } ?>
                    <?php if(in_array(4,$jobAssign)){ ?>
                    <li class="sidebar-list">
                      <a class="sidebar-link sidebar-title" href="#">
                        <span>Diesel Management</span>
                      </a>
                      <ul class="sidebar-submenu">
                        <li><a href="<?php echo base_url();?>/admin/diesel_entry">Diesel Entry</a></li>
                        <li><a href="<?php echo base_url();?>/admin/extra_diesel">Extra Diesel</a></li>
                        <li><a href="<?php echo base_url();?>/admin/passenger_diesel">Passenger Diesel</a></li>
                        <li><a href="<?php echo base_url();?>/admin/diesel_rate">Diesel Rate Master</a></li>
                      </ul>
                    </li>
                    <?php } ?>
                    <?php if(in_array(5,$jobAssign)){ ?>
                    <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav " href="<?php echo base_url();?>/admin/add_inhouse"><span>In House Maintance </span></a></li>
                    <?php } ?>
                    <?php if(in_array(6,$jobAssign)){ ?>
                    <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav " href="<?php echo base_url();?>/admin/outside_mentainance"><span>Out Side Maintance </span></a></li> 
                    <?php } ?>
                    <?php if(in_array(7,$jobAssign)){ ?>
                    <!-- <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav " href="<?php echo base_url();?>/admin/staf_advance"><span>Staff Advance </span></a></li> -->
                    <li class="sidebar-list">
                      <a class="sidebar-link sidebar-title" href="#"><span >Advance</span></a>
                      <ul class="sidebar-submenu">
                     <li><a  href="<?php echo base_url();?>/admin/staf_advance">Advance</a></li>
                     <li><a  href="<?php echo base_url();?>/admin/CashBank">Cash/Bank</a></li>
                    </ul> 
                   </li>
                    <?php } ?>
                    <?php if(in_array(8,$jobAssign)){ ?>
                    <li class="sidebar-list">
                      <a class="sidebar-link sidebar-title" href="#">
                        <span>Driver Assignment</span>
                      </a>
                      <ul class="sidebar-submenu">
                        <li><a href="<?php echo base_url();?>/admin/Driver_Assignment">Assignment</a></li>
                        <li><a href="<?php echo base_url();?>/admin/material_issue">Materil Issue</a></li>
                        <li><a href="<?php echo base_url();?>/admin/re_issue">Re-Issue</a></li>
                      </ul>
                    </li> 
                    <?php } ?>
                    <?php if(in_array(37,$jobAssign)){ ?>
                    <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav " href="<?php echo base_url();?>/admin/task_Assignment"><span>Task Assignment  </span></a></li>
                    <?php } ?>
                    <?php if(in_array(9,$jobAssign)){ ?>
                    <li class="sidebar-list">
                      <a class="sidebar-link sidebar-title" href="#">
                        <span>Checkup</span>
                      </a>
                      <ul class="sidebar-submenu">
                        <li><a href="<?php echo base_url();?>/admin/Regular_Checkup">Regular Checkup</a></li>
                        <li><a href="<?php echo base_url();?>/admin/Uria_Checkup">Uria Checkup</a></li>
                      </ul>
                    </li> 
                    <?php } ?>
                    <?php if(in_array(10,$jobAssign)){ ?>
                    <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav " href="<?php echo base_url();?>/admin/Overall_Expence"><span>Overall Expence  </span></a></li> 
                    
                    <?php } ?>
                    <?php if(in_array(11,$jobAssign)){ ?>
                    <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav " href="<?php echo base_url();?>/admin/Driver_Salary"><span>Driver Salary  </span></a></li>
                    <?php } ?>
                    <?php if(in_array(35,$jobAssign)){ ?>
                    <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav " href="<?php echo base_url();?>/admin/adjust_salary"><span>Adjust Salary  </span></a></li> 
                    <?php } ?>
                    <?php if(in_array(12,$jobAssign)){ ?>
                    <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav " href="<?php echo base_url();?>/admin/staff_Salary"><span>Staff Salary  </span></a></li> 
                    <?php } ?>
                    <?php if(in_array(40,$jobAssign)){ ?>
                    <li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav " href="<?php echo base_url();?>/admin/payment_voucher"><span>Payment Voucher</span></a></li>
                    <?php } ?>
                    <?php if(in_array(45,$jobAssign) || in_array(46,$jobAssign) || in_array(47,$jobAssign) || in_array(48,$jobAssign)){ ?>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title" href="#"><span>Attendance</span></a>
                        <ul class="sidebar-submenu">
                            <?php if(in_array(45,$jobAssign)){ ?>
                            <li><a href="<?php echo base_url();?>/admin/attendance">View Attendance</a></li>
                            <?php } ?>

                            <?php if(in_array(47,$jobAssign)){ ?>
                            <li><a href="<?php echo base_url();?>/admin/attendance/bulk">Bulk Upload</a></li>
                            <?php } ?>
                            <?php if(in_array(48,$jobAssign)){ ?>
                            <li><a href="<?php echo base_url();?>/admin/attendance/reports">Reports</a></li>
                            <?php } ?>
                            <li><a href="<?php echo base_url();?>/admin/attendance/calendar">Calendar View</a></li>
                            <li><a href="<?php echo base_url();?>/admin/attendance/analytics">Analytics</a></li>
                        </ul>
                    </li>
                    <?php } ?>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title" href="#"><span>Accounting</span></a>
                        <ul class="sidebar-submenu">
                            <li><a href="<?= base_url(); ?>admin/payment_voucher_new">Payment Voucher</a></li>
                            <li><a href="<?= base_url(); ?>admin/receipt_voucher_new">Receipt Voucher</a></li>
                            <li><a href="<?= base_url(); ?>admin/journal_voucher_new">Journal Voucher</a></li>
                        </ul> 
                    </li>
                    <?php if(in_array(41,$jobAssign)){ ?>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title" href="#"><span>Payment Report</span></a>
                        <ul class="sidebar-submenu">
                            <?php if(in_array(42,$jobAssign)){ ?>
                            <li><a href="<?php echo base_url();?>/admin/pump_report">Pump</a></li>
                            <?php } ?>
                            <?php if(in_array(43,$jobAssign)){ ?>
                            <li><a href="<?php echo base_url();?>/admin/party_report">Party</a></li>
                            <?php } ?>
                            <?php if(in_array(44,$jobAssign)){ ?>
                            <li><a href="<?php echo base_url();?>/admin/vendor_report">Vendor</a></li>
                            <?php } ?>
                        </ul>
                    </li>
                    <?php } ?>
                    <?php if(in_array(29,$jobAssign)){ ?>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title" href="#"><span>Vehicle</span></a>
                        <ul class="sidebar-submenu">
                            <?php if(in_array(13,$jobAssign)){ ?>
                            <li><a href="<?php echo base_url();?>/admin/vehicle">Vehicle Master</a></li>
                            <?php } ?>
                            <?php if(in_array(14,$jobAssign)){ ?>
                            <li><a href="<?php echo base_url();?>/admin/Statutory_Entry">Statutory Entry</a></li>
                            <?php } ?>
                            <li><a href="<?php echo base_url();?>/admin/track_vehicle">Track Vehicle</a></li>
                        </ul>
                    </li>
                    <?php } ?>
                     
                    <?php if(in_array(30,$jobAssign)){ ?>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title" href="#"><span>Master Entry</span></a>
                        <ul class="sidebar-submenu">
                            <?php if(in_array(15,$jobAssign)){ ?>
                            <li><a href="<?php echo base_url();?>/admin/staf">Staff/Driver</a></li>
                            <?php } ?>
                            <?php if(in_array(16,$jobAssign)){ ?>
                            <li><a href="<?php echo base_url();?>/admin/Vendor">Vendor</a></li>
                            <?php } ?>
                            <?php if(in_array(17,$jobAssign)){ ?>
                            <li><a href="<?php echo base_url();?>/admin/Items">Items</a></li>
                            <?php } ?>
                            <?php if(in_array(18,$jobAssign)){ ?>
                            <li><a href="<?php echo base_url();?>/admin/unit">Unit</a></li>
                            <?php } ?>
                            <?php if(in_array(19,$jobAssign)){ ?>
                            <li><a href="<?php echo base_url();?>/admin/location">Location</a></li>
                            <?php } ?>
                            <?php if(in_array(20,$jobAssign)){ ?>
                            <li><a href="<?php echo base_url();?>/admin/Route">Route</a></li>
                            <?php } ?>
                            <?php if(in_array(21,$jobAssign)){ ?>
                            <li><a href="<?php echo base_url();?>/admin/bank">Bank</a></li>
                            <?php } ?>
                            <li><a href="<?php echo base_url();?>/admin/group">Master Group</a></li>
                            <li><a href="<?php echo base_url();?>/admin/financial_year">Financial Year</a></li>
                        </ul>
                    </li>
                    <?php } ?>
                      
                    <?php if(in_array(31,$jobAssign)){ ?>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title" href="#"><span>Reports</span></a>
                        <ul class="sidebar-submenu">
                            <?php if(in_array(22,$jobAssign)){ ?>
                            <li><a href="<?php echo base_url();?>/admin/Stock_Report">Stock Report</a></li>
                            <?php } ?>
                            <li><a href="<?php echo base_url();?>/admin/Driver_Report">Driver Report</a></li>
                            <li><a href="<?php echo base_url();?>/admin/Vehicle_Report">Vehicle Report</a></li>
                            <?php if(in_array(23,$jobAssign)){ ?>
                            <li><a href="<?php echo base_url();?>/admin/Vehicle_Ledger">Vehicle Ledger</a></li>
                            <?php } ?>
                            <li><a href="<?= base_url(); ?>admin/ledger_statement">Ledger Statement</a></li>
                        </ul>
                    </li>
                    <?php } ?>
                  
                   <?php if(in_array(32,$jobAssign)){ ?>
                   <li class="sidebar-list">
                       <a class="sidebar-link sidebar-title" href="#"><span>Tyre Management</span></a>
                       <ul class="sidebar-submenu">
                           <?php if(in_array(24,$jobAssign)){ ?>
                           <li><a href="<?php echo base_url();?>/admin/tyer_management">Purchase Tyre</a></li>
                           <?php } ?>
                           <?php if(in_array(34,$jobAssign)){ ?>
                           <li><a href="<?php echo base_url();?>/admin/StockTyer_management">Stock Tyre</a></li>
                           <?php } ?>
                           <?php if(in_array(38,$jobAssign)){ ?>
                           <li><a href="<?php echo base_url();?>/admin/trashTyer_management">Trash Tyre</a></li>
                           <?php } ?>
                           <?php if(in_array(25,$jobAssign)){ ?>
                           <li><a href="<?php echo base_url();?>/admin/Asign_Tyer">Assign Tyre</a></li>
                           <?php } ?>
                           <?php if(in_array(26,$jobAssign)){ ?>
                           <li><a href="<?php echo base_url();?>/admin/tyer_report">Report Tyre</a></li>
                           <?php } ?>
                           <?php if(in_array(33,$jobAssign)){ ?>
                           <li><a href="<?php echo base_url();?>/admin/repaire_report">Repair Report</a></li>
                           <?php } ?>
                       </ul>
                   </li>
                   <?php } ?>
                  
                   <!--<li class="sidebar-list"><a class="sidebar-link sidebar-title link-nav " href="<?php echo base_url();?>/admin/staff_Salary"><span>Download Database  </span></a></li> -->
                
                   <?php if(in_array(34,$jobAssign)){ ?>
                   <li class="sidebar-list">
                       <a class="sidebar-link sidebar-title link-nav" href="<?php echo base_url();?>/AditionalAdminPart/downloadDatabase">
                           <span>Download Database</span>
                       </a>
                   </li>
                   <?php } ?>
                </ul>
              </div>
              <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
            </nav>
          </div>
        </div>
        <!-- Page Sidebar Ends-->