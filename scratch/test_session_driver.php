<?php
// Simulate env override: session.driver = file
putenv('session.driver=file');
$_ENV['session.driver'] = 'file';
$_SERVER['session.driver'] = 'file';

define('FCPATH', __DIR__ . '/../public/');
define('ROOTPATH', __DIR__ . '/../');
define('APPPATH', ROOTPATH . 'app/');
define('SYSTEMPATH', ROOTPATH . 'system/');
define('WRITEPATH', ROOTPATH . 'writable/');
define('TESTPATH', ROOTPATH . 'tests/');

require ROOTPATH . 'vendor/autoload.php';

$config = new Config\Session();

echo 'driver: ' . $config->driver . PHP_EOL;
echo 'ok: ' . (class_exists($config->driver) ? 'yes' : 'no') . PHP_EOL;
