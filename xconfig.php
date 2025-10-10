<?php
// config.php
// Edit DB credentials
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$dbname = getenv('DB_NAME');
$port = getenv('DB_PORT');

$conn = new mysqli($host, $user, $pass, $dbname, $port);
// $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed', 'msg' => $e->getMessage()]);
    exit;
}

// Basic helper: read JSON body
function get_json_body() {
    $json = file_get_contents('php://input');
    return json_decode($json, true) ?? [];
}

// Euclidean distance
function euclidean_distance($a, $b) {
    $sum = 0.0;
    foreach ($a as $k => $v) {
        $diff = ($v - $b[$k]);
        $sum += $diff * $diff;
    }
    return sqrt($sum);
}
