<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
$res = mysqli_query($db, 'SELECT amount, count(*) FROM staff_advance GROUP BY amount HAVING amount > 0');
while ($row = mysqli_fetch_row($res)) {
    echo "Amount: ".$row[0]." Count: ".$row[1]."\n";
}
?>
