<?php
// Benchmark driver salary load - simulate May 2026 all locations
$mysqli = new mysqli('localhost', 'root', '', 'transport_demo');
$year = 2026; $month = 5;
$first = "$year-$month-01";
$last = date('Y-m-t', strtotime($first));

$t0 = microtime(true);
$r = $mysqli->query("SELECT COUNT(*) c FROM driver_assignment WHERE from_date <= '$last' AND from_date >= '$first'");
$assignCount = $r->fetch_assoc()['c'];
$t1 = microtime(true);

// Approximate main query row count via subquery logic
$sql = "SELECT COUNT(*) c FROM (
SELECT staff.id, driver_assignment.from_date
FROM staff
LEFT JOIN location ON location.location_id = staff.address
LEFT JOIN adjust_salary ON adjust_salary.driver_id = staff.id
INNER JOIN (
  SELECT driver AS staff_id, from_date, to_date, vehicle_no, opening_hsd, closing_hsd
  FROM driver_assignment
  WHERE from_date <= '$last' AND from_date >= '$first'
) driver_assignment ON driver_assignment.staff_id = staff.id
LEFT JOIN vehicle ON vehicle.id = driver_assignment.vehicle_no
WHERE staff.user_type = 'DRIVER'
GROUP BY staff.id, driver_assignment.from_date
) x";
$r = $mysqli->query($sql);
$rowCount = $r->fetch_assoc()['c'];
$t2 = microtime(true);

echo "Assignment rows in month: $assignCount\n";
echo "Grid rows (driver+assignment): $rowCount\n";
echo "Per-row queries if N+1: " . ($rowCount * 2) . " (tripexpence1 + calculateDriverTripDieselLitres)\n";
echo "Count query ms: " . round(($t2-$t1)*1000) . "\n";

// Sample one tripexpence1-style query timing
$t3 = microtime(true);
$mysqli->query("SELECT DATE(d.des_date) d, COUNT(d.despatch_id) c
FROM despatch d
JOIN do_registration ON do_registration.do_registration_id = d.do_no
LEFT JOIN doprice_change ON do_registration.do_registration_id = doprice_change.dono AND d.des_date >= doprice_change.from_date
JOIN driver_assignment da ON da.vehicle_no = d.vehicle_no
WHERE d.vehicle_no = 41 AND YEAR(d.des_date)=$year AND MONTH(d.des_date)=$month
AND da.driver = 1371
AND d.des_date >= da.from_date AND d.des_date <= da.to_date
GROUP BY DATE(d.des_date), da.driver LIMIT 50");
$t4 = microtime(true);
echo "Single tripexpence1-like query ms: " . round(($t4-$t3)*1000) . "\n";
echo "Est. tripexpence1 total ms (rows * single): " . round(($t4-$t3)*1000 * $rowCount) . "\n";

// despatch table size
$r = $mysqli->query("SELECT COUNT(*) c FROM despatch");
echo "Total despatch rows: " . $r->fetch_assoc()['c'] . "\n";
$r = $mysqli->query("SELECT COUNT(*) c FROM staff WHERE user_type='DRIVER'");
echo "Total drivers: " . $r->fetch_assoc()['c'] . "\n";
