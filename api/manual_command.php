<?php
require_once __DIR__ . '/../config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "No input"]);
    exit;
}

$heater = !empty($data['heater']) ? 1 : 0;
$fan = !empty($data['fan']) ? 1 : 0;
$pump = !empty($data['pump']) ? 1 : 0;
$light_act = !empty($data['light_act']) ? 1 : 0;

$stmt = $pdo->prepare("INSERT INTO commands (heater, fan, pump, light_act, source) VALUES (?,?,?,?,?)");
$stmt->execute([$heater, $fan, $pump, $light_act, 'manual']);

echo json_encode(["status" => "ok"]);
