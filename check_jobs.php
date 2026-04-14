<?php
require_once 'system/bootstrap.php';

// Create database connection
$db = \Config\Database::connect();

// Get all job IDs from job_assign table
$builder = $db->table('job_assign');
$builder->select('*');
$builder->orderBy('job_id', 'ASC');
$jobs = $builder->get()->getResult();

echo "=== ALL JOB IDs IN SYSTEM ===\n";
foreach($jobs as $job) {
    echo $job->job_id . " => " . $job->job_name . "\n";
}

echo "\n=== USER ROLES ===\n";
// Get sample users and their roles
$userBuilder = $db->table('user');
$userBuilder->select('id, full_name, user_name, roles, user_type');
$userBuilder->limit(5);
$users = $userBuilder->get()->getResult();

foreach($users as $user) {
    echo "User: " . $user->full_name . " | Roles: " . $user->roles . "\n";
}
?>
