<?php
$db = new mysqli('localhost','root','','transport');
if($db->connect_error){ die("Connection failed: ".$db->connect_error); }
$res = $db->query("SELECT * FROM ledger");
if ($res) {
    if ($res->num_rows > 0) {
        while($r = $res->fetch_assoc()) {
            echo "Ledger: " . $r['ledger_id'] . " | Name: " . $r['ledger_name'] . " | Group: " . $r['group_id'] . "\n";
        }
    } else {
        echo "Ledger table is empty.\n";
    }
} else {
    echo "Query failed.\n";
}
