<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
$staff_id = 28;
$first = "2025-10-01";
$last = "2025-10-31";

echo "--- Despatch Records for Driver #$staff_id in Oct 2025 ---\n";
// Joining with driver_assignment like the controller does
$q = "SELECT d.despatch_id, d.des_date, d.vehicle_no FROM despatch d 
      JOIN driver_assignment da ON da.vehicle_no = d.vehicle_no
      WHERE da.driver = $staff_id AND d.des_date >= '$first' AND d.des_date <= '$last'
      AND d.des_date >= da.from_date AND (d.des_date <= da.to_date OR da.to_date IS NULL)";
$res = mysqli_query($db, $q);
while ($row = mysqli_fetch_assoc($res)) {
    echo "ID: {$row['despatch_id']} | Date: {$row['des_date']} | Vehicle: {$row['vehicle_no']}\n";
}
?>
