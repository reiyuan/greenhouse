<?php
// training dataset (could also be stored in DB)
// features = [temp, humidity, soil, light]
// labels = actuators [heater, fan, pump, light_act]
$training_data = [
    [ "features" => [20, 60, 30, 200], "label" => [1,0,1,0] ], // cold & dry → heater + pump
    [ "features" => [30, 80, 70, 900], "label" => [0,1,0,0] ], // hot & humid → fan
    [ "features" => [25, 50, 20, 100], "label" => [0,0,1,1] ], // low soil moisture & light → pump + light
    [ "features" => [27, 55, 60, 700], "label" => [0,0,0,0] ], // normal conditions → all off
];

function euclidean_distance($a, $b) {
    $sum = 0;
    for ($i=0; $i<count($a); $i++) {
        $sum += pow($a[$i] - $b[$i], 2);
    }
    return sqrt($sum);
}

function knn_decision($temp, $hum, $soil, $light, $k=3) {
    global $training_data;

    $distances = [];
    foreach ($training_data as $sample) {
        $dist = euclidean_distance([$temp, $hum, $soil, $light], $sample['features']);
        $distances[] = ["dist" => $dist, "label" => $sample['label']];
    }

    // sort by distance
    usort($distances, function($a, $b){ return $a['dist'] <=> $b['dist']; });

    // take k nearest
    $votes = [0,0,0,0]; // heater, fan, pump, light
    for ($i=0; $i<$k && $i<count($distances); $i++) {
        $label = $distances[$i]['label'];
        for ($j=0; $j<4; $j++) {
            $votes[$j] += $label[$j];
        }
    }

    // majority vote
    $decision = [];
    $decision['heater']   = $votes[0] >= ceil($k/2) ? 1 : 0;
    $decision['fan']      = $votes[1] >= ceil($k/2) ? 1 : 0;
    $decision['pump']     = $votes[2] >= ceil($k/2) ? 1 : 0;
    $decision['light_act']= $votes[3] >= ceil($k/2) ? 1 : 0;

    return $decision;
}
