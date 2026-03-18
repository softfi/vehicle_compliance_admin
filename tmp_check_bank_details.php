<?php
$db = new mysqli('localhost','root','','transport');
if($db->connect_error){ die("Connection failed: ".$db->connect_error); }
$res = $db->query("SELECT * FROM bank");
while($r = $res->fetch_assoc()) {
    echo "BANK: " . $r['bank_name'] . " | ID: " . $r['bank_id'] . "\n";
}
