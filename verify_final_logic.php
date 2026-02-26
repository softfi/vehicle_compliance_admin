<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
$staff_id = 28;
$first = "2025-10-01";
$last = "2025-10-31";

echo "--- VOLUNTEER ASSIGNMENTS FOR STAFF #28 (Oct 2025) ---\n";
$q_ass = "SELECT * FROM driver_assignment 
          WHERE driver = '$staff_id' 
          AND from_date <= '$last' 
          AND (to_date >= '$first' OR to_date IS NULL OR to_date = '0000-00-00')
          ORDER BY from_date ASC";
$res_ass = mysqli_query($db, $q_ass);
$assignments = [];
while ($row = mysqli_fetch_assoc($res_ass)) {
    $assignments[] = $row;
    echo "AssID: {$row['id']} | Vehicle: {$row['vehicle_no']} | From: {$row['from_date']} | To: {$row['to_date']}\n";
}

echo "\n--- SYSTEM-CALCULATED DIESEL ENTRIES (Admin.php Logic) ---\n";
// The actual query from Admin.php
$q_diesel = "SELECT de.*, da.id as assignment_id FROM diselentry de 
             JOIN driver_assignment da ON da.vehicle_no = de.vehicle_id
             WHERE da.driver = '$staff_id' 
             AND de.diesel_date >= '$first' 
             AND de.diesel_date <= '$last'
             AND de.diesel_date >= da.from_date 
             AND (de.diesel_date <= da.to_date OR da.to_date IS NULL OR da.to_date = '0000-00-00' OR da.to_date = '')
             AND de.deleted_by IS NULL
             GROUP BY de.diselentry_id
             ORDER BY de.diesel_date ASC";

$res_diesel = mysqli_query($db, $q_diesel);
$shown_count = 0;
while ($row = mysqli_fetch_assoc($res_diesel)) {
    $shown_count++;
    echo "Date: {$row['diesel_date']} | Vehicle: {$row['vehicle_id']} | Qty: {$row['qty']} | Matched Assignment: {$row['assignment_id']}\n";
}

echo "\nTOTAL ENTRIES SHOWN: $shown_count\n";

echo "\n--- CHECKING FOR POTENTIAL 'LEAKED' DATA (Oct 2025 Diesel for OTHER vehicles) ---\n";
// Let's see if there are diesel entries for OTHER vehicles in Oct 2025 that we might have missed or accidentally included
$q_leak = "SELECT de.diselentry_id, de.vehicle_id, de.diesel_date FROM diselentry de 
           WHERE de.diesel_date BETWEEN '$first' AND '$last'
           AND de.vehicle_id NOT IN (SELECT vehicle_no FROM driver_assignment WHERE driver = '$staff_id' AND from_date <= '$last' AND (to_date >= '$first' OR to_date IS NULL))
           AND deleted_by IS NULL
           LIMIT 5";
$res_leak = mysqli_query($db, $q_leak);
if (mysqli_num_rows($res_leak) > 0) {
    echo "Found diesel entries for other vehicles (Correctly ignored):\n";
    while ($row = mysqli_fetch_assoc($res_leak)) {
        echo "Date: {$row['diesel_date']} | Vehicle: {$row['vehicle_id']} (Was NOT assigned to #28)\n";
    }
} else {
    echo "No other diesel entries found in Oct 2025 besides those assigned.\n";
}
?>
