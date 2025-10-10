<?php
require_once __DIR__ . '/../config.php';

// Fetch the 50 most recent sensor readings
$stmt = $pdo->query("SELECT * FROM sensor_readings ORDER BY id DESC LIMIT 50");
$readings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return as JSON
header('Content-Type: application/json');
echo json_encode($readings);
?>
