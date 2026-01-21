<?php include("header.php"); ?>
<!-- Page Body Start-->
<div class="page-body-wrapper" style="background:#ececec;">
    <?php include("mainsidebar.php"); ?>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Bank </h3>
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
                        <form action="<?php echo base_url(); ?>/Admin/insert_bank" enctype="multipart/form-data" method="post">
                            <div class="uk-margin-bottom">
                                <lable>Bank Name</lable>
                                <input type="text" name="bank_name" placeholder="enter Bank name" id="bank_name" class="uk-input" value="<?= set_value('bank_name') ?>" required />
                            </div>
                            <div class="uk-margin-bottom">
                                <lable>IFSC Code </lable>
                                <input type="text" name="ifsc_code" placeholder="enter IFSC Code" id="ifsc_code" class="uk-input" value="<?= set_value('ifsc_code') ?>" required />
                            </div>
                            <div class="uk-margin-bottom">
                                <lable>A/c No.</lable>
                                <input type="number" name="ac_no" placeholder="enter Account NO." id="ac_no" class="uk-input" value="<?= set_value('ac_no') ?>" />
                            </div>
                            <div class="uk-margin-bottom">
                                <lable>Short Name</lable>
                                <input type="text" name="short_name" placeholder="enter Short Name  " id="short_name" class="uk-input" value="<?= set_value('short_name') ?>" required />
                            </div>
                            <div class="uk-margin-bottom">
                                <lable>Opening Balance</lable>
                                <input type="number" name="opening_balance" placeholder="enter Opening Balance " id="opening_balance" class="uk-input" value="<?= set_value('opening_balance') ?>" required />
                            </div>
                            <div class="uk-margin-bottom">
                                <?php if (in_array(21.1, $jobAssign)) { ?>
                                    <button type="submit" class="btn btn-primary">Submit</button>
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
                                        <th>Bank Name</th>
                                        <th>IFSC Code</th>
                                        <th>A/c No.</th>
                                        <th>Short Name</th>
                                        <th>Opening Balance</th>
                                        <th>Edit</th>
                                        <th>Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bank_details as $index => $bank): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($bank->bank_name) ?></td>
                                            <td><?= htmlspecialchars($bank->ifsc_code) ?></td>
                                            <td><?= htmlspecialchars($bank->ac_no) ?></td>
                                            <td><?= htmlspecialchars($bank->short_name) ?></td>
                                            <td><?= htmlspecialchars($bank->opening_balance) ?></td>
                                            <td>
                                                <?php if (in_array(21.2, $jobAssign)) { ?>
                                                    <a class="btn btn-warning" href="#modal-<?= $bank->id ?>" uk-toggle>Edit</a>
                                                <?php } ?>
                                                <div id="modal-<?= $bank->id ?>" class="uk-flex-top" uk-modal>
                                                    <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
                                                        <button class="uk-modal-close-default" type="button" uk-close></button>
                                                        <form action="<?= base_url(); ?>/Admin/update_bank/<?= $bank->id ?>" method="post">
                                                            <div class="uk-margin-bottom">
                                                                <input type="hidden" name="bank_id" placeholder="Enter Bank Name" class="uk-input" value="<?= htmlspecialchars($bank->id) ?>" required />
                                                                <label>Bank Name</label>
                                                                <input type="text" name="bank_name" placeholder="Enter Bank Name" class="uk-input" value="<?= htmlspecialchars($bank->bank_name) ?>" required />
                                                            </div>
                                                            <div class="uk-margin-bottom">
                                                                <label>IFSC Code</label>
                                                                <input type="text" name="ifsc_code" placeholder="Enter IFSC Code" class="uk-input" value="<?= htmlspecialchars($bank->ifsc_code) ?>" required />
                                                            </div>
                                                            <div class="uk-margin-bottom">
                                                                <label>A/c No.</label>
                                                                <input type="number" name="ac_no" placeholder="Enter Account No." class="uk-input" value="<?= htmlspecialchars($bank->ac_no) ?>" required />
                                                            </div>
                                                            <div class="uk-margin-bottom">
                                                                <label>Short Name</label>
                                                                <input type="text" name="short_name" placeholder="Enter Short Name" class="uk-input" value="<?= htmlspecialchars($bank->short_name) ?>" required />
                                                            </div>
                                                            <div class="uk-margin-bottom">
                                                                <label>Opening Balance</label>
                                                                <input type="number" name="opening_balance" placeholder="Enter Opening Balance" class="uk-input" value="<?= htmlspecialchars($bank->opening_balance) ?>" required />
                                                            </div>
                                                            <div class="uk-margin-bottom">
                                                                <button type="submit" class="btn btn-primary">Submit</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <form action="<?= base_url(); ?>/Admin/delete_bank/<?= $bank->id ?>" method="post" onsubmit="return confirm('Are you sure you want to delete this bank?');">
                                                    <?php if (in_array(21.3, $jobAssign)) { ?>
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    <?php } ?>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Sl no</th>
                                        <th>Bank Name</th>
                                        <th>IFSC Code</th>
                                        <th>A/c No.</th>
                                        <th>Short Name</th>
                                        <th>Opening Balance</th>
                                        <th>Edit</th>
                                        <th>Delete</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Container-fluid Ends-->
        </div>
    </div>
    <script>
        document.getElementById('download_excel').addEventListener('click', function() {
            const baseUrl = '<?php echo base_url(); ?>/AditionalAdminPart/download_excel_bank';
            const url = `${baseUrl}`;
            window.location.href = url;
        });
    </script>
    <?php include("footer.php"); ?>