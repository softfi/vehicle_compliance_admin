<?php
$db = new mysqli('localhost','root','','transport');
if($db->connect_error){ die("Connection failed: ".$db->connect_error); }
$groups = $db->query('SELECT * FROM `group`');
while($r = $groups->fetch_assoc()) {
    echo $r['group_id'].'|'.$r['group_name']."\n";
}
