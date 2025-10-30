<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../config.php';

// --- Use Philippine Standard Time ---
date_default_timezone_set('Asia/Manila');

// --- Read JSON from ESP32 ---
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// --- Backward-compatibility (form POST fallback) ---
if (!$data && !empty($_POST)) {
    $data = $_POST;
}

// --- Validate data ---
if (
    !isset($data['temp']) ||
    !isset($data['humidity']) ||
    !isset($data['soil_moisture']) ||
    !isset($data['light_intensity'])
) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid data structure"]);
    exit;
}

$temp = floatval($data['temp']);
$humidity = floatval($data['humidity']);
$soil = floatval($data['soil_moisture']);
$light = floatval($data['light_intensity']);

// --- Determine timestamp ---
$timestamp = isset($data['timestamp']) && !empty($data['timestamp'])
    ? date('Y-m-d H:i:s', strtotime($data['timestamp']))
    : date('Y-m-d H:i:s'); // default to current Philippine time

// --- Insert into database ---
try {
    $stmt = $pdo->prepare("
        INSERT INTO sensor_readings (temp, humidity, soil_moisture, light_intensity, created_at)
        VALUES (:temp, :humidity, :soil, :light, :created_at)
    ");
    $stmt->execute([
        ':temp' => $temp,
        ':humidity' => $humidity,
        ':soil' => $soil,
        ':light' => $light,
        ':created_at' => $timestamp
    ]);

    echo json_encode([
        "status" => "ok",
        "message" => "Reading saved",
        "timestamp" => $timestamp
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
