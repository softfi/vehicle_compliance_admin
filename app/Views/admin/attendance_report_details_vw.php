<?php include("header.php"); ?>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>

    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-6 p-0">
                        <h3>Attendance Report</h3>
                        <p><?= date('F d, Y', strtotime($from_date)); ?> to <?= date('F d, Y', strtotime($to_date)); ?></p>
                    </div>
                    <div class="col-sm-6 p-0 text-right">
                        <button onclick="window.print();" class="btn btn-secondary mr-2 mt-3">Print</button>
                        <a href="<?= base_url('admin/attendance/export-excel?from_date=' . $from_date . '&to_date=' . $to_date); ?>" class="btn btn-success mr-2 mt-3">Export to Excel</a>
                        <a href="<?= base_url('admin/attendance/reports'); ?>" class="btn btn-secondary mt-3">Back</a>
                    </div>
                </div>
            </div>

            <!-- Statistics Summary -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center">
                                <h3 class="text-success"><?= $stats->present_count ?? 0; ?></h3>
                                <p class="text-muted">Present</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center">
                                <h3 class="text-danger"><?= $stats->absent_count ?? 0; ?></h3>
                                <p class="text-muted">Absent</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center">
                                <h3 class="text-warning"><?= $stats->leave_count ?? 0; ?></h3>
                                <p class="text-muted">Leave</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center">
                                <h3 class="text-info"><?= $stats->half_day_count ?? 0; ?></h3>
                                <p class="text-muted">Half-day</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Report Table -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Detailed Attendance</h5>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($attendance)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No attendance records found for the selected period.</td>
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
                                        <td><?= substr($record->notes, 0, 30) ?? '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary by Status -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Summary by Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td>Total Records</td>
                                        <td><strong><?= $stats->total_records ?? 0; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Present</td>
                                        <td><strong class="text-success"><?= $stats->present_count ?? 0; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Absent</td>
                                        <td><strong class="text-danger"><?= $stats->absent_count ?? 0; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Leave</td>
                                        <td><strong class="text-warning"><?= $stats->leave_count ?? 0; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Half-day</td>
                                        <td><strong class="text-info"><?= $stats->half_day_count ?? 0; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Sick Leave</td>
                                        <td><strong><?= $stats->sick_leave_count ?? 0; ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div id="statusChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Print Footer -->
            <div class="mt-4 text-center text-muted" style="border-top: 1px solid #ddd; padding-top: 20px;">
                <p>Generated on <?= date('F d, Y H:i:s'); ?></p>
                <p>Transport Management System</p>
            </div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>
