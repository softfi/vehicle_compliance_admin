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
                        <button type="button" class="btn btn-outline-success btn-action shadow-sm" style="border-radius: 8px; padding: 8px 16px; font-weight: 600;" onclick="openBulkMarkModal()">
                            <i class="fa fa-users mr-1"></i> Mark Bulk Attendance
                        </button>
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
                            <label>Month</label>
                            <select class="form-control" name="month">
                                <?php for ($m = 1; $m <= 12; $m++): $mStr = str_pad($m, 2, '0', STR_PAD_LEFT); ?>
                                    <option value="<?= $mStr; ?>" <?= $month == $mStr ? 'selected' : ''; ?>>
                                        <?= date('F', mktime(0, 0, 0, $m, 1)); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Year</label>
                            <select class="form-control" name="year">
                                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                    <option value="<?= $y; ?>" <?= $year == $y ? 'selected' : ''; ?>><?= $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Location</label>
                            <select class="form-control select2-search" name="location_id" id="main_filter_location" onchange="filterMainStaffByLocation()">
                                <option value="">-- All --</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= $loc->location_id; ?>" <?= isset($filters['location_id']) && $filters['location_id'] == $loc->location_id ? 'selected' : ''; ?>>
                                        <?= $loc->location_name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Staff Member</label>
                            <select class="form-control select2-search" name="staff_id" id="main_staff_id">
                                <option value="">-- Select Staff --</option>
                                <?php foreach ($staff as $s): ?>
                                    <option value="<?= $s->id; ?>" data-location="<?= $s->location_id; ?>" <?= isset($filters['staff_id']) && $filters['staff_id'] == $s->id ? 'selected' : ''; ?>>
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
                                <option value="Holiday" <?= isset($filters['status']) && $filters['status'] == 'Holiday' ? 'selected' : ''; ?>>Holiday</option>
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

            <!-- Attendance Matrix Grid -->
            <div class="card mt-3 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                        <h5 class="font-weight-bold mb-0" style="font-size: 1.1rem;">Attendance Overview <span class="badge badge-light-primary ml-2"><?= $pagination['total']; ?> Staff</span></h5>
                        <div class="small">
                            <span class="mr-3">Note: <i class="fa fa-star text-warning mr-1"></i> Holiday | <i class="fa fa-check text-success mr-1"></i> Present | <i class="fa fa-times text-danger mr-1"></i> Absent</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive attendance-matrix-container">
                        <style>
                            .attendance-matrix-container {
                                max-height: 700px;
                                overflow: auto;
                            }
                            .attendance-table {
                                border-collapse: separate;
                                border-spacing: 0;
                                width: 100%;
                            }
                            .attendance-table th, .attendance-table td {
                                border: 1px solid #f0f0f0;
                                padding: 6px 4px;
                                text-align: center;
                                white-space: nowrap;
                                font-size: 0.75rem;
                            }
                            .sticky-col {
                                position: sticky;
                                left: 0;
                                background-color: #fff;
                                /* z-index: 15; */
                                text-align: left !important;
                                min-width: 180px;
                                box-shadow: 2px 0 5px rgba(0,0,0,0.05);
                            }
                            .attendance-table thead th {
                                position: sticky !important;
                                /* z-index: 9999 !important; */
                                background-color: #f8f9fa !important;
                                border: 1px solid #ddd !important;
                            }
                            .attendance-table thead tr:nth-child(1) th {
                                top: 0 !important;
                            }
                             .attendance-table thead .sticky-header-staff {
                                top: 0 !important;
                                z-index: 20;
                            }
                             .attendance-table thead .sticky-header-total {
                                top: 0 !important;
                                z-index: 20;
                            }
                            .attendance-table thead tr:nth-child(2) th {
                                top: 31px !important;
                            }
                            .sticky-header-staff {
                                left: 0 !important;
                                min-width: 200px !important;
                                box-shadow: 2px 0 5px rgba(0,0,0,0.05);
                            }
                            .sticky-header-total {
                                right: 0 !important;
                                min-width: 80px !important;
                                box-shadow: -2px 0 5px rgba(0,0,0,0.05);
                            }
                            .day-header {
                                min-width: 35px;
                                font-size: 0.7rem;
                                color: #666;
                                font-weight: 600;
                            }
                            .date-header {
                                font-size: 0.7rem;
                                font-weight: 600;
                                color: #888;
                            }
                            .staff-info {
                                display: flex;
                                align-items: center;
                                gap: 8px;
                            }
                            .staff-avatar {
                                width: 30px;
                                height: 30px;
                                border-radius: 8px;
                                background: #eef2f7;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-weight: bold;
                                color: #4b49ac;
                                font-size: 0.75rem;
                                overflow: hidden;
                            }
                            .staff-name {
                                font-weight: 600;
                                color: #333;
                                font-size: 0.8rem;
                                line-height: 1.1;
                                display: block;
                            }
                            .staff-role {
                                font-size: 0.65rem;
                                color: #999;
                                display: block;
                            }
                            .status-icon {
                                font-size: 0.9rem;
                                cursor: pointer;
                                transition: transform 0.2s;
                            }
                            .status-icon:hover {
                                transform: scale(1.2);
                            }
                            .status-present { color: #4b49ac; }
                            .status-absent { color: #ced4da; }
                            .status-holiday { color: #ffc107; }
                            .status-leave { color: #17a2b8; }
                            .status-none { color: #f0f0f0; }
                            
                            .total-col {
                                font-weight: 700;
                                color: #333;
                                min-width: 60px;
                            }
                            
                            .me-badge {
                                background: #4b49ac;
                                color: white;
                                font-size: 0.55rem;
                                padding: 1px 4px;
                                border-radius: 3px;
                                margin-left: 3px;
                            }
                            tr:hover td {
                                background-color: #f8f9fa;
                            }
                            tr:hover td.sticky-col {
                                background-color: #f8f9fa;
                            }
                            .weekend {
                                background-color: #fbfbfb !important;
                            }
                            .text-total {
                                font-weight: 800;
                                color: #333;
                            }
                        </style>
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th class="sticky-header-staff">Employee</th>
                                    <?php foreach ($dates as $date): ?>
                                        <th class="day-header <?= in_array($date['short_day'], ['Sat', 'Sun']) ? 'weekend' : ''; ?>"><?= $date['day']; ?></th>
                                    <?php endforeach; ?>
                                    <th class="sticky-header-total">Total</th>
                                </tr>
                                <tr>
                                    <th class="sticky-header-staff"></th>
                                    <?php foreach ($dates as $date): ?>
                                        <th class="date-header <?= in_array($date['short_day'], ['Sat', 'Sun']) ? 'weekend' : ''; ?>"><?= $date['short_day']; ?></th>
                                    <?php endforeach; ?>
                                    <th class="sticky-header-total"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($staffList)): ?>
                                    <tr>
                                        <td colspan="<?= count($dates) + 1; ?>" class="text-center py-5 text-muted">No staff found for the selected filters.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($staffList as $staffItem): ?>
                                        <tr>
                                            <td class="sticky-col">
                                                <div class="staff-info">
                                                    <div class="staff-avatar">
                                                        <?php if (!empty($staffItem->profile_image)): ?>
                                                            <img src="<?= base_url('uploads/staff/' . $staffItem->profile_image); ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                                                        <?php else: ?>
                                                            <?= strtoupper(substr($staffItem->name, 0, 1)); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <span class="staff-name"><?= $staffItem->name; ?>
                                                            <?php 
                                                                $isMe = false;
                                                                if (isset($singleuser) && is_array($singleuser) && !empty($singleuser)) {
                                                                    $me = $singleuser[0];
                                                                    if (($me->full_name ?? '') == $staffItem->name) {
                                                                        $isMe = true;
                                                                    }
                                                                }
                                                                if ($isMe): 
                                                            ?>
                                                                <span class="me-badge">it's you</span>
                                                            <?php endif; ?>
                                                        </span>
                                                        <span class="staff-role"><?= $staffItem->location_name ?? 'Staff'; ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <?php 
                                                $presentCount = 0;
                                                $totalWorkingDays = 0;
                                                foreach ($dates as $date) {
                                                    $isResigned = (!empty($staffItem->resign_date) && $staffItem->resign_date != '0000-00-00' && $date['full'] > $staffItem->resign_date);
                                                    $isJoined = (empty($staffItem->doj) || $staffItem->doj == '0000-00-00' || $date['full'] >= $staffItem->doj);
                                                    if ($isJoined && !$isResigned) {
                                                        $totalWorkingDays++;
                                                        $record = $attendanceMatrix[$staffItem->id][$date['full']] ?? null;
                                                        if ($record && ($record->status == 'Present' || $record->status == 'Half-day')) {
                                                            $presentCount += ($record->status == 'Half-day' ? 0.5 : 1);
                                                        }
                                                    }
                                                }
                                            ?>
                                            <?php foreach ($dates as $date): ?>
                                                <?php 
                                                    $record = $attendanceMatrix[$staffItem->id][$date['full']] ?? null;
                                                    $isWeekend = in_array($date['short_day'], ['Sat', 'Sun']);
                                                    
                                                    // Tenure Check
                                                    $isJoined = (empty($staffItem->doj) || $staffItem->doj == '0000-00-00' || $date['full'] >= $staffItem->doj);
                                                    $isResigned = (!empty($staffItem->resign_date) && $staffItem->resign_date != '0000-00-00' && $date['full'] > $staffItem->resign_date);
                                                    $isActive = $isJoined && !$isResigned;
                                                ?>
                                                <td class="<?= $isWeekend ? 'weekend' : ''; ?>">
                                                    <?php if ($isActive): ?>
                                                        <?php if ($record): ?>
                                                            <?php 
                                                                $icon = 'fa-minus status-none';
                                                                if ($record->status == 'Present') $icon = 'fa-check status-present';
                                                                elseif ($record->status == 'Absent') $icon = 'fa-times status-absent';
                                                                elseif ($record->status == 'Leave' || $record->status == 'Sick-leave') $icon = 'fa-plane status-leave';
                                                                elseif ($record->status == 'Half-day') $icon = 'fa-adjust status-half';
                                                                elseif ($record->status == 'Holiday') $icon = 'fa-star status-holiday';
                                                            ?>
                                                            <i class="fa <?= $icon; ?> status-icon" 
                                                               onclick="openEditModal(<?= $record->id; ?>)" 
                                                               title="<?= $record->status; ?><?= $record->notes ? ': ' . $record->notes : ''; ?>"></i>
                                                        <?php else: ?>
                                                            <i class="fa fa-plus status-none status-icon" 
                                                               onclick="quickAdd('<?= $staffItem->id; ?>', '<?= $date['full']; ?>', '<?= $staffItem->location_id; ?>')"
                                                               title="Add Attendance"></i>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted" style="font-size: 0.6rem;">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <td class="text-total" style="position: sticky; right: 0; background: #fff; z-index: 5; border-left: 1px solid #f0f0f0; box-shadow: -2px 0 5px rgba(0,0,0,0.02);"><?= $presentCount; ?> / <?= $totalWorkingDays; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['totalPages'] > 1):
                    $filterQuery = http_build_query([
                        'month'       => $month,
                        'year'        => $year,
                        'staff_id'    => $filters['staff_id'] ?? '',
                        'status'      => $filters['status'] ?? '',
                        'location_id' => $filters['location_id'] ?? '',
                    ]);
                ?>
                    <div class="card-footer bg-white border-top-0 px-4 py-3">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-primary justify-content-end mb-0">
                                <?php if ($pagination['page'] > 1): ?>
                                    <li class="page-item"><a class="page-link" href="<?= base_url('admin/attendance?page=1&' . $filterQuery); ?>"><i class="fa fa-angle-double-left"></i></a></li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $pagination['page'] - 2); $i <= min($pagination['totalPages'], $pagination['page'] + 2); $i++): ?>
                                    <li class="page-item <?= $i == $pagination['page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?= base_url('admin/attendance?page=' . $i . '&' . $filterQuery); ?>"><?= $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                                    <li class="page-item"><a class="page-link" href="<?= base_url('admin/attendance?page=' . $pagination['totalPages'] . '&' . $filterQuery); ?>"><i class="fa fa-angle-double-right"></i></a></li>
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
                                    <option value="Holiday">Holiday</option>
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

<!-- Bulk Mark Attendance Modal -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    #bulkMarkModal .bulk-staff-box {
        border: 1px solid #e6e6e6;
        border-radius: 6px;
        overflow: hidden;
    }
    #bulkMarkModal .bulk-staff-toolbar {
        padding: 8px 12px;
        background: #f8f9fa;
        border-bottom: 1px solid #e6e6e6;
    }
    #bulkMarkModal .bulk-staff-list {
        max-height: 280px;
        overflow-y: auto;
    }
    #bulkMarkModal .bulk-staff-row {
        padding: 8px 12px;
        border-bottom: 1px solid #f1f1f1;
        display: flex;
        align-items: center;
    }
    #bulkMarkModal .bulk-staff-row:last-child {
        border-bottom: none;
    }
    #bulkMarkModal .bulk-staff-row:hover:not(.disabled) {
        background: #fafbff;
    }
    #bulkMarkModal .bulk-staff-row.disabled {
        background: #f8f9fa;
        opacity: 0.7;
    }
    #bulkMarkModal .bulk-staff-name {
        font-weight: 600;
        font-size: 0.85rem;
        color: #333;
    }
    #bulkMarkModal .bulk-staff-meta {
        font-size: 0.7rem;
        color: #888;
    }
    #bulkMarkModal .bulk-count-badge {
        font-size: 0.8rem;
        font-weight: 600;
        color: #4b49ac;
    }
