<?php include("header.php"); ?>



<style>

    .calendar-table th {

        background-color: #f8f9fa;

        text-transform: uppercase;

        font-size: 0.8rem;

        letter-spacing: 0.5px;

        color: #777;

    }

    .calendar-day {

        height: 100px;

        vertical-align: top;

        transition: all 0.2s ease;

        position: relative;

    }

    .calendar-day:hover {

        background-color: #f1f1f1 !important;

    }

    .day-number {

        font-size: 1.1rem;

        font-weight: 700;

        margin-bottom: 5px;

        display: block;

    }

    .status-text {

        font-size: 0.75rem;

        font-weight: 600;

        padding: 3px 6px;

        border-radius: 4px;

        display: inline-block;

    }

    .legend-item {

        padding: 10px;

        border-radius: 8px;

        text-align: center;

        font-weight: 600;

        font-size: 0.85rem;

        margin-bottom: 10px;

    }

    .premium-card {

        border-radius: 15px;

        border: none;

        box-shadow: 0 4px 15px rgba(0,0,0,0.05);

    }

    .btn-action {

        border-radius: 8px;

        padding: 8px 16px;

        font-weight: 600;

        transition: all 0.3s ease;

    }

    .btn-action:hover {

        transform: translateY(-2px);

    }

</style>



