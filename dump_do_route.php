<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

echo "--- do_registration columns ---\n";
$res1 = mysqli_query($db, 'DESCRIBE do_registration');
while ($row = mysqli_fetch_row($res1)) {
    echo $row[0].' - '.$row[1]."\n";
}

echo "\n--- route columns ---\n";
$res2 = mysqli_query($db, 'DESCRIBE route');
while ($row = mysqli_fetch_row($res2)) {
    echo $row[0].' - '.$row[1]."\n";
}
?>
