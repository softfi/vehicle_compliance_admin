<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport');
echo "=== REISSUE HISTORY ===\n";
$r = $mysqli->query('SELECT * FROM driver_material_reissue ORDER BY id DESC LIMIT 10');
while ($row = $r->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
echo "\n=== ALL ISSUES AFTER ANY REISSUE ===\n";
$r2 = $mysqli->query('SELECT id, driver_id, item_name, status FROM driver_material_issue ORDER BY id');
while ($row = $r2->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
