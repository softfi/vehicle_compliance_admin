<?php
try {
    $dsn = 'mysql:host=localhost;dbname=transport;charset=utf8mb4';
    $username = 'root';
    $password = '';
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $vehicle_no = 'OD16J2202';
    $target_date = '2026-02-19';
    
    // Get vehicle ID
    $stmt = $pdo->prepare("SELECT id FROM vehicle WHERE vehicle_no = ?");
    $stmt->execute([$vehicle_no]);
    $vehicle = $stmt->fetch();

    if (!$vehicle) {
        echo "Vehicle $vehicle_no not found\n";
        exit;
    }

    $vehicle_id = $vehicle['id'];
    echo "Vehicle ID: " . $vehicle_id . "\n";

    // Get assignments active on target date
    $stmt = $pdo->prepare("
        SELECT da.*, s.name as driver_name 
        FROM driver_assignment da 
        LEFT JOIN staff s ON s.id = da.driver 
        WHERE da.vehicle_no = ? 
        AND da.from_date <= ? 
        AND (da.to_date >= ? OR da.to_date IS NULL)
        ORDER BY da.from_date DESC, da.id DESC
    ");
    $stmt->execute([$vehicle_id, $target_date, $target_date]);
    $assignments = $stmt->fetchAll();

    echo "Assignments active on $target_date for vehicle $vehicle_id:\n";
    foreach ($assignments as $asgn) {
        echo "ID: {$asgn['id']}, Driver: {$asgn['driver']} ({$asgn['driver_name']}), From: {$asgn['from_date']}, To: " . ($asgn['to_date'] ?? 'NULL') . "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
