<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `pbl` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "CREATED\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
