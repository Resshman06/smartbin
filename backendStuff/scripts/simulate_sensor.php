<?php
// Demo helper: php scripts/simulate_sensor.php <SENSOR_KEY> [sensor_id] [distance_cm] [times]
// Sends fake readings to the local API so you can test the alert + route logic.
[$s, $key, $sensor, $dist, $times] = $argv + [null, null, 'SENSOR-01-1', 10, 3];
if (!$key) exit("Usage: php scripts/simulate_sensor.php <SENSOR_KEY> [sensor_id] [distance_cm] [times]\n");
for ($i = 0; $i < (int)$times; $i++) {
    $ch = curl_init('http://localhost:8000/api/v1/readings');
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', "X-API-Key: $key"],
        CURLOPT_POSTFIELDS => json_encode(['sensor_id' => $sensor, 'distance_cm' => (float)$dist, 'battery_percent' => 90])]);
    echo curl_exec($ch), "\n";
}
