<?php
// Simulate store validation for common wrong inputs
$mysqli = new mysqli('localhost', 'root', '', 'transport');

function check($mysqli, $driverId, $itemId, $label) {
    $stmt = $mysqli->prepare("SELECT id, driver_id, item_name, status FROM driver_material_issue WHERE id = ? AND driver_id = ? AND status = 'Active'");
    $stmt->bind_param('ii', $itemId, $driverId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    echo $label . ': ' . ($row ? json_encode($row) : 'NOT FOUND') . "\n";
}

$tests = [
    [14, 12, 'correct driver 14 + issue 12'],
    [14, 1, 'driver 14 + item_id 1 (dropdown index guess)'],
    [14, 5, 'driver 14 + item_id 5 (random)'],
    [1554, 12, 'wrong driver for issue 12'],
    [14, 9, 'driver 14 + issue 9 (other driver issue)'],
    [14, 10, 'driver 14 + issue 10 Stepny row'],
];

foreach ($tests as [$d, $i, $label]) {
    check($mysqli, $d, $i, $label);
}

// Check if id-only lookup without driver filter
echo "\n=== Row by id only ===\n";
$r = $mysqli->query('SELECT id, driver_id, item_name, status FROM driver_material_issue WHERE id IN (1,2,3,4,5,6,7,8)');
while ($row = $r->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
echo "(ids 1-8: none if empty above)\n";
