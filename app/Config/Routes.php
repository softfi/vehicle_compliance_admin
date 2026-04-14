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