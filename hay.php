<?php
// one-time script to create admin
require_once 'config.php';
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)")->execute(['admin', $hash]);
echo "Admin created: username=admin, password=admin123";
?>
