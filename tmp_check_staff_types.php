<?php
$db = new mysqli('localhost','root','','transport');
if($db->connect_error){ die("Connection failed: ".$db->connect_error); }
$res = $db->query("SELECT DISTINCT user_type FROM staff");
while($r = $res->fetch_assoc()) {
    echo "USER_TYPE: " . ($r['user_type'] ?: '[EMPTY]') . "\n";
}
