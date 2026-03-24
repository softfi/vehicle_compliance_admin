<?php include("header.php"); ?>
<!-- Page Body Start-->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    #myTable { width: 100%; }
    .clickable-img { cursor: pointer; border-radius: 4px; transition: 0.3s; }
    .clickable-img:hover { transform: scale(1.1); }
    #modalImage { max-height: 85vh; object-fit: contain; } /* Bigger preview control */
</style>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>
    <?php foreach ($singleuser as $singledata) {} ?>

    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Add Staff / Driver / Mechanic</h3>
                        <?php if (session()->getFlashdata('msg')): ?>
                            <div class="alert alert-success mt-2"><?= session()->getFlashdata('msg'); ?></div>
                        <?php endif; ?>
                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger mt-2"><?= session()->getFlashdata('error'); ?></div>
                        <?php endif; ?>
                        <?php if (isset($validation)): ?>
                            <div class="alert alert-danger mt-2">Validation failed. Please check the form fields.</div>
                        <?php endif; ?>
                    </div>

                    <div class="col-sm-6 p-0 text-right">
                        <button class="btn btn-primary mr-2 mt-3" id="download_excel">Download Excel</button>

                        <?php if (in_array(15.1, $jobAssign)) { ?>
                            <button class="btn btn-primary mt-3" uk-toggle="target: #offcanvas-flip">Add Staff</button>
                        <?php } ?>

                        <?php if (in_array(15.2, $jobAssign)) { ?>
                            <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#uploadexcel">Upload Excel</button>
                        <?php } ?>
                    </div>
                </div>

                <!-- Offcanvas Add Staff Form -->
                <div id="offcanvas-flip" uk-offcanvas="flip: true; overlay: true">
                    <div class="uk-offcanvas-bar uk-padding-remove uk-width-1-2@m" style="background:#fff;">
                        <button class="uk-offcanvas-close" type="button" uk-close></button>
                        <div class="uk-card uk-card-body uk-card-default">
                            <form action="<?= base_url(); ?>/admin/Add_staf" enctype="multipart/form-data" method="post">
                                <div class="row">
                                    <!-- Basic Details -->
                                    <div class="col-sm-6">
                                        <label>Employee Name</label>
                                        <input type="text" class="form-control" name="name" placeholder="Enter Your Name" value="<?= set_value('name'); ?>">
                                        <?php if (isset($validation) && $validation->getError('name')): ?>
                                            <span class="text-danger"><?= $validation->getError('name'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Employee Type</label>
                                        <select class="form-control" name="user_type">
                                            <option value="DRIVER" <?= set_select('user_type', 'DRIVER'); ?>>Driver</option>
                                            <option value="STAFF" <?= set_select('user_type', 'STAFF'); ?>>Staff Master</option>
                                            <option value="MECHANIC" <?= set_select('user_type', 'MECHANIC'); ?>>Mechanic</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Date of Join</label>
                                        <input type="date" class="form-control" name="doj" value="<?= set_value('doj'); ?>">
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Salary</label>
                                        <input type="text" class="form-control" name="salary" placeholder="Enter Salary" value="<?= set_value('salary'); ?>">
                                        <?php if (isset($validation) && $validation->getError('salary')): ?>
                                            <span class="text-danger"><?= $validation->getError('salary'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Upload Image</label>
                                        <input type="file" name="img" class="form-control">
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Name in Bank</label>
                                        <input type="text" class="form-control" name="name_bank" placeholder="Name in Bank" value="<?= set_value('name_bank'); ?>">
                                    </div>
                                    <div class="col-sm-6">
                                        <label>A/c No</label>
                                        <input type="number" class="form-control" name="ac_no" placeholder="Account No" value="<?= set_value('ac_no'); ?>">
                                    </div>
                                    <div class="col-sm-6">
                                        <label>IFSC Code</label>
                                        <input type="text" class="form-control" name="ifsc" placeholder="IFSC Code" value="<?= set_value('ifsc'); ?>">
                                    </div>
                                    <!-- DL -->
                                    <div class="col-sm-6">
                                        <label>DL Front</label>
                                        <input type="file" class="form-control" name="dl_front">
                                    </div>
                                    <div class="col-sm-6">
                                        <label>DL Back</label>
                                        <input type="file" class="form-control" name="dl_back">
                                    </div>
                                    <div class="col-sm-6">
                                        <label>DL Number</label>
                                        <input type="text" class="form-control" name="dl_number" placeholder="DL Number" value="<?= set_value('dl_number'); ?>">
                                    </div>
                                    <div class="col-sm-6">
                                        <label>DL Expiry</label>
                                        <input type="date" class="form-control" name="dl_expiry" value="<?= set_value('dl_expiry'); ?>">
                                    </div>
                                    <!-- Aadhaar -->
                                    <div class="col-sm-6">
                                        <label>Aadhaar Number</label>
                                        <input type="text" class="form-control" name="aadhaar_no" placeholder="Aadhaar No" value="<?= set_value('aadhaar_no'); ?>">
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Aadhaar Front</label>
                                        <input type="file" class="form-control" name="aadhaar_front">
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Aadhaar Back</label>
                                        <input type="file" class="form-control" name="aadhaar_back">
                                    </div>
                                    <!-- Contact & Family -->
                                    <div class="col-sm-6">
                                        <label>Contact No.</label>
                                        <input type="tel" class="form-control" name="tel" placeholder="Contact Number" value="<?= set_value('tel'); ?>">
                                        <?php if (isset($validation) && $validation->getError('tel')): ?>
                                            <span class="text-danger"><?= $validation->getError('tel'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Father's Name</label>
                                        <input type="text" class="form-control" name="fathers_name" placeholder="Father's Name" value="<?= set_value('fathers_name'); ?>">
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Spouse Name</label>
                                        <input type="text" class="form-control" name="spouse_name" placeholder="Spouse Name" value="<?= set_value('spouse_name'); ?>">
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Date of Birth</label>
                                        <input type="date" class="form-control" name="dob" value="<?= set_value('dob'); ?>">
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Family Contact No.</label>
                                        <input type="tel" class="form-control" name="family_contact" placeholder="Family Contact" value="<?= set_value('family_contact'); ?>">
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Blood Group</label>
                                        <select class="form-control" name="blood_group">
                                            <?php foreach (['O+','O-','A+','A-','B+','B-','AB+','AB-'] as $bg): ?>
                                                <option value="<?= $bg ?>" <?= set_select('blood_group', $bg); ?>><?= $bg ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Opening Balance</label>
                                        <input type="text" name="opening_balance" class="form-control" />
                                    </div>
                                    <div class="col-sm-12">
                                        <label>Address</label>
                                        <select name="address" class="form-control">
                                            <option value="">Select Location</option>
                                            <?php foreach ($location as $loc): ?>
                                                <option value="<?= $loc->location_id; ?>" <?= set_select('address', $loc->location_id); ?>><?= $loc->location_name; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (isset($validation) && $validation->getError('address')): ?>
                                            <span class="text-danger"><?= $validation->getError('address'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <p></p>
                                <button type="submit" class="btn btn-primary mt-2">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff Table -->
            <div class="container-fluid default-dashboard mt-4">
                <div class="uk-card uk-card-body uk-card-default">
                    <table id="myTable" class="display nowrap table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Sl No</th>
                                <th>User Type</th>
                                <th>Name</th>
                                <th>DOJ</th>
                                <th>Staff Code</th>
                                <th>Bank Name</th>
                                <th>A/c No</th>
                                <th>IFSC</th>
                                <th>DL Front</th>
                                <th>DL Back</th>
                                <th>DL No</th>
                                <th>DL Expiry</th>
                                <th>Aadhaar No</th>
                                <th>Aadhaar Front</th>
                                <th>Aadhaar Back</th>
                                <th>Salary</th>
                                <th>Contact</th>
                                <th>Spouse</th>
                                <th>DOB</th>
                                <th>Family Contact</th>
                                <th>Blood Group</th>
                                <th>Address</th>
                                <th>Photo</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $i=1; foreach ($allstaf as $staf) { ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= $staf->user_type ?></td>
                                <td><?= $staf->name ?></td>
                                <td><?= !empty($staf->doj) ? date('d/m/Y', strtotime($staf->doj)) : '' ?></td>
                                <td><?= $staf->staff_code ?></td>
                                <td><?= $staf->name_bank ?></td>
                                <td><?= $staf->ac_no ?></td>
                                <td><?= $staf->ifsc ?></td>

                                <!-- DL Front -->
                                <td>
                                    <?php if (!empty($staf->dl_front) && file_exists(FCPATH.'uploads/'.$staf->dl_front)): ?>
                                        <img src="<?= base_url('uploads/'.$staf->dl_front) ?>" class="clickable-img" width="50"
                                             onclick="viewImage('<?= base_url('uploads/'.$staf->dl_front) ?>')">
                                    <?php endif; ?>
                                </td>

                                <!-- DL Back -->
                                <td>
                                    <?php if (!empty($staf->dl_back) && file_exists(FCPATH.'uploads/'.$staf->dl_back)): ?>
                                        <img src="<?= base_url('uploads/'.$staf->dl_back) ?>" class="clickable-img" width="50"
                                             onclick="viewImage('<?= base_url('uploads/'.$staf->dl_back) ?>')">
                                    <?php endif; ?>
                                </td>

                                <td><?= $staf->dl_number ?></td>
                                <td><?= !empty($staf->dl_expiry) ? date('d/m/Y', strtotime($staf->dl_expiry)) : '' ?></td>
                                <td><?= $staf->aadhaar_no ?></td>

                                <!-- Aadhaar Front -->
                                <td>
                                    <?php if (!empty($staf->aadhaar_front) && file_exists(FCPATH.'uploads/'.$staf->aadhaar_front)): ?>
                                        <img src="<?= base_url('uploads/'.$staf->aadhaar_front) ?>" class="clickable-img" width="50"
                                             onclick="viewImage('<?= base_url('uploads/'.$staf->aadhaar_front) ?>')">
                                    <?php endif; ?>
                                </td>

                                <!-- Aadhaar Back -->
                                <td>
                                    <?php if (!empty($staf->aadhaar_back) && file_exists(FCPATH.'uploads/'.$staf->aadhaar_back)): ?>
                                        <img src="<?= base_url('uploads/'.$staf->aadhaar_back) ?>" class="clickable-img" width="50"
                                             onclick="viewImage('<?= base_url('uploads/'.$staf->aadhaar_back) ?>')">
                                    <?php endif; ?>
                                </td>

                                <td><?= $staf->salary ?></td>
                                <td><?= $staf->tel ?></td>
                                <td><?= $staf->spouse_name ?></td>
                                <td><?= !empty($staf->dob) ? date('d/m/Y', strtotime($staf->dob)) : '' ?></td>
                                <td><?= $staf->family_contact ?></td>
                                <td><?= $staf->blood_group ?></td>
                                <td><?= $staf->location_name ?></td>

                                <!-- Profile Photo -->
                                <td>
                                    <?php if (!empty($staf->img) && file_exists(FCPATH.'uploads/'.$staf->img)): ?>
                                        <img src="<?= base_url('uploads/'.$staf->img) ?>" class="clickable-img" width="50"
                                             onclick="viewImage('<?= base_url('uploads/'.$staf->img) ?>')">
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="btn-group">
                                        <?php if (in_array(15.3, $jobAssign)) { ?>
                                            <a href="javascript:void(0);" onclick="editvehicle('<?= $staf->id; ?>');" class="btn btn-sm btn-secondary">Edit</a>
                                        <?php } ?>
                                        <?php if (in_array(15.4, $jobAssign)) { ?>
                                            <a href="<?= base_url('Admin/DeleteStaff/'.$staf->id); ?>" onclick="return confirm('Are you sure?');" class="btn btn-sm btn-danger">Delete</a>
                                        <?php } ?>
                                        <a href="javascript:void(0);" onclick="printvehicle('<?= $staf->id; ?>');" class="btn btn-sm btn-primary">Print</a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Upload Excel Modal -->
            <div class="modal fade" id="uploadexcel" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content p-3">
                        <p><a href="<?= base_url('sampleexcel/staffexcell.xlsx') ?>">Click here</a> to download sample</p>
                        <form action="<?= base_url('Admin/upload_staff_excel') ?>" method="post" enctype="multipart/form-data">
                            <input type="file" name="file" class="form-control mb-2" accept=".csv,.xlsx">
                            <button class="btn btn-primary">Upload</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit / Print Offcanvas -->
            <div id="edit_vehicle" uk-offcanvas="flip: true; overlay: true">
                <div class="uk-offcanvas-bar uk-padding-remove uk-width-1-2" style="background:#fff">
                    <button class="uk-offcanvas-close" type="button" uk-close></button>
                    <div class="uk-card uk-card-body">
                        <div id="edit_staff_form"></div>
                    </div>
                </div>
            </div>

            <div id="print_vehicle" uk-offcanvas="flip: true; overlay: true">
                <div class="uk-offcanvas-bar uk-padding-remove uk-width-1-2" style="background:#fff">
                    <button class="uk-offcanvas-close" type="button" uk-close></button>
                    <div class="uk-card uk-card-body">
                        <div id="print_staff_form"></div>
                    </div>
                </div>
            </div>

            <!-- Image Modal (Bigger) -->
            <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl"> <!-- modal-xl for extra width -->
                    <div class="modal-content bg-dark">
                        <div class="modal-header border-0">
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center p-0">
                            <img id="modalImage" src="" class="img-fluid rounded">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    $(document).ready(function () {
        <?php if (isset($validation)): ?>
            UIkit.offcanvas('#offcanvas-flip').show();
        <?php endif; ?>

        var table = $('#myTable').DataTable({
            responsive: true,
            scrollX: true,
            scrollY: '400px',
            scrollCollapse: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100]
        });
        table.columns.adjust();
    });

    // View image in large modal
    function viewImage(src) {
        $('#modalImage').attr('src', src);
        $('#imageModal').modal('show');
    }

    // Download Excel
    document.getElementById('download_excel').addEventListener('click', function () {
        window.location.href = '<?= base_url(); ?>/AditionalAdminPart/download_excel';
    });

    function editvehicle(id) {
        $.post('<?= base_url(); ?>/Admin/edit_staff', { staff_id: id }, function (response) {
            $('#edit_staff_form').html(response);
            UIkit.offcanvas('#edit_vehicle').show();
        });
    }

    function printvehicle(id) {
        $.post('<?= base_url(); ?>/Admin/PrintStaff', { staff_id: id }, function (response) {
            $('#print_staff_form').html(response);
            UIkit.offcanvas('#print_vehicle').show();
        });
    }
</script>

<?php include("footer.php"); ?>
