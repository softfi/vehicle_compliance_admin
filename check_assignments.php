<?php
// Load CodeIgniter
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require 'system/bootstrap.php';

$db = \Config\Database::connect();

$vehicle_no = 'OD16J2202';
$vehicle = $db->table('vehicle')->where('vehicle_no', $vehicle_no)->get()->getRow();

if (!$vehicle) {
    echo "Vehicle $vehicle_no not found\n";
    exit;
}

echo "Vehicle ID: " . $vehicle->id . "\n";

$assignments = $db->table('driver_assignment')
    ->select('driver_assignment.*, staff.name as driver_name')
    ->join('staff', 'staff.id = driver_assignment.driver', 'left')
    ->where('vehicle_no', $vehicle->id)
    ->orderBy('from_date', 'ASC')
    ->get()->getResult();

echo "Assignments for vehicle " . $vehicle->id . ":\n";
foreach ($assignments as $asgn) {
    echo "ID: {$asgn->id}, Driver: {$asgn->driver} ({$asgn->driver_name}), From: {$asgn->from_date}, To: " . ($asgn->to_date ?? 'NULL') . "\n";
}
