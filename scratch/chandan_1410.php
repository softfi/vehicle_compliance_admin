<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport_demo');
$staff_id = 1410;
$first = '2026-05-01'; $last = '2026-05-31';
$sql = "SELECT d.despatch_id, DATE(d.des_date) AS trip_date, dr.do_no
FROM despatch d
JOIN driver_assignment da ON da.vehicle_no = d.vehicle_no AND da.driver = $staff_id
LEFT JOIN do_registration dr ON dr.do_registration_id = d.do_no
WHERE d.des_date >= '$first' AND d.des_date <= '$last'
AND d.des_date >= da.from_date AND d.des_date <= da.to_date
GROUP BY d.despatch_id";
$r = $mysqli->query($sql);
$all = []; while ($row = $r->fetch_assoc()) $all[] = $row;
$byDate = [];
foreach ($all as $t) $byDate[$t['trip_date']][] = $t;
echo "Staff 1410 CHANDAN YADAV\n";
echo "Slip trips: " . count($all) . "\n";
echo "Unique days: " . count($byDate) . "\n";
$r2 = $mysqli->query("SELECT COUNT(DISTINCT d.despatch_id) c FROM despatch d
JOIN driver_assignment da ON da.vehicle_no=d.vehicle_no AND da.driver=$staff_id
WHERE d.des_date BETWEEN '$first' AND '$last' AND d.des_date>=da.from_date AND d.des_date<=da.to_date
AND d.deleted_at IS NULL AND d.deleted_by IS NULL");
echo "Non-deleted: " . $r2->fetch_assoc()['c'] . "\n";
