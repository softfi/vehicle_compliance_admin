<?php include("header.php"); ?>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>

    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Add Attendance Record</h3>
                        <?php if (session()->getFlashdata('validation')): ?>
                            <div class="alert alert-danger mt-2">
                                <strong>Validation Errors:</strong>
                                <?php foreach ($validation->getErrors() as $error): ?>
                                    <br><?= $error;?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger mt-2"><?= session()->getFlashdata('error'); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm-6 p-0 text-right">
                        <a href="<?= base_url('admin/attendance'); ?>" class="btn btn-secondary mt-3">Back to List</a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?= base_url('admin/attendance/store'); ?>">
                        <?= csrf_field(); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="staff_id" class="form-label">Staff Member <span class="text-danger">*</span></label>
                                    <select class="form-control" id="staff_id" name="staff_id" required>
                                        <option value="">-- Select Staff --</option>
                                        <?php foreach ($staff as $s): ?>
                                            <option data-doj="<?= $s->doj ?? ''; ?>" data-resign-date="<?= $s->resign_date ?? ''; ?>" value="<?= $s->id; ?>" <?= old('staff_id') == $s->id ? 'selected' : ''; ?>>
                                                <?= $s->name; ?> (<?= $s->staff_code; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($validation) && $validation->getError('staff_id')): ?>
                                        <span class="text-danger"><?= $validation->getError('staff_id'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="attendance_date" class="form-label">Attendance Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="attendance_date" name="attendance_date" value="<?= old('attendance_date') ?? $today; ?>" required>
                                    <?php if (isset($validation) && $validation->getError('attendance_date')): ?>
                                        <span class="text-danger"><?= $validation->getError('attendance_date'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-control" id="status" name="status" required onchange="toggleLeaveType()">
                                        <option value="">-- Select Status --</option>
                                        <option value="Present" <?= old('status') == 'Present' ? 'selected' : ''; ?>>Present</option>
                                        <option value="Absent" <?= old('status') == 'Absent' ? 'selected' : ''; ?>>Absent</option>
                                        <option value="Leave" <?= old('status') == 'Leave' ? 'selected' : ''; ?>>Leave</option>
                                        <option value="Half-day" <?= old('status') == 'Half-day' ? 'selected' : ''; ?>>Half-day</option>
                                        <option value="Sick-leave" <?= old('status') == 'Sick-leave' ? 'selected' : ''; ?>>Sick-leave</option>
                                        <option value="Holiday" <?= old('status') == 'Holiday' ? 'selected' : ''; ?>>Holiday</option>
                                    </select>
                                    <?php if (isset($validation) && $validation->getError('status')): ?>
                                        <span class="text-danger"><?= $validation->getError('status'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-6" id="leave_type_div" style="display: none;">
                                <div class="form-group">
                                    <label for="leave_type" class="form-label">Leave Type</label>
                                    <select class="form-control" id="leave_type" name="leave_type">
                                        <option value="">-- Select Leave Type --</option>
                                        <option value="Annual" <?= old('leave_type') == 'Annual' ? 'selected' : ''; ?>>Annual Leave</option>
                                        <option value="Casual" <?= old('leave_type') == 'Casual' ? 'selected' : ''; ?>>Casual Leave</option>
                                        <option value="Sick" <?= old('leave_type') == 'Sick' ? 'selected' : ''; ?>>Sick Leave</option>
                                        <option value="Unpaid" <?= old('leave_type') == 'Unpaid' ? 'selected' : ''; ?>>Unpaid Leave</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="time_fields" style="display: none;">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="check_in_time" class="form-label">Check-in Time</label>
                                    <input type="time" class="form-control" id="check_in_time" name="check_in_time" value="<?= old('check_in_time'); ?>">
                                    <?php if (isset($validation) && $validation->getError('check_in_time')): ?>
                                        <span class="text-danger"><?= $validation->getError('check_in_time'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="check_out_time" class="form-label">Check-out Time</label>
                                    <input type="time" class="form-control" id="check_out_time" name="check_out_time" value="<?= old('check_out_time'); ?>">
                                    <?php if (isset($validation) && $validation->getError('check_out_time')): ?>
                                        <span class="text-danger"><?= $validation->getError('check_out_time'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Enter any additional notes..." maxlength="500"><?= old('notes'); ?></textarea>
                            <small class="form-text text-muted">Max 500 characters</small>
                            <?php if (isset($validation) && $validation->getError('notes')): ?>
                                <br><span class="text-danger"><?= $validation->getError('notes'); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Save Attendance</button>
                            <a href="<?= base_url('admin/attendance'); ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleLeaveType() {
    const status = document.getElementById('status').value;
    const leaveTypeDiv = document.getElementById('leave_type_div');
    const timeFields = document.getElementById('time_fields');

    if (status === 'Leave' || status === 'Sick-leave') {
        leaveTypeDiv.style.display = 'block';
        timeFields.style.display = 'none';
    } else if (status === 'Present' || status === 'Half-day') {
        leaveTypeDiv.style.display = 'none';
        timeFields.style.display = 'block';
    } else {
        leaveTypeDiv.style.display = 'none';
        timeFields.style.display = 'none';
    }
}

function filterStaffByDate() {
    const selectedDate = document.getElementById('attendance_date').value;
    const staffSelect = document.getElementById('staff_id');
    if (!selectedDate || !staffSelect) return;

    const options = staffSelect.options;
    const currentSelected = staffSelect.value;

    for (let i = 0; i < options.length; i++) {
        const option = options[i];
        if (option.value === "") continue;

        const doj = option.getAttribute('data-doj');
        const resignDate = option.getAttribute('data-resign-date');
        
        let show = true;
        if (doj && doj !== '0000-00-00' && doj !== '0000-00-00 00:00:00' && selectedDate < doj) {
            show = false;
        }
        if (resignDate && resignDate !== '0000-00-00' && resignDate !== '0000-00-00 00:00:00' && selectedDate > resignDate) {
            show = false;
        }

        if (show) {
            option.disabled = false;
            option.style.display = "";
        } else {
            option.disabled = true;
            option.style.display = "none";
            if (currentSelected === option.value) {
                staffSelect.value = "";
            }
        }
    }
}

// Run on load
document.addEventListener('DOMContentLoaded', function() {
    toggleLeaveType();
    filterStaffByDate();
    
    document.getElementById('attendance_date').addEventListener('change', filterStaffByDate);
});
</script>

<?php include("footer.php"); ?>
