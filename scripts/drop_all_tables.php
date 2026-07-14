<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=pbl', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $stmt = $pdo->query("SELECT CONCAT('`', table_name, '`') AS table_name FROM information_schema.tables WHERE table_schema = DATABASE()");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS $table");
        echo "Dropped $table\n";
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    echo "Completed drop all tables.\n";
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
