<!DOCTYPE html>
<html lang="en">
  
<!-- Mirrored from admin.pixelstrap.net/dunzo/template/login_one.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 23 May 2024 06:37:32 GMT -->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dunzo admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Dunzo admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="pixelstrap">
    <link rel="icon" href="<?php echo base_url();?>/assets/admin/images/favicon.png" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo base_url();?>/assets/admin/images/favicon.png" type="image/x-icon">
    <title>  Admin</title>
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Outfit:400,400i,500,500i,700,700i&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/admin/css/font-awesome.css">
    <!-- ico-font-->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>/assets/admin/css/vendors/icofont.css">
    <!-- Themify icon-->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>/assets/admin/css/vendors/themify.css">
    <!-- Flag icon-->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>/assets/admin/css/vendors/flag-icon.css">
    <!-- Feather icon-->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>/assets/admin/css/vendors/feather-icon.css">
    <!-- Plugins css start-->
    <!-- Plugins css Ends-->
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>/assets/admin/css/vendors/bootstrap.css">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>/assets/admin/css/style.css">
    <link id="color" rel="stylesheet" href="<?php echo base_url();?>/assets/admin/css/color-1.css" media="screen">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>/assets/admin/css/responsive.css">
  </head>
  <body>
    <!-- login page start-->
    <div class="container-fluid">
      <div class="row">
        <div class="col-xl-7"><img class="bg-img-cover bg-center" src="<?php echo base_url();?>/assets/admin/images/login/2.jpg" alt="looginpage"></div>
        <div class="col-xl-5 p-0">
          <div class="login-card login-dark">
            <div>
              <div><a class="logo text-start" href="#"><img class="img-fluid for-light" src="<?php echo base_url();?>/assets/admin/images/logo/logo-1.png" alt="looginpage"><img class="img-fluid for-dark" src="<?php echo base_url();?>/assets/admin/images/logo/logo.png" alt="looginpage"></a></div>
              <div class="login-main"> 
              <?php if(session()->getFlashdata('msg')):?>
                                                <div class="alert alert-warning">
                                                <?= session()->getFlashdata('msg') ?>
                                                </div>
                                            <?php endif;?>
                                            
                                            
                <form class="theme-form" action="<?=base_url();?>/admin/login" method="post" id="form-login">
                  <h3>Sign in to account</h3>
                  <p>Enter your email & password to login</p>
                  <div class="form-group">
                    <label class="col-form-label">User Name</label>
                    <input class="form-control" name="username" type="text" required="" placeholder="User Name" value="<?= set_value('username') ?>">
                  </div>
                  <div class="form-group">
                    <label class="col-form-label">Password</label>
                    <div class="form-input position-relative">
                      <input class="form-control" type="password" name="password" required="" placeholder="*********" value="<?= set_value('password') ?>">
                      
                    </div>
                  </div>
                  <div class="form-group mb-0">
                    <!--<div class="checkbox p-0">
                      <input id="checkbox1" type="checkbox">
                      <label class="text-muted" for="checkbox1">Remember password</label>
                    </div>-->
                    <button class="btn btn-primary btn-block w-100" type="submit">Sign in</button>
                  </div>
                  <p>&nbsp;</p>
                  <!--<h6 class="text-muted mt-4 or">Or Sign in with</h6>
                  <div class="social mt-4">
                    <div class="btn-showcase"><a class="btn btn-light" href="https://www.linkedin.com/login" target="_blank"><i class="txt-linkedin" data-feather="linkedin"></i> LinkedIn </a><a class="btn btn-light" href="https://twitter.com/login?lang=en" target="_blank"><i class="txt-twitter" data-feather="twitter"></i>twitter</a><a class="btn btn-light" href="https://www.facebook.com/" target="_blank"><i class="txt-fb" data-feather="facebook"></i>facebook</a></div>
                  </div>-->
                 <!-- <p class="mt-4 mb-0 text-center">Don't have account?<a class="ms-2" href="sign-up.html">Create Account</a></p>-->
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- latest jquery-->
      <script src="<?php echo base_url();?>/assets/admin/js/jquery.min.js"></script>
      <!-- Bootstrap js-->
      <script src="<?php echo base_url();?>/assets/admin/js/bootstrap/bootstrap.bundle.min.js"></script>
      <!-- feather icon js-->
      <script src="<?php echo base_url();?>/assets/admin/js/icons/feather-icon/feather.min.js"></script>
      <script src="<?php echo base_url();?>/assets/admin/js/icons/feather-icon/feather-icon.js"></script>
      <!-- scrollbar js-->
      <!-- Sidebar jquery-->
      <script src="<?php echo base_url();?>/assets/admin/js/config.js"></script>
      <!-- Plugins JS start-->
      <!-- Plugins JS Ends-->
      <!-- Theme js-->
      <script src="<?php echo base_url();?>/assets/admin/js/script.js"></script>
      <!-- Plugin used-->
    </div>
  </body>

<!-- Mirrored from admin.pixelstrap.net/dunzo/template/login_one.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 23 May 2024 06:37:32 GMT -->
</html>