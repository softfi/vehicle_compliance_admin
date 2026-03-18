<?php
$db = new mysqli('localhost','root','','transport');
if($db->connect_error){ die("Connection failed: ".$db->connect_error); }
$res = $db->query('SELECT group_id, COUNT(*) as count FROM ledger GROUP BY group_id');
while($r = $res->fetch_assoc()) {
    echo $r['group_id'].'|'.$r['count']."\n";
}
echo "-- ALL --\n";
$res = $db->query('SELECT l.ledger_id, l.ledger_name, g.group_name FROM ledger l JOIN `group` g ON l.group_id = g.group_id LIMIT 10');
while($r = $res->fetch_assoc()) {
    echo $r['ledger_id'].'|'.$r['ledger_name'].'|'.$r['group_name']."\n";
}
