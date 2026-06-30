<?php
// Simulate new dashboard driver count for location 21
require 'vendor/autoload.php';
// Can't easily bootstrap CI - use raw SQL instead
$mysqli = new mysqli('localhost', 'root', '', 'transport_demo');
$loc = 21; $date = '2026-06-23';
$sql = "SELECT COUNT(DISTINCT s.id) c FROM staff s
WHERE s.user_type='DRIVER'
AND (s.doj IS NULL OR s.doj='0000-00-00' OR s.doj<='$date')
AND (s.resign_date IS NULL OR s.resign_date='0000-00-00' OR s.resign_date>='$date')
AND (
  s.location_id=$loc
  OR ((s.location_id IS NULL OR s.location_id=0 OR s.location_id='') AND s.address='$loc')
  OR s.id IN (
    SELECT da.driver FROM driver_assignment da
    INNER JOIN vehicle v ON v.id=da.vehicle_no
    WHERE v.location_id=$loc AND da.from_date<='$date'
    AND (da.to_date IS NULL OR da.to_date='0000-00-00' OR da.to_date='' OR da.to_date>='$date')
  )
)";
echo 'new driver count=' . $mysqli->query($sql)->fetch_assoc()['c'] . "\n";
echo 'vehicle count=' . $mysqli->query("SELECT COUNT(*) c FROM vehicle WHERE location_id=$loc")->fetch_assoc()['c'] . "\n";
