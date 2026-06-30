<?php
$conn = mysqli_connect('localhost', 'root', '', 'transport_demo');
$sql = "SELECT DISTINCT user_type FROM staff";
$res = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($res)) {
    echo "User Type: " . $row['user_type'] . "\n";
}
?>
