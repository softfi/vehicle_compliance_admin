<?php
$db = new mysqli('localhost','root','','transport');
if($db->connect_error){ die("Connection failed: ".$db->connect_error); }
$res = $db->query("SELECT * FROM staff LIMIT 20");
while($r = $res->fetch_assoc()) {
    echo "ID: " . $r['id'] . " | Name: " . $r['name'] . " | Designation: " . ($r['designation'] ?? 'N/A') . " | Type: " . ($r['type'] ?? 'N/A') . "\n";
}
