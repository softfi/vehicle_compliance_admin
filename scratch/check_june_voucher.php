<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport');
$r = $mysqli->query("SELECT av.id, av.voucher_date, ave.narration FROM account_vouchers av JOIN account_voucher_entries ave ON ave.voucher_id=av.id WHERE av.id=11 AND ave.group_id IN (4,5) LIMIT 1");
$row = $r->fetch_assoc();
print_r($row);
if (preg_match('/Adv ID: (\d+)/', $row['narration'] ?? '', $m)) {
    $advId = (int) $m[1];
    $r2 = $mysqli->query("SELECT id, adv_date, location_id, amount FROM staff_advance WHERE id = {$advId}");
    print_r($r2->fetch_assoc());
}
$r3 = $mysqli->query('SELECT id, adv_date, location_id, amount FROM staff_advance WHERE id = 109122');
echo "staff_advance 109122:\n";
print_r($r3->fetch_assoc());
