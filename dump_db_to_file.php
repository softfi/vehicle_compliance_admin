<?php
try {
    $mysqli = new mysqli("localhost", "root", "", "transport");
    if ($mysqli->connect_error) {
        file_put_contents("c:/xampp/htdocs/transport/db_info.txt", "Connect Error: " . $mysqli->connect_error);
        exit;
    }
    
    $output = "VOUCHER TABLE STRUCTURE:\n";
    $result = $mysqli->query("DESCRIBE voucher");
    while ($row = $result->fetch_assoc()) {
        $output .= $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    $output .= "\nRECENT DATA:\n";
    $result = $mysqli->query("SELECT id, group_code, receipt_image FROM voucher ORDER BY id DESC LIMIT 5");
    while ($row = $result->fetch_assoc()) {
        $output .= "ID: " . $row['id'] . ", Code: " . $row['group_code'] . ", Image: " . $row['receipt_image'] . "\n";
    }
    
    $mysqli->close();
    file_put_contents("c:/xampp/htdocs/transport/db_info.txt", $output);
} catch (Exception $e) {
    file_put_contents("c:/xampp/htdocs/transport/db_info.txt", "Error: " . $e->getMessage());
}
?>