</style>
<div class="modal fade" id="bulkMarkModal" tabindex="-1" role="dialog" aria-labelledby="bulkMarkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkMarkModalLabel">Mark Bulk Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkMarkForm" method="POST" action="<?= base_url('admin/attendance/quick-bulk-store'); ?>">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Attendance Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="attendance_date" id="bulk_attendance_date" required value="<?= date('Y-m-d'); ?>" max="<?= date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select class="form-control" name="status" id="bulk_status" required onchange="toggleBulkFields()">
                                    <option value="Present">Present</option>
                                    <option value="Absent">Absent</option>
                                    <option value="Leave">Leave</option>
                                    <option value="Half-day">Half-day</option>
                                    <option value="Sick-leave">Sick-leave</option>
                                    <option value="Holiday">Holiday</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Location</label>
                                <select class="form-control" id="bulk_location_id">
                                    <option value="">-- All Locations --</option>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?= $loc->location_id; ?>" <?= isset($filters['location_id']) && $filters['location_id'] == $loc->location_id ? 'selected' : ''; ?>>
                                            <?= $loc->location_name; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Search Staff</label>
                                <input type="text" class="form-control" id="bulk_search" placeholder="Type name or code...">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Select Staff <span class="text-danger">*</span></label>
                        <div class="bulk-staff-box">
                            <div class="bulk-staff-toolbar d-flex justify-content-between align-items-center">
                                <div class="form-check m-0">
                                    <input type="checkbox" id="bulk_select_all" class="form-check-input">
                                    <label for="bulk_select_all" class="form-check-label ml-2" style="cursor:pointer; font-weight:500;">Select All</label>
                                </div>
                                <span class="bulk-count-badge">
                                    <span id="bulk_selected_count">0</span> / <span id="bulk_total_count">0</span> selected
                                </span>
                            </div>
                            <div id="bulk_staff_list" class="bulk-staff-list">
                                <div class="text-center text-muted py-4">
                                    <i class="fa fa-spinner fa-spin mr-2"></i> Loading staff...
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="bulk_leave_type_row" style="display:none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Leave Type</label>
                                <select class="form-control" name="leave_type" id="bulk_leave_type">
                                    <option value="">-- Select Leave --</option>
                                    <option value="Annual">Annual Leave</option>
                                    <option value="Casual">Casual Leave</option>
                                    <option value="Sick">Sick Leave</option>
                                    <option value="Unpaid">Unpaid Leave</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="bulk_time_fields">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Check-in Time</label>
                                <input type="time" class="form-control" name="check_in_time" id="bulk_check_in_time">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Check-out Time</label>
                                <input type="time" class="form-control" name="check_out_time" id="bulk_check_out_time">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" id="bulk_notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="bulk_submit_btn" disabled>
                        Save Attendance (<span id="bulk_submit_count">0</span>)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal(staffId = "", date = "", locationId = "") {
    $('#attendanceModalLabel').text('Add Attendance');
    $('#attendanceForm').attr('action', '<?= base_url('admin/attendance/store'); ?>');
    $('#attendanceForm')[0].reset();
    
    // Set Date
    if (date) {
        $('#modal_attendance_date').val(date);
    } else {
        $('#modal_attendance_date').val('<?= date('Y-m-d'); ?>');
    }

    // Set Location and trigger filter
    if (locationId) {
        $('#modal_filter_location').val(locationId).trigger('change');
    } else {
        $('#modal_filter_location').val("").trigger('change');
    }
    
    // filterStaffByLocation() is automatically called by the trigger('change') above
    
    // Use a small timeout to ensure the staff list is updated before selecting the staff
    setTimeout(() => {
        if (staffId) {
            $('#modal_staff_id').val(staffId).trigger('change');
        }
    }, 300);

    toggleModalFields();
    $('#attendanceModal').modal('show');
}

