<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport');
if (!$mysqli->query("SHOW TABLES LIKE 'api_tokens'")->num_rows) {
    echo "no api_tokens table\n";
    exit;
}
$r = $mysqli->query("SELECT id, user_id, LEFT(token_hash,20) h, expires_at FROM api_tokens ORDER BY id DESC LIMIT 3");
while ($row = $r->fetch_assoc()) {
    echo json_encode($row) . "\n";
}
