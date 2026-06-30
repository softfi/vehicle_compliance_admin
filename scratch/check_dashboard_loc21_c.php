<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport_demo');
$loc = 21; $date = '2026-06-23';

$sql = "SELECT id, name, staff_code, location_id, address, doj, resign_date FROM staff
WHERE user_type='DRIVER'
AND (location_id=$loc OR ((location_id IS NULL OR location_id=0 OR location_id='') AND address='$loc'))
AND (doj IS NULL OR doj='0000-00-00' OR doj<='$date')
AND (resign_date IS NULL OR resign_date='0000-00-00' OR resign_date>='$date')";
$r = $mysqli->query($sql);
echo "legacy+location_id count=" . $r->num_rows . "\n";
while ($row = $r->fetch_assoc()) echo json_encode($row) . "\n";

$sql2 = "SELECT id, name, location_id, address FROM staff WHERE user_type='DRIVER'
AND (location_id=$loc OR address='$loc')";
$r = $mysqli->query($sql2);
echo "\nno date filter count=" . $r->num_rows . "\n";

$sql3 = "SELECT COUNT(DISTINCT s.id) c FROM staff s
JOIN driver_assignment da ON da.driver=s.id
JOIN vehicle v ON v.id=da.vehicle_no AND v.location_id=$loc
WHERE s.user_type='DRIVER'
AND da.from_date<='$date' AND (da.to_date IS NULL OR da.to_date='0000-00-00' OR da.to_date='' OR da.to_date>='$date')
AND (s.doj IS NULL OR s.doj='0000-00-00' OR s.doj<='$date')
AND (s.resign_date IS NULL OR s.resign_date='0000-00-00' OR s.resign_date>='$date')";
$r = $mysqli->query($sql3);
echo "assigned to loc vehicles=" . $r->fetch_assoc()['c'] . "\n";
