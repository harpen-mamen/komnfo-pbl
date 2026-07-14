<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=pbl', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo 'CONNECTED\n';
    $stmt = $pdo->query('SELECT DATABASE()');
    echo 'DATABASE: '.$stmt->fetchColumn()."\n";
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo 'TABLES: '.count($tables)."\n";
    foreach (array_slice($tables, 0, 10) as $table) {
        echo ' - '.$table."\n";
    }
} catch (Throwable $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
}
