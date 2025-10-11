<?php
/**
 * KNN Decision Algorithm for IoT Greenhouse
 *
 * Uses a simple KNN-based logic to automatically decide actuator states
 * (heater, fan, pump, light) based on temperature, humidity, soil moisture, and light intensity.
 */

function knn_decision($temp, $humidity, $soil, $light)
{
    // -------------------------------------------
    // Define training dataset (example patterns)
    // -------------------------------------------
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

    $k = 3; // number of nearest neighbors to consider

    // -------------------------------------------
    // Compute distance from current reading to each training example
    // -------------------------------------------
    $distances = [];
    foreach ($training as $sample) {
        $d = sqrt(
            pow($temp - $sample[0], 2) +
            pow($humidity - $sample[1], 2) +
            pow($soil - $sample[2], 2) +
            pow($light - $sample[3], 2)
        );
        $distances[] = ['distance' => $d, 'heater' => $sample[4], 'fan' => $sample[5], 'pump' => $sample[6], 'light_act' => $sample[7]];
    }

    // Sort by ascending distance
    usort($distances, function($a, $b) {
        return $a['distance'] <=> $b['distance'];
    });

    // -------------------------------------------
    // Select top K neighbors
    // -------------------------------------------
    $nearest = array_slice($distances, 0, $k);

    // -------------------------------------------
    // Compute majority decision
    // -------------------------------------------
    $sum = ['heater' => 0, 'fan' => 0, 'pump' => 0, 'light_act' => 0];
    foreach ($nearest as $n) {
        $sum['heater'] += $n['heater'];
        $sum['fan'] += $n['fan'];
        $sum['pump'] += $n['pump'];
        $sum['light_act'] += $n['light_act'];
    }

    // Round decision (majority)
    $decision = [
        'heater' => $sum['heater'] >= ceil($k / 2) ? 1 : 0,
        'fan' => $sum['fan'] >= ceil($k / 2) ? 1 : 0,
        'pump' => $sum['pump'] >= ceil($k / 2) ? 1 : 0,
        'light_act' => $sum['light_act'] >= ceil($k / 2) ? 1 : 0
    ];

    // -------------------------------------------
    // Optional fine-tuning thresholds (override)
    // -------------------------------------------
    // Heater logic
    if ($temp < 22) $decision['heater'] = 1;
    if ($temp > 28) $decision['heater'] = 0;

    // Fan logic
    if ($temp > 30 || $humidity > 75) $decision['fan'] = 1;
    if ($temp < 25 && $humidity < 60) $decision['fan'] = 0;

    // Pump logic
    if ($soil < 40) $decision['pump'] = 1;
    if ($soil > 60) $decision['pump'] = 0;

    // Light logic
    if ($light < 800) $decision['light_act'] = 1;
    if ($light > 1800) $decision['light_act'] = 0;

    // -------------------------------------------
    // Return final decision
    // -------------------------------------------
    return $decision;
}
?>


