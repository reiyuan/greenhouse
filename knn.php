<?php
// ======================================================
// KNN Decision Logic - tuned for Philippine conditions
// ======================================================

function knn_decision($temp, $hum, $soil, $light) {
    // ✅ Example dataset — simplified but balanced for PH climate
    $training_data = [
        // temp, humidity, soil_moisture, light_intensity, heater, fan, pump, light_act
        [24, 85, 60, 2500, 1, 0, 0, 0], // cold morning, turn heater on
        [27, 80, 70, 3000, 0, 0, 0, 0], // normal mild
        [30, 75, 60, 3500, 0, 0, 0, 0], // ideal
        [33, 70, 50, 4000, 0, 1, 0, 0], // warm afternoon, fan on
        [36, 65, 45, 5000, 0, 1, 0, 0], // very hot, fan active
        [29, 85, 35, 2500, 0, 0, 1, 0], // soil dry, pump on
        [32, 80, 38, 2800, 0, 0, 1, 0], // soil still dry, pump active
        [28, 88, 65, 800, 0, 0, 0, 1],  // low light, light on
        [31, 60, 55, 1500, 0, 0, 0, 0], // sunny normal
        [26, 90, 70, 1000, 0, 0, 0, 1], // cloudy, light on
    ];

    // K value
    $k = 3;

    // Compute Euclidean distances
    $distances = [];
    foreach ($training_data as $row) {
        $distance = sqrt(
            pow($temp - $row[0], 2) +
            pow($hum - $row[1], 2) +
            pow($soil - $row[2], 2) +
            pow(($light - $row[3]) / 1000, 2) // normalize light effect
        );
        $distances[] = ['distance' => $distance, 'values' => $row];
    }

    // Sort by distance ascending
    usort($distances, fn($a, $b) => $a['distance'] <=> $b['distance']);

    // Pick K nearest neighbors
    $neighbors = array_slice($distances, 0, $k);

    // Majority voting
    $sum = ['heater' => 0, 'fan' => 0, 'pump' => 0, 'light_act' => 0];
    foreach ($neighbors as $n) {
        $v = $n['values'];
        $sum['heater'] += $v[4];
        $sum['fan'] += $v[5];
        $sum['pump'] += $v[6];
        $sum['light_act'] += $v[7];
    }

    // Decision (majority rule)
    $cmd = [
        'heater' => $sum['heater'] >= ($k / 2) ? 1 : 0,
        'fan' => $sum['fan'] >= ($k / 2) ? 1 : 0,
        'pump' => $sum['pump'] >= ($k / 2) ? 1 : 0,
        'light_act' => $sum['light_act'] >= ($k / 2) ? 1 : 0
    ];

    // ✅ Additional manual fine-tuning for realism
    if ($temp < 26) $cmd['heater'] = 1;
    if ($temp > 34) $cmd['fan'] = 1;
    if ($soil < 40) $cmd['pump'] = 1;
    if ($light < 1500) $cmd['light_act'] = 1;

    return $cmd;
}
?>
