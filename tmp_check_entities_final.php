<?php
$db = new mysqli('localhost','root','','transport');
if($db->connect_error){ die("Connection failed: ".$db->connect_error); }
$res = $db->query("SELECT * FROM bank LIMIT 10");
while($r = $res->fetch_assoc()) {
    echo json_encode($r) . "\n";
}
echo "Vehicles:\n";
$res = $db->query("SELECT * FROM vehicle LIMIT 10");
while($r = $res->fetch_assoc()) {
    echo $r['id'] . ":" . $r['vehicle_no'] . " ";
}
echo "\n";
