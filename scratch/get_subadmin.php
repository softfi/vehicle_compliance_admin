<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport');
$r = $mysqli->query("SELECT id, user_name, user_type FROM user WHERE user_type=2 AND deleted_by IS NULL LIMIT 5");
while ($row = $r->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
