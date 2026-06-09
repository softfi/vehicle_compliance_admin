<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport');
if ($mysqli->connect_error) {
    die('connect failed: ' . $mysqli->connect_error);
}

echo "=== STRUCTURE ===\n";
$r = $mysqli->query('DESCRIBE driver_material_issue');
while ($row = $r->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== RECENT ROWS ===\n";
$r2 = $mysqli->query('SELECT id, driver_id, item_name, issued_date, status FROM driver_material_issue ORDER BY id DESC LIMIT 25');
while ($row = $r2->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== STATUS COUNTS ===\n";
$r3 = $mysqli->query('SELECT status, COUNT(*) AS c FROM driver_material_issue GROUP BY status');
while ($row = $r3->fetch_assoc()) {
    echo json_encode($row) . "\n";
}

echo "\n=== ACTIVE BY DRIVER ===\n";
$r4 = $mysqli->query("SELECT id, driver_id, item_name, status FROM driver_material_issue WHERE status = 'Active' ORDER BY driver_id, id");
while ($row = $r4->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
