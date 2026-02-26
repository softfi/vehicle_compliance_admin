<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
$query = "SELECT driver_name, amount, remark FROM adjust_salary WHERE LOWER(remark) NOT LIKE '%bonus%'";
$res = mysqli_query($db, $query);
while($row = mysqli_fetch_assoc($res)) {
    echo $row['driver_name'] . " | " . $row['amount'] . " | " . $row['remark'] . "\n";
}
?>
