<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport_demo');
$year = 2026; $month = 5;
$first = "$year-$month-01";
$last = date('Y-m-t', strtotime($first));

foreach (['all locations', 'location filter empty'] as $label) {
    $sql = "SELECT COUNT(*) c FROM (
    SELECT staff.id, driver_assignment.from_date
    FROM staff
    INNER JOIN (
      SELECT driver AS staff_id, from_date, to_date, vehicle_no, opening_hsd, closing_hsd
      FROM driver_assignment
      WHERE from_date <= '$last' AND from_date >= '$first'
    ) driver_assignment ON driver_assignment.staff_id = staff.id
    WHERE staff.user_type = 'DRIVER'
    GROUP BY staff.id, driver_assignment.from_date
    ) x";
    $c = $mysqli->query($sql)->fetch_assoc()['c'];
    echo "$label: $c rows\n";
}

// Full main query EXPLAIN
echo "\n--- EXPLAIN driver list subquery ---\n";
$r = $mysqli->query("EXPLAIN SELECT staff.id FROM staff
INNER JOIN (SELECT driver AS staff_id, from_date FROM driver_assignment WHERE from_date <= '$last' AND from_date >= '$first') da ON da.staff_id = staff.id
WHERE staff.user_type='DRIVER' LIMIT 5");
while ($row = $r->fetch_assoc()) print_r($row);

// showadjust_salary - is it heavy?
$t0 = microtime(true);
$r = $mysqli->query("SELECT * FROM adjust_salary");
echo "\nadjust_salary rows: " . $r->num_rows . " time ms: " . round((microtime(true)-$t0)*1000) . "\n";

// Time 10 consecutive tripexpence1
$total = 0;
for ($i=0; $i<10; $i++) {
    $t0 = microtime(true);
    $mysqli->query("SELECT DATE(d.des_date) d, COUNT(d.despatch_id) c
    FROM despatch d
    JOIN do_registration ON do_registration.do_registration_id = d.do_no
    LEFT JOIN doprice_change ON do_registration.do_registration_id = doprice_change.dono AND d.des_date >= doprice_change.from_date
    JOIN driver_assignment da ON da.vehicle_no = d.vehicle_no
    WHERE d.vehicle_no = 41 AND YEAR(d.des_date)=$year AND MONTH(d.des_date)=$month
    AND da.driver = 1371 AND d.des_date >= da.from_date AND d.des_date <= da.to_date
    GROUP BY DATE(d.des_date), da.driver");
    $total += (microtime(true)-$t0)*1000;
}
echo "Avg tripexpence1 ms (10 runs): " . round($total/10) . "\n";

$t0 = microtime(true);
for ($i=0; $i<10; $i++) {
    $mysqli->query("SELECT d.despatch_id FROM despatch d
    JOIN do_registration ON do_registration.do_registration_id = d.do_no
    JOIN driver_assignment da ON da.vehicle_no = d.vehicle_no AND da.driver = 1371
    WHERE da.from_date='2026-05-01' AND da.to_date='2026-05-19' AND d.vehicle_no=41
    AND d.des_date >= '2026-05-01' AND d.des_date <= '2026-05-19'
    AND d.deleted_at IS NULL AND d.deleted_by IS NULL GROUP BY d.despatch_id");
}
echo "Avg calculateDriverTripDieselLitres ms (10 runs): " . round(((microtime(true)-$t0)*1000)/10) . "\n";

echo "\nEstimated server time for 172 rows: " . round(172 * (187 + 50) / 1000, 1) . " seconds (queries only)\n";
