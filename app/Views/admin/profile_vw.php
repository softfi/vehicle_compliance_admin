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
                  <h3>Profile </h3>
                </div>
                <div class="col-sm-6 p-0">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">
                        <svg class="stroke-icon">
                          <use href="https://admin.pixelstrap.net/dunzo/<?php echo base_url();?>/assets/admin/svg/icon-sprite.svg#stroke-home"></use>
                        </svg></a></li>
                    <li class="breadcrumb-item">Dashboard</li>
                    <li class="breadcrumb-item active">Profile      </li>
                  </ol>
                </div>
              </div>
            </div>
          </div>
          <!-- Container-fluid starts-->
          <div class="container-fluid default-dashboard">
            <div class="row">
      	<div class="col-xs-12">
        <div class="card ">
        <div class="card-body">
        	<form action="<?php echo base_url();?>/admin/pro" method="post" enctype="multipart/form-data">
            <div class="form-group">
                  <label for="exampleInputEmail1"> Name</label>
                  <input type="text" class="form-control" name="fullname" id="fullname" value="<?= $singledata->full_name;?>">
                   <?php if(isset($validation)) { ?>
					<span class="text-danger"><?= $error = $validation->getError('fullname'); ?></span>
                    <?php } ?>

                </div>
             
             <div class="form-group">
                  <label for="exampleInputEmail1">Email </label>
                  <input type="email" class="form-control" id="email" name="email" value="<?= $singledata->email;?>">
                  <?php if(isset($validation)) { ?>
				 	<span class="text-danger"><?= $error = $validation->getError('email'); ?></span>
                 <?php } ?>
                </div>
                
                <div class="form-group">
                  <label for="exampleInputEmail1">Contat No </label>
                  <input type="tel" class="form-control" id="contact" name="contact" value="<?= $singledata->contact_no;?>">
                  <?php if(isset($validation)) { ?>
					<span class="text-danger"><?= $error = $validation->getError('contact'); ?></span>
                  <?php } ?>
                </div>
                
                 <div class="form-group">
                  <label for="exampleInputPassword1">User Name</label>
                  <input type="text" class="form-control" id="username" name="username" value="<?= $singledata->user_name;?>">
                   <?php if(isset($validation)) { ?>
					<span class="text-danger"><?= $error = $validation->getError('username'); ?></span>
                   <?php } ?>
                </div>
                
                <div class="form-group">
                  <label for="exampleInputPassword1">Password</label>
                  <input type="text" class="form-control" name="password" id="password" value="<?= base64_decode(base64_decode($singledata->password));?>">
                  <?php if(isset($validation)) { ?>
					<span class="text-danger"><?= $error = $validation->getError('password'); ?></span>
                   <?php } ?>
                </div>
                
                <div class="form-group">
                  <label for="exampleInputFile">File input</label>
                  <input type="file" class="form-control" id="img" name="img">
                
                <?php if($singledata->profile_image<>''){?>
        <img src="<?php echo base_url();?>/uploads/<?= $singledata->profile_image;?>"  width="100" height="100" >
        <?php }else{?>
         <img src="images/default.png"  >
        <?php }?>
                
                </div>
                
                
                
            <button class="btn btn-primary" type="submit">submit</button>
            </form>
   			</div>
       </div>
     
        </div>
      </div>
          </div>
          <!-- Container-fluid Ends-->
        </div>
        <!-- footer start-->
       <?php include("footer.php");?>
