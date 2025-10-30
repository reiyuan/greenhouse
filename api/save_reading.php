<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

// Get raw JSON input
$input = file_get_contents("php://input");
file_put_contents(__DIR__ . '/../debug_input.log', date('Y-m-d H:i:s') . " | " . $input . "\n", FILE_APPEND);

$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "No input", "raw" => $input]);
    exit;
}

$temp = $data['temp'] ?? null;
$hum = $data['humidity'] ?? null;
$soil = $data['soil_moisture'] ?? null;
$light = $data['light_intensity'] ?? null;

if ($temp === null || $hum === null || $soil === null || $light === null) {
    echo json_encode(["error" => "Incomplete data", "data" => $data]);
    exit;
}

// Save sensor reading
$stmt = $pdo->prepare("INSERT INTO sensor_readings (temp, humidity, soil_moisture, light_intensity) VALUES (?,?,?,?)");
$stmt->execute([$temp, $hum, $soil, $light]);

// Run KNN decision
require_once __DIR__ . '/../knn.php';
$cmd = knn_decision($temp, $hum, $soil, $light);

// Save command
$stmt = $pdo->prepare("INSERT INTO commands (heater, fan, pump, light_act, source) VALUES (?,?,?,?,?)");
$stmt->execute([$cmd['heater'], $cmd['fan'], $cmd['pump'], $cmd['light_act'], "knn"]);

// Respond to ESP32
echo json_encode(["status" => "ok", "cmd" => $cmd]);
?>