<div class="page-body-wrapper">

    <?php include("mainsidebar.php"); ?>



    <div class="page-body">

        <div class="container-fluid">

            <div class="page-title">

                <div class="row align-items-center">

                    <div class="col-sm-6">

                        <h3>Attendance Calendar</h3>

                        <p class="text-muted mt-1">Detailed month-view for staff members</p>

                    </div>

                    <div class="col-sm-6 text-right d-flex justify-content-end align-items-center" style="gap: 10px;">

                        <a href="<?= base_url('admin/attendance/calendar'); ?>" class="btn btn-outline-secondary btn-action shadow-sm">

                            <i class="fa fa-refresh mr-1"></i> Reset

                        </a>

                        <a href="<?= base_url('admin/attendance/reports'); ?>" class="btn btn-primary btn-action shadow-sm">

                            <i class="fa fa-arrow-left mr-1"></i> Back to Reports

                        </a>

                    </div>

                </div>

            </div>



            <!-- Filters -->

            <div class="card premium-card mb-4">

                <div class="card-body">

                    <form method="GET" class="row">

                        <div class="col-md-4">

                            <label class="form-label small font-weight-bold text-uppercase">Location</label>

                            <select class="form-control select2-search" name="location_id" id="calendar_location_id" onchange="filterStaffByLocation()">

                                <option value="">-- All Locations --</option>

                                <?php foreach ($locations as $loc): ?>

                                    <option value="<?= $loc->location_id; ?>" <?= (isset($location_id) && $location_id == $loc->location_id) ? 'selected' : ''; ?>>

                                        <?= $loc->location_name; ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="col-md-4">

                            <label class="form-label small font-weight-bold text-uppercase">Select Staff Member</label>

                            <select class="form-control select2-search" name="staff_id" id="calendar_staff_id" required onchange="this.form.submit()">

                                <option value="">-- Select Staff --</option>

                                <?php foreach ($staff as $s): ?>

                                    <option value="<?= $s->id; ?>" data-location="<?= $s->location_id; ?>" <?= $staff_id == $s->id ? 'selected' : ''; ?>>

                                        <?= $s->name; ?> (<?= $s->staff_code; ?>)

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="col-md-4">

                            <label class="form-label small font-weight-bold text-uppercase">Select Month</label>

                            <input type="month" class="form-control" name="month" value="<?= $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT); ?>" onchange="this.form.submit()">

                        </div>

                    </form>

                </div>

            </div>



            <?php if ($staff_id): ?>

            <!-- Calendar Card -->

            <div class="card premium-card">

                <div class="card-header bg-white border-bottom-0 pb-0">

                    <div class="row align-items-center">

                        <div class="col">

                            <h5 class="font-weight-bold text-dark mb-0">

                                <i class="fa fa-calendar-o text-primary mr-2"></i>

                                <?= date('F Y', strtotime($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01')); ?>

                            </h5>

                        </div>

                        <div class="col text-right d-flex justify-content-end" style="gap: 10px;">

                            <a href="<?= base_url('admin/attendance/calendar?staff_id=' . $staff_id . '&month=' . ($month == 1 ? 12 : $month - 1) . '&year=' . ($month == 1 ? $year - 1 : $year)); ?>" class="btn btn-sm btn-outline-primary btn-action">

                                <i class="fa fa-chevron-left mr-1"></i> Previous

                            </a>

                            <a href="<?= base_url('admin/attendance/calendar?staff_id=' . $staff_id . '&month=' . ($month == 12 ? 1 : $month + 1) . '&year=' . ($month == 12 ? $year + 1 : $year)); ?>" class="btn btn-sm btn-outline-primary btn-action">

                                Next <i class="fa fa-chevron-right ml-1"></i>

                            </a>

                        </div>

                    </div>

                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered calendar-table text-center">

                            <thead>

                                <tr>

                                    <th width="14.28%">Sun</th>

                                    <th width="14.28%">Mon</th>

                                    <th width="14.28%">Tue</th>

                                    <th width="14.28%">Wed</th>

                                    <th width="14.28%">Thu</th>

                                    <th width="14.28%">Fri</th>

                                    <th width="14.28%">Sat</th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php

                                $firstDay = date('w', strtotime($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01'));

                                $daysInMonth = date('t', strtotime($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01'));

                                

                                $attendanceMap = [];

                                foreach ($attendance as $record) {

                                    $day = date('d', strtotime($record->attendance_date));

                                    $attendanceMap[$day] = $record->status;

                                }



                                $day = 1;

                                for ($i = 0; $i < 6; $i++) {

                                    echo '<tr>';

                                    for ($j = 0; $j < 7; $j++) {

                                        if ($i == 0 && $j < $firstDay) {

                                            echo '<td class="bg-light"></td>';

                                        } elseif ($day <= $daysInMonth) {

                                            $dateStr = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);

                                            $status = $attendanceMap[str_pad($day, 2, '0', STR_PAD_LEFT)] ?? null;

                                            

                                            $bgColor = '#fff';

                                            $statusText = '';

                                            $textClass = 'text-muted';

                                            $bgBadge = 'bg-light';

                                            

                                            if ($status) {

                                                switch ($status) {

                                                    case 'Present':

                                                        $bgColor = '#f0fff4';

                                                        $statusText = 'Present';

                                                        $textClass = 'text-success';

                                                        $bgBadge = 'bg-success text-white';

                                                        break;

                                                    case 'Absent':

                                                        $bgColor = '#fff5f5';

                                                        $statusText = 'Absent';

                                                        $textClass = 'text-danger';

                                                        $bgBadge = 'bg-danger text-white';

                                                        break;

                                                    case 'Leave':

                                                        $bgColor = '#fffaf0';

                                                        $statusText = 'Leave';

                                                        $textClass = 'text-warning';

                                                        $bgBadge = 'bg-warning text-dark';

                                                        break;

                                                    case 'Half-day':

                                                        $bgColor = '#ebf8ff';

                                                        $statusText = 'Half-day';

                                                        $textClass = 'text-info';

                                                        $bgBadge = 'bg-info text-white';

                                                        break;

                                                    case 'Sick-leave':

                                                        $bgColor = '#edf2f7';

                                                        $statusText = 'Sick-leave';

                                                        $textClass = 'text-muted';

                                                        $bgBadge = 'bg-dark text-white';

                                                        break;

                                                    case 'Holiday':

                                                        $bgColor = '#fffdf0';

                                                        $statusText = 'Holiday';

                                                        $textClass = 'text-warning';

                                                        $bgBadge = 'bg-warning text-dark';

                                                        break;

                                                }

                                            }



                                            $isToday = $dateStr == $currentDate ? 'border: 2px solid #007bff !important;' : '';

                                            echo '<td class="calendar-day" style="background-color: ' . $bgColor . '; ' . $isToday . '">';

                                            echo '<span class="day-number">' . $day . '</span>';

                                            if ($statusText) {

                                                echo '<span class="status-text ' . $bgBadge . '">' . $statusText . '</span>';

                                            } else {

                                                echo '<span class="status-text text-muted">—</span>';

                                            }

                                            echo '</td>';

                                            $day++;

                                        } else {

                                            echo '<td class="bg-light"></td>';

                                        }

                                    }

                                    echo '</tr>';

                                    if ($day > $daysInMonth) break;

                                }

                                ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>



            <!-- Legend Card -->

            <div class="card premium-card mt-4">

                <div class="card-body">

                    <h6 class="font-weight-bold mb-3"><i class="fa fa-info-circle mr-2 text-primary"></i>Legend</h6>

                    <div class="row">

                        <div class="col-md-2 col-6">

                            <div class="legend-item" style="background-color: #f0fff4; color: #38a169;">Present</div>

                        </div>

                        <div class="col-md-2 col-6">

                            <div class="legend-item" style="background-color: #fff5f5; color: #e53e3e;">Absent</div>

                        </div>

                        <div class="col-md-2 col-6">

                            <div class="legend-item" style="background-color: #fffaf0; color: #d69e2e;">Leave</div>

                        </div>

                        <div class="col-md-2 col-6">

                            <div class="legend-item" style="background-color: #ebf8ff; color: #3182ce;">Half-day</div>

                        </div>

                        <div class="col-md-2 col-6">

                            <div class="legend-item" style="background-color: #edf2f7; color: #4a5568;">Sick-leave</div>

                        </div>

                    </div>

                </div>

            </div>



            <?php else: ?>

                <div class="alert alert-info premium-card mt-4 shadow-sm border-0" style="background-color: #e3f2fd; color: #0d47a1;">

                    <i class="fa fa-lightbulb-o mr-2"></i>

                    <strong>Action Required:</strong> Please select a location and staff member above to visualize the attendance calendar.

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>



<script>

    function filterStaffByLocation() {

        var locationId = $('#calendar_location_id').val();

        var staffSelect = $('#calendar_staff_id');

        

        if (!window.originalCalendarOptions) {

            window.originalCalendarOptions = [];

            staffSelect.find('option').each(function() {

                window.originalCalendarOptions.push({

                    id: $(this).val(),

                    text: $(this).text(),

                    location: $(this).data('location'),

                    selected: $(this).is(':selected')

                });

            });

        }



        var currentVal = staffSelect.val();

        staffSelect.empty();

        

        window.originalCalendarOptions.forEach(function(opt) {

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

