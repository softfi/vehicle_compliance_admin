<?php

define('FCPATH', __DIR__ . '/../public/');
require __DIR__ . '/../app/Config/Paths.php';

$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
CodeIgniter\Boot::bootTest($paths);

$model = new \App\Models\AdminModel();
$admin = new \App\Controllers\Admin();
$ref = new ReflectionClass($admin);

$year = 2026;
$month = 5;
$first = '2026-05-01';
$last = '2026-05-31';

$alldriver = $model->driver_salary_details($year, $month, '', null);
echo 'Rows: ' . count($alldriver) . PHP_EOL;

$pairs = [];
foreach ($alldriver as $staf) {
    $vid = (int) ($staf->assignment_vehicle_no ?? 0);
    $did = (int) ($staf->id ?? 0);
    if ($vid > 0 && $did > 0) {
        $pairs[$vid . '|' . $did] = ['vehicle_id' => $vid, 'driver_id' => $did];
    }
}

$t0 = microtime(true);
$batchTrip = $model->tripexpence1BatchTotals(array_values($pairs), $year, $month);
$t1 = microtime(true);

$mismatches = 0;
foreach ($alldriver as $staf) {
    $key = (int) $staf->assignment_vehicle_no . '|' . (int) $staf->id;
    $old = 0.0;
    foreach ($model->tripexpence1($staf->assignment_vehicle_no, $staf->id, $year, $month) as $r) {
        $old += (float) $r->day_trip_expense;
    }
    $new = $batchTrip[$key] ?? 0.0;
    if (abs($old - $new) > 0.0001) {
        $mismatches++;
        echo "Trip mismatch key=$key old=$old new=$new driver={$staf->name}\n";
    }
}

$build = $ref->getMethod('buildDriverSalaryBatchCaches');
$build->setAccessible(true);
$batch = $build->invoke($admin, $alldriver, $year, $month, $first, $last);
$t2 = microtime(true);

$calc = $ref->getMethod('calculateDriverTripDieselLitres');
$calc->setAccessible(true);
$keyMethod = $ref->getMethod('driverSalaryDieselLitresKey');
$keyMethod->setAccessible(true);

$dieselMismatch = 0;
foreach ($alldriver as $staf) {
    if (empty($staf->from_date) || empty($staf->to_date)) {
        continue;
    }
    $old = $calc->invoke($admin, $staf->assignment_vehicle_no, $staf->id, $staf->from_date, $staf->to_date);
    $key = $keyMethod->invoke($admin, $staf->assignment_vehicle_no, $staf->id, $staf->from_date, $staf->to_date);
    $new = $batch['diesel_litres'][$key] ?? 0.0;
    if (abs($old - $new) > 0.0001) {
        $dieselMismatch++;
        echo "Diesel mismatch key=$key old=$old new=$new driver={$staf->name}\n";
    }
}

echo "Trip mismatches=$mismatches / " . count($alldriver) . PHP_EOL;
echo "Diesel mismatches=$dieselMismatch\n";
echo 'Batch trip ms: ' . round(($t1 - $t0) * 1000) . PHP_EOL;
echo 'Batch diesel ms: ' . round(($t2 - $t1) * 1000) . PHP_EOL;

$tOld0 = microtime(true);
foreach (array_slice($alldriver, 0, 20) as $staf) {
    $model->tripexpence1($staf->assignment_vehicle_no, $staf->id, $year, $month);
    $calc->invoke($admin, $staf->assignment_vehicle_no, $staf->id, $staf->from_date ?? '', $staf->to_date ?? '');
}
echo 'Old 20-row sample ms: ' . round((microtime(true) - $tOld0) * 1000) . PHP_EOL;
