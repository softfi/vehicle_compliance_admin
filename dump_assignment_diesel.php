<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

echo "--- driver_assignment columns ---\n";
$res1 = mysqli_query($db, 'DESCRIBE driver_assignment');
while ($row = mysqli_fetch_row($res1)) {
    echo $row[0].' - '.$row[1]."\n";
}

echo "\n--- diselentry columns ---\n";
$res2 = mysqli_query($db, 'DESCRIBE diselentry');
while ($row = mysqli_fetch_row($res2)) {
    echo $row[0].' - '.$row[1]."\n";
}
?>
