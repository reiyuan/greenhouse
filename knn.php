<?php
// ======================================================
// KNN DECISION LOGIC — tuned for Philippine room environment
// ======================================================

// K nearest neighbors to consider
$K = 3;

// Indoor environment training dataset
$training_data = [
    // ☀ Warm, bright day near window
    ["features" => [27, 45, 50, 1500], "label" => [0, 1, 0, 0]], // Fan ON (warm) temp, humidity, soil, lux ---- heater, fan, pump, led
    ["features" => [25, 50, 55, 1000], "label" => [0, 0, 0, 0]], // Comfortable
    ["features" => [24, 55, 60, 700],  "label" => [0, 0, 0, 0]], // Ideal condition

    // 🌙 Cool evening / night
    ["features" => [18.5, 55, 60, 80],  "label" => [1, 0, 0, 1]], // Heater + Light ON
    ["features" => [19.5, 60, 70, 40],  "label" => [1, 0, 0, 1]], // Cold + dark

    // 🌱 Dry soil daytime
    ["features" => [23, 40, 22, 900],  "label" => [0, 0, 1, 0]], // Pump ON (dry)
    ["features" => [26, 38, 25, 1200], "label" => [0, 1, 1, 0]], // Hot & dry → Fan + Pump

    // 💧 Humid + warm (ventilation)
    ["features" => [28, 65, 50, 800],  "label" => [0, 1, 0, 0]], // Fan ON

    // 🌑 Dim but warm → Light ON for plants
    ["features" => [22, 48, 55, 120],  "label" => [0, 0, 0, 1]], // Light ON (dark)
    ["features" => [23, 45, 65, 300],  "label" => [0, 0, 0, 0]], // Dim but acceptable

    // 🌞 Bright but cool
    ["features" => [21, 40, 50, 2500], "label" => [0, 0, 0, 0]], // All OFF

    // 🧱 Hot & dry edge case
    ["features" => [28.5, 35, 18, 1400], "label" => [0, 1, 1, 0]], // Fan + Pump

    // 🌧 Night, wet soil
    ["features" => [20, 70, 78, 60],   "label" => [0, 0, 0, 1]], // Light ON only
];

// Normalize a data point for comparison
function normalize($point) {
    // Adjust normalization range to fit typical indoor (Philippine) conditions
    $max = [ 'temp' => 40, 'humidity' => 100, 'soil' => 100, 'light' => 3000 ];
    return [
        $point[0] / $max['temp'],
        $point[1] / $max['humidity'],
        $point[2] / $max['soil'],
        $point[3] / $max['light']
    ];
}

// Compute Euclidean distance between normalized feature vectors
function euclidean_distance($a, $b) {
    $sum = 0;
    for ($i = 0; $i < count($a); $i++) {
        $sum += pow($a[$i] - $b[$i], 2);
    }
    return sqrt($sum);
}

// KNN decision function
function knn_decision($temp, $humidity, $soil, $light) {
    global $training_data, $K;

    // Normalize input
    $input = normalize([$temp, $humidity, $soil, $light]);

    // Calculate distances
    $distances = [];
    foreach ($training_data as $data) {
        $train = normalize($data["features"]);
        $dist = euclidean_distance($input, $train);
        $distances[] = ["distance" => $dist, "label" => $data["label"]];
    }

    // Sort by distance
    usort($distances, fn($a, $b) => $a["distance"] <=> $b["distance"]);

    // Take K nearest neighbors
    $neighbors = array_slice($distances, 0, $K);

    // Majority vote for each actuator
    $votes = ["heater" => 0, "fan" => 0, "pump" => 0, "light_act" => 0];
    foreach ($neighbors as $n) {
        $votes["heater"]    += $n["label"][0];
        $votes["fan"]       += $n["label"][1];
        $votes["pump"]      += $n["label"][2];
        $votes["light_act"] += $n["label"][3];
    }

    // Final decision (majority rule)
    return [
        "heater"    => ($votes["heater"] > ($K / 2)) ? 1 : 0,
        "fan"       => ($votes["fan"] > ($K / 2)) ? 1 : 0,
        "pump"      => ($votes["pump"] > ($K / 2)) ? 1 : 0,
        "light_act" => ($votes["light_act"] > ($K / 2)) ? 1 : 0,
    ];
}

// Example test (uncomment for CLI testing)
// print_r(knn_decision(26, 50, 45, 600));
?>

