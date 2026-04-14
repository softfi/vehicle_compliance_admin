<?php
// Simple database check and cleanup script
$host = 'localhost';
$db = 'transport';
$user = 'root';
$pass = '';

try {
    $conn = new mysqli($host, $user, $pass, $db);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Check if job_assign table exists
    $result = $conn->query("SHOW TABLES LIKE 'job_assign'");
    
    if ($result->num_rows > 0) {
        echo "✓ job_assign table EXISTS\n";
        echo "\nCurrent job_assign entries:\n";
        $rows = $conn->query("SELECT * FROM job_assign ORDER BY job_id");
        if ($rows->num_rows > 0) {
            while ($row = $rows->fetch_assoc()) {
                echo "  Job ID: " . $row['job_id'] . " => " . $row['job_name'] . "\n";
            }
        }
        
        // Check how many users have these roles
        echo "\n\nUsers with roles:\n";
        $userRows = $conn->query("SELECT id, full_name, roles FROM user WHERE roles IS NOT NULL AND roles != '' LIMIT 5");
        while ($user = $userRows->fetch_assoc()) {
            echo "  " . $user['full_name'] . " => Roles: " . $user['roles'] . "\n";
        }
        
        echo "\n\n=== DELETING job_assign TABLE ===\n";
        if ($conn->query("DROP TABLE job_assign")) {
            echo "✓ job_assign table DELETED successfully\n";
            echo "\nNote: User roles are still stored in user.roles column\n";
            echo "The sidebar checks will still work based on user.roles\n";
        } else {
            echo "✗ Error deleting table: " . $conn->error . "\n";
        }
    } else {
        echo "✗ job_assign table does NOT exist\n";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
