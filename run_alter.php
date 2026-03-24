<?php
$db = mysqli_connect('localhost', 'root', '', 'transport');
if (!$db) die("Conn fail");

mysqli_query($db, "ALTER TABLE stock ADD COLUMN bill_photo VARCHAR(255) DEFAULT NULL;");
mysqli_query($db, "ALTER TABLE stock ADD COLUMN remarks TEXT DEFAULT NULL;");
echo "Added bill_photo and remarks to stock.\n";
?>
