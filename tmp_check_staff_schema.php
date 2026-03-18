<?php
$db = new mysqli('localhost','root','','transport');
if($db->connect_error){ die("Connection failed: ".$db->connect_error); }
$res = $db->query("DESCRIBE staff");
while($r = $res->fetch_assoc()) {
    echo $r['Field'] . " | " . $r['Type'] . "\n";
}
