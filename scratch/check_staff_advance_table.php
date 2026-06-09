<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport');
echo "=== staff_advance STRUCTURE ===\n";
$r = $mysqli->query('DESCRIBE staff_advance');
while ($row = $r->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
echo "\n=== RECENT ROW ===\n";
$r2 = $mysqli->query('SELECT * FROM staff_advance ORDER BY id DESC LIMIT 1');
$row = $r2->fetch_assoc();
echo $row ? json_encode($row) : "no rows\n";
