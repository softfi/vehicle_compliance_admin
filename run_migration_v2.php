<?php
$log = "";
try {
    $mysqli = new mysqli("localhost", "root", "", "transport");
    if ($mysqli->connect_error) {
        $log .= "Connect Error: " . $mysqli->connect_error . "\n";
    } else {
        $log .= "Connected successfully.\n";
        if ($mysqli->query("ALTER TABLE voucher MODIFY receipt_image TEXT")) {
            $log .= "ALTER SUCCESSFUL.\n";
        } else {
            $log .= "ALTER FAILED: " . $mysqli->error . "\n";
        }
        
        $res = $mysqli->query("DESCRIBE voucher");
        while ($row = $res->fetch_assoc()) {
            if ($row['Field'] == 'receipt_image') {
                $log .= "Current type: " . $row['Type'] . "\n";
            }
        }
        $mysqli->close();
    }
} catch (Exception $e) {
    $log .= "Exception: " . $e->getMessage() . "\n";
}
file_put_contents("c:/xampp/htdocs/transport/migration_log.txt", $log);
echo "Done.";
?>
