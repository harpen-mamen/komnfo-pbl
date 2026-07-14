<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$config = $app->make('config');

echo 'FILAMENT_CORE_PATH=' . $config->get('filament.core_path') . PHP_EOL;
echo 'FILAMENT_DOMAIN=' . var_export($config->get('filament.domain'), true) . PHP_EOL;
echo 'FILAMENT_PATH=' . $config->get('filament.path') . PHP_EOL;
echo 'FILAMENT_MIDDLEWARE_BASE=' . json_encode($config->get('filament.middleware.base')) . PHP_EOL;
echo 'APP_URL=' . $config->get('app.url') . PHP_EOL;
