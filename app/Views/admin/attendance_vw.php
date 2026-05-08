<?php include("header.php"); ?>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>

    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h3>Attendance Management</h3>
                        <p class="text-muted mt-1">Daily records and personnel check-ins</p>
                    </div>
                    <style>
                        .select2-container--default .select2-selection--single {
                            height: 38px !important;
                            border: 1px solid #ced4da !important;
                            padding-top: 5px;
                        }
                        .select2-container {
                            display: block !important;
                        }
                    </style>
                    <div class="col-sm-6 text-right d-flex justify-content-end align-items-center" style="gap: 10px;">
                        <a href="<?= base_url('admin/attendance/bulk'); ?>" class="btn btn-outline-primary btn-action shadow-sm" style="border-radius: 8px; padding: 8px 16px; font-weight: 600;">
                            <i class="fa fa-upload mr-1"></i> Bulk Upload
                        </a>
                        <button type="button" class="btn btn-primary btn-action shadow-sm" style="border-radius: 8px; padding: 8px 16px; font-weight: 600;" onclick="openAddModal()">
                            <i class="fa fa-plus mr-1"></i> Add Attendance
                        </button>
                    </div>
                </div>
                <?php if (session()->getFlashdata('msg')): ?>
                    <div class="alert alert-success mt-3 shadow-sm border-0"><?= session()->getFlashdata('msg'); ?></div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger mt-3 shadow-sm border-0"><?= session()->getFlashdata('error'); ?></div>
                <?php endif; ?>
            </div>

            <!-- Filters Section -->
            <div class="card">
                <div class="card-header">
                    <h5>Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-md-2">
                            <label>From Date</label>
                            <input type="date" class="form-control" name="from_date" value="<?= $from_date; ?>">
                        </div>
                        <div class="col-md-2">
                            <label>To Date</label>
                            <input type="date" class="form-control" name="to_date" value="<?= $to_date; ?>">
                        </div>
                        <div class="col-md-2">
                            <label>Staff Member</label>
                            <select class="form-control select2-search" name="staff_id">
                                <option value="">-- Select Staff --</option>
                                <?php foreach ($staff as $s): ?>
                                    <option value="<?= $s->id; ?>" <?= isset($filters['staff_id']) && $filters['staff_id'] == $s->id ? 'selected' : ''; ?>>
                                        <?= $s->name; ?> (<?= $s->staff_code; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Status</label>
                            <select class="form-control" name="status">
                                <option value="">-- All Status --</option>
                                <option value="Present" <?= isset($filters['status']) && $filters['status'] == 'Present' ? 'selected' : ''; ?>>Present</option>
                                <option value="Absent" <?= isset($filters['status']) && $filters['status'] == 'Absent' ? 'selected' : ''; ?>>Absent</option>
                                <option value="Leave" <?= isset($filters['status']) && $filters['status'] == 'Leave' ? 'selected' : ''; ?>>Leave</option>
                                <option value="Half-day" <?= isset($filters['status']) && $filters['status'] == 'Half-day' ? 'selected' : ''; ?>>Half-day</option>
                                <option value="Sick-leave" <?= isset($filters['status']) && $filters['status'] == 'Sick-leave' ? 'selected' : ''; ?>>Sick-leave</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Location</label>
                            <select class="form-control select2-search" name="location_id">
                                <option value="">-- All --</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= $loc->location_id; ?>" <?= isset($filters['location_id']) && $filters['location_id'] == $loc->location_id ? 'selected' : ''; ?>>
                                        <?= $loc->location_name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            <a href="<?= base_url('admin/attendance'); ?>" class="btn btn-secondary btn-sm">Reset</a>
                        </div>
                        <div class="col-md-12 mt-3">
                            <a href="<?= base_url('admin/attendance/export-excel?' . http_build_query($_GET)); ?>" class="btn btn-success btn-sm">Export to Excel (Filtered)</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Attendance Table -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Attendance Records (Page <?= $pagination['page']; ?> of <?= $pagination['totalPages']; ?>)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Staff Name</th>
                                <th>Staff Code</th>
                                <th>Location</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($attendance)): ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No attendance records found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($attendance as $record): ?>
                                    <tr>
                                        <td><?= $record->id; ?></td>
                                        <td><?= $record->staff_name; ?></td>
                                        <td><?= $record->staff_code; ?></td>
                                        <td><?= $record->location_name ?? '-'; ?></td>
                                        <td><?= date('d-M-Y', strtotime($record->attendance_date)); ?></td>
                                        <td>
                                            <span class="badge badge-<?= $record->status == 'Present' ? 'success' : ($record->status == 'Absent' ? 'danger' : 'warning'); ?>">
                                                <?= $record->status; ?>
                                            </span>
                                        </td>
                                        <td><?= $record->check_in_time ?? '-'; ?></td>
                                        <td><?= $record->check_out_time ?? '-'; ?></td>
                                        <td><?= substr($record->notes, 0, 20) ?? '-'; ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" onclick="openEditModal(<?= $record->id; ?>)">Edit</button>
                                            <a href="<?= base_url('admin/attendance/delete/' . $record->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['totalPages'] > 1):
                    // Build the base query string with all current filters
                    $filterQuery = http_build_query([
                        'from_date'   => $from_date,
                        'to_date'     => $to_date,
                        'staff_id'    => $filters['staff_id'] ?? '',
                        'status'      => $filters['status'] ?? '',
                        'location_id' => $filters['location_id'] ?? '',
                    ]);
                ?>
                    <div class="card-footer">
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php if ($pagination['page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= base_url('admin/attendance?page=1&' . $filterQuery); ?>">First</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= base_url('admin/attendance?page=' . ($pagination['page'] - 1) . '&' . $filterQuery); ?>">Previous</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['totalPages'], $pagination['page'] + 2); $i++): ?>
                                    <li class="page-item <?= $i == $pagination['page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?= base_url('admin/attendance?page=' . $i . '&' . $filterQuery); ?>"><?= $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= base_url('admin/attendance?page=' . ($pagination['page'] + 1) . '&' . $filterQuery); ?>">Next</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= base_url('admin/attendance?page=' . $pagination['totalPages'] . '&' . $filterQuery); ?>">Last</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog" aria-labelledby="attendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceModalLabel">Add Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="attendanceForm" method="POST" action="">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Location</label>
                                <select class="form-control select2-search" id="modal_filter_location" onchange="filterStaffByLocation()">
                                    <option value="">-- All Locations --</option>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?= $loc->location_id; ?>"><?= $loc->location_name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Staff Member <span class="text-danger">*</span></label>
                                <select class="form-control select2-search" name="staff_id" id="modal_staff_id" required>
                                    <option value="">-- Select Staff --</option>
                                    <?php foreach ($staff as $s): ?>
                                        <option value="<?= $s->id; ?>" data-location="<?= $s->location_id; ?>"><?= $s->name; ?> (<?= $s->staff_code; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Attendance Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="attendance_date" id="modal_attendance_date" required value="<?= date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select class="form-control" name="status" id="modal_status" required onchange="toggleModalFields()">
                                    <option value="Present">Present</option>
                                    <option value="Absent">Absent</option>
                                    <option value="Leave">Leave</option>
                                    <option value="Half-day">Half-day</option>
                                    <option value="Sick-leave">Sick-leave</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6" id="modal_leave_type_div" style="display: none;">
                            <div class="form-group">
                                <label>Leave Type</label>
                                <select class="form-control" name="leave_type" id="modal_leave_type">
                                    <option value="">-- Select Leave --</option>
                                    <option value="Annual">Annual Leave</option>
                                    <option value="Casual">Casual Leave</option>
                                    <option value="Sick">Sick Leave</option>
                                    <option value="Unpaid">Unpaid Leave</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="modal_time_fields">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Check-in Time</label>
                                <input type="time" class="form-control" name="check_in_time" id="modal_check_in_time">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Check-out Time</label>
                                <input type="time" class="form-control" name="check_out_time" id="modal_check_out_time">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" id="modal_notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    $('#attendanceModalLabel').text('Add Attendance');
    $('#attendanceForm').attr('action', '<?= base_url('admin/attendance/store'); ?>');
    $('#attendanceForm')[0].reset();
    $('#modal_attendance_date').val('<?= date('Y-m-d'); ?>');
    $('#modal_filter_location').val("");
    filterStaffByLocation();
    toggleModalFields();
    $('#attendanceModal').modal('show');
}

function openEditModal(id) {
    $('#attendanceModalLabel').text('Edit Attendance');
    $('#attendanceForm').attr('action', '<?= base_url('admin/attendance/update/'); ?>' + id);
    
    // Fetch data via AJAX
    $.ajax({
        url: '<?= base_url('admin/attendance/get-data/'); ?>' + id,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                const data = response.data;
                $('#modal_staff_id').val(data.staff_id);
                
                // Set location filter based on staff
                const staffLocation = $('#modal_staff_id option:selected').data('location');
                if(staffLocation) {
                    $('#modal_filter_location').val(staffLocation);
                } else {
                    $('#modal_filter_location').val("");
                }
                filterStaffByLocation();
                $('#modal_staff_id').val(data.staff_id); // Re-set staff after filter

                $('#modal_attendance_date').val(data.attendance_date);
                $('#modal_status').val(data.status);
                $('#modal_leave_type').val(data.leave_type);
                $('#modal_check_in_time').val(data.check_in_time);
                $('#modal_check_out_time').val(data.check_out_time);
                $('#modal_notes').val(data.notes);
                
                toggleModalFields();
                $('#attendanceModal').modal('show');
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Failed to fetch data');
        }
    });
}

function filterStaffByLocation() {
    const locationId = $('#modal_filter_location').val();
    const staffSelect = $('#modal_staff_id');
    const allOptions = staffSelect.find('option');
    
    allOptions.each(function() {
        const option = $(this);
        const staffLocationId = option.data('location');
        
        if (locationId === "" || staffLocationId == locationId || option.val() === "") {
            option.show();
        } else {
            option.hide();
        }
    });

    // If current selected staff is hidden, reset it
    const selectedOption = staffSelect.find('option:selected');
    if (selectedOption.is(':hidden')) {
        staffSelect.val("");
    }
}

function toggleModalFields() {
    const status = $('#modal_status').val();
    if (status === 'Leave' || status === 'Sick-leave') {
        $('#modal_leave_type_div').show();
        $('#modal_time_fields').hide();
    } else if (status === 'Present' || status === 'Half-day') {
        $('#modal_leave_type_div').hide();
        $('#modal_time_fields').show();
    } else {
        $('#modal_leave_type_div').hide();
        $('#modal_time_fields').hide();
    }
}
$(document).ready(function() {
    $('.select2-search').each(function() {
        var $this = $(this);
        var options = {
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        };
        
        // If element is inside a modal, add dropdownParent
        if ($this.closest('.modal').length) {
            options.dropdownParent = $this.closest('.modal');
        }
        
        $this.select2(options);
    });
});
</script>

<?php include("footer.php"); ?>
