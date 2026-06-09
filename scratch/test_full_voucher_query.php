<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport');
$location_id = 21;
$from_date = '2026-05-01';
$to_date = '2026-05-10';

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
    WHERE ave.group_id = 2 AND ave.ledger_id = ?
      AND av.voucher_date >= ? AND av.voucher_date <= ?
      AND av.id NOT IN ({$excludeStaffAdvanceVoucherSql})
    GROUP BY av.id, av.voucher_date, av.voucher_type
    ORDER BY av.voucher_date
    LIMIT 5
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('iss', $location_id, $from_date, $to_date);
if (! $stmt->execute()) {
    die($stmt->error);
}
$r = $stmt->get_result();
echo "May filter Payment vouchers (non-staff-advance, first 5):\n";
while ($row = $r->fetch_assoc()) {
    echo date('d-m-Y', strtotime($row['date'])) . " {$row['source']} debit={$row['debit']}\n";
}

$sql2 = str_replace('>= ?', ">= '2026-06-01'", $sql);
$sql2 = str_replace('<= ?', "<= '2026-06-09'", $sql2);
$sql2 = preg_replace('/bind.*/', '', $sql2);
$r2 = $mysqli->query("
    SELECT COUNT(*) c FROM account_vouchers av
    JOIN account_voucher_entries ave ON ave.voucher_id = av.id
    WHERE ave.group_id = 2 AND ave.ledger_id = 21
      AND av.voucher_date >= '2026-06-01' AND av.voucher_date <= '2026-06-09'
      AND av.voucher_date = '2026-05-06'
");
echo 'May 6 vouchers in June filter: ' . $r2->fetch_assoc()['c'] . "\n";
