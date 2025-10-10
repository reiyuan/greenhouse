<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../knn.php';
$config = require __DIR__ . '/../config.php';

// read ESP32 JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { http_response_code(400); echo json_encode(['error'=>'Invalid JSON']); exit; }

$device_id = $input['device_id'] ?? 'esp32';
$temperature = floatval($input['temperature'] ?? 0);
$humidity = floatval($input['humidity'] ?? 0);
$soil_moisture = floatval($input['soil_moisture'] ?? 0);
$light_level = floatval($input['light_level'] ?? 0);

// store reading
$stmt = $pdo->prepare("INSERT INTO readings (device_id, temperature, humidity, soil_moisture, light_level) VALUES (?,?,?,?,?)");
$stmt->execute([$device_id,$temperature,$humidity,$soil_moisture,$light_level]);

// KNN classify
$samples=[];
foreach($pdo->query("SELECT temperature,humidity,soil_moisture,light_level,label FROM knn_samples") as $r){
    $samples[]=['features'=>[(float)$r['temperature'],(float)$r['humidity'],(float)$r['soil_moisture'],(float)$r['light_level']], 'label'=>$r['label']];
}
$k=intval($config['knn_k']??3);
$prediction=knn_classify([$temperature,$humidity,$soil_moisture,$light_level],$samples,$k);

// current actuator state
$stmt=$pdo->prepare("SELECT pump_state, fan_state FROM actuators WHERE device_id=?");
$stmt->execute([$device_id]);
$act=$stmt->fetch() ?: ['pump_state'=>0,'fan_state'=>0];

// === AUTO RULES BY PREDICTION (existing simple mapping) ===
$rstmt=$pdo->prepare("SELECT pump_state,fan_state,auto_enabled FROM actuation_rules WHERE label=? LIMIT 1");
$rstmt->execute([$prediction]);
$rule=$rstmt->fetch();
if($rule && intval($rule['auto_enabled'])===1){
    if($rule['pump_state']!==null) $act['pump_state']=intval($rule['pump_state']);
    if($rule['fan_state']!==null)  $act['fan_state']=intval($rule['fan_state']);
}

// === CONDITIONAL RULES ===
$q=$pdo->query("SELECT * FROM conditional_rules WHERE auto_enabled=1");
$conditions_applied=[];
foreach($q as $cr){
    $labelFilter=$cr['label'];
    if($labelFilter && $labelFilter!==$prediction) continue; // skip if label mismatch

    // prepare environment variables
    $env=[
        'temperature'=>$temperature,
        'humidity'=>$humidity,
        'soil_moisture'=>$soil_moisture,
        'light_level'=>$light_level
    ];

    // evaluate condition safely
    $cond = $cr['condition_text'];
    $safe = preg_match('/^[\w\s<>=!&|().+-]+$/', $cond); // simple safety check
    if(!$safe) continue;

    // build eval string
    extract($env);
    $result=false;
    try {
        eval('$result = ('.$cond.');');
    } catch(Throwable $e) { $result=false; }

    if($result){
        if($cr['pump_state']!==null) $act['pump_state']=intval($cr['pump_state']);
        if($cr['fan_state']!==null)  $act['fan_state']=intval($cr['fan_state']);
        $conditions_applied[]=[
            'id'=>$cr['id'],
            'label'=>$cr['label'],
            'condition'=>$cr['condition_text']
        ];
    }
}

// update actuators table
$up=$pdo->prepare("
INSERT INTO actuators (device_id,pump_state,fan_state)
VALUES (?,?,?)
ON DUPLICATE KEY UPDATE pump_state=VALUES(pump_state), fan_state=VALUES(fan_state)
");
$up->execute([$device_id,$act['pump_state'],$act['fan_state']]);

echo json_encode([
    'status'=>'ok',
    'prediction'=>$prediction,
    'features'=>compact('temperature','humidity','soil_moisture','light_level'),
    'actuators'=>$act,
    'conditions_applied'=>$conditions_applied,
    'timestamp'=>date('c')
]);
