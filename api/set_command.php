<?php
// api/set_command.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
$body = get_json_body();

$heater = isset($body['heater']) ? (int)$body['heater'] : 0;
$fan = isset($body['fan']) ? (int)$body['fan'] : 0;
$pump = isset($body['pump']) ? (int)$body['pump'] : 0;
$light_act = isset($body['light_act']) ? (int)$body['light_act'] : 0;

$stmt = $pdo->prepare("INSERT INTO commands (heater, fan, pump, light_act, source) VALUES (?, ?, ?, ?, 'manual')");
$stmt->execute([$heater,$fan,$pump,$light_act]);

echo json_encode(['status'=>'ok','cmd'=>['heater'=>$heater,'fan'=>$fan,'pump'=>$pump,'light_act'=>$light_act]]);
