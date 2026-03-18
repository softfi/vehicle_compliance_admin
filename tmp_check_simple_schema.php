<?php
$db = new mysqli('localhost','root','','transport');
if($db->connect_error){ die("Connection failed: ".$db->connect_error); }

function checkTable($db, $table) {
    echo "\nTable: $table\n";
    $res = $db->query("DESCRIBE `$table` ");
    if ($res) {
        $fields = [];
        while($r = $res->fetch_assoc()) {
            $fields[] = $r['Field'] . "(" . $r['Type'] . ")";
        }
        echo "| " . implode(' | ', $fields) . " |\n";
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
echo "\nGroups:\n";
$res = $db->query('SELECT * FROM `group`');
while($r = $res->fetch_assoc()) {
    echo $r['group_id'] . ":" . $r['group_name'] . " ";
}
echo "\n";
