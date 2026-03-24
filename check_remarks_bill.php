<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (!$db) die("Conn fail");

echo "--- diselentry ---\n";
$res = mysqli_query($db, "DESCRIBE diselentry");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n--- extra_diesel_issue ---\n";
$res = mysqli_query($db, "DESCRIBE extra_diesel_issue");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\n--- passenger_vehicle_diesel ---\n";
$res = mysqli_query($db, "DESCRIBE passenger_vehicle_diesel");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
