<?php include("header.php"); ?>
<!-- Page Body Start-->
<div class="page-body-wrapper" style="background:#ececec;">
  <?php include("mainsidebar.php"); ?>
  <div class="page-body">
    <div class="container-fluid">
      <div class="page-title">
        <div class="row">
          <div class="col-sm-6 p-0">
            <h3>Route </h3>
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
            <form action="<?php echo base_url(); ?>/Admin/insert_route" enctype="multipart/form-data" method="post">
              <div class="uk-margin-bottom">
                <lable>Location Name</lable>
                <select class="uk-select" name="location_name" required>
                  <option value=''>Select Location</option>
                  <?php foreach ($location as $loc) { ?>
                    <option value="<?= $loc->location_id ?>"><?= $loc->location_name ?></option>
                  <?php } ?>
                </select>

                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('location_name'); ?></span><?php } ?>
              </div>
              <div class="uk-margin-bottom">
                <lable>Short Name</lable>
                <input type="text" name="short_name" placeholder="enter short name" id="short_name" class="uk-input" value="<?= set_value('short_name') ?>" required />
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('short_name'); ?></span><?php } ?>

              </div>
              <div class="uk-margin-bottom">
                <lable>From</lable>
                <input type="text" name="from" placeholder="enter From city" id="from" class="uk-input" value="<?= set_value('from') ?>" required />
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('from'); ?></span><?php } ?>
              </div>
              <div class="uk-margin-bottom">
                <lable>To</lable>
                <input type="text" name="tor" placeholder="enter To city " id="tor" class="uk-input" value="<?= set_value('tor') ?>" required />
                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('tor'); ?></span><?php } ?>
              </div>
              <div class="uk-margin-bottom">
                <?php if (in_array(20.1, $jobAssign)) { ?>
                  <button type="submit" class="btn btn-primary">Submit</button>
                <?php } ?>
              </div>
            </form>

            <hr>
            <form action="<?php echo base_url(); ?>/Admin/excel_route" method="post" enctype="multipart/form-data">
              <div class="uk-margin-bottom">

                <input type="file" name="file" id="file" class="form-control" accept=".csv, .xlsx">
              </div>
              <div class="uk-margin-bottom">
                <?php if (in_array(20.2, $jobAssign)) { ?>
                  <button type="submit" class="btn btn-primary">Upload Excel</button>
                <?php } ?>
              </div>
            </form>
          </div>
        </div>
        <div class="uk-width-2-3@m">
          <div class="uk-card uk-card-body uk-card-default uk-card-small ">
            <div class="table-responsive custom-scrollbar custom-scrollbar">
              <table class="display" id="row_create" style="width:100%">
                <thead>
                  <tr>
                    <th>Sl no</th>
                    <th>Location Name</th>
                    <th>Short Name</th>
                    <th>Form</th>
                    <th>To</th>
                    <th>Edit</th>
                    <th>Delete</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $i = 1;
                  foreach ($route as $rut) { ?>
                    <tr>
                      <td><?= $i++; ?></td>
                      <td><?= $rut->location_name; ?></td>
                      <td><?= $rut->location_shortname; ?></td>
                      <td><?= $rut->from_city; ?></td>
                      <td><?= $rut->to_city; ?></td>

                      <td>
                        <?php if (in_array(20.3, $jobAssign)) { ?>
                          <a class="btn btn-warning" href="#modal-center<?= $rut->id; ?>" uk-toggle>Edit</a>
                        <?php } ?>

                        <div id="modal-center<?= $rut->id; ?>" class="uk-flex-top" uk-modal>
                          <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">

                            <button class="uk-modal-close-default" type="button" uk-close></button>
                            <form action="<?php echo base_url(); ?>/Admin/edit_route" enctype="multipart/form-data" method="post">
                              <div class="uk-margin-bottom">
                                <lable>Location Name</lable>
                                <input type="hidden" name="route_id" value="<?= $rut->id; ?>" />
                                <select class="uk-select" name="location_name" required>
                                  <option value=''>Select Location</option>
                                  <?php foreach ($location as $loc) { ?>
                                    <option <?php if ($loc->location_id == $rut->location_id) {
                                              echo "selected";
                                            } ?> value="<?= $loc->location_id ?>"><?= $loc->location_name ?></option>
                                  <?php } ?>
                                </select>

                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('location_name'); ?></span><?php } ?>
                              </div>

                              <div class="uk-margin-bottom">
                                <lable>Short Name</lable>
                                <input type="text" name="short_name" placeholder="enter short name" id="short_name" class="uk-input" value="<?= $rut->location_shortname ?>" required />
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('location_shortname'); ?></span><?php } ?>

                              </div>
                              <div class="uk-margin-bottom">
                                <lable>From</lable>
                                <input type="text" name="from" placeholder="enter From city" id="from" class="uk-input" value="<?= $rut->from_city ?>" required />
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('from'); ?></span><?php } ?>
                              </div>
                              <div class="uk-margin-bottom">
                                <lable>To</lable>
                                <input type="text" name="tor" placeholder="enter To city " id="tor" class="uk-input" value="<?= $rut->to_city; ?>" required />
                                <?php if (isset($validation)) { ?><span class="text-danger"><?= $error = $validation->getError('from'); ?></span><?php } ?>
                              </div>
                              <div class="uk-margin-bottom">
                                <button type="submit" class="btn btn-primary">Submit</button>
                              </div>
                            </form>

                          </div>
                        </div>
                      </td>
                      <td>
                        <?php if (in_array(20.4, $jobAssign)) { ?>
                          <a href="javascript:void(0);" onClick="deleteRecord('<?= $rut->id; ?>');" class="btn btn-danger">Delete</a>
                        <?php } ?>
                      </td>
                      <td></td>
                    </tr>
                  <?php } ?>
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sl no</th>
                    <th>Location Name</th>
                    <th>Short Name</th>
                    <th>Form</th>
                    <th>To</th>
                    <th>Edit</th>
                    <th>Delete</th>
                    <th></th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
      <!-- Container-fluid Ends-->
    </div>
    <!-- footer start-->
  </div>
</div>


<form name="frm_deleteBanner" id="frm_deleteBanner" action="<?php echo base_url(); ?>/admin/delete_route" method="post">
  <input type="hidden" name="user_id" id="user_id" value="">
</form>
<script type="text/javascript">
  function deleteRecord(id) {
    $("#user_id").val(id);
    var conf = confirm("Are you sure want to delete this Subadmin");
    if (conf) {
      $("#frm_deleteBanner").submit();
    }
  }
</script>
<script>
  document.getElementById('download_excel').addEventListener('click', function() {
    const baseUrl = '<?php echo base_url(); ?>/AditionalAdminPart/download_excel_route';
    const url = `${baseUrl}`;
    window.location.href = url;
  });
</script>
<?php include("footer.php"); ?>