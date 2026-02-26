<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
$staff_id = 28;
$first = "2025-10-01";
$last = "2025-10-31";

echo "--- Driver Assignments for Staff #28 ---\n";
$q1 = "SELECT * FROM driver_assignment WHERE driver = '$staff_id'";
$res1 = mysqli_query($db, $q1);
while ($row = mysqli_fetch_assoc($res1)) {
    echo "ID: {$row['id']} | Vehicle ID: {$row['vehicle_no']} | From: {$row['from_date']} | To: {$row['to_date']}\n";
}

echo "\n--- Diesel Entries for Vehicles Assigned to #28 in Oct 2025 ---\n";
// Joining like the query I put in Admin.php
$q3 = "SELECT de.*, da.id as assignment_id FROM diselentry de 
       JOIN driver_assignment da ON da.vehicle_no = de.vehicle_id
       WHERE da.driver = '$staff_id' 
       AND de.diesel_date BETWEEN '$first' AND '$last'
       AND de.diesel_date >= da.from_date 
       AND (de.diesel_date <= da.to_date OR da.to_date IS NULL OR da.to_date = '0000-00-00')
       AND de.deleted_by IS NULL";
$res3 = mysqli_query($db, $q3);
while ($row = mysqli_fetch_assoc($res3)) {
    echo "Date: {$row['diesel_date']} | Vehicle: {$row['vehicle_id']} | Qty: {$row['qty']} | AssignID: {$row['assignment_id']}\n";
}
?>
