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