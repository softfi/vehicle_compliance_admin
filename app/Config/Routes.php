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

// Face Attendance Registration (Web)
$routes->get('admin/attendance/register-face', 'FaceAttendanceController::index');
$routes->post('admin/attendance/register-face', 'FaceAttendanceController::registerEmbedding');

// Diesel Entry Routes
$routes->get('admin/diesel_entry/download_sample', 'Admin::download_diesel_sample');

// Despatch Entry Routes
$routes->get('admin/despatch_entry/download_sample', 'Admin::download_despatch_sample');

// Staff Advance Routes
$routes->get('admin/staf_advance/download_sample', 'Admin::download_staffadvance_sample');

// Mobile App API Routes
$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
    $routes->post('subadmin/login', 'AuthController::login');
    $routes->post('login', 'AuthController::login');

    // Diesel entry (public — no Bearer token)
    $routes->get('diesel/meta', 'DieselController::meta');
    $routes->get('diesel', 'DieselController::index');
    $routes->post('diesel/store', 'DieselController::store');

    // Despatch entry store (public — no Bearer token)
    $routes->post('despatch-entry/store', 'DespatchEntryController::store');
    $routes->post('dispatch-entry/store', 'DespatchEntryController::store');

    $routes->group('', ['filter' => 'apiauth'], static function ($routes) {
        $routes->get('subadmin/me', 'AuthController::me');
        $routes->get('profile', 'AuthController::profile');
        $routes->post('subadmin/logout', 'AuthController::logout');
        $routes->post('subadmin/refresh', 'AuthController::refresh');

        // Dashboard (Bearer token required)
        $routes->get('dashboard', 'DashboardController::index');

        // In-house maintenance (Bearer token required) — admin/add_inhouse & admin/inhouse_maintenance
        $routes->get('inhouse-maintenance/form', 'InhouseMaintenanceController::form');
        $routes->get('inhouse-maintenance/mechanics', 'InhouseMaintenanceController::mechanics');
        $routes->match(['get', 'post'], 'inhouse-maintenance/items', 'InhouseMaintenanceController::itemsByLocation');
        $routes->match(['get', 'post'], 'inhouse-maintenance/vehicle-driver', 'InhouseMaintenanceController::vehicleDriver');
        $routes->post('inhouse-maintenance/store', 'InhouseMaintenanceController::store');
        $routes->get('inhouse-maintenance/delete/(:segment)', 'InhouseMaintenanceController::destroy/$1');
        $routes->post('inhouse-maintenance/(:segment)/delete', 'InhouseMaintenanceController::destroy/$1');
        $routes->get('inhouse-maintenance/(:segment)/edit', 'InhouseMaintenanceController::edit/$1');
        $routes->post('inhouse-maintenance/(:segment)', 'InhouseMaintenanceController::update/$1');
        $routes->delete('inhouse-maintenance/(:segment)', 'InhouseMaintenanceController::destroy/$1');
        $routes->get('inhouse-maintenance/(:segment)', 'InhouseMaintenanceController::show/$1');
        $routes->get('inhouse-maintenance', 'InhouseMaintenanceController::index');
        $routes->get('add-inhouse', 'InhouseMaintenanceController::index');

        // Locations (Bearer token required)
        $routes->get('locations', 'LocationController::index');
        $routes->match(['get', 'post'], 'location/verify-and-staff', 'LocationStaffController::verifyAndStaff');

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

        // Despatch entry (Bearer token required) — admin/despatch_entry
        $routes->get('dispatch-entry/form', 'DespatchEntryController::form');
        $routes->get('dispatch-entry', 'DespatchEntryController::index');
        $routes->get('dispatch-entry/(:num)', 'DespatchEntryController::show/$1');
        $routes->post('dispatch-entry/(:num)', 'DespatchEntryController::update/$1');
        $routes->delete('dispatch-entry/(:num)', 'DespatchEntryController::destroy/$1');
        $routes->get('despatch-entry/form', 'DespatchEntryController::form');
        $routes->get('despatch-entry', 'DespatchEntryController::index');
        $routes->get('despatch-entry/(:num)', 'DespatchEntryController::show/$1');
        $routes->post('despatch-entry/(:num)', 'DespatchEntryController::update/$1');
        $routes->delete('despatch-entry/(:num)', 'DespatchEntryController::destroy/$1');

        // Purchase entry (Bearer token required)
        $routes->get('purchase-entry/suppliers', 'PurchaseEntryController::suppliers');
        $routes->get('purchase-entry/items', 'PurchaseEntryController::items');
        $routes->post('purchase-entry/items', 'PurchaseEntryController::items');
        $routes->post('purchase-entry/store', 'PurchaseEntryController::store');
        $routes->post('purchase-entry/(:num)/update', 'PurchaseEntryController::update/$1');
        $routes->post('purchase-entry/(:num)', 'PurchaseEntryController::update/$1');
        $routes->get('purchase-entry', 'PurchaseEntryController::index');
        $routes->get('purchase-entry/view/(:num)', 'PurchaseEntryController::show/$1');
        $routes->delete('purchase-entry/(:num)', 'PurchaseEntryController::destroy/$1');
        $routes->get('purchase-entry/(:num)', 'PurchaseEntryController::show/$1');

        // Tyre assignment positions (Bearer token required)
        $routes->get('tyre-assignment/stock-tyres', 'TyreAssignmentController::stockTyres');
        $routes->post('tyre-assignment/assign', 'TyreAssignmentController::assign');
        $routes->post('tyre-assignment/replace', 'TyreAssignmentController::replace');
        $routes->post('tyre-assignment/back-to-stock', 'TyreAssignmentController::backToStock');
        $routes->post('tyre-assignment/rotate', 'TyreAssignmentController::rotate');
        $routes->get('tyre-assignment', 'TyreAssignmentController::index');
        $routes->get('tyre-assignment/(:num)', 'TyreAssignmentController::show/$1');

        // Tyre purchase management (Bearer token required) — admin/tyer_management
        $routes->get('tyre-purchase/form', 'StockTyreController::purchaseBillCreateForm');
        $routes->post('tyre-purchase/store', 'StockTyreController::purchaseBillStore');
        $routes->get('tyre-purchase/bill-serials', 'StockTyreController::purchaseBillSerials');
        $routes->get('tyre-purchase/bill-detail', 'StockTyreController::purchaseBillShow');
        $routes->get('tyre-purchase/(:num)/edit', 'StockTyreController::purchaseBillEdit/$1');
        $routes->post('tyre-purchase/(:num)/update', 'StockTyreController::purchaseBillUpdate/$1');
        $routes->get('tyre-purchase/delete/(:num)', 'StockTyreController::purchaseBillDestroy/$1');
        $routes->post('tyre-purchase/(:num)/delete', 'StockTyreController::purchaseBillDestroy/$1');
        $routes->delete('tyre-purchase/tyre/(:num)', 'StockTyreController::purchaseTyreDestroy/$1');
        $routes->delete('tyre-purchase/(:num)', 'StockTyreController::purchaseBillDestroy/$1');
        $routes->get('tyre-purchase/(:num)', 'StockTyreController::purchaseBillShow/$1');
        $routes->get('tyre-purchase', 'StockTyreController::purchaseBills');
        $routes->get('tyre-management', 'StockTyreController::purchaseBills');
        $routes->get('tyre-management/delete/(:num)', 'StockTyreController::purchaseBillDestroy/$1');
        $routes->post('tyre-management/(:num)/delete', 'StockTyreController::purchaseBillDestroy/$1');
        $routes->delete('tyre-management/(:num)', 'StockTyreController::purchaseBillDestroy/$1');

        // Tyre transfer (Bearer token required) — admin/tyreTransfer
        $routes->get('tyre-transfer/form', 'StockTyreController::transferForm');
        $routes->match(['get', 'post'], 'tyre-transfer/tyres', 'StockTyreController::transferTyresByLocation');
        $routes->match(['get', 'post'], 'tyre-transfer/tyre-detail', 'StockTyreController::transferTyreDetail');
        $routes->post('tyre-transfer', 'StockTyreController::transferStore');

        // Stock tyre list (Bearer token required) — admin/StockTyer_management
        $routes->get('stock-tyre/brands', 'StockTyreController::brands');
        $routes->get('tyre-brands', 'StockTyreController::brands');
        $routes->get('stock-tyre/vendors', 'StockTyreController::vendors');
        $routes->post('stock-tyre/exchange/store', 'StockTyreController::exchangeStore');
        $routes->post('stock-tyre/(:num)/request-exchange', 'StockTyreController::requestExchange/$1');
        $routes->get('stock-tyre/(:num)/history', 'StockTyreController::history/$1');
        $routes->post('stock-tyre/(:num)/update-status', 'StockTyreController::updateStatus/$1');
        $routes->get('stock-tyre', 'StockTyreController::index');

        // Tyre details + history (Bearer token required) — admin/tyre_details_vw/{id}
        $routes->get('tyre-details/(:num)', 'StockTyreController::history/$1');

        // Scrap yard list (Bearer token required) — admin/scrapTyer_management
        $routes->post('scrap-tyre/return-to-stock', 'StockTyreController::returnToStock');
        $routes->post('scrap-tyre/sell', 'StockTyreController::sell');
        $routes->get('scrap-tyre', 'StockTyreController::scrap');

        // Sent to vendor / exchange requested (Bearer token required) — admin/sentToVendorTyer_management
        $routes->get('vendor-exchange/(:num)', 'StockTyreController::vendorExchangeShow/$1');
        $routes->post('vendor-exchange/(:num)', 'StockTyreController::vendorExchangeUpdate/$1');
        $routes->get('sent-to-vendor-tyre', 'StockTyreController::sentToVendor');

        // Sold tyre list (Bearer token required) — admin/soldTyer_management
        $routes->post('sold-tyre/restore', 'StockTyreController::restoreSold');
        $routes->get('sold-tyre', 'StockTyreController::sold');

        // Tyre inventory report (Bearer token required) — admin/tyer_report
        $routes->get('tyre-report', 'StockTyreController::report');

        // Repair report (Bearer token required) — admin/repaire_report
        $routes->post('repair-report/back-to-stock', 'StockTyreController::repairBackToStock');
        $routes->post('repair-report/(:num)/back-to-stock', 'StockTyreController::repairBackToStock/$1');
        $routes->get('repair-report', 'StockTyreController::repairReport');

        // Tyre exchange report (Bearer token required) — admin/tyre_exchange_report
        $routes->get('tyre-exchange-report/(:num)/history', 'StockTyreController::exchangeReportHistory/$1');
        $routes->get('tyre-exchange-report/(:num)', 'StockTyreController::exchangeReportShow/$1');
        $routes->get('tyre-exchange-report', 'StockTyreController::exchangeReport');

        // Driver master (Bearer token required) — admin/Add_staf (DRIVER)
        $routes->get('drivers/blood-groups', 'DriverController::bloodGroups');
        $routes->get('drivers/(:num)', 'DriverController::show/$1');
        $routes->get('drivers', 'DriverController::index');
        $routes->post('drivers/store', 'DriverController::store');

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
        $routes->get('diesel-rates', 'DieselRateController::index');
        $routes->get('diesel-rate/list', 'DieselRateController::index');
        $routes->get('diesel-rate/current', 'DieselRateController::current');
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

        // Mobile Face Attendance
        $routes->post('attendance/mark-face', '\App\Controllers\FaceAttendanceController::markAttendance');

        // Staff attendance (Bearer token required)
        $routes->get('attendance', 'AttendanceController::index');
        $routes->match(['get', 'post'], 'attendance/date-wise', 'AttendanceController::dateWise');
        $routes->post('attendance/mark-present', 'AttendanceController::markPresent');
        $routes->get('attendance/(:num)', 'AttendanceController::show/$1');

        // Task Management (Bearer token required)
        $routes->get('tasks/users/assign-to', 'TaskController::assignToUsers');
        $routes->get('tasks/users/cc', 'TaskController::ccUsers');
        $routes->get('tasks/users', 'TaskController::users');
        $routes->get('tasks', 'TaskController::index');
        $routes->post('tasks/store', 'TaskController::store');
        $routes->get('tasks/(:num)', 'TaskController::show/$1');
        $routes->post('tasks/(:num)', 'TaskController::update/$1');
        $routes->delete('tasks/(:num)', 'TaskController::destroy/$1');
    });
});