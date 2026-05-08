<?php include("header.php");?>
      <!-- Page Body Start-->
     <div class="page-body-wrapper">
        <?php include("mainsidebar.php"); ?>
        <?php foreach($singleuser as $singledata){}?>
       
        <div class="page-body">
          <div class="container-fluid">        
            <div class="page-title">
              <div class="row">
                <div class="col-sm-6 p-0">
                  <h3>Sub Admin </h3>
                </div>
                <div class="col-sm-6 p-0"></div>
          </div>
          <!-- Container-fluid starts-->
          <div class="container-fluid default-dashboard">
            <div class="row">
      	<div class="col-xs-12">
        <div class="card ">
       
</div>
                  </ol>
                </div>
              </div>
            </div>
          </div>
          <!-- Container-fluid starts-->
          
          <!-- Container-fluid starts-->
          <div class="container-fluid default-dashboard">
                        <div class="uk-grid-small uk-child-width-expand@m" uk-grid>
                        <div class="uk-width-2-5@m">
                        	<div class="uk-card uk-card-body uk-card-small uk-card-default">
      <div class="modal-header">
      <h4 class="modal-title">Add Subadmin</h4>
      </div>
      <form action="<?php echo base_url();?>/admin/addsubadmin" enctype="multipart/form-data" method="post">
      <div class="modal-body">
                <div class="row">
        	<div class="col-sm-12">
           	 <div class="form-group">
                  <label>Name</label>
                  <input type="text" class="form-control" name="name" placeholder="Enter full name"  value="<?= set_value('name') ?>">
                  <?php if(isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('name'); ?></span><?php } ?>
                </div>
             </div>
            
            <div class="col-sm-12">
                <div class="form-group">
                    <label>Contact No</label>
                    <input type="tel" class="form-control" id="contat" name="contact"  placeholder="contact no" value="<?php echo set_value('contact'); ?>">
                    <?php if(isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('contact'); ?></span><?php } ?>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-group">
                    <label>Whatsapp No</label>
                    <input type="tel" class="form-control" id="contat" name="whatsapp"  placeholder="whatsapp no" value="<?php echo set_value('whatsapp'); ?>">
                    <?php if(isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('whatsapp'); ?></span><?php } ?>
                </div>
            </div>
            
            <div class="form-group">
                  <label>Location</label>
                  
                  <?php if(isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('contact'); ?></span><?php } ?>
                <select class="form-control" name="location">
                    <?php foreach($location as $loc){?>
                    <option value="<?=$loc->location_id?>"><?=$loc->location_name?></option>
                    <?php } ?>
                </select>
            </div>
            
            
            <div class="col-sm-12">
           		 <label >Uploade Image</label>
    <input type="file"  name="img" id="exampleFormControlFile1" class="form-control" >
                </div>
            <div class="col-sm-6"><div class="form-group">
                  <label>Username</label>
                  <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" value="<?php echo set_value('username'); ?>">
                  <?php if(isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('username'); ?></span><?php } ?>
                  
                </div></div>
            <div class="col-sm-6"><div class="form-group">
                  <label>Password</label>
                  <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password"  value="<?php echo set_value('password'); ?>">
                 <?php if(isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('password'); ?></span><?php } ?>
                </div></div>
                
                
        </div>
              </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary" >Submit</button>
      </div>
      </form>
    </div>
                        </div>
                        
                            <div>
                        <div class="uk-card uk-card-body uk-card-default uk-card-small">
                        	<h3>Sub admin</h3> 
                            <div class="table-responsive">
                                <table id="example-datatable" class="table table-vcenter table-condensed table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center">ID</th>
                                            <th class="text-center"><i class="gi gi-user"></i></th>
                                            <th>Client</th>
                                            <th>Email</th>
                                            <th>Phone no.</th>
                                            <th>Whatsapp no.</th>
                                             <th>Location</th>
                                            <th>Status</th>
                                            <th class="text-center">Actions</th>
                                            <th class="text-center">Role</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                          <?php
                                          $currentPage = $pager->getCurrentPage();
                                          $perPage = 10;
                                          $i = 1 + ($perPage * ($currentPage - 1));
                                          foreach($allsubadmin as $subadmin ){
                                          ?>
                                        <tr>
                                            <td class="text-center"><?= $i++;?></td>
                                            <?php if($subadmin->profile_image != '' ){?>
                                             <td class="text-center"><img src="<?php echo base_url();?>/uploads/<?=$subadmin->profile_image?>" alt="avatar" class="img-circle" width="50px"></td>
                                            <?php }else{?>
                                            <td class="text-center"><img src="img/placeholders/avatars/avatar11.jpg" alt="avatar" class="img-circle"></td>
                                            <?php }?>
                                            <td><?= $subadmin->full_name;?></td>
                                            <td><?= $subadmin->email;?></td>
                                            <td><?= $subadmin->contact_no;?></td>
                                            <td><?= $subadmin->whatsapp_no;?></td>
                                            <td>
                                                 <?php foreach($location as $loc){
                                                 if($loc->location_id==$subadmin->location_id){
                                                 ?>
                                                    <?=$loc->location_name?>
                                                    <?php }} ?>
                                            </td>
                                            
                                            <td>
                                                    <?php if($subadmin->status ==0){?>
                  <a href="<?php echo base_url();?>/admin/statusActive/<?php echo $subadmin->id; ?>"  ><button type="button" class="btn btn-danger ">Deactivate</button></a>
                  <?php }else{ ?>
                   <a href="<?php echo base_url();?>/admin/statusBlock/<?php echo $subadmin->id; ?>" > <button type="button" class="btn btn-success"> Active </button></a>
                    <?php }?>
                                                </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="#modal-center<?= $subadmin->id;?>" uk-toggle title="Edit" class="btn btn-xs btn-default"><i class="fa fa-pencil"></i></a>
                                                    <a href="javascript:void(0);" onClick="deleteRecord('<?= $subadmin->id ; ?>');" title="Delete" class="btn btn-xs btn-danger"><i class="fa fa-times"></i></a>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <a class="btn btn-primary" href="#modal-sections<?= $subadmin->id;?>" uk-toggle>Role</a>

                                                
                                                <?php if($subadmin->roles!=''){ ?>
 <?php $jobAssign = explode(',',$subadmin->roles);   ?>  
<?php }else{ ?>
<?php $jobAssign = explode(',',0,0.0);   ?>
<?php } ?>


<div id="modal-sections<?= $subadmin->id;?>" uk-modal>
    <div class="uk-modal-dialog">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <div class="uk-modal-header">
            <h2 class="uk-modal-title"><?= $subadmin->full_name;?>''s Role</h2>hiii
        </div>
        <form action="<?php echo base_url();?>/admin/role" enctype="multipart/form-data" method="post">
            
        <div class="uk-modal-body">
            <input type="hidden" value="<?php echo $subadmin->id; ?>" name="id">
            <input type="hidden" name="role[]" class="uk-checkbox" value="0" />
            <ul class="uk-list uk-list-divider">
                <li><input type="checkbox" name="role[]" class="uk-checkbox" value="28" <?php if(in_array(28,$jobAssign)){ echo "checked";}?>/>DashBoard<br></li>
                <li>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="27" <?php if(in_array(27,$jobAssign)){ echo "checked";}?>/>Sub admin<br>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="27.1" <?php if(in_array(27.1,$jobAssign)){ echo "checked";}?>/>Submit 
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="27.2" <?php if(in_array(27.2,$jobAssign)){ echo "checked";}?>/>Deactive/Active
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="27.3" <?php if(in_array(27.3,$jobAssign)){ echo "checked";}?>/>Edit 
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="27.4" <?php if(in_array(27.4,$jobAssign)){ echo "checked";}?>/>Delete
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="27.5" <?php if(in_array(27.5,$jobAssign)){ echo "checked";}?>/> Role
                </li>
                <li>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="1" <?php if(in_array(1,$jobAssign)){ echo "checked";}?>/>Purchase<br>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="1.1" <?php if(in_array(1.1,$jobAssign)){ echo "checked";}?>/>Enter Stock 
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="1.2" <?php if(in_array(1.2,$jobAssign)){ echo "checked";}?>/>View
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="1.3" <?php if(in_array(1.3,$jobAssign)){ echo "checked";}?>/> Delete
                </li>
            
                <li>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="2" <?php if(in_array(2,$jobAssign)){ echo "checked";}?>/> Do Registration<br>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="2.1" <?php if(in_array(2.1,$jobAssign)){ echo "checked";}?>/> Submit
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="2.2"  <?php if(in_array(2.2,$jobAssign)){ echo "checked";}?>/>Change Price
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="2.3" <?php if(in_array(2.3,$jobAssign)){ echo "checked";}?>/>Edit
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="2.4" <?php if(in_array(2.4,$jobAssign)){ echo "checked";}?>/>Delete 
                </li>
            
                <li>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="3" <?php if(in_array(3,$jobAssign)){ echo "checked";}?>/>Despatch Entry<br>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="3.1" <?php if(in_array(3.1,$jobAssign)){ echo "checked";}?>/>Submit
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="3.2" <?php if(in_array(3.2,$jobAssign)){ echo "checked";}?>/>Upload Exel
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="3.3" <?php if(in_array(3.3,$jobAssign)){ echo "checked";}?>/> Filter
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="3.4" <?php if(in_array(3.4,$jobAssign)){ echo "checked";}?>/>Download Exel
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="3.5" <?php if(in_array(3.5,$jobAssign)){ echo "checked";}?>/>Edit
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="3.6" <?php if(in_array(3.6,$jobAssign)){ echo "checked";}?>/>Delete
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="3.7" <?php if(in_array(3.7,$jobAssign)){ echo "checked";}?>/>Delete Multiple
                </li>
                <li>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="36" <?php if(in_array(36,$jobAssign)){ echo "checked";}?>/>Voucher Entry <br>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="36.1" <?php if(in_array(36.1,$jobAssign)){ echo "checked";}?>/>Add
                </li>
                <li>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="4" <?php if(in_array(4,$jobAssign)){ echo "checked";}?>/>Diesel Entry<br>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="4.1" <?php if(in_array(4.1,$jobAssign)){ echo "checked";}?>/>Submit
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="4.2"  <?php if(in_array(4.2,$jobAssign)){ echo "checked";}?>/>Upload Exel
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="4.3" <?php if(in_array(4.3,$jobAssign)){ echo "checked";}?>/>Filter
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="4.4" <?php if(in_array(4.4,$jobAssign)){ echo "checked";}?>/>Download Exel
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="4.5" <?php if(in_array(4.5,$jobAssign)){ echo "checked";}?>/>Delete
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="4.6" <?php if(in_array(4.6,$jobAssign)){ echo "checked";}?>/>Edit
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="4.7" <?php if(in_array(4.7,$jobAssign)){ echo "checked";}?>/>Delete Multiple<br>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="4.8" <?php if(in_array(4.8,$jobAssign)){ echo "checked";}?>/>Delete all
                </li>
            
                <li>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="5" <?php if(in_array(5,$jobAssign)){ echo "checked";}?>/>Inhouse Maintanance<br>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="5.1" <?php if(in_array(5.1,$jobAssign)){ echo "checked";}?>/> Add New
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="5.2" <?php if(in_array(5.2,$jobAssign)){ echo "checked";}?>/>Edit
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="5.3" <?php if(in_array(5.3,$jobAssign)){ echo "checked";}?>/>View
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="5.4" <?php if(in_array(5.4,$jobAssign)){ echo "checked";}?>/> Delete
                </li>
            
                <li>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="6" <?php if(in_array(6,$jobAssign)){ echo "checked";}?>/> Outside Maintanance<br>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="6.1" <?php if(in_array(6.1,$jobAssign)){ echo "checked";}?>/>Submit
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="6.2" <?php if(in_array(6.2,$jobAssign)){ echo "checked";}?>/>Delete
                </li>
            
           <li><input type="checkbox" name="role[]" class="uk-checkbox" value="7" <?php if(in_array(7,$jobAssign)){ echo "checked";}?>/>Staff Advance<br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="7.1" <?php if(in_array(7.1,$jobAssign)){ echo "checked";}?>/>Submit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="7.2" <?php if(in_array(7.2,$jobAssign)){ echo "checked";}?>/>Upload Exel
           <input type="checkbox" name="role[]" class="uk-checkbox" value="7.3"  <?php if(in_array(7.3,$jobAssign)){ echo "checked";}?>/>Edit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="7.4" <?php if(in_array(7.4,$jobAssign)){ echo "checked";}?>/>Delete 
           </li>
            
           <li><input type="checkbox" name="role[]" class="uk-checkbox" value="8" <?php if(in_array(8,$jobAssign)){ echo "checked";}?>/>Driver Asignment<br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="8.1" <?php if(in_array(8.1,$jobAssign)){ echo "checked";}?>/>Submit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="8.2" <?php if(in_array(8.2,$jobAssign)){ echo "checked";}?>/>Filter
           <input type="checkbox" name="role[]" class="uk-checkbox" value="8.3" <?php if(in_array(8.3,$jobAssign)){ echo "checked";}?>/>Edit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="8.4" <?php if(in_array(8.4,$jobAssign)){ echo "checked";}?>/>Delete

           </li>
           
           
           <li><input type="checkbox" name="role[]" class="uk-checkbox" value="9" <?php if(in_array(9,$jobAssign)){ echo "checked";}?>/>Regular Checkup<br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="9.1" <?php if(in_array(9.1,$jobAssign)){ echo "checked";}?>/>Submit
           
           </li>
           
           
           <li><input type="checkbox" name="role[]" class="uk-checkbox" value="10" <?php if(in_array(10,$jobAssign)){ echo "checked";}?>/>Overall Expence<br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="10.1" <?php if(in_array(10.1,$jobAssign)){ echo "checked";}?>/>Submit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="10.2" <?php if(in_array(10.2,$jobAssign)){ echo "checked";}?>/>Download Excel
           <input type="checkbox" name="role[]" class="uk-checkbox" value="10.3" <?php if(in_array(10.3,$jobAssign)){ echo "checked";}?>/>Delete
           
           </li>
           
           
           
            
           <li><input type="checkbox" name="role[]" class="uk-checkbox" value="11" <?php if(in_array(11,$jobAssign)){ echo "checked";}?>/>Driver Salary<br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="11.1" <?php if(in_array(11.1,$jobAssign)){ echo "checked";}?>/>Submit

          </li>
          <li><input type="checkbox" name="role[]" class="uk-checkbox" value="35"<?php if(in_array(35,$jobAssign)){ echo "checked";}?>/> Adjust Salary <br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="35.1"<?php if(in_array(35.1,$jobAssign)){ echo "checked";}?>/>submit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="35.2"<?php if(in_array(35.2,$jobAssign)){ echo "checked";}?>/>Sample Excel
           <input type="checkbox" name="role[]" class="uk-checkbox" value="35.3"<?php if(in_array(35.3,$jobAssign)){ echo "checked";}?>/>upload
           <input type="checkbox" name="role[]" class="uk-checkbox" value="35.4"<?php if(in_array(35.4,$jobAssign)){ echo "checked";}?>/>Filter
           <input type="checkbox" name="role[]" class="uk-checkbox" value="35.5"<?php if(in_array(35.5,$jobAssign)){ echo "checked";}?>/>Edit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="35.6"<?php if(in_array(35.6,$jobAssign)){ echo "checked";}?>/>Delete
          </li>
           <li><input type="checkbox" name="role[]" class="uk-checkbox" value="12" <?php if(in_array(12,$jobAssign)){ echo "checked";}?>/>Staff Salary<br>
               <input type="checkbox" name="role[]" class="uk-checkbox" value="12.1" <?php if(in_array(12.1,$jobAssign)){ echo "checked";}?>/>Submit
               <input type="checkbox" name="role[]" class="uk-checkbox" value="12.2" <?php if(in_array(12.2,$jobAssign)){ echo "checked";}?>/>Download Excel

           </li>
           <li><input type="checkbox" name="role[]" class="uk-checkbox" value="29"<?php if(in_array(29,$jobAssign)){ echo "checked";}?>/> Vehicle<br>
           <li><input type="checkbox" name="role[]" class="uk-checkbox" value="13"<?php if(in_array(13,$jobAssign)){ echo "checked";}?>/> Vehicle Master <br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="13.1"<?php if(in_array(13.1,$jobAssign)){ echo "checked";}?>/>Upload Excel
           <input type="checkbox" name="role[]" class="uk-checkbox" value="13.2"<?php if(in_array(13.2,$jobAssign)){ echo "checked";}?>/>Admin New Vehicle
           <input type="checkbox" name="role[]" class="uk-checkbox" value="13.3"<?php if(in_array(13.3,$jobAssign)){ echo "checked";}?>/>Sample Excel
           <input type="checkbox" name="role[]" class="uk-checkbox" value="13.4"<?php if(in_array(13.4,$jobAssign)){ echo "checked";}?>/>Edit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="13.5"<?php if(in_array(13.5,$jobAssign)){ echo "checked";}?>/>Delete
          </li>
           <li><input type="checkbox" name="role[]" class="uk-checkbox" value="14" <?php if(in_array(14,$jobAssign)){ echo "checked";}?>/> Statutory Entry <br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="14.1"<?php if(in_array(14.1,$jobAssign)){ echo "checked";}?>/>Submit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="14.2"<?php if(in_array(14.2,$jobAssign)){ echo "checked";}?>/>Delete
           <input type="checkbox" name="role[]" class="uk-checkbox" value="14.3"<?php if(in_array(14.3,$jobAssign)){ echo "checked";}?>/>Edit
          </li>
          </li>
          <li><input type="checkbox" name="role[]" class="uk-checkbox" value="30"<?php if(in_array(30,$jobAssign)){ echo "checked";}?>/> Master Entry<br>
          <li><input type="checkbox" name="role[]" class="uk-checkbox" value="15" <?php if(in_array(15,$jobAssign)){ echo "checked";}?>/>Add Staff/Driver<br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="15.1"<?php if(in_array(15.1,$jobAssign)){ echo "checked";}?>/>Upload Excel
           <input type="checkbox" name="role[]" class="uk-checkbox" value="15.2"<?php if(in_array(15.2,$jobAssign)){ echo "checked";}?>/>Add Staff
           <input type="checkbox" name="role[]" class="uk-checkbox" value="15.3"<?php if(in_array(15.3,$jobAssign)){ echo "checked";}?>/>Edit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="15.4"<?php if(in_array(15.4,$jobAssign)){ echo "checked";}?>/>Delete


          </li>
          <li><input type="checkbox" name="role[]" class="uk-checkbox" value="16" <?php if(in_array(16,$jobAssign)){ echo "checked";}?>/>Vender<br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="16.1"<?php if(in_array(16.1,$jobAssign)){ echo "checked";}?>/>Upload Excel
           <input type="checkbox" name="role[]" class="uk-checkbox" value="16.2"<?php if(in_array(16.2,$jobAssign)){ echo "checked";}?>/>Add Vender/Party
           <input type="checkbox" name="role[]" class="uk-checkbox" value="16.3"<?php if(in_array(16.3,$jobAssign)){ echo "checked";}?>/>Edit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="16.4"<?php if(in_array(16.4,$jobAssign)){ echo "checked";}?>/>View Rate
           <input type="checkbox" name="role[]" class="uk-checkbox" value="16.5"<?php if(in_array(16.5,$jobAssign)){ echo "checked";}?>/>Delete


          </li>
          <li><input type="checkbox" name="role[]" class="uk-checkbox" value="17" <?php if(in_array(17,$jobAssign)){ echo "checked";}?>/>Items<br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="17.1"<?php if(in_array(17.1,$jobAssign)){ echo "checked";}?>/>Submit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="17.2"<?php if(in_array(17.2,$jobAssign)){ echo "checked";}?>/>Upload Excel
           <input type="checkbox" name="role[]" class="uk-checkbox" value="17.3"<?php if(in_array(17.3,$jobAssign)){ echo "checked";}?>/>Upload Excel
           <input type="checkbox" name="role[]" class="uk-checkbox" value="17.4"<?php if(in_array(17.4,$jobAssign)){ echo "checked";}?>/>Edit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="17.5"<?php if(in_array(17.5,$jobAssign)){ echo "checked";}?>/>Delete


          </li>
          <li><input type="checkbox" name="role[]" class="uk-checkbox" value="18" <?php if(in_array(18,$jobAssign)){ echo "checked";}?>/> Unit <br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="18.1"<?php if(in_array(18.1,$jobAssign)){ echo "checked";}?>/>Submit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="18.2"<?php if(in_array(18.2,$jobAssign)){ echo "checked";}?>/>Upload Excel
           <input type="checkbox" name="role[]" class="uk-checkbox" value="18.3"<?php if(in_array(18.3,$jobAssign)){ echo "checked";}?>/>Edit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="18.4"<?php if(in_array(18.4,$jobAssign)){ echo "checked";}?>/>Delete

          </li>
          <li><input type="checkbox" name="role[]" class="uk-checkbox" value="19"<?php if(in_array(19,$jobAssign)){ echo "checked";}?> />Location<br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="19.1"<?php if(in_array(19.1,$jobAssign)){ echo "checked";}?>/>Submit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="19.2"<?php if(in_array(19.2,$jobAssign)){ echo "checked";}?>/>Upload Excel
           <input type="checkbox" name="role[]" class="uk-checkbox" value="19.3"<?php if(in_array(19.3,$jobAssign)){ echo "checked";}?>/>Edit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="19.4"<?php if(in_array(19.4,$jobAssign)){ echo "checked";}?>/>Delete
          </li>
          <li><input type="checkbox" name="role[]" class="uk-checkbox" value="20" <?php if(in_array(20,$jobAssign)){ echo "checked";}?>/>Route<br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="20.1"<?php if(in_array(20.1,$jobAssign)){ echo "checked";}?>/>Submit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="20.2"<?php if(in_array(20.2,$jobAssign)){ echo "checked";}?>/>Upload Excel
           <input type="checkbox" name="role[]" class="uk-checkbox" value="20.3"<?php if(in_array(20.3,$jobAssign)){ echo "checked";}?>/>Edit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="20.4"<?php if(in_array(20.4,$jobAssign)){ echo "checked";}?>/>Delete

          </li>
          <li><input type="checkbox" name="role[]" class="uk-checkbox" value="21"<?php if(in_array(21,$jobAssign)){ echo "checked";}?> />Bank <br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="21.1"<?php if(in_array(21.1,$jobAssign)){ echo "checked";}?>/>Submit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="21.2"<?php if(in_array(21.2,$jobAssign)){ echo "checked";}?>/>Edit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="21.3"<?php if(in_array(21.3,$jobAssign)){ echo "checked";}?>/>Delete
          </li>

          </li>
          <li><input type="checkbox" name="role[]" class="uk-checkbox" value="31"<?php if(in_array(31,$jobAssign)){ echo "checked";}?>/>Report<br>
          <li><input type="checkbox" name="role[]" class="uk-checkbox" value="22" <?php if(in_array(22,$jobAssign)){ echo "checked";}?>/>Stock Report<br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="22.1"<?php if(in_array(22.1,$jobAssign)){ echo "checked";}?>/>Submit

          </li>
          <li><input type="checkbox" name="role[]" class="uk-checkbox" value="23"<?php if(in_array(23,$jobAssign)){ echo "checked";}?>/>Vehicle Ledger<br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="23.1"<?php if(in_array(23.1,$jobAssign)){ echo "checked";}?>/>Submit
          </li>
          </li>
          <li><input type="checkbox" name="role[]" class="uk-checkbox" value="32"<?php if(in_array(32,$jobAssign)){ echo "checked";}?>/>Tyer Management<br>
          <li><input type="checkbox" name="role[]" class="uk-checkbox" value="24" <?php if(in_array(24,$jobAssign)){ echo "checked";}?>/>Purchase Tyer<br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="24.1"<?php if(in_array(24.1,$jobAssign)){ echo "checked";}?>/>Enter Tyer Stock
           <input type="checkbox" name="role[]" class="uk-checkbox" value="24.2"<?php if(in_array(24.2,$jobAssign)){ echo "checked";}?>/>View
           <input type="checkbox" name="role[]" class="uk-checkbox" value="24.3"<?php if(in_array(24.3,$jobAssign)){ echo "checked";}?>/>Edit
           <input type="checkbox" name="role[]" class="uk-checkbox" value="24.4"<?php if(in_array(24.4,$jobAssign)){ echo "checked";}?>/>Delete
          </li>
            <li>
                <input type="checkbox" name="role[]" class="uk-checkbox" value="25" <?php if(in_array(25,$jobAssign)){ echo "checked";}?>/>Assign Tyer<br>
                <input type="checkbox" name="role[]" class="uk-checkbox" value="25.1"<?php if(in_array(25.1,$jobAssign)){ echo "checked";}?>/>Assign Tyer
                <input type="checkbox" name="role[]" class="uk-checkbox" value="25.2"<?php if(in_array(25.2,$jobAssign)){ echo "checked";}?>/>Tyer Exchange
          </li>
          <li><input type="checkbox" name="role[]" class="uk-checkbox" value="26" <?php if(in_array(26,$jobAssign)){ echo "checked";}?>/>Report <br>
           <input type="checkbox" name="role[]" class="uk-checkbox" value="26.1"<?php if(in_array(26.1,$jobAssign)){ echo "checked";}?>/>Filter

          </li>
            <li>
                <input type="checkbox" name="role[]" class="uk-checkbox" value="33" <?php if(in_array(33,$jobAssign)){ echo "checked";}?>/>Repaire Report <br>
                <input type="checkbox" name="role[]" class="uk-checkbox" value="33.1"<?php if(in_array(33.1,$jobAssign)){ echo "checked";}?>/>Submit
            </li>
            <li>
                <input type="checkbox" name="role[]" class="uk-checkbox" value="38" <?php if(in_array(38,$jobAssign)){ echo "checked";}?>/>Scrap Tyre<br>
                <input type="checkbox" name="role[]" class="uk-checkbox" value="38.1"<?php if(in_array(38.1,$jobAssign)){ echo "checked";}?>/>Back To Stock
            </li>
          </li>
                <li>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="34" <?php if(in_array(34,$jobAssign)){ echo "checked";}?>/>Download Database<br>
                </li>
                <li>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="36" <?php if(in_array(36,$jobAssign)){ echo "checked";}?>/>Group<br>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="36.1"<?php if(in_array(36.1,$jobAssign)){ echo "checked";}?>/>Status
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="36.2"<?php if(in_array(36.2,$jobAssign)){ echo "checked";}?>/>Delete
                </li>
                 <li>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="37" <?php if(in_array(37,$jobAssign)){ echo "checked";}?>/>Task Assignment<br>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="37.1"<?php if(in_array(37.1,$jobAssign)){ echo "checked";}?>/>Edit
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="37.2"<?php if(in_array(37.2,$jobAssign)){ echo "checked";}?>/>Delete
                </li>
                <li>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="40" <?php if(in_array(40,$jobAssign)){ echo "checked";}?>/>Payment Voucher<br>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="40.1"<?php if(in_array(40.1,$jobAssign)){ echo "checked";}?>/>Submit
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="40.2"<?php if(in_array(40.2,$jobAssign)){ echo "checked";}?>/>Upload
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="40.3"<?php if(in_array(40.3,$jobAssign)){ echo "checked";}?>/>Edit
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="40.4"<?php if(in_array(40.4,$jobAssign)){ echo "checked";}?>/>Delete
                </li>
                <li>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="41" <?php if(in_array(41,$jobAssign)){ echo "checked";}?>/>Payment Report
                </li>
                <li>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="42" <?php if(in_array(42,$jobAssign)){ echo "checked";}?>/>Pump <br>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="42.1"<?php if(in_array(42.1,$jobAssign)){ echo "checked";}?>/>Submit
                </li>
                <li>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="43" <?php if(in_array(43,$jobAssign)){ echo "checked";}?>/>party<br>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="43.1"<?php if(in_array(43.1,$jobAssign)){ echo "checked";}?>/>Submit
                </li>
                <li>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="44" <?php if(in_array(44,$jobAssign)){ echo "checked";}?>/>Vendor<br>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="44.1"<?php if(in_array(44.1,$jobAssign)){ echo "checked";}?>/>Submit
                </li>
                <li>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="45" <?php if(in_array(45,$jobAssign)){ echo "checked";}?>/>Attendance<br>
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="45"<?php if(in_array(45,$jobAssign)){ echo "checked";}?>/>View Attendance
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="46"<?php if(in_array(46,$jobAssign)){ echo "checked";}?>/>Add Attendance
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="47"<?php if(in_array(47,$jobAssign)){ echo "checked";}?>/>Bulk Upload
                    <input type="checkbox" name="role[]" class="uk-checkbox" value="48"<?php if(in_array(48,$jobAssign)){ echo "checked";}?>/>Reports
                </li>
            </ul>
        </div>        <div class="uk-modal-footer uk-text-right">
            <button class="uk-button uk-button-default uk-modal-close" type="button">Cancel</button>
            <button class="uk-button uk-button-primary" type="submit">Save</button>
        </div>
        </form>
    </div>
</div>
                                          
                                            </td>
                                        </tr>
                                        
 
                                        <?php }?>

                                  
                                    </tbody>
                                </table>
                                <div class="d-flex justify-content-center mt-3">
                                    <?= $pager->links() ?>
                                </div>
                                
