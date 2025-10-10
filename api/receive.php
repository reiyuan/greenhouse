<?php
// api/receive.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

$body = get_json_body();

// Expect temp, humidity, soil_moisture, light_intensity
$temp = isset($body['temp']) ? floatval($body['temp']) : null;
$humidity = isset($body['humidity']) ? floatval($body['humidity']) : null;
$soil = isset($body['soil_moisture']) ? floatval($body['soil_moisture']) : null;
$light = isset($body['light_intensity']) ? floatval($body['light_intensity']) : null;

// Validate
if ($temp === null || $humidity === null || $soil === null || $light === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing sensor fields. Expect temp, humidity, soil_moisture, light_intensity.']);
    exit;
}

// Insert reading
$stmt = $pdo->prepare("INSERT INTO sensor_readings (temp, humidity, soil_moisture, light_intensity) VALUES (?, ?, ?, ?)");
$stmt->execute([$temp, $humidity, $soil, $light]);

// KNN prediction
$k = 3; // adjust as desired
// Read training set
$rows = $pdo->query("SELECT temp, humidity, soil_moisture, light_intensity, heater, fan, pump, light_act FROM training_data")->fetchAll();

$features_input = [
    'temp' => $temp,
    'humidity' => $humidity,
    'soil_moisture' => $soil,
    'light_intensity' => $light
];

$distances = [];
foreach ($rows as $r) {
    $features_row = [
        'temp' => floatval($r['temp']),
        'humidity' => floatval($r['humidity']),
        'soil_moisture' => floatval($r['soil_moisture']),
        'light_intensity' => floatval($r['light_intensity'])
    ];
    $d = euclidean_distance($features_input, $features_row);
    $distances[] = ['dist'=>$d, 'row'=>$r];
}

usort($distances, function($a,$b){ return $a['dist'] <=> $b['dist']; });

$neighbors = array_slice($distances, 0, max(1, $k));

// Tally votes for each actuator
$vote = ['heater'=>0,'fan'=>0,'pump'=>0,'light_act'=>0];
foreach ($neighbors as $n) {
    $r = $n['row'];
    $vote['heater'] += intval($r['heater']);
    $vote['fan'] += intval($r['fan']);
    $vote['pump'] += intval($r['pump']);
    $vote['light_act'] += intval($r['light_act']);
}

// Decide: majority voting (>= half of k) -> on, else off
$command = [];
foreach ($vote as $act => $count) {
    $command[$act] = ($count >= ceil($k/2)) ? 1 : 0;
}

// Insert command into DB
$ins = $pdo->prepare("INSERT INTO commands (heater, fan, pump, light_act, source) VALUES (?, ?, ?, ?, 'auto')");
$ins->execute([$command['heater'],$command['fan'],$command['pump'],$command['light_act']]);

// Return JSON for ESP32
echo json_encode([
    'status' => 'ok',
    'command' => $command,
    'neighbors' => array_map(function($n){
        return ['dist'=>floatval($n['dist'])];
    }, $neighbors)
]);
