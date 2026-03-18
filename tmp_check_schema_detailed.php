<?php
$db = new mysqli('localhost','root','','transport');
if($db->connect_error){ die("Connection failed: ".$db->connect_error); }

function checkTable($db, $table) {
    echo "\nTable: $table\n";
    $res = $db->query("DESCRIBE `$table` ");
    if ($res) {
        while($r = $res->fetch_assoc()) {
            echo "| " . $r['Field'] . " | " . $r['Type'] . " | " . $r['Null'] . " | " . $r['Key'] . " |\n";
        }
    } else {
        echo "| SELECT failed.\n";
    }
}

checkTable($db, 'ledger');
checkTable($db, 'account_voucher_entries');
checkTable($db, 'vendor');
checkTable($db, 'staff');
checkTable($db, 'vehicle');
checkTable($db, 'bank');
