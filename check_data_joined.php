<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (!$db) die("Connection failed");

$query = "SELECT dr.do_no, r.from_city, r.to_city, r.location_shortname 
          FROM do_registration dr
          LEFT JOIN route r ON r.id = dr.route_id
          LIMIT 5";
$res = mysqli_query($db, $query);
while ($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
?>
