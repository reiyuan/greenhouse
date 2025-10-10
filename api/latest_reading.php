<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

try {
    // Fetch latest 50 readings (oldest → newest)
    $stmt = $pdo->query("
        SELECT * FROM (
            SELECT id, temp, humidity, soil_moisture, light_intensity, created_at
            FROM sensor_readings
            ORDER BY id DESC
            LIMIT 50
        ) sub
        ORDER BY id ASC
    ");
    $readings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($readings);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
