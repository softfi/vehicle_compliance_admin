<?php
try {
    $dsn = 'mysql:host=localhost;dbname=transport;charset=utf8mb4';
    $username = 'root';
    $password = '';
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $vehicle_id = 2; // OD16J2202
    $target_date = '2026-02-19';

    // Simulated query with new ordering
    $stmt = $pdo->prepare("
        SELECT da.*, s.name as driver_name 
        FROM driver_assignment da 
        LEFT JOIN staff s ON s.id = da.driver 
        WHERE da.vehicle_no = ? 
        ORDER BY da.from_date DESC, da.id DESC
    ");
    $stmt->execute([$vehicle_id]);
    $assignments = $stmt->fetchAll();

    echo "--- Assignments in Sorted Order (Latest First) ---\n";
    foreach ($assignments as $asgn) {
        echo "ID: {$asgn['id']}, Driver: {$asgn['driver_name']} ({$asgn['driver']}), From: {$asgn['from_date']}, To: " . ($asgn['to_date'] ?? 'NULL') . "\n";
    }

    echo "\n--- Simulating Selection for $target_date ---\n";
    $active_asgn = null;
    foreach ($assignments as $asgn) {
        if ($target_date >= $asgn['from_date'] && ($asgn['to_date'] === null || $target_date <= $asgn['to_date'])) {
            $active_asgn = $asgn;
            break; 
        }
    }

    if ($active_asgn) {
        echo "RESULT: Selected [{$active_asgn['driver_name']}] via Assignment ID [{$active_asgn['id']}]\n";
    } else {
        echo "RESULT: No driver assigned.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
