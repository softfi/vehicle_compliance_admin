<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport');
if ($mysqli->connect_error) {
    die('connect failed: ' . $mysqli->connect_error);
}

$loc = 21;
$from = '2026-06-01';
$to = '2026-06-09';

echo "=== voucher_date column type ===\n";
$r4 = $mysqli->query('DESCRIBE account_vouchers');
while ($row = $r4->fetch_assoc()) {
    if ($row['Field'] === 'voucher_date') {
        print_r($row);
    }
}

echo "\n=== Payments location 21 amount 700 (all dates) ===\n";
$r = $mysqli->query("
    SELECT av.id, av.voucher_date, av.voucher_type, ave.amount, ave.entry_type
    FROM account_vouchers av
    JOIN account_voucher_entries ave ON ave.voucher_id = av.id
    WHERE ave.group_id = 2 AND ave.ledger_id = {$loc} AND ave.amount = 700
    ORDER BY av.voucher_date DESC
    LIMIT 15
");
while ($row = $r->fetch_assoc()) {
    print_r($row);
}

echo "\n=== CORRECT BETWEEN {$from} AND {$to} ===\n";
$r2 = $mysqli->query("
    SELECT av.id, av.voucher_date, av.voucher_type, ave.amount
    FROM account_vouchers av
    JOIN account_voucher_entries ave ON ave.voucher_id = av.id
    WHERE ave.group_id = 2 AND ave.ledger_id = {$loc}
      AND av.voucher_date BETWEEN '{$from}' AND '{$to}'
      AND av.voucher_type = 'Payment'
");
while ($row = $r2->fetch_assoc()) {
    print_r($row);
}

echo "\n=== WRONG BETWEEN (bug simulation: 21 and 2026-06-01) ===\n";
$r5 = $mysqli->query("
    SELECT av.id, av.voucher_date, av.voucher_type, ave.amount
    FROM account_vouchers av
    JOIN account_voucher_entries ave ON ave.voucher_id = av.id
    WHERE ave.group_id = 2 AND ave.ledger_id = {$loc}
      AND av.voucher_date BETWEEN '21' AND '2026-06-01'
      AND av.voucher_type = 'Payment'
    LIMIT 10
");
while ($row = $r5->fetch_assoc()) {
    print_r($row);
}

echo "\n=== staff_advance location 21 in June range ===\n";
$r6 = $mysqli->query("
    SELECT id, adv_date, amount, bank_cash
    FROM staff_advance
    WHERE location_id = {$loc} AND adv_date BETWEEN '{$from}' AND '{$to}'
    LIMIT 10
");
while ($row = $r6->fetch_assoc()) {
    print_r($row);
}

echo "\n=== entries for voucher 85 ===\n";
$r8 = $mysqli->query('SELECT * FROM account_voucher_entries WHERE voucher_id = 85');
while ($row = $r8->fetch_assoc()) {
    print_r($row);
}

echo "\n=== staff advance vouchers excluded? ids 85,86 ===\n";
$exclude = "
    SELECT DISTINCT av_sa.id
    FROM account_vouchers av_sa
    INNER JOIN account_voucher_entries ave_cash ON ave_cash.voucher_id = av_sa.id
        AND ave_cash.group_id = 2 AND ave_cash.entry_type = 2 AND ave_cash.ledger_id = 21
    INNER JOIN account_voucher_entries ave_party ON ave_party.voucher_id = av_sa.id
        AND ave_party.group_id IN (4, 5) AND ave_party.entry_type = 1
    WHERE av_sa.voucher_type = 'Payment' AND av_sa.id IN (85,86)
";
$r9 = $mysqli->query($exclude);
while ($row = $r9->fetch_assoc()) {
    print_r($row);
}

echo "\n=== staff_advance location 21 May 6 ===\n";
$r7 = $mysqli->query("
    SELECT id, adv_date, amount, bank_cash
    FROM staff_advance
    WHERE location_id = {$loc} AND adv_date = '2026-05-06'
    LIMIT 10
");
while ($row = $r7->fetch_assoc()) {
    print_r($row);
}
