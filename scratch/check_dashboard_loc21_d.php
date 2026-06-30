<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport_demo');
$loc = 21;

echo "=== Assignments ever on loc21 vehicles ===\n";
$r = $mysqli->query("SELECT DISTINCT s.id, s.name, s.location_id, s.address, v.vehicle_no
FROM driver_assignment da
JOIN staff s ON s.id=da.driver
JOIN vehicle v ON v.id=da.vehicle_no AND v.location_id=$loc
ORDER BY s.name");
echo 'count=' . $r->num_rows . "\n";
while ($row = $r->fetch_assoc()) echo json_encode($row) . "\n";

echo "\n=== KINDA location name check ===\n";
$r = $mysqli->query("SELECT * FROM location WHERE location_id=$loc OR location_name LIKE '%KINDA%'");
while ($row = $r->fetch_assoc()) echo json_encode($row) . "\n";
