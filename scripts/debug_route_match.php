<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

Illuminate\Support\Facades\Facade::setFacadeApplication($app);
$app->make(Illuminate\Contracts\Http\Kernel::class);

function debugMatch($uri) {
    $request = Illuminate\Http\Request::create($uri, 'GET');
    $routes = Illuminate\Support\Facades\Route::getRoutes();
    try {
        $route = $routes->match($request);
        echo "Matched route: " . $route->getName() . "\n";
        echo "URI: " . $route->uri() . "\n";
        echo "Parameters: " . json_encode($route->parameters()) . "\n";
    } catch (Exception $e) {
        echo "Match failed: " . get_class($e) . ": " . $e->getMessage() . "\n";
    }
}

debugMatch('/filament/assets/app.css?id=051ae0c568738c610482ba758aff7ae4');
debugMatch('/livewire/message/filament.core.auth.login');

echo "Done\n";