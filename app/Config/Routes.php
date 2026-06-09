<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('admin', 'Admin::index');
$routes->post('admin/login', 'Admin::loginAuth');
$routes->get('logout', 'Admin::logout');
$routes->get('subadmin','Admin::addsubadmin');
$routes->get('salary-pdf/(:num)', 'Admin::salary_pdf/$1');

// Attendance Routes
$routes->get('admin/attendance', 'Attendance::index');
$routes->get('admin/attendance/add', 'Attendance::addAttendance');
$routes->post('admin/attendance/store', 'Attendance::store');
$routes->get('admin/attendance/edit/(:num)', 'Attendance::edit/$1');
$routes->post('admin/attendance/update/(:num)', 'Attendance::update/$1');
$routes->get('admin/attendance/delete/(:num)', 'Attendance::delete/$1');
$routes->get('admin/attendance/bulk', 'Attendance::bulkAdd');
$routes->post('admin/attendance/bulk-store', 'Attendance::bulkStore');
$routes->get('admin/attendance/download-template', 'Attendance::downloadTemplate');
$routes->get('admin/attendance/export-excel', 'Attendance::exportToExcel');
$routes->get('admin/attendance/reports', 'Attendance::reports');
$routes->get('admin/attendance/attendance-report', 'Attendance::attendanceReport');
$routes->get('admin/attendance/calendar', 'Attendance::calendarView');
$routes->get('admin/attendance/analytics', 'Attendance::analytics');
$routes->get('admin/attendance/get-data/(:num)', 'Attendance::getAttendanceData/$1');
$routes->get('admin/attendance/api/staff-list', 'Attendance::getStaffList');
$routes->get('admin/attendance/api/staff-by-location', 'Attendance::getStaffByLocation');
$routes->post('admin/attendance/quick-bulk-store', 'Attendance::quickBulkStore');

// Diesel Entry Routes
$routes->get('admin/diesel_entry/download_sample', 'Admin::download_diesel_sample');

// Despatch Entry Routes
$routes->get('admin/despatch_entry/download_sample', 'Admin::download_despatch_sample');

// Staff Advance Routes
$routes->get('admin/staf_advance/download_sample', 'Admin::download_staffadvance_sample');

