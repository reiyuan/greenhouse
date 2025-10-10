<?php
/**
 * PostgreSQL Database Configuration for Render.com
 * -------------------------------------------------
 * Make sure to replace the placeholders below with
 * your actual Render PostgreSQL credentials.
 */

// Example Render PostgreSQL connection string:
// postgres://<username>:<password>@<host>:5432/<database>

$host = "singapore-postgres.render.com";      // e.g. dpg-cj12345abcd12345.a.render.com
$port = "5432";
$dbname = "postgresql_greenhouse";      // e.g. greenhouse_db
$user = "postgresql_greenhouse_user";        // e.g. greenhouse_user
$pass = "FijLlTawgYqe3kfoxvCmCvBI5wvJRbvB";    // from Render dashboard

try {
    // ✅ Use PostgreSQL PDO driver
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);

    // Optional: force UTF-8 encoding
    $pdo->exec("SET NAMES 'utf8'");

    // Set error handling to exception mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ✅ Test connection
    // echo "✅ Connected to PostgreSQL successfully.";
} catch (PDOException $e) {
    die("❌ PostgreSQL connection failed: " . $e->getMessage());
}
?>
