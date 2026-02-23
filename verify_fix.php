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

    echo "Simulating selection for $target_date:\n";
    $active_asgn = null;
    foreach ($assignments as $asgn) {
        if ($target_date >= $asgn['from_date'] && ($asgn['to_date'] === null || $target_date <= $asgn['to_date'])) {
            $active_asgn = $asgn;
            break; // Found the latest one!
        }
    }

    if ($active_asgn) {
        echo "Selected Driver: {$active_asgn['driver_name']} (ID: {$active_asgn['driver']})\n";
        echo "Assignment ID: {$active_asgn['id']}, From: {$active_asgn['from_date']}, To: " . ($active_asgn['to_date'] ?? 'NULL') . "\n";
    } else {
        echo "No driver assigned on this date.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
