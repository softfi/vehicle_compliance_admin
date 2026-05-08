<?php include("header.php"); ?>
<style>
    .kpi-card {
        border-radius: 15px;
        transition: all 0.3s ease;
        border: none !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .kpi-icon {
        width: 55px;
        height: 55px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    .kpi-content {
        flex-grow: 1;
    }
    .kpi-label {
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    .kpi-value {
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1;
    }
</style>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>

    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h3>Attendance Dashboard</h3>
                        <p class="text-muted mt-1">Unified analytics and reporting system</p>
                    </div>
                </div>
            </div>

            <!-- Dashboard Filters -->
            <div class="card shadow-none border" style="border-radius: 15px;">
                <div class="card-body p-3">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small font-weight-bold text-uppercase">From Date</label>
                            <input type="date" class="form-control" name="from_date" value="<?= $from_date; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small font-weight-bold text-uppercase">To Date</label>
                            <input type="date" class="form-control" name="to_date" value="<?= $to_date; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small font-weight-bold text-uppercase">Location</label>
                            <select class="form-control select2-search" name="location_id" id="report_location_id" onchange="filterStaffByLocation()">
                                <option value="">-- All Locations --</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= $loc->location_id; ?>" <?= isset($filters['location_id']) && $filters['location_id'] == $loc->location_id ? 'selected' : ''; ?>>
                                        <?= $loc->location_name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small font-weight-bold text-uppercase">Staff Member</label>
                            <select class="form-control select2-search" id="report_staff_id" name="staff_id">
                                <option value="">-- All Staff --</option>
                                <?php foreach ($staff as $s): ?>
                                    <option value="<?= $s->id; ?>" data-location="<?= $s->location_id; ?>" <?= isset($filters['staff_id']) && $filters['staff_id'] == $s->id ? 'selected' : ''; ?>>
                                        <?= $s->name; ?> (<?= $s->staff_code; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 text-right mt-3">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fa fa-filter mr-2"></i>Apply Filters</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="row">
                <div class="col-sm-6 col-xl-3 col-lg-6">
                    <div class="card kpi-card mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="kpi-content">
                                    <p class="kpi-label text-primary">Total Records</p>
                                    <h4 class="kpi-value text-dark"><?= $stats->total_records ?? 0; ?></h4>
                                </div>
                                <div class="kpi-icon bg-light-primary text-primary"><i class="fa fa-database"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3 col-lg-6">
                    <div class="card kpi-card mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="kpi-content">
                                    <p class="kpi-label text-success">Present Days</p>
                                    <h4 class="kpi-value text-dark"><?= $stats->present_count ?? 0; ?></h4>
                                </div>
                                <div class="kpi-icon bg-light-success text-success"><i class="fa fa-check-circle"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3 col-lg-6">
                    <div class="card kpi-card mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="kpi-content">
                                    <p class="kpi-label text-danger">Absent Days</p>
                                    <h4 class="kpi-value text-dark"><?= $stats->absent_count ?? 0; ?></h4>
                                </div>
                                <div class="kpi-icon bg-light-danger text-danger"><i class="fa fa-times-circle"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3 col-lg-6">
                    <div class="card kpi-card mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="kpi-content">
                                    <p class="kpi-label text-warning">Leave / Other</p>
                                    <h4 class="kpi-value text-dark"><?= ($stats->leave_count ?? 0) + ($stats->half_day_count ?? 0) + ($stats->sick_leave_count ?? 0); ?></h4>
                                </div>
                                <div class="kpi-icon bg-light-warning text-warning"><i class="fa fa-calendar-check-o"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Status Distribution Chart -->
                <div class="col-xl-4 col-md-6">
                    <div class="card shadow-none border">
                        <div class="card-header pb-0 bg-white">
                            <h5>Status Distribution</h5>
                        </div>
                        <div class="card-body">
                            <div id="status-pie-chart"></div>
                        </div>
                    </div>
                </div>

                <!-- Export & Summary -->
                <div class="col-xl-8 col-md-6">
                    <div class="card shadow-none border">
                        <div class="card-header pb-0 bg-white d-flex justify-content-between align-items-center">
                            <h5>Detailed Records</h5>
                            <div>
                                <a href="<?= base_url('admin/attendance/export-excel?' . http_build_query(service('request')->getGet())); ?>" class="btn btn-outline-success btn-sm">
                                    <i class="fa fa-file-excel-o mr-1"></i>Export to Excel
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="reports_table">
                                    <thead>
                                        <tr class="bg-light">
                                            <th>Staff</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Location</th>
                                            <th>Check-in/out</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($attendance)): ?>
                                            <?php foreach ($attendance as $record): ?>
                                                <tr>
                                                    <td>
                                                        <div class="font-weight-bold text-dark"><?= $record->staff_name; ?></div>
                                                        <small class="text-muted"><?= $record->staff_code; ?></small>
                                                    </td>
                                                    <td><?= date('d M, Y', strtotime($record->attendance_date)); ?></td>
                                                    <td>
                                                        <?php
                                                        $badgeClass = 'secondary';
                                                        if ($record->status == 'Present') $badgeClass = 'success';
                                                        elseif ($record->status == 'Absent') $badgeClass = 'danger';
                                                        elseif (strpos($record->status, 'Leave') !== false || $record->status == 'Leave') $badgeClass = 'warning';
                                                        elseif ($record->status == 'Half-day') $badgeClass = 'info';
                                                        ?>
                                                        <span class="badge badge-<?= $badgeClass; ?>"><?= $record->status; ?></span>
                                                    </td>
                                                    <td><?= $record->location_name; ?></td>
                                                    <td class="small">
                                                        <i class="fa fa-sign-in text-success mr-1"></i> <?= $record->check_in_time ?: '--:--'; ?> <br>
                                                        <i class="fa fa-sign-out text-danger mr-1"></i> <?= $record->check_out_time ?: '--:--'; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" class="text-center">No records found for selected filters.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function filterStaffByLocation() {
        var locationId = $('#report_location_id').val();
        var staffSelect = $('#report_staff_id');
        
        // Store original options on the first run
        if (!window.originalReportOptions) {
            window.originalReportOptions = [];
            staffSelect.find('option').each(function() {
                window.originalReportOptions.push({
                    id: $(this).val(),
                    text: $(this).text(),
                    location: $(this).data('location'),
                    selected: $(this).is(':selected')
                });
            });
        }

        var currentVal = staffSelect.val();
        staffSelect.empty();
        
        window.originalReportOptions.forEach(function(opt) {
            if (locationId === "" || opt.location == locationId || opt.id === "") {
                var newOption = new Option(opt.text, opt.id, false, opt.id === currentVal);
                $(newOption).attr('data-location', opt.location);
                staffSelect.append(newOption);
            }
        });

        if (staffSelect.hasClass('select2-hidden-accessible')) {
            staffSelect.select2('destroy');
        }
        
        staffSelect.select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Run filter on load to handle pre-selected filters
        filterStaffByLocation();

        // Pie Chart Configuration
        var options = {
            series: [
                <?= $stats->present_count ?? 0; ?>,
                <?= $stats->absent_count ?? 0; ?>,
                <?= $stats->leave_count ?? 0; ?>,
                <?= $stats->half_day_count ?? 0; ?>,
                <?= $stats->sick_leave_count ?? 0; ?>
            ],
            chart: {
                width: '100%',
                type: 'pie',
                toolbar: { show: false }
            },
            labels: ['Present', 'Absent', 'Leave', 'Half-day', 'Sick-leave'],
            colors: ['#51bb25', '#dc3545', '#ffc107', '#17a2b8', '#000000'],
            legend: {
                position: 'bottom'
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: { width: 200 },
                    legend: { position: 'bottom' }
                }
            }]
        };

        var chart = new ApexCharts(document.querySelector("#status-pie-chart"), options);
        chart.render();

        // Initialize DataTable for reports
        $('#reports_table').DataTable({
            "pageLength": 10,
            "searching": false,
            "lengthChange": false,
            "ordering": true,
            "info": true
        });
    });
</script>

<?php include("footer.php"); ?>

