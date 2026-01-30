<?php
try {
    $mysqli = new mysqli("localhost", "root", "", "transport");
    if ($mysqli->connect_error) {
        die("Connect Error: " . $mysqli->connect_error);
    }
    
    $mysqli->query("ALTER TABLE voucher MODIFY COLUMN receipt_image TEXT");
    echo "Column receipt_image modified to TEXT.\n";
    
    $mysqli->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
