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
            
           <?php foreach($tyer_data as $tyer){}?>
            
            <div class="uk-card uk-card-body uk-card-default uk-card-small uk-width-1-2@m">
                
                 <form action="<?php echo base_url(); ?>/Admin/update_tyer_report" method="post" enctype="multipart/form-data">
        <h5><?= $tyer->tyer_sl_no ?></h5>
        <input type="hidden" value="<?= $tyer->id ?>" name="tyer_id"/>
        <div>
                            <label>Select Vendor</label>
                            <select name="vendor_id" id="single" class="form-control">
                                <option value="">Select Vendor</option>
                                <?php foreach ($vendor as $ven) { ?>
                                    <option value="<?= $ven->id; ?>"><?= $ven->name; ?></option>
                                <?php } ?>
                            </select>
                        </div>
        <p>update status</p>
        <select class="form-control" name="status">
            <option value="1">Exchange</option>
            <option value="4">Repair</option>
            <option value="3">Trash</option>
        </select>
        <p>Remark</p>
       <textarea class="form-control" name="remark"></textarea>
       <p>&nbsp;</p>
     <button class="btn btn-primary">Submit</button>
     
     </form>
     
     
               
            </div>
        </div>
        <!-- Container-fluid Ends-->
    </div>
    <!-- footer start-->

 
















<?php include("footer.php"); ?>