<?php foreach($allsubadmin as $subadmin ){ ?>                                       
<div id="modal-center<?= $subadmin->id;?>" class="uk-flex-top" uk-modal>
    <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">

        <button class="uk-modal-close-default" type="button" uk-close></button>

       <form action="<?php echo base_url();?>/admin/editsubadmin" enctype="multipart/form-data" method="post">
      <div class="modal-body">


				<?php if(session()->getFlashdata('uid')==$subadmin->id):?>
                    <div class="alert alert-warning">
                       <?= session()->getFlashdata('msg') ?>
                    </div>
                <?php endif;?>
                
                
                

      <input type="hidden" name="id" value="<?= $subadmin->id;?>">
                <div class="row uk-text-left">
        	<div class="col-sm-6">
           	 <div class="form-group">
                  <label>Name</label>
                  <input type="text" class="form-control" id="name" name="name" placeholder="Enter full name" value="<?= $subadmin->full_name;?>" required>
                </div>
             </div>
            <div class="col-sm-6">
            <div class="form-group">
                  <label>Email address</label>
                  <input type="email" class="form-control" id="email" name="email" placeholder="Enter email"  value="<?= $subadmin->email;?>" required>
                </div>
             </div>
            <div class="col-sm-12">
            <div class="form-group">
                <label>Contact No</label>
                <input type="tel" class="form-control" id="contat" name="contact" title="Enter Only 10 digit Mobile no " value="<?= $subadmin->contact_no;?>" placeholder="contact no" pattern="[1-9]{1}[0-9]{9}" required>
            </div>
            <div class="form-group">
                <label>Whatsapp No</label>
                <input type="tel" class="form-control" id="contat" name="whatsapp" title="Enter Only 10 digit Mobile no " value="<?= $subadmin->whatsapp_no;?>" placeholder="whatsapp no" pattern="[1-9]{1}[0-9]{9}" required>
            </div>
            </div>
             <div class="form-group">
                  <label>Location</label>
                  
                  <?php if(isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('contact'); ?></span><?php } ?>
                <select class="form-control" name="location">
                    <?php foreach($location as $loc){
                    
                    ?>
                    <option <?php  if($loc->location_id==$subadmin->location_id){ echo "selected"; }?>value="<?=$loc->location_id?>"><?=$loc->location_name?></option>
                    <?php } ?>
                </select>
            </div>
            
            <div class="col-sm-12">
           		 <label >Upload Image</label>
    <input type="file" name="img"   id="exampleFormControlFile1" class="form-control" >
                </div>
            <div class="col-sm-6"><div class="form-group">
                  <label>Username</label>
                  <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" value="<?= $subadmin->user_name;?>" required>
                </div></div>
            <div class="col-sm-6"><div class="form-group">
                  <label>Password</label>
                  <input type="text" class="form-control" id="password" name="password" placeholder="Enter Password" value="<?= base64_decode(base64_decode($subadmin->password));?>" required>
                </div></div>
            
            

        </div>
              </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary" >Submit</button>
        
      </div>
      </form>

    </div>
</div>                               
      <?php }?> 
                                
                               
                            </div>
                            </div>
                            </div>
                        </div>
                        <!-- END Mini Top Stats Row -->

                        
                    </div>
                    <!-- END Page Content -->

 <form name="frm_deleteBanner" id="frm_deleteBanner" action="<?php echo base_url();?>/admin/deleteSubadmin" method="post">
 <input type="hidden" name="operation" id="operation" value="">
 <input type="hidden" name="user_id" id="user_id" value="">
 </form>
<script type="text/javascript">
function deleteRecord(id){
	$("#operation").val('delete');
	$("#user_id").val(id);
	var conf=confirm("Are you sure want to delete this Subadmin");
	if(conf){
	   $("#frm_deleteBanner").submit();
	}
}
</script> 



    <script>
        UIkit.modal('#modal-center<?= session()->getFlashdata('uid') ?>').show();
    </script>

        
        
        
        
        
        
        
        
        
        
        
            </div>
         </div>
        </div>
    </div>
        <!-- footer start-->
       <?php include("footer.php");?>