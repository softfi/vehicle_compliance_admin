<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport');
echo "=== DISTINCT user_type ===\n";
$r = $mysqli->query("SELECT DISTINCT user_type, COUNT(*) c FROM staff GROUP BY user_type ORDER BY user_type");
while ($row = $r->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
echo "\n=== SAMPLE STAFF (STAFF type) ===\n";
$r2 = $mysqli->query("SELECT id, name, staff_code, user_type, location_id, doj, resign_date FROM staff WHERE user_type='STAFF' LIMIT 3");
while ($row = $r2->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
echo "\n=== SAMPLE DRIVER ===\n";
$r3 = $mysqli->query("SELECT id, name, staff_code, user_type, location_id, doj, resign_date FROM staff WHERE user_type='DRIVER' LIMIT 3");
while ($row = $r3->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
