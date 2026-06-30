<?php
$conn = mysqli_connect('localhost', 'root', '', 'transport_demo');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$res = mysqli_query($conn, 'SHOW COLUMNS FROM despatch'); 
$cols = mysqli_fetch_all($res, MYSQLI_ASSOC);
print_r($cols);
?>
