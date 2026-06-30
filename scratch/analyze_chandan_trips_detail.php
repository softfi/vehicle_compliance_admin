<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport_demo');
$staff_id = 1371; // CHANDAN YADAV - has May 2026 data
$first = '2026-05-01'; $last = '2026-05-31';

echo "=== CHANDAN YADAV (id $staff_id) May 2026 trip analysis ===\n\n";

$sql = "SELECT d.despatch_id, DATE(d.des_date) AS trip_date, d.do_no, dr.do_no AS do_reg_no, v.vehicle_no
FROM despatch d
JOIN driver_assignment da ON da.vehicle_no = d.vehicle_no AND da.driver = $staff_id
LEFT JOIN do_registration dr ON dr.do_registration_id = d.do_no
LEFT JOIN vehicle v ON v.id = d.vehicle_no
WHERE d.des_date >= '$first' AND d.des_date <= '$last'
AND d.des_date >= da.from_date
AND (d.des_date <= da.to_date OR da.to_date IS NULL OR da.to_date = '0000-00-00' OR da.to_date = '')
GROUP BY d.despatch_id
ORDER BY trip_date";

$r = $mysqli->query($sql);
$all = [];
while ($row = $r->fetch_assoc()) $all[] = $row;

echo "Salary slip Total Trips (despatch rows): " . count($all) . "\n";

$byDate = [];
foreach ($all as $t) {
    $byDate[$t['trip_date']][] = $t;
}
echo "Unique working days with any despatch: " . count($byDate) . "\n\n";

echo "--- Per day breakdown (days with multiple challans) ---\n";
$multiDays = 0;
$extraChallans = 0;
foreach ($byDate as $date => $trips) {
    $c = count($trips);
    if ($c > 1) {
        $multiDays++;
        $extraChallans += ($c - 1);
        $dos = array_count_values(array_column($trips, 'do_reg_no'));
        echo "$date: $c despatch entries | DOs: " . json_encode($dos) . "\n";
    }
}
echo "\nDays with multiple entries: $multiDays\n";
echo "Extra challans above 1-per-day: $extraChallans\n";
echo "If user counts 1 trip per day: ~" . count($byDate) . " vs slip " . count($all) . "\n";

// Unique date + DO combinations
$dateDo = [];
foreach ($all as $t) {
    $dateDo[$t['trip_date'] . '|' . $t['do_no']] = true;
}
echo "Unique (date + DO) combinations: " . count($dateDo) . "\n";

// tripexpence1 style - group by date only
echo "Unique dates (tripexpence1 groups by date): " . count($byDate) . "\n";

// Check deleted
$r = $mysqli->query("SELECT COUNT(*) c FROM despatch d
JOIN driver_assignment da ON da.vehicle_no = d.vehicle_no AND da.driver = $staff_id
WHERE d.des_date BETWEEN '$first' AND '$last'
AND (d.deleted_at IS NOT NULL OR d.deleted_by IS NOT NULL)");
echo "Deleted despatch rows still in vehicle+driver range: " . $r->fetch_assoc()['c'] . "\n";

// Other drivers on same vehicle May 4
echo "\n--- May 4 on OD16K2248: all despatch vs Chandan assignment ---\n";
$r = $mysqli->query("SELECT COUNT(*) c FROM despatch WHERE vehicle_no=41 AND des_date='2026-05-04'");
echo "Total despatch on vehicle 41 May 4: " . $r->fetch_assoc()['c'] . "\n";
$r = $mysqli->query("SELECT COUNT(DISTINCT d.despatch_id) c FROM despatch d
JOIN driver_assignment da ON da.vehicle_no=d.vehicle_no AND da.driver=$staff_id
WHERE d.vehicle_no=41 AND d.des_date='2026-05-04'
AND d.des_date >= da.from_date AND d.des_date <= da.to_date");
echo "Counted for Chandan May 4: " . $r->fetch_assoc()['c'] . "\n";

// Other assignments on vehicle 41 in May
echo "\n--- All driver assignments on vehicle 41 in May ---\n";
$r = $mysqli->query("SELECT da.id, da.driver, s.name, da.from_date, da.to_date FROM driver_assignment da
JOIN staff s ON s.id=da.driver
WHERE da.vehicle_no=41 AND da.from_date <= '$last' AND (da.to_date IS NULL OR da.to_date='0000-00-00' OR da.to_date >= '$first')
ORDER BY da.from_date");
while ($row = $r->fetch_assoc()) echo json_encode($row) . "\n";
