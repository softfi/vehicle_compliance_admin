<?php
$db = new mysqli('localhost','root','','transport');
if($db->connect_error){ die("Connection failed: ".$db->connect_error); }
$res = $db->query('SHOW TABLES');
while($r = $res->fetch_row()) {
    echo $r[0]."\n";
}
