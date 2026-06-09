<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport');
$location_id = 21;
$from_date = '2026-06-01';
$to_date = '2026-06-09';

$excludeStaffAdvanceVoucherSql = "
    SELECT DISTINCT av_sa.id
    FROM account_vouchers av_sa
    INNER JOIN account_voucher_entries ave_cash ON ave_cash.voucher_id = av_sa.id
        AND ave_cash.group_id = 2 AND ave_cash.entry_type = 2 AND ave_cash.ledger_id = {$location_id}
    INNER JOIN account_voucher_entries ave_party ON ave_party.voucher_id = av_sa.id
        AND ave_party.group_id IN (4, 5) AND ave_party.entry_type = 1
    WHERE av_sa.voucher_type = 'Payment'
";

$sql = "
    SELECT av.voucher_date as date, av.voucher_type as source,
           SUM(CASE WHEN ave.entry_type = 1 THEN ave.amount ELSE 0 END) as debit,
           SUM(CASE WHEN ave.entry_type = 2 THEN ave.amount ELSE 0 END) as credit
    FROM account_vouchers av
    JOIN account_voucher_entries ave ON ave.voucher_id = av.id
    WHERE ave.group_id = 2
      AND ave.ledger_id = ?
      AND av.voucher_date >= ?
      AND av.voucher_date <= ?
      AND av.id NOT IN ({$excludeStaffAdvanceVoucherSql})
    GROUP BY av.id, av.voucher_date, av.voucher_type
    ORDER BY av.voucher_date ASC
    LIMIT 5
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('iss', $location_id, $from_date, $to_date);
$stmt->execute();
$result = $stmt->get_result();

echo "First 5 rows (June filter):\n";
while ($row = $result->fetch_assoc()) {
    echo date('d-m-Y', strtotime($row['date'])) . " | {$row['source']} | debit={$row['debit']} | credit={$row['credit']}\n";
}

$countSql = "
    SELECT COUNT(*) as c FROM (
        SELECT av.id
        FROM account_vouchers av
        JOIN account_voucher_entries ave ON ave.voucher_id = av.id
        WHERE ave.group_id = 2 AND ave.ledger_id = ?
          AND av.voucher_date >= ? AND av.voucher_date <= ?
          AND av.id NOT IN ({$excludeStaffAdvanceVoucherSql})
        GROUP BY av.id
    ) t
";
$stmt2 = $mysqli->prepare($countSql);
$stmt2->bind_param('iss', $location_id, $from_date, $to_date);
$stmt2->execute();
$count = $stmt2->get_result()->fetch_assoc()['c'];
echo "\nTotal voucher rows in June range: {$count}\n";

$maySql = "
    SELECT av.id, av.voucher_date
    FROM account_vouchers av
    JOIN account_voucher_entries ave ON ave.voucher_id = av.id
    WHERE ave.group_id = 2 AND ave.ledger_id = ?
      AND av.voucher_date >= ? AND av.voucher_date <= ?
      AND av.id NOT IN ({$excludeStaffAdvanceVoucherSql})
      AND av.voucher_date < '2026-06-01'
    LIMIT 5
";
$stmt3 = $mysqli->prepare($maySql);
$stmt3->bind_param('iss', $location_id, $from_date, $to_date);
$stmt3->execute();
$result3 = $stmt3->get_result();
echo "\nMay rows incorrectly in June filter (should be 0):\n";
while ($row = $result3->fetch_assoc()) {
    print_r($row);
}

$r4 = $mysqli->query("
    SELECT COUNT(*) AS c FROM account_vouchers av
    JOIN account_voucher_entries ave ON ave.voucher_id = av.id
    WHERE ave.group_id = 2 AND ave.ledger_id = {$location_id}
      AND av.voucher_date BETWEEN '{$from_date}' AND '{$to_date}'
");
echo "\nTotal cash vouchers June (before exclude): " . $r4->fetch_assoc()['c'] . "\n";

$r5 = $mysqli->query("
    SELECT COUNT(*) AS c FROM staff_advance
    WHERE location_id = {$location_id} AND adv_date BETWEEN '{$from_date}' AND '{$to_date}'
");
echo "staff_advance June rows: " . $r5->fetch_assoc()['c'] . "\n";

$r6 = $mysqli->query("
    SELECT COUNT(*) AS c FROM staff_advance
    WHERE location_id = {$location_id} AND adv_date BETWEEN '2026-05-01' AND '2026-05-31'
");
echo "staff_advance May rows: " . $r6->fetch_assoc()['c'] . "\n";
