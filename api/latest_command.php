<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM commands ORDER BY id DESC LIMIT 1");
    $cmd = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cmd) {
        echo json_encode([
            "id" => (int)$cmd['id'],
            "heater" => (int)$cmd['heater'],
            "fan" => (int)$cmd['fan'],
            "pump" => (int)$cmd['pump'],
            "light_act" => (int)$cmd['light_act'],
            "source" => $cmd['source'],
            "created_at" => $cmd['created_at']
        ]);
    } else {
        echo json_encode(["error" => "No commands found"]);
    }
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
