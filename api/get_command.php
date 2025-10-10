<?php
require_once __DIR__ . '/../config.php';

// check for recent manual command (last 5 minutes)
$stmt = $pdo->prepare("SELECT * FROM commands WHERE source='manual' ORDER BY id DESC LIMIT 1");
$stmt->execute();
$manual = $stmt->fetch();

$use_manual = false;
if ($manual) {
    $manual_time = strtotime($manual['created_at']);
    $now = time();
    if (($now - $manual_time) <= 300) { // 300 sec = 5 min
        $use_manual = true;
    }
}

if ($use_manual) {
    $cmd = $manual;
} else {
    // fall back to latest command (KNN or manual older than 5min)
    $cmd = $pdo->query("SELECT * FROM commands ORDER BY id DESC LIMIT 1")->fetch();
}

if ($cmd) {
    echo json_encode([
        "heater"    => (int)$cmd['heater'],
        "fan"       => (int)$cmd['fan'],
        "pump"      => (int)$cmd['pump'],
        "light_act" => (int)$cmd['light_act'],
        "source"    => $cmd['source'],
        "created_at"=> $cmd['created_at']
    ]);
} else {
    echo json_encode([
        "heater" => 0,
        "fan" => 0,
        "pump" => 0,
        "light_act" => 0,
        "source" => "none",
        "created_at" => null
    ]);
}
