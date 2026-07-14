<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

Illuminate\Support\Facades\Facade::setFacadeApplication($app);

/** @var \Illuminate\Contracts\Console\Kernel $consoleKernel */
$consoleKernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$consoleKernel->bootstrap();

$route = Illuminate\Support\Facades\Route::getRoutes()->getByName('filament.asset');
if (! $route) {
    echo "route not found\n";
    exit(1);
}

echo 'name=' . $route->getName() . PHP_EOL;
echo 'uri=' . $route->uri() . PHP_EOL;
echo 'domain=' . var_export($route->domain(), true) . PHP_EOL;
echo 'methods=' . implode(',', $route->methods()) . PHP_EOL;
echo 'prefix=' . var_export($route->getPrefix(), true) . PHP_EOL;
echo 'action=' . json_encode($route->getAction()) . PHP_EOL;

echo 'config filament.domain=' . var_export($app->make('config')->get('filament.domain'), true) . PHP_EOL;
echo 'config filament.core_path=' . var_export($app->make('config')->get('filament.core_path'), true) . PHP_EOL;
echo 'config filament.path=' . var_export($app->make('config')->get('filament.path'), true) . PHP_EOL;

echo 'route group prefixes: ' . PHP_EOL;
foreach ($route->gatherMiddleware() as $middleware) {
    echo '  middleware: ' . $middleware . PHP_EOL;
}
