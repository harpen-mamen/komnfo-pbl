<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=pbl', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Tables:\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) echo " - $t\n";
    echo "\nMigrations table contents:\n";
    $stmt = $pdo->query("SELECT * FROM migrations ORDER BY id");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) echo " - {$r['migration']} (batch {$r['batch']})\n";
} catch (Throwable $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
}
