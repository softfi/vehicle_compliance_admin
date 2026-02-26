<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}
$res = mysqli_query($db, 'DESCRIBE despatch');
while ($row = mysqli_fetch_row($res)) {
    echo $row[0].' - '.$row[1]."\n";
}
?>
