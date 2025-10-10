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
    $training = [
        // temp, humidity, soil, light, heater, fan, pump, light_act
        [20, 80, 60, 1000, 1, 0, 0, 1], // cool & humid — heater on, light on
        [35, 40, 40, 2000, 0, 1, 1, 0], // hot & dry — fan and pump on
        [28, 60, 70, 1500, 0, 0, 0, 0], // moderate
        [18, 90, 80, 800, 1, 0, 0, 1],  // cold and humid — heater and light
        [40, 30, 20, 2500, 0, 1, 1, 0], // very hot & dry
        [25, 50, 40, 300, 0, 0, 1, 1],  // low light — pump + light on
        [30, 70, 60, 1000, 0, 1, 0, 1], // hot + humid
        [22, 60, 30, 400, 1, 0, 1, 1],  // cool + dry
        [27, 55, 50, 1200, 0, 0, 0, 1], // balanced
        [33, 45, 45, 1800, 0, 1, 1, 0]  // slightly hot
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
