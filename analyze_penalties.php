<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (!$db) die("Connection failed");

echo "--- Penalties (Adjust Salary without 'bonus' in remark) ---\n";
// Adjust Salary table, filter out bonus
$query = "SELECT driver_id, driver_name, amount, remark, from_date FROM adjust_salary WHERE LOWER(remark) NOT LIKE '%bonus%'";
$res = mysqli_query($db, $query);

if ($res && mysqli_num_rows($res) > 0) {
    echo str_pad("Driver Name", 25) . " | " . str_pad("Amount", 10) . " | " . str_pad("Date", 12) . " | Remark\n";
    echo str_repeat("-", 70) . "\n";
    while ($row = mysqli_fetch_assoc($res)) {
        echo str_pad($row['driver_name'], 25) . " | " . str_pad($row['amount'], 10) . " | " . str_pad($row['from_date'], 12) . " | " . $row['remark'] . "\n";
    }
} else {
    echo "No penalties found in adjust_salary table.\n";
}

echo "\n--- Bonuses (Adjust Salary with 'bonus' in remark) ---\n";
$queryBonus = "SELECT driver_id, driver_name, amount, remark, from_date FROM adjust_salary WHERE LOWER(remark) LIKE '%bonus%'";
$resB = mysqli_query($db, $queryBonus);

if ($resB && mysqli_num_rows($resB) > 0) {
    while ($row = mysqli_fetch_assoc($resB)) {
        echo str_pad($row['driver_name'], 25) . " | " . str_pad($row['amount'], 10) . " | " . str_pad($row['from_date'], 12) . " | " . $row['remark'] . "\n";
    }
} else {
    echo "No bonuses found in adjust_salary table.\n";
}
?>
