<?php

$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '';
$dbName = 'pbl';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "Connected to MySQL server at {$host}:{$port}\n";
    $pdo->exec("DROP DATABASE IF EXISTS `{$dbName}`;");
    echo "Dropped database {$dbName}\n";
    $pdo->exec("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Created database {$dbName}\n";
    echo "DONE\n";
} catch (PDOException $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
