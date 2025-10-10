<?php
require_once __DIR__ . '/../config.php';

// Fetch latest 50 readings (oldest → newest)
$stmt = $pdo->query("
    SELECT id, temp, humidity, soil_moisture, light_intensity, created_at
    FROM (
        SELECT * FROM sensor_readings ORDER BY id DESC LIMIT 50
    ) sub
    ORDER BY id ASC
");

$readings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Send as JSON
header('Content-Type: application/json');
echo json_encode($readings);
?>
