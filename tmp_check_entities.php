<?php
$db = new mysqli('localhost','root','','transport');
if($db->connect_error){ die("Connection failed: ".$db->connect_error); }

function checkTable($db, $table) {
    echo "Table: $table\n";
    $res = $db->query("SELECT * FROM `$table` LIMIT 5");
    if ($res) {
        while($r = $res->fetch_row()) {
            echo "| " . implode(' | ', $r) . "\n";
        }
    } else {
        echo "| ERROR or EMPTY\n";
    }
}

checkTable($db, 'vendor');
checkTable($db, 'staff');
checkTable($db, 'vehicle');
checkTable($db, 'bank');
