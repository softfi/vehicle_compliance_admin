<?php
// Check and fix admin Attendance permissions

$host = 'localhost';
$db = 'transport';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check current admin (id=1) roles
$result = $conn->query("SELECT id, full_name, roles FROM user WHERE id = 1");
$admin = $result->fetch_assoc();

echo "Current Admin (ID=1):\n";
echo "Name: " . $admin['full_name'] . "\n";
echo "Roles: " . $admin['roles'] . "\n\n";

// Check if admin has attendance permissions (45,46,47,48)
$roles = explode(',', $admin['roles']);
$has_attendance = false;

foreach ([45, 46, 47, 48] as $job_id) {
    if (in_array($job_id, $roles)) {
        echo "✓ Has permission: $job_id\n";
        $has_attendance = true;
    } else {
        echo "✗ Missing permission: $job_id\n";
    }
}

echo "\n";

// If admin doesn't have attendance permissions, add them
if (!$has_attendance) {
    echo "Adding Attendance permissions to admin...\n";
    
    // Add attendance permissions
    $new_roles = $admin['roles'] . ',45,46,47,48';
    
    $update = $conn->prepare("UPDATE user SET roles = ? WHERE id = 1");
    $update->bind_param("s", $new_roles);
    
    if ($update->execute()) {
        echo "✓ Successfully added Attendance permissions!\n";
        echo "New roles: " . $new_roles . "\n";
    } else {
        echo "✗ Error updating: " . $conn->error . "\n";
    }
} else {
    echo "✓ Admin already has all Attendance permissions!\n";
}

$conn->close();
?>
