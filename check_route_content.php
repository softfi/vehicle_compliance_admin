<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
$res = mysqli_query($db, 'SELECT * FROM route LIMIT 10');
while ($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
?>
