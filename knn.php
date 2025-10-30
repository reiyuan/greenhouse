<?php
require_once '../config.php';

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["error" => "No input data"]);
    exit;
}

$temp = isset($data['temp']) ? floatval($data['temp']) : null;
$humidity = isset($data['humidity']) ? floatval($data['humidity']) : null;
$soil = isset($data['soil_moisture']) ? floatval($data['soil_moisture']) : null;
$light = isset($data['light_intensity']) ? floatval($data['light_intensity']) : null;

// Check valid input
if (is_null($temp) || is_null($humidity) || is_null($soil) || is_null($light)) {
    echo json_encode(["error" => "Invalid data"]);
    exit;
}

// --- Fetch last command to preserve current states ---
$lastCmd = $pdo->query("SELECT * FROM commands ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Default to previous states or OFF
$heater = $lastCmd ? $lastCmd['heater'] : 0;
$fan = $lastCmd ? $lastCmd['fan'] : 0;
$pump = $lastCmd ? $lastCmd['pump'] : 0;
$light_act = $lastCmd ? $lastCmd['light_act'] : 0;

// ================== TUNED KNN LOGIC ==================
// Philippine tropical greenhouse thresholds (typical):
// Temperature: 27–35°C
// Humidity: 60–90%
// Soil Moisture: 40–70%
// Light (BH1750 lux): 100–50,000 lux

// --- Heater & Fan Control ---
if ($temp < 27) {           // Too cold
    $heater = 1;
    $fan = 0;
} elseif ($temp > 33) {     // Too hot
    $heater = 0;
    $fan = 1;
} else {                    // Comfortable range
    $heater = 0;
    $fan = 0;
}

// --- Soil Moisture Control ---
if ($soil < 40) {           // Dry
    $pump = 1;
} elseif ($soil > 70) {     // Wet
    $pump = 0;
}

// --- Light Control (BH1750FVI) ---
// Hysteresis prevents flicker
if ($light < 150) {         // Dark (evening / cloudy)
    $light_act = 1;         // Turn ON grow lights
} elseif ($light > 500) {   // Bright enough (daytime)
    $light_act = 0;         // Turn OFF lights
}

// =====================================================

// Save decision to database
$stmt = $pdo->prepare("INSERT INTO commands (heater, fan, pump, light_act, source) VALUES (?, ?, ?, ?, 'auto')");
$stmt->execute([$heater, $fan, $pump, $light_act]);

// Return JSON response for ESP32
$response = [
    "status" => "ok",
    "temp" => $temp,
    "humidity" => $humidity,
    "soil_moisture" => $soil,
    "light_intensity" => $light,
    "heater" => $heater,
    "fan" => $fan,
    "pump" => $pump,
    "light_act" => $light_act,
    "source" => "auto"
];

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
?>
