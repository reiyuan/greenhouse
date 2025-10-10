<?php
require_once __DIR__ . '/../config.php';

$stmt = $pdo->query("SELECT * FROM sensor_readings ORDER BY id DESC LIMIT 20");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(array_reverse($data)); // send in chronological order
