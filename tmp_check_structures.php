<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

echo "--- diselentry table structure ---\n";
$res = mysqli_query($db, 'DESCRIBE diselentry');
while($row = mysqli_fetch_assoc($res)) {
    echo "Field: {$row['Field']} | Type: {$row['Type']} | Null: {$row['Null']} | Key: {$row['Key']} | Default: {$row['Default']}\n";
}

echo "\n--- vehicle table structure (partial) ---\n";
$res = mysqli_query($db, 'DESCRIBE vehicle');
while($row = mysqli_fetch_assoc($res)) {
    echo "Field: {$row['Field']} | Type: {$row['Type']}\n";
}

echo "\n--- driver_assignment table structure (partial) ---\n";
$res = mysqli_query($db, 'DESCRIBE driver_assignment');
while($row = mysqli_fetch_assoc($res)) {
    echo "Field: {$row['Field']} | Type: {$row['Type']}\n";
}
?>
