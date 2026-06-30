<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport_demo');
if ($mysqli->connect_error) {
    die('DB fail: ' . $mysqli->connect_error);
}

$loc  = 21;
$date = '2026-06-23';

echo "=== VEHICLES location_id=$loc ===\n";
$r = $mysqli->query("SELECT id, vehicle_no, location_id FROM vehicle WHERE location_id=$loc");
echo 'count=' . $r->num_rows . "\n";
while ($row = $r->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== DRIVERS API logic (staff.location_id=$loc) ===\n";
$sql = "SELECT id, name, staff_code, location_id, address, doj, resign_date FROM staff
    WHERE user_type='DRIVER' AND location_id=$loc
    AND (doj IS NULL OR doj='0000-00-00' OR doj<='$date')
    AND (resign_date IS NULL OR resign_date='0000-00-00' OR resign_date>='$date')";
$r = $mysqli->query($sql);
echo 'count=' . $r->num_rows . "\n";
while ($row = $r->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== DRIVERS via staff.address=$loc ===\n";
$sql2 = "SELECT id, name, staff_code, location_id, address, doj, resign_date FROM staff
    WHERE user_type='DRIVER' AND address=$loc
    AND (doj IS NULL OR doj='0000-00-00' OR doj<='$date')
    AND (resign_date IS NULL OR resign_date='0000-00-00' OR resign_date>='$date')";
$r = $mysqli->query($sql2);
echo 'count=' . $r->num_rows . "\n";
while ($row = $r->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== DRIVERS location_id OR address = $loc ===\n";
$sql3 = "SELECT id, name, staff_code, location_id, address, doj, resign_date FROM staff
    WHERE user_type='DRIVER' AND (location_id=$loc OR address=$loc)
    AND (doj IS NULL OR doj='0000-00-00' OR doj<='$date')
    AND (resign_date IS NULL OR resign_date='0000-00-00' OR resign_date>='$date')";
$r = $mysqli->query($sql3);
echo 'count=' . $r->num_rows . "\n";
while ($row = $r->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== ACTIVE ASSIGNMENTS on $date for location $loc vehicles ===\n";
$sql4 = "SELECT da.id, da.driver, s.name, s.location_id, s.address, v.vehicle_no, v.location_id as veh_loc
    FROM driver_assignment da
    JOIN staff s ON s.id = da.driver
    JOIN vehicle v ON v.id = da.vehicle_no
    WHERE v.location_id = $loc
    AND da.from_date <= '$date'
    AND (da.to_date IS NULL OR da.to_date = '0000-00-00' OR da.to_date = '' OR da.to_date >= '$date')";
$r = $mysqli->query($sql4);
echo 'count=' . $r->num_rows . "\n";
while ($row = $r->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
