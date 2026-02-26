<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
$staff_id = 28;
$first = "2025-10-01";
$last = "2025-10-31";

echo "--- Driver Assignments for Staff #$staff_id ---\n";
$q1 = "SELECT * FROM driver_assignment WHERE driver = '$staff_id'";
$res1 = mysqli_query($db, $q1);
while ($row = mysqli_fetch_assoc($res1)) {
    echo "ID: {$row['id']} | Vehicle ID: {$row['vehicle_no']} | From: {$row['from_date']} | To: {$row['to_date']}\n";
}

echo "\n--- Diesel Entries for Oct 2025 (All) --\n";
$q2 = "SELECT * FROM diselentry WHERE diesel_date BETWEEN '$first' AND '$last' AND deleted_by IS NULL ORDER BY diesel_date";
$res2 = mysqli_query($db, $q2);
while ($row = mysqli_fetch_assoc($res2)) {
    echo "ID: {$row['diselentry_id']} | Vehicle ID: {$row['vehicle_id']} | Date: {$row['diesel_date']} | Qty: {$row['qty']}\n";
}
?>
