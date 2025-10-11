<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM sensor_readings ORDER BY id DESC LIMIT 1");
    $reading = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($reading ?: []);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