function quickAdd(staffId, date, locationId) {
    openAddModal(staffId, date, locationId);
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

var allMainStaffOptions = null;

$(document).ready(function() {
    // Store original options for the main filter
    allMainStaffOptions = $('#main_staff_id option').clone();
    
    // Initial filter run in case a location is already selected
    if ($('#main_filter_location').val()) {
        filterMainStaffByLocation(true);
    }
});

function filterMainStaffByLocation(isInitialLoad = false) {
    if (!allMainStaffOptions) return;
    
    var locationId = $('#main_filter_location').val();
    var staffSelect = $('#main_staff_id');
    var currentValue = staffSelect.val();
    
    staffSelect.empty();
    
    allMainStaffOptions.each(function() {
        var opt = $(this).clone();
        var staffLoc = opt.data('location');
        if (locationId === "" || staffLoc == locationId || opt.val() === "") {
            staffSelect.append(opt);
        }
    });
    
    if (staffSelect.find('option[value="' + currentValue + '"]').length > 0) {
        staffSelect.val(currentValue);
    } else {
        staffSelect.val('');
    }
    
    // Don't trigger change on initial load to prevent infinite loops or unnecessary events
    if (!isInitialLoad) {
        staffSelect.trigger('change');
    }
}

// ============ Bulk Mark Attendance ============
var bulkStaffData = [];

function openBulkMarkModal() {
    $('#bulkMarkForm')[0].reset();
    $('#bulk_attendance_date').val('<?= date('Y-m-d'); ?>');
    $('#bulk_status').val('Present');
    $('#bulk_search').val('');

    // Pre-select location from main page filter if present
    var mainLoc = $('#main_filter_location').val();
    $('#bulk_location_id').val(mainLoc || '');

    toggleBulkFields();
    loadBulkStaff();
    $('#bulkMarkModal').modal('show');
}

function toggleBulkFields() {
    var status = $('#bulk_status').val();
    if (status === 'Leave' || status === 'Sick-leave') {
        $('#bulk_leave_type_row').show();
        $('#bulk_time_fields').hide();
    } else if (status === 'Present' || status === 'Half-day') {
        $('#bulk_leave_type_row').hide();
        $('#bulk_time_fields').show();
    } else {
        $('#bulk_leave_type_row').hide();
        $('#bulk_time_fields').hide();
    }
}

function loadBulkStaff() {
    var locationId = $('#bulk_location_id').val();
    var date = $('#bulk_attendance_date').val();

    $('#bulk_staff_list').html('<div class="text-center text-muted py-4"><i class="fa fa-spinner fa-spin mr-2"></i> Loading staff...</div>');
    $('#bulk_selected_count').text(0);
    $('#bulk_total_count').text(0);
    $('#bulk_submit_count').text(0);
    $('#bulk_submit_btn').prop('disabled', true);
    $('#bulk_select_all').prop('checked', false).prop('indeterminate', false);

    $.ajax({
        url: '<?= base_url('admin/attendance/api/staff-by-location'); ?>',
        type: 'GET',
        dataType: 'json',
        data: { location_id: locationId, attendance_date: date },
        success: function(response) {
            if (response.status === 'success') {
                bulkStaffData = response.data || [];
                renderBulkStaffList();
            } else {
                $('#bulk_staff_list').html('<div class="text-center text-danger py-4">' + (response.message || 'Failed to load staff') + '</div>');
            }
        },
        error: function() {
            $('#bulk_staff_list').html('<div class="text-center text-danger py-4">Error loading staff list</div>');
        }
    });
}

function renderBulkStaffList() {
    var search = ($('#bulk_search').val() || '').toLowerCase().trim();
    var html = '';
    var visibleCount = 0;

    if (bulkStaffData.length === 0) {
        $('#bulk_staff_list').html('<div class="text-center text-muted py-4">No staff found for this location.</div>');
        $('#bulk_total_count').text(0);
        updateBulkSelection();
        return;
    }

    bulkStaffData.forEach(function(s) {
        // Search filter
        var nameMatch = s.name.toLowerCase().indexOf(search) !== -1;
        var codeMatch = (s.staff_code || '').toLowerCase().indexOf(search) !== -1;
        if (search && !nameMatch && !codeMatch) return;

        visibleCount++;
        var disabled = s.already_marked ? 'disabled' : '';
        var rowCls = s.already_marked ? 'bulk-staff-row disabled' : 'bulk-staff-row';
        var badge = s.already_marked
            ? '<span class="badge badge-warning ml-2" style="font-size:0.6rem; vertical-align:middle;">Already: ' + s.existing_status + '</span>'
            : '';

        html += '<div class="' + rowCls + '">';
        html += '  <input type="checkbox" class="form-check-input bulk-staff-cb m-0" name="staff_ids[]" value="' + s.id + '" id="bulk_cb_' + s.id + '" ' + disabled + '>';
        html += '  <label for="bulk_cb_' + s.id + '" class="ml-3 mb-0 flex-grow-1" style="cursor:' + (disabled ? 'not-allowed' : 'pointer') + ';">';
        html += '    <div class="bulk-staff-name">' + escapeHtml(s.name) + ' <span class="text-muted" style="font-weight:400;">(' + escapeHtml(s.staff_code || '') + ')</span>' + badge + '</div>';
        html += '    <div class="bulk-staff-meta">' + escapeHtml(s.location_name || '-') + '</div>';
        html += '  </label>';
        html += '</div>';
    });

    if (visibleCount === 0) {
        html = '<div class="text-center text-muted py-4">No staff matched your search.</div>';
    }

    $('#bulk_staff_list').html(html);
    $('#bulk_total_count').text(visibleCount);
    updateBulkSelection();
}

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"']/g, function(m) {
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m];
    });
}

