<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport_demo');
$loc = 21; $date = '2026-06-23';

echo "=== ALL DRIVERS with location_id=$loc OR address=$loc (no active filter) ===\n";
$r = $mysqli->query("SELECT id, name, staff_code, location_id, address, doj, resign_date FROM staff WHERE user_type='DRIVER' AND (location_id=$loc OR address='$loc')");
echo 'count=' . $r->num_rows . "\n";
while ($row = $r->fetch_assoc()) echo json_encode($row) . "\n";

echo "\n=== DRIVERS excluded by date rules but location match ===\n";
$r = $mysqli->query("SELECT id, name, location_id, address, doj, resign_date FROM staff WHERE user_type='DRIVER' AND (location_id=$loc OR address='$loc') AND NOT ((doj IS NULL OR doj='0000-00-00' OR doj<='$date') AND (resign_date IS NULL OR resign_date='0000-00-00' OR resign_date>='$date'))");
echo 'count=' . $r->num_rows . "\n";
while ($row = $r->fetch_assoc()) echo json_encode($row) . "\n";

echo "\n=== DRIVERS assigned to loc21 vehicles (any staff location) ===\n";
$r = $mysqli->query("SELECT DISTINCT s.id, s.name, s.location_id, s.address, v.vehicle_no
FROM driver_assignment da
JOIN staff s ON s.id = da.driver AND s.user_type='DRIVER'
JOIN vehicle v ON v.id = da.vehicle_no AND v.location_id=$loc
WHERE da.from_date <= '$date' AND (da.to_date IS NULL OR da.to_date='0000-00-00' OR da.to_date='' OR da.to_date >= '$date')");
echo 'count=' . $r->num_rows . "\n";
while ($row = $r->fetch_assoc()) echo json_encode($row) . "\n";

echo "\n=== Total drivers in system ===\n";
$r = $mysqli->query("SELECT COUNT(*) c FROM staff WHERE user_type='DRIVER'");
echo $r->fetch_assoc()['c'] . "\n";

echo "\n=== Drivers with empty/null location_id ===\n";
$r = $mysqli->query("SELECT COUNT(*) c FROM staff WHERE user_type='DRIVER' AND (location_id IS NULL OR location_id=0 OR location_id='')");
echo $r->fetch_assoc()['c'] . "\n";
