<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

$staff_id = 28;
$year = 2025;
$month = 10;
$first = "2025-10-01";
$last = "2025-10-31";

echo "--- Driver Assignments for Staff #$staff_id in Oct 2025 ---\n";
$q1 = "SELECT da.*, v.vehicle_no FROM driver_assignment da 
       JOIN vehicle v ON v.id = da.vehicle_no
       WHERE da.driver = $staff_id AND da.from_date <= '$last' AND (da.to_date >= '$first' OR da.to_date IS NULL)";
$res1 = mysqli_query($db, $q1);
while ($row = mysqli_fetch_assoc($res1)) {
    echo "Vehicle: {$row['vehicle_no']} (ID: {$row['vehicle_no']}) | From: {$row['from_date']} | To: {$row['to_date']}\n";
}

echo "\n--- Diesel Entries for these assignments ---\n";
$q2 = "SELECT de.*, v.vehicle_no, ven.name as pump FROM diselentry de 
       JOIN vehicle v ON v.id = de.vehicle_id
       LEFT JOIN vendor ven ON ven.id = de.vendor_id
       JOIN driver_assignment da ON da.vehicle_no = de.vehicle_id
       WHERE da.driver = $staff_id 
       AND de.diesel_date >= '$first' AND de.diesel_date <= '$last'
       AND de.diesel_date >= da.from_date AND (de.diesel_date <= da.to_date OR da.to_date IS NULL)
       AND de.deleted_by IS NULL";
$res2 = mysqli_query($db, $q2);
while ($row = mysqli_fetch_assoc($res2)) {
    echo "Date: {$row['diesel_date']} | Vehicle: {$row['vehicle_no']} | Qty: {$row['qty']} | Pump: {$row['pump']}\n";
}
?>