function updateBulkSelection() {
    var $boxes = $('.bulk-staff-cb:not(:disabled)');
    var $checked = $boxes.filter(':checked');
    var total = $boxes.length;
    var sel = $checked.length;

    $('#bulk_selected_count').text(sel);
    $('#bulk_submit_count').text(sel);
    $('#bulk_submit_btn').prop('disabled', sel === 0);

    var $master = $('#bulk_select_all');
    if (sel === 0) {
        $master.prop('checked', false).prop('indeterminate', false);
    } else if (sel === total) {
        $master.prop('checked', true).prop('indeterminate', false);
    } else {
        $master.prop('checked', false).prop('indeterminate', true);
    }
}

$(document).ready(function() {
    // Reload staff when location or date changes
    $(document).on('change', '#bulk_location_id, #bulk_attendance_date', function() {
        loadBulkStaff();
    });

    // Search filter (client-side, preserves selection state through bulkStaffData rerender)
    $(document).on('input', '#bulk_search', function() {
        renderBulkStaffList();
    });

    // Master select all (only affects currently visible & enabled rows)
    $(document).on('change', '#bulk_select_all', function() {
        var checked = $(this).is(':checked');
        $('.bulk-staff-cb:not(:disabled)').prop('checked', checked);
        updateBulkSelection();
    });

    // Individual checkbox change
    $(document).on('change', '.bulk-staff-cb', function() {
        updateBulkSelection();
    });

    // Confirm before submit
    $('#bulkMarkForm').on('submit', function(e) {
        var $form = $(this);
        var sel = $('.bulk-staff-cb:checked').length;

        if (sel === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'No Staff Selected',
                text: 'Please select at least one staff member.',
                confirmButtonColor: '#4b49ac'
            });
            return false;
        }

        // If already confirmed, allow native submit
        if ($form.data('confirmed')) {
            return true;
        }

        e.preventDefault();
        var date = $('#bulk_attendance_date').val();
        var status = $('#bulk_status').val();
        var dateFormatted = new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

        Swal.fire({
            title: 'Confirm Attendance',
            html: 'Mark <b>' + status + '</b> for <b>' + sel + '</b> staff on <b>' + dateFormatted + '</b>?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4b49ac',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Save',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then(function(result) {
            if (result.isConfirmed) {
                $form.data('confirmed', true);
                $form.trigger('submit');
            }
        });
    });
});
</script>

<?php include("footer.php"); ?>
