<?php include("header.php"); ?>

<style>
    .analytics-card {
        border-radius: 15px;
        border: none !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s ease;
    }
    .analytics-card:hover {
        transform: translateY(-5px);
    }
    .stat-number {
        font-size: 2.2rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 5px;
    }
    .stat-label {
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #777;
    }
    .trend-table th {
        background-color: #f8f9fa;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
    }
    .performance-badge {
        font-size: 0.9rem;
        padding: 5px 12px;
        border-radius: 20px;
    }
    .btn-action {
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
</style>

<div class="page-body-wrapper">
    <?php include("mainsidebar.php"); ?>

    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h3>Attendance Analytics</h3>
                        <p class="text-muted mt-1">Personnel performance and trends</p>
                    </div>
                    <div class="col-sm-6 text-right d-flex justify-content-end align-items-center" style="gap: 10px;">
                        <a href="<?= base_url('admin/attendance/analytics'); ?>" class="btn btn-outline-secondary btn-action shadow-sm">
                            <i class="fa fa-refresh mr-1"></i> Reset
                        </a>
                        <a href="<?= base_url('admin/attendance/reports'); ?>" class="btn btn-primary btn-action shadow-sm">
                            <i class="fa fa-arrow-left mr-1"></i> Back to Reports
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card analytics-card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small font-weight-bold text-uppercase">Location</label>
                            <select class="form-control select2-search" name="location_id" id="analytics_location_id" onchange="filterStaffByLocation()">
                                <option value="">-- All Locations --</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= $loc->location_id; ?>" <?= (isset($location_id) && $location_id == $loc->location_id) ? 'selected' : ''; ?>>
                                        <?= $loc->location_name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small font-weight-bold text-uppercase">Staff Member</label>
                            <select class="form-control select2-search" name="staff_id" id="analytics_staff_id" required>
                                <option value="">-- Select Staff --</option>
                                <?php foreach ($staff as $s): ?>
                                    <option value="<?= $s->id; ?>" data-location="<?= $s->location_id; ?>" <?= $staff_id == $s->id ? 'selected' : ''; ?>>
                                        <?= $s->name; ?> (<?= $s->staff_code; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small font-weight-bold text-uppercase">Year</label>
                            <select class="form-control select2-search" name="year">
                                <?php 
                                $currentYear = date('Y');
                                for($y = $currentYear; $y >= 2020; $y--): ?>
                                    <option value="<?= $y; ?>" <?= $year == $y ? 'selected' : ''; ?>><?= $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block btn-action shadow-sm">
                                <i class="fa fa-search mr-2"></i>Analyze
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($staff_id && $stats): ?>
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="card analytics-card">
                        <div class="card-body text-center p-4">
                            <h2 class="stat-number text-success"><?= $stats->present ?? 0; ?></h2>
                            <p class="stat-label mb-0">Days Present</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card analytics-card">
                        <div class="card-body text-center p-4">
                            <h2 class="stat-number text-danger"><?= $stats->absent ?? 0; ?></h2>
                            <p class="stat-label mb-0">Days Absent</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card analytics-card">
                        <div class="card-body text-center p-4">
                            <h2 class="stat-number text-warning"><?= $stats->leave ?? 0; ?></h2>
                            <p class="stat-label mb-0">Leave Days</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card analytics-card">
                        <div class="card-body text-center p-4">
                            <h2 class="stat-number text-primary"><?= $stats->percentage ?? 0; ?>%</h2>
                            <p class="stat-label mb-0">Attendance Rank</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Monthly Trend -->
                <div class="col-xl-8">
                    <div class="card analytics-card">
                        <div class="card-header bg-white pb-0">
                            <h5><i class="fa fa-line-chart mr-2 text-primary"></i>Monthly Performance - <?= $year; ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover trend-table">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Total Days</th>
                                            <th>Present</th>
                                            <th>Absent</th>
                                            <th>Efficiency</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($trends)): ?>
                                            <tr><td colspan="5" class="text-center text-muted p-4">No data available for <?= $year; ?></td></tr>
                                        <?php else: ?>
                                            <?php
                                            $monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                            foreach ($trends as $trend):
                                            ?>
                                                <tr>
                                                    <td class="font-weight-bold"><?= $monthNames[$trend->month] ?? ''; ?></td>
                                                    <td><?= $trend->total_days ?? 0; ?></td>
                                                    <td><span class="text-success font-weight-bold"><?= $trend->present_days ?? 0; ?></span></td>
                                                    <td><span class="text-danger font-weight-bold"><?= $trend->absent_days ?? 0; ?></span></td>
                                                    <td>
                                                        <div class="d-flex align-items-center" style="gap: 10px;">
                                                            <div class="progress flex-grow-1" style="height: 8px; border-radius: 10px;">
                                                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $trend->percentage ?? 0; ?>%;"></div>
                                                            </div>
                                                            <small class="font-weight-bold"><?= round($trend->percentage, 1) ?? 0; ?>%</small>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <!-- Performance Index -->
                    <div class="card analytics-card">
                        <div class="card-header bg-white pb-0">
                            <h5><i class="fa fa-star mr-2 text-warning"></i>Performance Index</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $percentage = $stats->percentage ?? 0;
                            $p_status = 'Poor'; $p_color = 'danger'; $p_msg = 'Urgent improvement needed';
                            if ($percentage >= 95) { $p_status = 'Excellent'; $p_color = 'success'; $p_msg = 'Outstanding discipline'; }
                            elseif ($percentage >= 85) { $p_status = 'Good'; $p_color = 'info'; $p_msg = 'Consistent attendance'; }
                            elseif ($percentage >= 75) { $p_status = 'Average'; $p_color = 'warning'; $p_msg = 'Maintain higher consistency'; }
                            ?>
                            <div class="text-center py-3">
                                <div class="stat-number text-<?= $p_color; ?>" style="font-size: 3.5rem;"><?= $percentage; ?>%</div>
                                <span class="badge performance-badge bg-<?= $p_color; ?> text-white mb-2"><?= $p_status; ?></span>
                                <p class="text-muted small"><?= $p_msg; ?></p>
                            </div>
                            <hr>
                            <h6 class="font-weight-bold small text-uppercase mb-3">Status Breakdown</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small text-muted"><i class="fa fa-circle text-success mr-2"></i>Present</span>
                                <span class="small font-weight-bold"><?= $stats->present ?? 0; ?> Days</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small text-muted"><i class="fa fa-circle text-danger mr-2"></i>Absent</span>
                                <span class="small font-weight-bold"><?= $stats->absent ?? 0; ?> Days</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small text-muted"><i class="fa fa-circle text-warning mr-2"></i>Leave</span>
                                <span class="small font-weight-bold"><?= $stats->leave ?? 0; ?> Days</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php else: ?>
                <div class="alert alert-info analytics-card mt-4 border-0 shadow-sm" style="background-color: #e3f2fd; color: #0d47a1;">
                    <i class="fa fa-bar-chart mr-2"></i>
                    <strong>Ready to analyze:</strong> Select a location, staff member, and year to generate detailed analytics.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function filterStaffByLocation() {
        var locationId = $('#analytics_location_id').val();
        var staffSelect = $('#analytics_staff_id');
        
        if (!window.originalAnalyticsOptions) {
            window.originalAnalyticsOptions = [];
            staffSelect.find('option').each(function() {
                window.originalAnalyticsOptions.push({
                    id: $(this).val(),
                    text: $(this).text(),
                    location: $(this).data('location'),
                    selected: $(this).is(':selected')
                });
            });
        }

        var currentVal = staffSelect.val();
        staffSelect.empty();
        
        window.originalAnalyticsOptions.forEach(function(opt) {
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
        filterStaffByLocation();
    });
</script>

<?php include("footer.php"); ?>
