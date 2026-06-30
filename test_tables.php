<?php
$conn = mysqli_connect('localhost', 'root', '', 'transport_demo');
$res = mysqli_query($conn, 'SHOW TABLES');
print_r(mysqli_fetch_all($res, MYSQLI_NUM));
?>