// Mobile App API Routes
$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
    $routes->post('subadmin/login', 'AuthController::login');

    // Diesel entry (public — no Bearer token)
    $routes->get('diesel/meta', 'DieselController::meta');
    $routes->get('diesel', 'DieselController::index');
    $routes->post('diesel/store', 'DieselController::store');

    $routes->group('', ['filter' => 'apiauth'], static function ($routes) {
        $routes->get('subadmin/me', 'AuthController::me');
        $routes->post('subadmin/logout', 'AuthController::logout');
        $routes->post('subadmin/refresh', 'AuthController::refresh');

        // Dashboard (Bearer token required)
        $routes->get('dashboard', 'DashboardController::index');

        // Locations (Bearer token required)
        $routes->get('locations', 'LocationController::index');

        // Material Issue (Bearer token required)
        $routes->get('material-issue/particulars', 'MaterialIssueController::particulars');
        $routes->get('material-issue/drivers', 'MaterialIssueController::drivers');
        $routes->get('material-issue/driver/assigned-vehicle', 'MaterialIssueController::assignedVehicle');
        $routes->post('material-issue/driver/assigned-vehicle', 'MaterialIssueController::assignedVehicle');
        $routes->get('material-issue', 'MaterialIssueController::index');
        $routes->get('material-issue/(:num)', 'MaterialIssueController::show/$1');
        $routes->post('material-issue/store', 'MaterialIssueController::store');
        $routes->post('material-issue/(:num)', 'MaterialIssueController::update/$1');

        // Material Re-Issue (Bearer token required)
        $routes->get('material-reissue/driver/active-items', 'MaterialReissueController::activeItems');
        $routes->post('material-reissue/driver/active-items', 'MaterialReissueController::activeItems');
        $routes->get('material-reissue', 'MaterialReissueController::index');
        $routes->get('material-reissue/(:num)', 'MaterialReissueController::show/$1');
        $routes->post('material-reissue/store', 'MaterialReissueController::store');
        $routes->post('material-reissue/(:num)', 'MaterialReissueController::update/$1');
        $routes->delete('material-reissue/(:num)', 'MaterialReissueController::destroy/$1');

        // Staff Advance (Bearer token required)
        $routes->get('staff-advance', 'StaffAdvanceController::index');
        $routes->get('staff-advance/staff-types', 'StaffAdvanceController::staffTypes');
        $routes->get('staff-advance/employees', 'StaffAdvanceController::employees');
        $routes->post('staff-advance/employees', 'StaffAdvanceController::employees');
        $routes->get('staff-advance/cash-paid-by/users', 'StaffAdvanceController::cashPaidByUsers');
        $routes->get('staff-advance/employee-details', 'StaffAdvanceController::employeeDetails');
        $routes->post('staff-advance/employee-details', 'StaffAdvanceController::employeeDetails');
        $routes->post('staff-advance/store', 'StaffAdvanceController::store');
        $routes->get('staff-advance/(:num)', 'StaffAdvanceController::show/$1');
        $routes->post('staff-advance/(:num)', 'StaffAdvanceController::update/$1');
        $routes->delete('staff-advance/(:num)', 'StaffAdvanceController::destroy/$1');

        // Driver Assignment (Bearer token required)
        $routes->get('driver-assignment/drivers', 'DriverAssignmentController::drivers');
        $routes->get('driver-assignment', 'DriverAssignmentController::index');
        $routes->get('driver-assignment/(:num)', 'DriverAssignmentController::show/$1');
        $routes->post('driver-assignment/store', 'DriverAssignmentController::store');
        $routes->post('driver-assignment/(:num)', 'DriverAssignmentController::update/$1');
        $routes->delete('driver-assignment/(:num)', 'DriverAssignmentController::destroy/$1');

        // Manage diesel (Bearer token required)
        $routes->get('manage-diesel/vehicles/active', 'ManageDieselController::activeVehicles');
        $routes->get('manage-diesel/vehicle/assigned-driver', 'ManageDieselController::assignedDriver');
        $routes->post('manage-diesel/vehicle/assigned-driver', 'ManageDieselController::assignedDriver');
        $routes->get('manage-diesel/pumps/active', 'ManageDieselController::activePumps');
        $routes->get('manage-diesel/entries', 'ManageDieselController::entries');
        $routes->get('manage-diesel/entries/(:num)', 'ManageDieselController::show/$1');
        $routes->post('manage-diesel/entries', 'ManageDieselController::store');
        $routes->delete('manage-diesel/entries/(:num)', 'ManageDieselController::destroy/$1');

        // Extra diesel issue (Bearer token required)
        $routes->get('extra-diesel/issued-by/users', 'ExtraDieselController::issuedByUsers');
        $routes->get('extra-diesel', 'ExtraDieselController::index');
        $routes->post('extra-diesel/store', 'ExtraDieselController::store');

        // Diesel rate master (Bearer token required)
        $routes->get('diesel-rate', 'DieselRateController::index');
        $routes->get('diesel-rate/(:num)', 'DieselRateController::show/$1');
        $routes->post('diesel-rate/store', 'DieselRateController::store');
        $routes->post('diesel-rate/(:num)', 'DieselRateController::update/$1');
        $routes->delete('diesel-rate/(:num)', 'DieselRateController::destroy/$1');

        // Passenger vehicle diesel (Bearer token required)
        $routes->get('passenger-diesel/issued-by/users', 'PassengerDieselController::issuedByUsers');
        $routes->get('passenger-diesel', 'PassengerDieselController::index');
        $routes->get('passenger-diesel/(:num)', 'PassengerDieselController::show/$1');
        $routes->post('passenger-diesel/store', 'PassengerDieselController::store');
        $routes->post('passenger-diesel/(:num)', 'PassengerDieselController::update/$1');
        $routes->delete('passenger-diesel/(:num)', 'PassengerDieselController::destroy/$1');
    });
});