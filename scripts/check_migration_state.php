<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=pbl', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Connected to database: ", $pdo->query('SELECT DATABASE()')->fetchColumn(), "\n\n";
    echo "Tables present:\n";
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() ORDER BY table_name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo " - $table\n";
    }
    echo "\nMigrations table entries:\n";
    $stmt = $pdo->query('SELECT migration, batch FROM migrations ORDER BY id');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo " - {$row['migration']} (batch {$row['batch']})\n";
    }
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
