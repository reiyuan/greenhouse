<?php
// FIX: Prevent BOM / accidental output
ob_start();

// Philippine Time
date_default_timezone_set("Asia/Manila");

// PostgreSQL configuration
$host = "singapore-postgres.render.com";
$port = "5432";
$dbname = "postgresql_greenhouse2";
$user = "postgresql_greenhouse2_user";
$pass = "1K37jHHcjCKIFxPZb3XAMHY1yfViDq1s";

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $pdo->exec("SET NAMES 'utf8'");

} catch (PDOException $e) {
    die("PostgreSQL connection failed: " . $e->getMessage());
}

// FIX: No closing tag!
