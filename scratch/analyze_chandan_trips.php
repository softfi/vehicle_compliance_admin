<?php
$mysqli = new mysqli('localhost', 'root', '', 'transport_demo');
if ($mysqli->connect_error) die($mysqli->connect_error);

// Find CHANDAN YADAV
$r = $mysqli->query("SELECT id, name, staff_code FROM staff WHERE name LIKE '%CHANDAN%YADAV%' OR name LIKE '%CHANDAN YADAV%'");
echo "=== STAFF ===\n";
$staffIds = [];
while ($row = $r->fetch_assoc()) {
    echo json_encode($row) . "\n";
    $staffIds[] = (int)$row['id'];
}
if (!$staffIds) {
    $r = $mysqli->query("SELECT id, name, staff_code FROM staff WHERE name LIKE '%CHANDAN%' AND user_type='DRIVER'");
    while ($row = $r->fetch_assoc()) { echo json_encode($row)."\n"; $staffIds[] = (int)$row['id']; }
}

$year = 2026; $month = 5;
$first = '2026-05-01'; $last = '2026-05-31';

foreach ($staffIds as $staff_id) {
    echo "\n========== STAFF ID $staff_id May 2026 ==========\n";

    echo "\n--- Assignments overlapping May ---\n";
    $r = $mysqli->query("SELECT da.id, da.from_date, da.to_date, da.vehicle_no, v.vehicle_no AS reg
        FROM driver_assignment da
        LEFT JOIN vehicle v ON v.id = da.vehicle_no
        WHERE da.driver = $staff_id
        AND da.from_date <= '$last'
        AND (da.to_date IS NULL OR da.to_date = '0000-00-00' OR da.to_date = '' OR da.to_date >= '$first')
        ORDER BY da.from_date");
    $assignments = [];
    while ($row = $r->fetch_assoc()) {
        echo json_encode($row) . "\n";
        $assignments[] = $row;
    }

    // Salary slip query (replica)
    $sqlSlip = "SELECT d.despatch_id, d.des_date, d.vehicle_no, v.vehicle_no AS reg, d.do_no,
        da.id AS asgn_id, da.from_date AS asgn_from, da.to_date AS asgn_to
        FROM despatch d
        LEFT JOIN vehicle v ON v.id = d.vehicle_no
        LEFT JOIN driver_assignment da ON da.vehicle_no = d.vehicle_no AND da.driver = $staff_id
        WHERE d.des_date >= '$first' AND d.des_date <= '$last'
        AND d.des_date >= da.from_date
        AND (d.des_date <= da.to_date OR da.to_date IS NULL OR da.to_date = '0000-00-00' OR da.to_date = '')
        GROUP BY d.despatch_id
        ORDER BY d.des_date";
    $r = $mysqli->query($sqlSlip);
    echo "\n--- Salary slip trips (with GROUP BY) count=" . $r->num_rows . " ---\n";
    while ($row = $r->fetch_assoc()) echo json_encode($row) . "\n";

    // Without groupBy - raw join duplicates
    $sqlRaw = "SELECT d.despatch_id, d.des_date, v.vehicle_no AS reg, da.id AS asgn_id, da.from_date, da.to_date
        FROM despatch d
        LEFT JOIN vehicle v ON v.id = d.vehicle_no
        LEFT JOIN driver_assignment da ON da.vehicle_no = d.vehicle_no AND da.driver = $staff_id
        WHERE d.des_date >= '$first' AND d.des_date <= '$last'
        AND d.des_date >= da.from_date
        AND (d.des_date <= da.to_date OR da.to_date IS NULL OR da.to_date = '0000-00-00' OR da.to_date = '')
        ORDER BY d.des_date, d.despatch_id";
    $r = $mysqli->query($sqlRaw);
    echo "\n--- Raw join rows (before dedupe) count=" . $r->num_rows . " ---\n";

    // Per assignment exact match (grid logic)
    echo "\n--- Grid logic (per assignment exact dates) ---\n";
    $gridTotal = 0;
    foreach ($assignments as $a) {
        $vid = (int)$a['vehicle_no'];
        $fd = $mysqli->real_escape_string($a['from_date']);
        $td = $mysqli->real_escape_string($a['to_date'] ?: '0000-00-00');
        $q = "SELECT COUNT(DISTINCT d.despatch_id) c FROM despatch d
            INNER JOIN driver_assignment da ON da.vehicle_no = d.vehicle_no AND da.driver = $staff_id
            WHERE da.from_date = '$fd' AND da.to_date = " . ($a['to_date'] && $a['to_date'] != '0000-00-00' ? "'$td'" : "da.to_date") . "
            AND d.vehicle_no = $vid
            AND d.des_date >= '$fd' AND d.des_date <= " . ($a['to_date'] && $a['to_date'] != '0000-00-00' ? "'$td'" : "'$last'") . "
            AND d.des_date >= '$first' AND d.des_date <= '$last'";
        // Simpler per assignment overlap
        $asgnEnd = ($a['to_date'] && $a['to_date'] != '0000-00-00' && $a['to_date'] != '') ? $a['to_date'] : $last;
        $asgnStart = max($a['from_date'], $first);
        $asgnEnd = min($asgnEnd, $last);
        $q2 = "SELECT COUNT(DISTINCT d.despatch_id) c FROM despatch d
            WHERE d.vehicle_no = $vid AND d.des_date >= '$asgnStart' AND d.des_date <= '$asgnEnd'";
        $c = (int)$mysqli->query($q2)->fetch_assoc()['c'];
        echo "Assignment {$a['id']} {$a['reg']} {$a['from_date']} to {$a['to_date']}: trips=$c\n";
        $gridTotal += $c;
    }
    echo "Sum per assignment (may double-count same trip if overlap): $gridTotal\n";

    // Open-ended assignments pulling extra vehicle trips
    echo "\n--- Trips on vehicles with OPEN (null) to_date assignment ---\n";
    foreach ($assignments as $a) {
        if ($a['to_date'] && $a['to_date'] != '0000-00-00' && $a['to_date'] != '') continue;
        $vid = (int)$a['vehicle_no'];
        $q = "SELECT COUNT(*) c FROM despatch d WHERE d.vehicle_no=$vid AND d.des_date BETWEEN '$first' AND '$last'";
        $c = $mysqli->query($q)->fetch_assoc()['c'];
        echo "Open assignment {$a['id']} vehicle {$a['reg']}: ALL May trips on vehicle = $c\n";
    }
}
