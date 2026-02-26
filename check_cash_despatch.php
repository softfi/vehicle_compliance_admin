<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
$res = mysqli_query($db, 'SELECT cash, count(*) FROM despatch GROUP BY cash HAVING cash > 0');
while ($row = mysqli_fetch_row($res)) {
    echo "Cash: ".$row[0]." Count: ".$row[1]."\n";
}
?>
