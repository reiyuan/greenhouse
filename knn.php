<?php
require_once __DIR__ . '/config.php';

// === CONFIG ===
define('K', 3); // Number of neighbors

// === TRAINING DATA ===
// Each sample represents: [temperature, humidity, soil_moisture, light_intensity]
// Label format: ['heater', 'fan', 'pump', 'light_act']
$training_data = [
    // 🌤 Typical hot daytime (Philippines)
    ["features" => [34, 60, 40, 30000], "label" => [0, 1, 0, 0]], // Fan ON (too hot)
    ["features" => [33, 55, 50, 20000], "label" => [0, 1, 0, 0]], // Slightly hot
    ["features" => [30, 70, 60, 10000], "label" => [0, 0, 0, 1]], // Moderate → Light ON

    // 🌧 Cool & Humid (Rainy)
    ["features" => [25, 90, 80, 5000], "label" => [1, 0, 0, 1]], // Heater + Light ON
    ["features" => [26, 85, 75, 3000], "label" => [1, 0, 0, 1]], // Dim light, cold

    // 🌞 Dry & Hot
    ["features" => [35, 50, 20, 40000], "label" => [0, 1, 1, 0]], // Fan + Pump ON
    ["features" => [32, 45, 30, 35000], "label" => [0, 1, 1, 0]], // Hot & Dry

    // 🌅 Evening (cooler)
    ["features" => [28, 65, 55, 2000], "label" => [0, 0, 0, 1]], // Light ON
    ["features" => [27, 60, 70, 1000], "label" => [0, 0, 0, 1]], // Dim + Moist

    // 🌙 Night (Cold)
    ["features" => [24, 80, 60, 500], "label" => [1, 0, 0, 1]], // Heater + Light
    ["features" => [23, 85, 65, 300], "label" => [1, 0, 0, 1]], // Night mode

    // 🌱 Dry soil, any temp
    ["features" => [30, 60, 25, 10000], "label" => [0, 0, 1, 0]], // Pump ON
    ["features" => [31, 55, 20, 12000], "label" => [0, 0, 1, 0]], // Very dry soil

    // 🪴 Wet soil, bright light
    ["features" => [29, 65, 80, 25000], "label" => [0, 0, 0, 0]], // Ideal → All OFF
];

// === READ LATEST SENSOR READING ===
$stmt = $pdo->query("SELECT temp, humidity, soil_moisture, light_intensity FROM sensor_readings ORDER BY id DESC LIMIT 1");
$current = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$current) {
    echo json_encode(["error" => "No sensor data found"]);
    exit;
}

$current_features = [
    floatval($current['temp']),
    floatval($current['humidity']),
    floatval($current['soil_moisture']),
    floatval($current['light_intensity'])
];

// === COMPUTE DISTANCES ===
$distances = [];
foreach ($training_data as $sample) {
    $dist = euclidean_distance($sample['features'], $current_features);
    $distances[] = ['distance' => $dist, 'label' => $sample['label']];
}

// Sort by distance
usort($distances, function ($a, $b) {
    return $a['distance'] <=> $b['distance'];
});

// Take top K neighbors
$neighbors = array_slice($distances, 0, K);

// === MAJORITY VOTE ===
$votes = [0, 0, 0, 0];
foreach ($neighbors as $n) {
    for ($i = 0; $i < 4; $i++) {
        $votes[$i] += $n['label'][$i];
    }
}

// Convert to binary (if 2 out of 3 say ON → turn ON)
$decision = array_map(function($v) {
    return $v >= ceil(K / 2) ? 1 : 0;
}, $votes);

// === SAVE COMMAND ===
$stmt = $pdo->prepare("INSERT INTO commands (heater, fan, pump, light_act, source, created_at) VALUES (?, ?, ?, ?, 'auto', NOW())");
$stmt->execute([$decision[0], $decision[1], $decision[2], $decision[3]]);

echo json_encode([
    "status" => "ok",
    "decision" => [
        "heater" => $decision[0],
        "fan" => $decision[1],
        "pump" => $decision[2],
        "light_act" => $decision[3]
    ],
    "current_reading" => $current
]);

// === FUNCTIONS ===
function euclidean_distance($a, $b) {
    $sum = 0;
    for ($i = 0; $i < count($a); $i++) {
        $sum += pow($a[$i] - $b[$i], 2);
    }
    return sqrt($sum);
}
?>
