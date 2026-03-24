<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
$res = mysqli_query($db, "ALTER TABLE staff_advance ADD COLUMN paid_by VARCHAR(255) NULL AFTER despatch_id");
if ($res) {
    echo "Column added successfully";
} else {
    echo "Error: " . mysqli_error($db);
}
?>
