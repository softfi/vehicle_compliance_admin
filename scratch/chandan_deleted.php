<?php
$m = new mysqli('localhost', 'root', '', 'transport_demo');
$s = 1371; $f = '2026-05-01'; $l = '2026-05-31';
$base = "FROM despatch d JOIN driver_assignment da ON da.vehicle_no=d.vehicle_no AND da.driver=$s
WHERE d.des_date BETWEEN '$f' AND '$l' AND d.des_date>=da.from_date
AND (d.des_date<=da.to_date OR da.to_date IS NULL OR da.to_date='0000-00-00' OR da.to_date='')";
echo 'All despatch rows: ' . $m->query("SELECT COUNT(DISTINCT d.despatch_id) c $base")->fetch_assoc()['c'] . "\n";
echo 'Non-deleted only: ' . $m->query("SELECT COUNT(DISTINCT d.despatch_id) c $base AND d.deleted_at IS NULL AND d.deleted_by IS NULL")->fetch_assoc()['c'] . "\n";
echo 'Deleted only: ' . $m->query("SELECT COUNT(DISTINCT d.despatch_id) c $base AND (d.deleted_at IS NOT NULL OR d.deleted_by IS NOT NULL)")->fetch_assoc()['c'] . "\n";
