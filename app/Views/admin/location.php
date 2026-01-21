<?php include("header.php"); ?>
<!-- Page Body Start-->
<div class="page-body-wrapper" style="background:#ececec;">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Default Location </h3>
                    </div>
                    <div class="col-sm-6 p-0">
                        <div>
                            <label for="download_excel">.</label>
                            <button class="btn btn-primary uk-align-right" type="button" id="download_excel" style="margin: 25px 20px 0px 30px;">Download Excel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid starts-->
        <div class="container-fluid default-dashboard">
            <div class="uk-grid-small" uk-grid>
                <div class="uk-width-1-3@m">
                    <div class="uk-card uk-card-body uk-card-default uk-card-small ">
                        <form action="<?php echo base_url(); ?>/Admin/insert_location" enctype="multipart/form-data" method="post">
                            <div class="uk-margin-bottom">
                                <lable>City</lable>
                                <input type="text" name="city_name" placeholder="enter city name" id="city_name" class="uk-input" value="<?= set_value('city_name') ?>" />
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('city_name'); ?></span><?php } ?>
                            </div>

                            <div class="uk-margin-bottom">
                                <lable>Short Name</lable>
                                <input type="text" name="short_name" placeholder="enter short name" id="short_name" class="uk-input" value="<?= set_value('short_name') ?>" />
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('short_name'); ?></span><?php } ?>
                            </div>
                            <div class="uk-margin-bottom">
                                <?php if (in_array(19.1, $jobAssign)) { ?>
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                <?php } ?>
                            </div>
                        </form>

                        <hr>
                        <form action="<?php echo base_url(); ?>/Admin/excel_location" method="post" enctype="multipart/form-data">
                            <div class="uk-margin-bottom">

                                <input type="file" name="file" id="file" class="form-control" accept=".csv, .xlsx">
                            </div>
                            <div class="uk-margin-bottom">
                                <?php if (in_array(19.2, $jobAssign)) { ?>
                                    <button type="submit" class="btn btn-primary">Upload Excel</button>
                                <?php } ?>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="uk-width-2-3@m">
                    <div class="uk-card uk-card-body uk-card-default uk-card-small">
                        <table class="display" id="row_create" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Sl no</th>
                                    <th>Location Name</th>
                                    <th>Location Short Name</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                    <th></th>
                                </tr>

                            </thead>
                            <tbody>

                                <?php
                                $i = 1;
                                foreach ($location as $loc) { ?>
                                    <tr>
                                        <td><?= $i++; ?></td>
                                        <td><?= $loc->location_name ?></td>
                                        <td><?= $loc->location_shordname ?></td>

                                        <td>
                                            <?php if (in_array(19.3, $jobAssign)) { ?>
                                                <a class="btn btn-warning" href="#modal-center<?= $loc->location_id ?>" uk-toggle>Edit</a>
                                            <?php } ?>
                                            <div id="modal-center<?= $loc->location_id ?>" class="uk-flex-top" uk-modal>
                                                <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">

                                                    <button class="uk-modal-close-default" type="button" uk-close></button>
                                                    <form action="<?php echo base_url(); ?>/Admin/edit_location" method="post">
                                                        <div class="uk-margin-bottom">
                                                            <lable>Location Name</lable>
                                                            <input type="hidden" name="unit_id" value="<?= $loc->location_id ?>" id="" class="uk-input" required />
                                                            <input type="text" name="name" value="<?= $loc->location_name ?>" id="" class="uk-input" />
                                                        </div>
                                                        <div class="uk-margin-bottom">
                                                            <lable>Short Name</lable>
                                                            <input type="text" name="sname" value="<?= $loc->location_shordname ?>" id="" class="uk-input" required />
                                                        </div>
                                                        <div class="uk-margin-bottom">
                                                            <button type="submit" class="btn btn-primary">Submit</button>
                                                        </div>
                                                    </form>



                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (in_array(19.4, $jobAssign)) { ?>
                                                <a href="javascript:void(0);" onClick="deleteRecord('<?= $loc->location_id; ?>');" class="btn btn-danger">Delete</a>
                                            <?php } ?>
                                        </td>
                                        <td></td>
                                    </tr>
                                <?php } ?>

                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Sl no</th>
                                    <th>Location Unit Name</th>
                                    <th>Location Short Name</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                    <th></th>
                                </tr>

                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Container-fluid Ends-->
        </div>
        <!-- footer start-->
    </div>
</div>

<form name="frm_deleteBanner" id="frm_deleteBanner" action="<?php echo base_url(); ?>/admin/delete_location" method="post">
    <input type="hidden" name="user_id" id="user_id" value="">
</form>
<script type="text/javascript">
    function deleteRecord(id) {
        $("#user_id").val(id);
        var conf = confirm("Are you sure want to delete this record");
        if (conf) {
            $("#frm_deleteBanner").submit();
        }
    }
</script>
<script>
    document.getElementById('download_excel').addEventListener('click', function() {
        const baseUrl = '<?php echo base_url(); ?>/AditionalAdminPart/download_excel_location';
        const url = `${baseUrl}`;
        window.location.href = url;
    });
</script>


<?php include("footer.php"); ?>