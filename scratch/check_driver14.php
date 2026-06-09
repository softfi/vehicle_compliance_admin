<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport');
$r = $mysqli->query("SELECT id, name, staff_code FROM staff WHERE id = 14 OR name LIKE '%rahul%' LIMIT 5");
while ($row = $r->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
echo "\nActive items API simulation for driver 14:\n";
$rows = $mysqli->query("SELECT id, driver_id, item_name, status FROM driver_material_issue WHERE driver_id=14 AND status='Active'");
while ($row = $rows->fetch_assoc()) {
    $names = array_map('trim', explode(',', $row['item_name']));
    foreach ($names as $name) {
        echo json_encode(['id' => (int)$row['id'], 'issue_id' => (int)$row['id'], 'item_name' => $name]) . "\n";
    }
}
