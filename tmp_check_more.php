<?php
$db = new mysqli('localhost','root','','transport');
if($db->connect_error){ die("Connection failed: ".$db->connect_error); }
$res = $db->query('SELECT * FROM `group`');
while($r = $res->fetch_assoc()) {
    echo "Group: " . $r['group_id'] . " | " . $r['group_name'] . "\n";
}
$res = $db->query('SELECT * FROM `ledger`');
if ($res) {
    while($r = $res->fetch_assoc()) {
        echo "Ledger: " . $r['ledger_id'] . " | " . $r['ledger_name'] . " | GroupID: " . $r['group_id'] . "\n";
    }
} else {
    echo "Ledger table SELECT failed or empty.\n";
}
