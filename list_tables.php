<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (!$db) die("Conn fail");

echo "--- ALL TABLES ---\n";
$res = mysqli_query($db, "SHOW TABLES");
while($row = mysqli_fetch_row($res)) {
    echo $row[0] . "\n";
}
?>
