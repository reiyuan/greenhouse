<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM commands ORDER BY id DESC LIMIT 1");
    $cmd = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cmd) {
        echo json_encode($cmd);
    } else {
        echo json_encode(["error" => "No commands found"]);
    }
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
